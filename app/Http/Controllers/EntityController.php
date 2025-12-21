<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EntityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        // Load entities with related locality, rooms and equipment
        $entities = $user->entities()
            ->with(['locality', 'rooms.equipment'])
            ->get()
            ->map(function ($entity) {
                // Calculate installed power (sum of equipment power in all rooms)
                $installedPower = $entity->rooms
                    ->flatMap(fn($room) => $room->equipment)
                    ->sum('power');
                $entity->installed_power = $installedPower;
                // Power per square meter and available percentage
                $entity->power_per_m2 = $entity->square_meters ? ($installedPower / $entity->square_meters) : 0;
                $entity->available_percentage = $entity->square_meters ? max(0, 100 - $entity->power_per_m2) : 0;
                return $entity;
            });
        return view('entities.index', compact('entities'));
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Authorize creation of hogar entity (default type)
        $this->authorize('create', [\App\Models\Entity::class, 'hogar']);

        $localities = \App\Models\Locality::with('province')->get();
        return view('entities.create', compact('localities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Get entity type from request or default to hogar
        $entityType = $request->input('type', 'hogar');

        // Authorize creation of this entity type
        $this->authorize('create', [\App\Models\Entity::class, $entityType]);

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|in:hogar,oficina,comercio',
            'address_street' => 'required|string|max:255',
            'address_postal_code' => 'required|string|max:20',
            'locality_id' => 'required|exists:localities,id',
            'description' => 'nullable|string',
            'square_meters' => 'required|integer|min:1',
            'people_count' => 'required|integer|min:1',
        ]);

        $entity = \App\Models\Entity::create($request->only([
            'name',
            'type',
            'address_street',
            'address_postal_code',
            'locality_id',
            'description',
            'square_meters',
            'people_count',
        ]));

        // Crear automáticamente la sala de Portátiles
        $entity->rooms()->create([
            'name' => 'Portátiles',
            'description' => 'Sala para equipos portátiles y recargables',
        ]);

        // Asociar entidad al usuario con su plan actual
        $user = auth()->user();
        $currentPlan = $user->currentPlan();

        $user->entities()->attach($entity->id, [
            'plan_id' => $currentPlan ? $currentPlan->id : 1,
            'subscribed_at' => now(),
        ]);

        return redirect()->route('entities.show', $entity->id)
            ->with('success', 'Entidad creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    /**
     * Display the specified resource.
     */
    public function show(string $id, \App\Services\Climate\ClimateDataService $climateService)
    {
        $entity = \App\Models\Entity::with('locality')->findOrFail($id);

        $climateProfile = null;
        if ($entity->locality) {
            $climateProfile = $climateService->getLocalityClimateProfile($entity->locality);
        }

        // Cargar relaciones necesarias
        $entity->load(['rooms.equipment', 'contracts']);

        // Obtener última factura para cálculos
        $invoice = \App\Models\Invoice::whereHas('contract', function ($query) use ($entity) {
            $query->where('entity_id', $entity->id);
        })->latest('end_date')->first();

        // Calcular oportunidades de reemplazo
        $replacementsCount = 0;
        if ($invoice) {
            $service = new \App\Services\Recommendations\ReplacementService();
            $opportunities = $service->generateOpportunities($invoice);
            $replacementsCount = count($opportunities);
        }

        return view('entities.show', compact('entity', 'climateProfile', 'replacementsCount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $entity = \App\Models\Entity::findOrFail($id);
        $localities = \App\Models\Locality::all();
        return view('entities.edit', compact('entity', 'localities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'address_street' => 'required|string|max:255',
            'address_postal_code' => 'required|string|max:20',
            'locality_id' => 'required|exists:localities,id',
            'description' => 'nullable|string',
            'square_meters' => 'required|integer|min:1',
            'people_count' => 'required|integer|min:1',
        ]);

        $entity = \App\Models\Entity::findOrFail($id);
        $entity->update($request->only([
            'name',
            'address_street',
            'address_postal_code',
            'locality_id',
            'description',
            'square_meters',
            'people_count',
        ]));

        return redirect()->route('entities.show', $entity->id)
            ->with('success', 'Entidad actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $entity = \App\Models\Entity::findOrFail($id);
        $entity->delete();
        return redirect()->route('entities.index')
            ->with('success', 'Entidad eliminada correctamente.');
    }

    /**
     * Show budget request placeholder for an entity.
     */
    public function budget(string $id, \App\Services\BudgetService $budgetService)
    {
        $entity = \App\Models\Entity::findOrFail($id);
        $budgetData = $budgetService->calculateBudgetData($entity);

        return view('entities.budget', array_merge(['entity' => $entity], $budgetData));
    }

    public function solarWaterHeater(string $id, \App\Services\SolarWaterHeaterService $service)
    {
        $entity = \App\Models\Entity::findOrFail($id);
        $data = $service->calculateWaterHeaterData($entity);

        return view('entities.solar_water_heater', array_merge(['entity' => $entity], $data));
    }

    /**
     * Show Standby Analysis for an entity.
     */
    public function standbyAnalysis(string $id, \App\Services\StandbyAnalysisService $service)
    {
        $entity = \App\Models\Entity::findOrFail($id);
        $data = $service->calculateStandbyAnalysis($entity);

        return view('entities.standby_analysis', array_merge(['entity' => $entity], $data));
    }

    /**
     * Toggle standby status for an equipment.
     */
    public function toggleStandby(Request $request, string $entityId, string $equipmentId, \App\Services\StandbyAnalysisService $service)
    {
        $equipment = \App\Models\Equipment::findOrFail($equipmentId);
        $entity = \App\Models\Entity::findOrFail($entityId);

        $service->toggleEquipmentStandby($equipment, $entity);

        return redirect()->route('entities.standby_analysis', $entityId)
            ->with('success', 'Estado de Stand By actualizado.');
    }

    /**
     * Show Grid Optimization Analysis.
     */
    public function gridOptimization(string $id, \App\Services\GridOptimizerService $optimizer)
    {
        $entity = \App\Models\Entity::with(['rooms.equipment.type'])->findOrFail($id);

        $tariffScheme = \App\Models\TariffScheme::with('bands')->first(); // Default to the first one (seeded)

        if (!$tariffScheme) {
            // Fallback or error
            // For now, let's just return empty or redirect
            return redirect()->back()->with('error', 'No hay esquemas tarifarios disponibles. Ejecute el seeder.');
        }

        // Get active equipment usages simulation
        $equipments = $entity->rooms->flatMap(fn($r) => $r->equipment);

        $usages = $equipments->map(function ($eq) {
            return (object) [
                'equipment' => $eq,
                'kwh_reconciled' => ($eq->type->default_power_watts ?? 0) * ($eq->type->default_avg_daily_use_hours ?? 0) * 30 / 1000,
                'daily_kwh' => ($eq->type->default_power_watts ?? 0) * ($eq->type->default_avg_daily_use_hours ?? 0) / 1000
            ];
        });

        $opportunities = $optimizer->calculateShiftSavings($usages, $tariffScheme);

        return view('grid.optimization', compact('entity', 'opportunities', 'tariffScheme'));
    }
}
