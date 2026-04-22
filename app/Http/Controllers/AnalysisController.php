<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\Invoice;
use App\Models\Room;
use App\Models\EquipmentUsage;
use App\Services\ConsumptionAnalysisService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Traits\HasActiveEntity;
use App\Traits\GroupsInvoices;

class AnalysisController extends Controller
{
    use HasActiveEntity, GroupsInvoices;

    protected $analysisService;

    public function __construct(ConsumptionAnalysisService $analysisService)
    {
        $this->analysisService = $analysisService;
    }

    /**
     * Dashboard de Consumo Real
     */
    public function realConsumption(Request $request)
    {
        $entity = $this->getActiveEntity($request);
        if (!$entity) return redirect()->route('dashboard');

        // 1. Desglose por Categoría (basado en la última factura calibrada o teórica si no hay)
        $latestInvoice = Invoice::whereHas('contract', function($q) use ($entity) {
            $q->where('entity_id', $entity->id);
        })->orderBy('end_date', 'desc')->first();

        $categoryBreakdown = [];
        $tankBreakdown = [
            ['name' => 'Tanque 1 (Base)', 'value' => 0, 'color' => '#0f172a'], // Slate 900
            ['name' => 'Tanque 2 (Clima)', 'value' => 0, 'color' => '#06b6d4'], // Cyan
            ['name' => 'Tanque 3 (Elasticidad)', 'value' => 0, 'color' => '#f59e0b'], // Amber
        ];

        if ($latestInvoice) {
            // 1.1 Category Data
            $categoryData = DB::table('equipment_usages')
                ->join('equipment', 'equipment_usages.equipment_id', '=', 'equipment.id')
                ->join('equipment_categories', 'equipment.category_id', '=', 'equipment_categories.id')
                ->where('equipment_usages.invoice_id', $latestInvoice->id)
                ->select('equipment_categories.name', DB::raw('SUM(COALESCE(kwh_reconciled, consumption_kwh, 0)) as total_kwh'))
                ->groupBy('equipment_categories.name')
                ->get();
            
            foreach ($categoryData as $data) {
                $categoryBreakdown[] = [
                    'name' => $data->name,
                    'value' => (float)$data->total_kwh
                ];
            }

            // 1.2 Tank Data (New!)
            $tankData = DB::table('equipment_usages')
                ->where('invoice_id', $latestInvoice->id)
                ->select('tank_assignment', DB::raw('SUM(COALESCE(kwh_reconciled, consumption_kwh, 0)) as total_kwh'))
                ->groupBy('tank_assignment')
                ->get();

            foreach ($tankData as $data) {
                $idx = (int)$data->tank_assignment - 1;
                if (isset($tankBreakdown[$idx])) {
                    $tankBreakdown[$idx]['value'] = (float)$data->total_kwh;
                }
            }
        }

        // 2. Histórico de 12 Meses
        $history = [];
        $twelveMonthsAgo = Carbon::now()->subMonths(12);
        
        $invoicesData = Invoice::whereHas('contract', function($q) use ($entity) {
            $q->where('entity_id', $entity->id);
        })
        ->where('end_date', '>=', $twelveMonthsAgo)
        ->orderBy('end_date', 'asc')
        ->get();

        foreach ($invoicesData as $inv) {
            $history[] = [
                'period' => Carbon::parse($inv->end_date)->format('M y'),
                'real' => (float)$inv->total_energy_consumed_kwh,
                'theoretical' => (float)($inv->recommended_kwh ?? 0)
            ];
        }

        return Inertia::render('Analisis/ConsumptionReal', [
            'entity' => $entity,
            'categoryBreakdown' => $categoryBreakdown,
            'tankBreakdown' => $tankBreakdown,
            'history' => $history,
            'latestInvoice' => $latestInvoice
        ]);
    }

    /**
     * Gestión de Ajuste de Uso (Calibración)
     */
    public function usageAdjustment(Request $request)
    {
        $entity = $this->getActiveEntity($request);
        if (!$entity) return redirect()->route('dashboard');

        $unifications = $this->getUnifiedPeriods($entity);

        return Inertia::render('Analisis/UsageAdjustment', [
            'entity' => $entity,
            'unifications' => $unifications
        ]);
    }
    
    /**
     * Detalle del ajuste de uso (Sintonía Fina)
     */
    public function usageAdjustmentDetail(Request $request, $contractId, $startDate, $endDate)
    {
        $entity = $this->getActiveEntity($request);
        if (!$entity) return redirect()->route('dashboard');

        $parsedStart = Carbon::parse($startDate)->format('Y-m-d');
        $parsedEnd = Carbon::parse($endDate)->format('Y-m-d');

        // Buscar todas las facturas del periodo para verificar si está completo
        $allInvoices = Invoice::where('contract_id', $contractId)
            ->where('start_date', 'like', $parsedStart . '%')
            ->where('end_date', 'like', $parsedEnd . '%')
            ->get();

        $invoice = $allInvoices->first();
        
        if (!$invoice) {
            return redirect()->route('analisis.usage')->with('error', 'No se encontró la factura para el periodo seleccionado. (' . $parsedStart . ' al ' . $parsedEnd . ')');
        }

        $installmentsCount = $allInvoices->count();
        $isComplete = ($installmentsCount >= ($invoice->total_installments ?? 2)) || ($installmentsCount == 1 && empty($invoice->installment_number));

        // Obtener usos existentes
        $usages = $invoice->equipmentUsages()->get()->keyBy('equipment_id');
        
        // Obtener ambientes y equipos instalados en este periodo
        $rooms = $entity->rooms()->with(['equipment' => function($q) use ($invoice) {
            $q->where(function($query) use ($invoice) {
                $query->whereNull('installed_at')
                      ->orWhere('installed_at', '<=', $invoice->end_date);
            })->where(function($query) use ($invoice) {
                $query->whereNull('removed_at')
                      ->orWhere('removed_at', '>=', $invoice->start_date);
            })->with(['type', 'category']);
        }])->get();

        // Agrupar por Tiers (Tanques)
        $tanks = [
            'base_critica' => ['label' => 'Tanque 1: Base Crítica', 'key' => 1, 'items' => []],
            'climatizacion' => ['label' => 'Tanque 2: Climatización', 'key' => 2, 'items' => []],
            'uso_variable' => ['label' => 'Tanque 3: Uso Variable', 'key' => 3, 'items' => []],
        ];

        foreach ($rooms as $room) {
            foreach ($room->equipment as $equipment) {
                $usage = $usages[$equipment->id] ?? [
                    'equipment_id' => $equipment->id,
                    'avg_daily_use_hours' => $equipment->avg_daily_use_hours,
                    'usage_frequency' => $equipment->usage_frequency ?? 'diario',
                    'is_standby' => $equipment->is_standby,
                ];

                $tier = $this->getEquipmentTier($equipment);
                
                // Estructura para la UI
                $item = [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'room_name' => $room->name,
                    'nominal_power_w' => $equipment->nominal_power_w,
                    'usage' => $usage,
                    'is_validated' => $equipment->is_validated ?? false,
                    'is_standby' => $equipment->is_standby ?? false,
                    'category_name' => $equipment->category->name ?? '',
                    'type_name' => $equipment->type->name ?? '',
                ];

                $tanks[$tier]['items'][] = $item;
            }
        }

        $periodTotalKwh = $allInvoices->sum('total_energy_consumed_kwh');
        $realBimonthlyKwh = $allInvoices->max('bimonthly_consumption_kwh') ?: $periodTotalKwh;

        $climateService = app(\App\Services\ClimateService::class);
        // Usar loadDataForDateRange asegura que los datos se bajen de la API si no existen en la BD
        $climateData = $climateService->loadDataForDateRange($entity, $startDate, $endDate);

        return Inertia::render('Analisis/UsageAdjustmentDetail', [
            'entity' => $entity,
            'invoice' => $invoice,
            'tanks' => array_values($tanks),
            'is_complete' => $isComplete,
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
                'days' => Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)),
                'total_kwh' => $realBimonthlyKwh,
                'cooling_days' => $climateData['cooling_days'] ?? 0,
                'heating_days' => $climateData['heating_days'] ?? 0,
            ]
        ]);
    }

    /**
     * Guardar el ajuste de sintonía fina
     */
    public function saveUsageAdjustment(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'usages' => 'required|array',
            'lock_period' => 'boolean'
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);
        
        DB::transaction(function() use ($request, $invoice) {
            foreach ($request->usages as $eqId => $data) {
                // 1. Guardar uso específico para este periodo (Factura)
                EquipmentUsage::updateOrCreate(
                    ['invoice_id' => $invoice->id, 'equipment_id' => $eqId],
                    [
                        'avg_daily_use_hours' => $data['avg_daily_use_hours'],
                        'usage_frequency' => $data['usage_frequency'],
                        'is_standby' => $data['is_standby'] ?? false,
                    ]
                );

                // 2. [MEJORA] Actualizar la "ficha técnica" global del equipo (Aprendizaje de hábitos)
                $equipment = \App\Models\Equipment::find($eqId);
                if ($equipment) {
                    $equipment->update([
                        'avg_daily_use_hours' => $data['avg_daily_use_hours'],
                        'usage_frequency' => $data['usage_frequency'],
                        'is_standby' => $data['is_standby'] ?? false,
                    ]);
                }
            }

            // Si se solicita cerrar, ejecutamos el motor
            if ($request->lock_period) {
                // Buscamos todas las facturas hermanas para la calibración unificada
                $invoices = Invoice::where('contract_id', $invoice->contract_id)
                    ->where('start_date', $invoice->start_date)
                    ->where('end_date', $invoice->end_date)
                    ->get();
                
                $this->analysisService->calibrateUnifiedPeriod($invoices);
            }
        });

        return redirect()->route('analisis.usage')->with('success', 'Ajustes guardados correctamente.');
    }

    /**
     * Lógica legacy de clasificación por Tiers
     */
    private function getEquipmentTier($equipment)
    {
        if ($equipment->category && str_contains(strtolower($equipment->category->name), 'climat')) {
            return 'climatizacion';
        }

        $hours = $equipment->avg_daily_use_hours;
        if ($hours >= 23) {
            return 'base_critica';
        }

        return 'uso_variable';
    }

    /**
     * Ejecutar el motor de calibración para un periodo unificado
     */
    public function runAdjustment(Request $request)
    {
        $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $entity = $this->getActiveEntity($request);
        
        // Cargar todas las facturas del periodo unificado
        $invoices = Invoice::where('contract_id', $request->contract_id)
            ->where('start_date', $request->start_date)
            ->where('end_date', $request->end_date)
            ->get();

        if ($invoices->isEmpty()) {
            return redirect()->back()->with('error', 'No se encontraron facturas para el periodo seleccionado.');
        }

        // Seguridad: Verificar que el usuario sea dueño del contrato
        if ($request->user()->cannot('update', $entity)) {
            abort(403);
        }

        try {
            // Ejecutar la calibración unificada
            $result = $this->analysisService->calibrateUnifiedPeriod($invoices);
            
            return redirect()->back()->with('success', 'Calibración del periodo completa. El Gemelo Digital se ha sincronizado.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en el motor de cálculo: ' . $e->getMessage());
        }
    }

    /**
     * Optimización de Horarios
     */
    public function gridOptimization(Request $request)
    {
        $entity = $this->getActiveEntity($request);
        if (!$entity) return redirect()->route('dashboard');

        // En esta fase, devolvemos una vista estática/dashboard que será poblada con lógica de desplazamiento
        return Inertia::render('Analisis/GridOptimization', [
            'entity' => $entity
        ]);
    }
}
