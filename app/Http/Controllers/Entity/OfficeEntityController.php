<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Models\Locality;
use App\Services\Climate\ClimateDataService;
use App\Services\Recommendations\ReplacementService;
use Illuminate\Http\Request;

/**
 * Controller for Office (Oficina) Entities
 * 
 * Handles CRUD operations specifically for office entities.
 * Offices have working hours and schedules (to be implemented).
 */
class OfficeEntityController extends Controller
{
    /**
     * Display a listing of office entities.
     */
    public function index()
    {
        $user = auth()->user();
        $entities = $user->entities()
            ->where('type', 'oficina')
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

        // TODO: Create specific view for offices
        return view('entities.office.index', compact('entities'));
    }

    /**
     * Show the form for creating a new office entity.
     */
    public function create()
    {
        $this->authorize('create', [Entity::class, 'oficina']);

        $localities = Locality::with('province')->get();
        // TODO: Create specific view for offices
        return view('entities.office.create', compact('localities'));
    }

    /**
     * Store a newly created office entity.
     */
    public function store(Request $request)
    {
        $this->authorize('create', [Entity::class, 'oficina']);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address_street' => 'required|string|max:255',
            'address_postal_code' => 'required|string|max:20',
            'locality_id' => 'required|exists:localities,id',
            'description' => 'nullable|string',
            'square_meters' => 'required|integer|min:1',
            'people_count' => 'required|integer|min:1',
            // Office-specific fields (to be added via migration)
            // 'opens_at' => 'nullable|date_format:H:i',
            // 'closes_at' => 'nullable|date_format:H:i',
            // 'working_days' => 'nullable|array',
        ]);

        // Force type to 'oficina'
        $validated['type'] = 'oficina';

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

        return redirect()->route('entities.office.show', $entity->id)
            ->with('success', 'Oficina creada correctamente.');
    }

    /**
     * Display the specified office entity.
     */
    public function show(string $id, ClimateDataService $climateService)
    {
        $entity = Entity::where('type', 'oficina')
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

        // TODO: Create specific view for offices
        return view('entities.office.show', compact('entity', 'climateProfile', 'replacementsCount'));
    }

    /**
     * Show the form for editing the specified office entity.
     */
    public function edit(string $id)
    {
        $entity = Entity::where('type', 'oficina')->findOrFail($id);
        $localities = Locality::all();

        // TODO: Create specific view for offices
        return view('entities.office.edit', compact('entity', 'localities'));
    }

    /**
     * Update the specified office entity.
     */
    public function update(Request $request, string $id)
    {
        $entity = Entity::where('type', 'oficina')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address_street' => 'required|string|max:255',
            'address_postal_code' => 'required|string|max:20',
            'locality_id' => 'required|exists:localities,id',
            'description' => 'nullable|string',
            'square_meters' => 'required|integer|min:1',
            'people_count' => 'required|integer|min:1',
            // Office-specific fields (to be added via migration)
            // 'opens_at' => 'nullable|date_format:H:i',
            // 'closes_at' => 'nullable|date_format:H:i',
            // 'working_days' => 'nullable|array',
        ]);

        $entity->update($validated);

        return redirect()->route('entities.office.show', $entity->id)
            ->with('success', 'Oficina actualizada correctamente.');
    }

    /**
     * Remove the specified office entity.
     */
    public function destroy(string $id)
    {
        $entity = Entity::where('type', 'oficina')->findOrFail($id);
        $entity->delete();

        return redirect()->route('entities.office.index')
            ->with('success', 'Oficina eliminada correctamente.');
    }
}
