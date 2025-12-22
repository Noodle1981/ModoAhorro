<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Models\Locality;
use App\Services\Climate\ClimateDataService;
use App\Services\Recommendations\ReplacementService;
use Illuminate\Http\Request;

/**
 * Base Controller for Entity Types
 * 
 * Provides shared functionality for Home, Office, and Trade entities.
 * Subclasses should set $entityType and can override methods as needed.
 */
abstract class BaseEntityController extends Controller
{
    /**
     * The entity type this controller handles.
     * Must be set by subclasses: 'hogar', 'oficina', or 'comercio'
     */
    protected string $entityType;

    /**
     * Get the configuration for this entity type.
     */
    protected function getConfig(): array
    {
        return config("entity_types.{$this->entityType}", []);
    }

    /**
     * Get enabled recommendations for this entity type.
     */
    protected function getEnabledRecommendations(): array
    {
        $config = $this->getConfig();
        $recommendations = $config['recommendations'] ?? [];
        
        return array_filter($recommendations, fn($rec) => $rec['enabled'] ?? false);
    }

    /**
     * Display a listing of entities.
     */
    public function index()
    {
        $config = $this->getConfig();
        $user = auth()->user();
        
        $entities = $user->entities()
            ->where('type', $this->entityType)
            ->with(['locality', 'rooms.equipment'])
            ->get()
            ->map(function ($entity) {
                $installedPower = $entity->rooms
                    ->flatMap(fn($room) => $room->equipment)
                    ->sum('power');
                $entity->installed_power = $installedPower;
                $entity->power_per_m2 = $entity->square_meters ? ($installedPower / $entity->square_meters) : 0;
                return $entity;
            });

        return view("entities.{$this->getViewFolder()}.index", compact('entities', 'config'));
    }

    /**
     * Show the form for creating a new entity.
     */
    public function create()
    {
        $this->authorize('create', [Entity::class, $this->entityType]);

        $config = $this->getConfig();
        $localities = Locality::with('province')->get();
        
        return view("entities.{$this->getViewFolder()}.create", compact('localities', 'config'));
    }

    /**
     * Store a newly created entity.
     */
    public function store(Request $request)
    {
        $this->authorize('create', [Entity::class, $this->entityType]);

        $config = $this->getConfig();
        
        $rules = [
            'name' => 'required|string|max:255',
            'address_street' => 'required|string|max:255',
            'address_postal_code' => 'required|string|max:20',
            'locality_id' => 'required|exists:localities,id',
            'description' => 'nullable|string',
            'square_meters' => 'required|integer|min:1',
            'people_count' => 'required|integer|min:1',
        ];

        // Add business hours validation for office/trade
        if ($config['has_business_hours'] ?? false) {
            $rules['opens_at'] = 'nullable|date_format:H:i';
            $rules['closes_at'] = 'nullable|date_format:H:i|after:opens_at';
            $rules['operating_days'] = 'nullable|array';
            $rules['operating_days.*'] = 'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo';
        }

        $validated = $request->validate($rules);
        $validated['type'] = $this->entityType;

        // Handle operating_days as JSON
        if (isset($validated['operating_days'])) {
            $validated['operating_days'] = json_encode($validated['operating_days']);
        }

        $entity = Entity::create($validated);

        // Create default rooms
        $defaultRooms = $config['default_rooms'] ?? ['General'];
        foreach ($defaultRooms as $roomName) {
            $entity->rooms()->create([
                'name' => $roomName,
                'description' => "Área predeterminada: {$roomName}",
            ]);
        }

        // Associate entity with user's current plan
        $user = auth()->user();
        $currentPlan = $user->currentPlan();

        $user->entities()->attach($entity->id, [
            'plan_id' => $currentPlan ? $currentPlan->id : 1,
            'subscribed_at' => now(),
        ]);

        return redirect()->route("{$config['route_prefix']}.show", $entity->id)
            ->with('success', "{$config['label']} creado correctamente.");
    }

    /**
     * Display the specified entity.
     */
    public function show(string $id, ClimateDataService $climateService)
    {
        $config = $this->getConfig();
        
        $entity = Entity::where('type', $this->entityType)
            ->with(['locality', 'rooms.equipment', 'contracts'])
            ->findOrFail($id);

        $climateProfile = null;
        if ($entity->locality) {
            $climateProfile = $climateService->getLocalityClimateProfile($entity->locality);
        }

        // Get latest invoice for calculations
        $invoice = \App\Models\Invoice::whereHas('contract', function ($query) use ($entity) {
            $query->where('entity_id', $entity->id);
        })->latest('end_date')->first();

        // Calculate replacement opportunities
        $replacementsCount = 0;
        if ($invoice) {
            $service = new ReplacementService();
            $opportunities = $service->generateOpportunities($invoice);
            $replacementsCount = count($opportunities);
        }

        $recommendations = $this->getEnabledRecommendations();

        return view("entities.{$this->getViewFolder()}.show", compact(
            'entity', 
            'climateProfile', 
            'replacementsCount', 
            'config',
            'recommendations'
        ));
    }

    /**
     * Show the form for editing the specified entity.
     */
    public function edit(string $id)
    {
        $config = $this->getConfig();
        
        $entity = Entity::where('type', $this->entityType)->findOrFail($id);
        $localities = Locality::all();

        return view("entities.{$this->getViewFolder()}.edit", compact('entity', 'localities', 'config'));
    }

    /**
     * Update the specified entity.
     */
    public function update(Request $request, string $id)
    {
        $config = $this->getConfig();
        
        $entity = Entity::where('type', $this->entityType)->findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'address_street' => 'required|string|max:255',
            'address_postal_code' => 'required|string|max:20',
            'locality_id' => 'required|exists:localities,id',
            'description' => 'nullable|string',
            'square_meters' => 'required|integer|min:1',
            'people_count' => 'required|integer|min:1',
        ];

        // Add business hours validation for office/trade
        if ($config['has_business_hours'] ?? false) {
            $rules['opens_at'] = 'nullable|date_format:H:i';
            $rules['closes_at'] = 'nullable|date_format:H:i|after:opens_at';
            $rules['operating_days'] = 'nullable|array';
            $rules['operating_days.*'] = 'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo';
        }

        $validated = $request->validate($rules);

        // Handle operating_days as JSON
        if (isset($validated['operating_days'])) {
            $validated['operating_days'] = json_encode($validated['operating_days']);
        }

        $entity->update($validated);

        return redirect()->route("{$config['route_prefix']}.show", $entity->id)
            ->with('success', "{$config['label']} actualizado correctamente.");
    }

    /**
     * Remove the specified entity.
     */
    public function destroy(string $id)
    {
        $config = $this->getConfig();
        
        $entity = Entity::where('type', $this->entityType)->findOrFail($id);
        $entity->delete();

        return redirect()->route("{$config['route_prefix']}.index")
            ->with('success', "{$config['label']} eliminado correctamente.");
    }

    /**
     * Get the view folder name for this entity type.
     */
    protected function getViewFolder(): string
    {
        return match($this->entityType) {
            'hogar' => 'home',
            'oficina' => 'office',
            'comercio' => 'trade',
            default => 'home',
        };
    }
}
