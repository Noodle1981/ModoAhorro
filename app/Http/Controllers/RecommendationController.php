<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\Invoice;
use App\Models\Equipment;
use App\Services\SolarPowerService;
use App\Services\SolarWaterHeaterService;
use App\Services\ReplacementService;
use App\Services\StandbyAnalysisService;
use App\Services\MaintenanceService;
use App\Services\VacationService;
use App\Services\ThermalProfileService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Traits\HasActiveEntity;

class RecommendationController extends Controller
{
    use HasActiveEntity;

    /**
     * Recomendaciones Solar (Paneles y Termotanques)
     */
    public function solar(Request $request, SolarPowerService $solarPower, SolarWaterHeaterService $solarWater)
    {
        $entity = $this->getActiveEntity($request);
        if (!$entity) return redirect()->route('dashboard');

        // Analizar facturas para obtener consumos
        $invoices = Invoice::whereHas('contract', fn($q) => $q->where('entity_id', $entity->id))->get();
        $maxConsumption = $invoices->max('total_energy_consumed_kwh') ?? 400;
        $avgConsumption = $invoices->avg('total_energy_consumed_kwh') ?? 300;

        $availableArea = 30; // Default 30m2
        
        $photovoltaic = $solarPower->calculateSolarCoverage($availableArea, $maxConsumption, $avgConsumption);
        $thermal = $solarWater->calculateWaterHeaterData($entity);

        return Inertia::render('Recomendaciones/Solar', [
            'entity' => $entity,
            'photovoltaic' => $photovoltaic,
            'thermal' => $thermal
        ]);
    }

    /**
     * Reemplazos de Equipos (Ahorro por Eficiencia)
     */
    public function replacements(Request $request, ReplacementService $service)
    {
        $entity = $this->getActiveEntity($request);
        if (!$entity) return redirect()->route('dashboard');

        $latestInvoice = Invoice::whereHas('contract', fn($q) => $q->where('entity_id', $entity->id))
            ->orderBy('end_date', 'desc')
            ->first();

        $opportunities = $service->generateOpportunities($entity, $latestInvoice);

        return Inertia::render('Recomendaciones/Replacements', [
            'entity' => $entity,
            'opportunities' => $opportunities
        ]);
    }

    /**
     * Consumo Fantasma (Standby)
     */
    public function standby(Request $request, StandbyAnalysisService $service)
    {
        $entity = $this->getActiveEntity($request);
        if (!$entity) return redirect()->route('dashboard');

        $results = $service->calculateStandbyAnalysis($entity);

        return Inertia::render('Recomendaciones/Standby', [
            'entity' => $entity,
            'analysis' => $results
        ]);
    }

    /**
     * Salud Térmica (Resultados de Diagnóstico)
     */
    public function thermalHealth(Request $request, ThermalProfileService $service)
    {
        $entity = $this->getActiveEntity($request);
        if (!$entity) return redirect()->route('dashboard');

        $profile = $service->calculate($entity->thermal_profile ?? []);

        return Inertia::render('Recomendaciones/ThermalHealth', [
            'entity' => $entity,
            'profile' => $profile
        ]);
    }

    /**
     * Mantenimiento Preventivo
     */
    public function maintenance(Request $request, MaintenanceService $service)
    {
        $entity = $this->getActiveEntity($request);
        if (!$entity) return redirect()->route('dashboard');

        $entity->load('rooms.equipment.type.maintenanceTasks');
        $allTasks = [];
        $equipments = $entity->rooms->flatMap->equipment;
        
        foreach ($equipments as $equipment) {
            $status = $service->checkStatus($equipment);
            foreach ($status['pending_tasks'] as $task) {
                $allTasks[] = [
                    'id' => uniqid(),
                    'name' => $task['task'],
                    'equipment_name' => $equipment->name,
                    'priority' => (float)str_replace('%', '', $task['impact']) > 10 ? 'Alta' : 'Media',
                    'efficiency_gain' => (float)str_replace('%', '', $task['impact']),
                    'frequency' => ($equipment->type->maintenanceTasks->where('title', $task['task'])->first()->frequency_days ?? 365) . ' días',
                    'due_date' => $task['due_date']
                ];
            }
        }

        return Inertia::render('Recomendaciones/Maintenance', [
            'entity' => $entity,
            'tasks' => $allTasks
        ]);
    }

    /**
     * Plan de Vacaciones
     */
    public function vacation(Request $request, VacationService $service)
    {
        $entity = $this->getActiveEntity($request);
        if (!$entity) return redirect()->route('dashboard');

        $entity->load('rooms.equipment.type');
        $result = $service->generateChecklist($entity, 15); // Default 15 días

        return Inertia::render('Recomendaciones/Vacations', [
            'entity' => $entity,
            'plan' => [
                'daily_standby_saving' => $result['total_savings'] / 15,
                'checklist' => $result['checklist'],
                'total_savings' => $result['total_savings']
            ]
        ]);
    }
}
