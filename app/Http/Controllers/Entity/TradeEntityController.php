<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Models\Locality;
use App\Services\Climate\ClimateDataService;
use App\Services\Recommendations\ReplacementService;
use Illuminate\Http\Request;

/**
 * Controller for Trade/Commerce (Comercio) Entities
 * 
 * Handles CRUD operations specifically for commercial entities.
 * Commercial entities have extended operating hours and specific equipment needs.
 */
class TradeEntityController extends Controller
{
    /**
     * Display a listing of trade entities.
     */
    public function index()
    {
        $user = auth()->user();
        $entities = $user->entities()
            ->where('type', 'comercio')
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

        // TODO: Create specific view for trade
        return view('entities.trade.index', compact('entities'));
    }

    /**
     * Show the form for creating a new trade entity.
     */
    public function create()
    {
        $this->authorize('create', [Entity::class, 'comercio']);

        $localities = Locality::with('province')->get();
        // TODO: Create specific view for trade
        return view('entities.trade.create', compact('localities'));
    }

    /**
     * Store a newly created trade entity.
     */
    public function store(Request $request)
    {
        $this->authorize('create', [Entity::class, 'comercio']);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address_street' => 'required|string|max:255',
            'address_postal_code' => 'required|string|max:20',
            'locality_id' => 'required|exists:localities,id',
            'description' => 'nullable|string',
            'square_meters' => 'required|integer|min:1',
            'people_count' => 'required|integer|min:1',
            // Trade-specific fields (to be added via migration)
            // 'opens_at' => 'nullable|date_format:H:i',
            // 'closes_at' => 'nullable|date_format:H:i',
            // 'operating_days' => 'nullable|array',
            // 'business_type' => 'nullable|string', // restaurant, shop, etc.
        ]);

        // Force type to 'comercio'
        $validated['type'] = 'comercio';

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

        return redirect()->route('entities.trade.show', $entity->id)
            ->with('success', 'Comercio creado correctamente.');
    }

    /**
     * Display the specified trade entity.
     */
    public function show(string $id, ClimateDataService $climateService)
    {
        $entity = Entity::where('type', 'comercio')
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

        // TODO: Create specific view for trade
        return view('entities.trade.show', compact('entity', 'climateProfile', 'replacementsCount'));
    }

    /**
     * Show the form for editing the specified trade entity.
     */
    public function edit(string $id)
    {
        $entity = Entity::where('type', 'comercio')->findOrFail($id);
        $localities = Locality::all();

        // TODO: Create specific view for trade
        return view('entities.trade.edit', compact('entity', 'localities'));
    }

    /**
     * Update the specified trade entity.
     */
    public function update(Request $request, string $id)
    {
        $entity = Entity::where('type', 'comercio')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address_street' => 'required|string|max:255',
            'address_postal_code' => 'required|string|max:20',
            'locality_id' => 'required|exists:localities,id',
            'description' => 'nullable|string',
            'square_meters' => 'required|integer|min:1',
            'people_count' => 'required|integer|min:1',
            // Trade-specific fields (to be added via migration)
            // 'opens_at' => 'nullable|date_format:H:i',
            // 'closes_at' => 'nullable|date_format:H:i',
            // 'operating_days' => 'nullable|array',
        ]);

        $entity->update($validated);

        return redirect()->route('entities.trade.show', $entity->id)
            ->with('success', 'Comercio actualizado correctamente.');
    }

    /**
     * Remove the specified trade entity.
     */
    public function destroy(string $id)
    {
        $entity = Entity::where('type', 'comercio')->findOrFail($id);
        $entity->delete();

        return redirect()->route('entities.trade.index')
            ->with('success', 'Comercio eliminado correctamente.');
    }
}
