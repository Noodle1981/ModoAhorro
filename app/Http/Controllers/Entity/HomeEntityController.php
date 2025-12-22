<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Models\Locality;
use App\Services\Climate\ClimateDataService;
use App\Services\Recommendations\ReplacementService;
use Illuminate\Http\Request;

/**
 * Controller for Home (Hogar) Entities
 * 
 * Handles CRUD operations specifically for residential entities.
 * No business hours or working schedules - these are homes.
 */
class HomeEntityController extends Controller
{
    /**
     * Display a listing of home entities.
     */
    public function index()
    {
        $user = auth()->user();
        $entities = $user->entities()
            ->where('type', 'hogar')
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

        return view('entities.home.index', compact('entities'));
    }

    /**
     * Show the form for creating a new home entity.
     */
    public function create()
    {
        $this->authorize('create', [Entity::class, 'hogar']);

        $localities = Locality::with('province')->get();
        return view('entities.home.create', compact('localities'));
    }

    /**
     * Store a newly created home entity.
     */
    public function store(Request $request)
    {
        $this->authorize('create', [Entity::class, 'hogar']);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address_street' => 'required|string|max:255',
            'address_postal_code' => 'required|string|max:20',
            'locality_id' => 'required|exists:localities,id',
            'description' => 'nullable|string',
            'square_meters' => 'required|integer|min:1',
            'people_count' => 'required|integer|min:1',
        ]);

        // Force type to 'hogar'
        $validated['type'] = 'hogar';

        $entity = Entity::create($validated);

        // Create default "Portátiles" room
        $entity->rooms()->create([
            'name' => 'Portátiles',
            'description' => 'Sala para equipos portátiles y recargables',
        ]);

        // Associate entity with user's current plan
        $user = auth()->user();
        $currentPlan = $user->currentPlan();

        $user->entities()->attach($entity->id, [
            'plan_id' => $currentPlan ? $currentPlan->id : 1,
            'subscribed_at' => now(),
        ]);

        return redirect()->route('entities.home.show', $entity->id)
            ->with('success', 'Hogar creado correctamente.');
    }

    /**
     * Display the specified home entity.
     */
    public function show(string $id, ClimateDataService $climateService)
    {
        $entity = Entity::where('type', 'hogar')
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

        return view('entities.home.show', compact('entity', 'climateProfile', 'replacementsCount'));
    }

    /**
     * Show the form for editing the specified home entity.
     */
    public function edit(string $id)
    {
        $entity = Entity::where('type', 'hogar')->findOrFail($id);
        $localities = Locality::all();

        return view('entities.home.edit', compact('entity', 'localities'));
    }

    /**
     * Update the specified home entity.
     */
    public function update(Request $request, string $id)
    {
        $entity = Entity::where('type', 'hogar')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address_street' => 'required|string|max:255',
            'address_postal_code' => 'required|string|max:20',
            'locality_id' => 'required|exists:localities,id',
            'description' => 'nullable|string',
            'square_meters' => 'required|integer|min:1',
            'people_count' => 'required|integer|min:1',
        ]);

        $entity->update($validated);

        return redirect()->route('entities.home.show', $entity->id)
            ->with('success', 'Hogar actualizado correctamente.');
    }

    /**
     * Remove the specified home entity.
     */
    public function destroy(string $id)
    {
        $entity = Entity::where('type', 'hogar')->findOrFail($id);
        $entity->delete();

        return redirect()->route('entities.home.index')
            ->with('success', 'Hogar eliminado correctamente.');
    }
}
