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

        $availablePeriods = $this->getUnifiedPeriods($entity);
        $currentPeriod = $availablePeriods->firstWhere('id', $request->input('period_id')) ?? $availablePeriods->first();

        $analytics = $this->getPeriodAnalytics($entity, $currentPeriod);

        return Inertia::render('Analisis/ConsumptionReal', array_merge([
            'entity' => $entity,
            'availableInvoices' => $availablePeriods,
            'latestInvoice' => $currentPeriod,
            'history' => $this->getTwelveMonthHistory($entity),
        ], $analytics));
    }

    /**
     * Obtiene todos los datos analíticos de un periodo específico
     */
    private function getPeriodAnalytics(Entity $entity, $period)
    {
        if (!$period) return [
            'categoryBreakdown' => [], 'roomBreakdown' => [], 'topConsumers' => [],
            'tankBreakdown' => [], 'equipmentDetails' => [], 'auditLogs' => [],
            'climateStats' => null, 'validation' => null, 'suggestions' => [], 'totalPotencia' => 0
        ];

        $invoiceIds = collect($period['invoices'])->pluck('id');

        // 1. Desgloses (Categoría y Ambiente) en una sola pasada de ser posible, o consultas optimizadas
        $categoryBreakdown = DB::table('equipment_usages')
            ->join('equipment', 'equipment_usages.equipment_id', '=', 'equipment.id')
            ->join('equipment_categories', 'equipment.category_id', '=', 'equipment_categories.id')
            ->whereIn('invoice_id', $invoiceIds)
            ->select('equipment_categories.name', DB::raw('SUM(COALESCE(kwh_reconciled, consumption_kwh, 0)) as value'))
            ->groupBy('equipment_categories.name')
            ->get()->toArray();

        $roomBreakdown = DB::table('equipment_usages')
            ->join('equipment', 'equipment_usages.equipment_id', '=', 'equipment.id')
            ->join('rooms', 'equipment.room_id', '=', 'rooms.id')
            ->whereIn('invoice_id', $invoiceIds)
            ->select('rooms.name', DB::raw('SUM(COALESCE(kwh_reconciled, consumption_kwh, 0)) as value'))
            ->groupBy('rooms.name')
            ->orderBy('value', 'desc')
            ->get()->toArray();

        // 2. Detalle de Equipos (Eager Loading)
        $usages = EquipmentUsage::with(['equipment.room', 'equipment.category'])
            ->whereIn('invoice_id', $invoiceIds)
            ->get()
            ->groupBy('equipment_id');

        $equipmentDetails = [];
        foreach ($usages as $eqId => $group) {
            $first = $group->first();
            $equipmentDetails[] = [
                'id' => $eqId,
                'equipment_name' => $first->equipment->name ?? 'Desconocido',
                'category_name' => $first->equipment->category->name ?? 'General',
                'room_name' => $first->equipment->room->name ?? '-',
                'nominal_power_w' => $first->equipment->nominal_power_w ?? 0,
                'consumption_kwh' => (float)$group->sum(fn($u) => $u->kwh_reconciled ?? $u->consumption_kwh ?? 0),
                'tank_assignment' => (int)$first->tank_assignment,
            ];
        }

        // 3. Tanques (Desde el periodo)
        $tankColors = [1 => '#1e293b', 2 => '#0f172a', 3 => '#06b6d4', 4 => '#f59e0b'];
        $tankNames = [
            1 => 'Tanque 1 (Certeza)', 
            2 => 'Tanque 2 (Base)', 
            3 => 'Tanque 3 (Clima)', 
            4 => 'Tanque 4 (Variable)'
        ];
        $tankBreakdown = [];
        foreach ($period['tanks'] ?? [] as $num => $val) {
            if (isset($tankNames[$num])) {
                $tankBreakdown[] = ['name' => $tankNames[$num], 'value' => (float)$val, 'color' => $tankColors[$num]];
            }
        }

        // 4. Clima y Validación
        $climateData = app(\App\Services\ClimateService::class)->loadDataForDateRange($entity, $period['start_date'], $period['end_date']);
        $totalCalculatedKwh = collect($equipmentDetails)->sum('consumption_kwh');
        $mockInvoice = (object)['total_energy_consumed_kwh' => $period['total_kwh']];
        $validationService = new \App\Services\Core\ValidationService();

        return [
            'categoryBreakdown' => $categoryBreakdown,
            'roomBreakdown' => $roomBreakdown,
            'equipmentDetails' => $equipmentDetails,
            'topConsumers' => collect($equipmentDetails)->sortByDesc('consumption_kwh')->take(10)->values()->toArray(),
            'tankBreakdown' => $tankBreakdown,
            'auditLogs' => $usages->flatten()->first(fn($u) => !empty($u->audit_logs))->audit_logs ?? [],
            'climateStats' => $climateData ? ['heating_days' => $climateData['heating_days'] ?? 0, 'cooling_days' => $climateData['cooling_days'] ?? 0] : null,
            'validation' => $validationService->calculateDeviation($mockInvoice, $totalCalculatedKwh),
            'suggestions' => $validationService->getSuggestions($mockInvoice, $totalCalculatedKwh),
            'totalPotencia' => collect($equipmentDetails)->sum('nominal_power_w'),
        ];
    }

    /**
     * Obtiene el histórico de 12 meses
     */
    private function getTwelveMonthHistory(Entity $entity)
    {
        $twelveMonthsAgo = Carbon::now()->subMonths(12);
        return Invoice::whereHas('contract', fn($q) => $q->where('entity_id', $entity->id))
            ->where('end_date', '>=', $twelveMonthsAgo)
            ->orderBy('end_date', 'asc')
            ->get()
            ->map(fn($inv) => [
                'period' => Carbon::parse($inv->end_date)->format('M y'),
                'real' => (float)$inv->total_energy_consumed_kwh,
                'theoretical' => (float)($inv->recommended_kwh ?? 0)
            ])->toArray();
    }

    /**
     * Análisis de Evolución en el Tiempo
     */
    public function timeAnalysis(Request $request)
    {
        $entity = $this->getActiveEntity($request);
        if (!$entity) return redirect()->route('dashboard');

        $periods = $this->getUnifiedPeriods($entity)->sortBy('start_date')->values();
        $evolutionData = $this->getTimeEvolutionData($entity, $periods);

        return Inertia::render('Analisis/TimeAnalysis', [
            'entity' => $entity,
            'periods' => $periods,
            'evolution' => $evolutionData
        ]);
    }

    /**
     * Procesa la evolución temporal de múltiples periodos
     */
    private function getTimeEvolutionData(Entity $entity, $periods)
    {
        if ($periods->isEmpty()) return [];

        $evolution = [];
        $climateService = app(\App\Services\ClimateService::class);

        foreach ($periods as $period) {
            $invoiceIds = collect($period['invoices'])->pluck('id');
            
            // 1. Cálculos de Motor (Teórico)
            $theoreticalKwh = DB::table('equipment_usages')
                ->whereIn('invoice_id', $invoiceIds)
                ->sum(DB::raw('COALESCE(kwh_reconciled, consumption_kwh, 0)'));

            // 2. Clima
            $climate = $climateService->loadDataForDateRange($entity, $period['start_date'], $period['end_date']);
            
            // 3. Costos
            $days = Carbon::parse($period['start_date'])->diffInDays(Carbon::parse($period['end_date'])) + 1;
            $dailyCost = $days > 0 ? $period['total_amount'] / $days : 0;
            $costPerKwh = $period['total_kwh'] > 0 ? $period['total_amount'] / $period['total_kwh'] : 0;

            $evolution[] = [
                'label' => Carbon::parse($period['end_date'])->locale('es')->translatedFormat('M y'),
                'billed' => (float)$period['total_kwh'],
                'theoretical' => (float)$theoreticalKwh,
                'recommended' => (float)($period['recommended_kwh'] ?? 0),
                'tanks' => [
                    't1' => (float)($period['tanks'][1] ?? 0),
                    't2' => (float)($period['tanks'][2] ?? 0),
                    't3' => (float)($period['tanks'][3] ?? 0),
                    't4' => (float)($period['tanks'][4] ?? 0),
                ],
                'climate' => [
                    'avg_temp' => (float)($climate['avg_temp'] ?? 0),
                    'hot_days' => (int)($climate['heating_days'] ?? 0), // En el legacy usan heating_days para calor
                    'cold_days' => (int)($climate['cooling_days'] ?? 0),
                ],
                'costs' => [
                    'daily' => (float)$dailyCost,
                    'per_kwh' => (float)$costPerKwh
                ]
            ];
        }

        return $evolution;
    }

    /**
     * Análisis de Coste por Equipo
     */
    public function equipmentCost(Request $request)
    {
        $entity = $this->getActiveEntity($request);
        if (!$entity) return redirect()->route('dashboard');

        $periods = $this->getUnifiedPeriods($entity)->sortByDesc('start_date')->values();
        $selectedPeriodId = $request->input('period_id', $periods->first()['id'] ?? null);
        $selectedPeriod = $periods->firstWhere('id', $selectedPeriodId);

        $equipmentData = [];
        $pricePerKwh = 0;

        if ($selectedPeriod) {
            $pricePerKwh = $selectedPeriod['total_kwh'] > 0 ? $selectedPeriod['total_amount'] / $selectedPeriod['total_kwh'] : 0;
            
            $invoiceIds = collect($selectedPeriod['invoices'])->pluck('id');
            $usages = \App\Models\EquipmentUsage::whereIn('invoice_id', $invoiceIds)
                ->with(['equipment.room', 'equipment.category'])
                ->get();

            $equipmentData = $usages->map(function($usage) use ($pricePerKwh) {
                $kwh = $usage->kwh_reconciled ?? $usage->consumption_kwh;
                return [
                    'id' => $usage->equipment->id,
                    'name' => $usage->equipment->name,
                    'room' => $usage->equipment->room->name ?? 'Sin área',
                    'category' => $usage->equipment->category->name ?? 'Sin categoría',
                    'kwh' => (float)$kwh,
                    'cost' => (float)($kwh * $pricePerKwh),
                    'hours' => $usage->daily_hours,
                ];
            })->sortByDesc('cost')->values();
        }

        return Inertia::render('Analisis/EquipmentCost', [
            'entity' => $entity,
            'periods' => $periods,
            'selectedPeriodId' => $selectedPeriodId,
            'equipmentData' => $equipmentData,
            'pricePerKwh' => $pricePerKwh
        ]);
    }

    /**
     * Gestión de Ajuste de Uso (Calibración)
     */
    public function usageAdjustment(Request $request)
    {
        $entity = $this->getActiveEntity($request);
        if (!$entity) return redirect()->route('dashboard');

        $unifications = $this->getUnifiedPeriods($entity)->sortBy('start_date')->values();

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
            'certeza' => ['label' => 'Tanque 1: Certeza Matemática', 'key' => 1, 'items' => []],
            'base_critica' => ['label' => 'Tanque 2: Base Crítica', 'key' => 2, 'items' => []],
            'climatizacion' => ['label' => 'Tanque 3: Climatización', 'key' => 3, 'items' => []],
            'uso_variable' => ['label' => 'Tanque 4: Uso Variable', 'key' => 4, 'items' => []],
        ];

        foreach ($rooms as $room) {
            foreach ($room->equipment as $equipment) {
                $usage = $usages[$equipment->id] ?? [
                    'equipment_id' => $equipment->id,
                    'avg_daily_use_hours' => $equipment->has_defined_pattern ? $equipment->avg_daily_use_hours : 0,
                    'usage_frequency' => $equipment->usage_frequency ?? 'diario',
                    'is_standby' => $equipment->is_standby,
                ];

                $tier = $this->getEquipmentTier($equipment);
                
                // Estructura para la UI
                $item = [
                    'id' => $equipment->id,
                    'name' => $equipment->name,
                    'brand' => $equipment->brand,
                    'model' => $equipment->model,
                    'room_name' => $room->name,
                    'nominal_power_w' => $equipment->nominal_power_w,
                    'usage' => $usage,
                    'is_validated' => $equipment->is_validated ?? false,
                    'has_defined_pattern' => $equipment->has_defined_pattern,
                    'is_standby' => $equipment->is_standby ?? false,
                    'category_name' => $equipment->category->name ?? '',
                    'type_name' => $equipment->type->name ?? '',
                    'usage_unit' => $equipment->type->usage_unit ?? 'hours',
                    'energy_per_cycle' => $equipment->type->energy_per_cycle,
                    'social_coefficient' => $equipment->type->social_coefficient ?? 0,
                    'cycles_per_period' => ($usage instanceof \App\Models\EquipmentUsage) ? $usage->cycles_per_period : ($usage['cycles_per_period'] ?? null),
                    'cycle_suggestion' => ($equipment->type->usage_unit === 'cycles' && $equipment->type->social_coefficient > 0) 
                        ? round($equipment->type->social_coefficient * ($entity->people_count ?? 1), 1)
                        : null,
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
     * Guardar el ajuste de sintonía fina (Solo contexto, sin motor)
     */
    public function saveContextOnly(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'usages' => 'required|array',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);
        
        DB::transaction(function() use ($request, $invoice) {
            foreach ($request->usages as $eqId => $data) {
                // Si el usuario envió ciclos por semana, convertimos a ciclos por periodo
                $cyclesPerPeriod = $data['cycles_per_period'] ?? null;
                if (isset($data['cycles_per_week']) && $data['cycles_per_week'] !== null) {
                    $invoice = Invoice::find($request->invoice_id);
                    $days = Carbon::parse($invoice->start_date)->diffInDays(Carbon::parse($invoice->end_date)) ?: 30;
                    $cyclesPerPeriod = $data['cycles_per_week'] * ($days / 7);
                }

                // 1. Guardar uso específico para este periodo (Factura)
                EquipmentUsage::updateOrCreate(
                    ['invoice_id' => $invoice->id, 'equipment_id' => $eqId],
                    [
                        'avg_daily_use_hours' => $data['avg_daily_use_hours'] ?? null,
                        'usage_frequency' => $data['usage_frequency'] ?? 'diario',
                        'cycles_per_period' => $cyclesPerPeriod,
                        'is_standby' => $data['is_standby'] ?? false,
                    ]
                );

                // 2. Actualizar la ficha técnica del equipo
                $equipment = \App\Models\Equipment::find($eqId);
                if ($equipment) {
                    $isFrozen = $data['has_defined_pattern'] ?? false;
                    // Si el usuario ingresó ciclos manualmente, forzamos el patrón definido
                    if ($cyclesPerPeriod !== null && $cyclesPerPeriod > 0) {
                        $isFrozen = true;
                    }
                    
                    $equipment->update(['has_defined_pattern' => $isFrozen]);
                    
                    if ($isFrozen) {
                        $equipment->update([
                            'avg_daily_use_hours' => $data['avg_daily_use_hours'] ?? null,
                            'usage_frequency' => $data['usage_frequency'] ?? 'diario',
                            'is_standby' => $data['is_standby'] ?? false,
                        ]);
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'Contexto guardado correctamente. Sintoniza el motor cuando estés listo.');
    }

    /**
     * Guardar contexto, ejecutar motor y mostrar resultados
     */
    public function calibrateAndShowResults(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'usages' => 'required|array',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);
        
        DB::transaction(function() use ($request, $invoice) {
            foreach ($request->usages as $eqId => $data) {
                // Conversión de ciclos por semana si aplica
                $cyclesPerPeriod = $data['cycles_per_period'] ?? null;
                if (isset($data['cycles_per_week']) && $data['cycles_per_week'] !== null) {
                    $days = Carbon::parse($invoice->start_date)->diffInDays(Carbon::parse($invoice->end_date)) ?: 30;
                    $cyclesPerPeriod = $data['cycles_per_week'] * ($days / 7);
                }

                // 1. Guardar uso del periodo
                EquipmentUsage::updateOrCreate(
                    ['invoice_id' => $invoice->id, 'equipment_id' => $eqId],
                    [
                        'avg_daily_use_hours' => $data['avg_daily_use_hours'] ?? null,
                        'usage_frequency' => $data['usage_frequency'] ?? 'diario',
                        'cycles_per_period' => $cyclesPerPeriod,
                        'is_standby' => $data['is_standby'] ?? false,
                    ]
                );

                // 2. Actualizar la ficha técnica del equipo (Patrón Fijo)
                $equipment = \App\Models\Equipment::find($eqId);
                if ($equipment) {
                    $isFrozen = $data['has_defined_pattern'] ?? false;
                    if ($cyclesPerPeriod !== null && $cyclesPerPeriod > 0) {
                        $isFrozen = true;
                    }
                    $equipment->update([
                        'has_defined_pattern' => $isFrozen,
                    ]);
                }
            }
        });

        // Ejecutar calibración sobre el periodo unificado
        $invoices = Invoice::where('contract_id', $invoice->contract_id)
            ->where('start_date', $invoice->start_date)
            ->where('end_date', $invoice->end_date)
            ->get();

        try {
            $result = $this->analysisService->calibrateUnifiedPeriod($invoices);
            
            return redirect()->route('analisis.usage.results', ['invoice' => $invoice->id])
                           ->with('engine_result', $result);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en el motor de cálculo: ' . $e->getMessage());
        }
    }

    /**
     * Mostrar vista de resultados del motor (Solo lectura)
     */
    public function showEngineResults(Request $request, Invoice $invoice)
    {
        $entity = $this->getActiveEntity($request);
        $engineResult = session('engine_result');

        if (!$engineResult) {
            return redirect()->route('analisis.usage.detail', [
                'contract' => $invoice->contract_id,
                'start_date' => $invoice->start_date,
                'end_date' => $invoice->end_date
            ])->with('warning', 'Ejecuta la sintonía para ver los resultados.');
        }

        return Inertia::render('Analisis/EngineResults', [
            'entity' => $entity,
            'invoice' => $invoice,
            'engine' => $engineResult,
            'period' => [
                'start_date' => $invoice->start_date,
                'end_date' => $invoice->end_date,
                'days' => \Carbon\Carbon::parse($invoice->start_date)->diffInDays(\Carbon\Carbon::parse($invoice->end_date)),
                'invoice_id' => $invoice->id,
            ]
        ]);
    }

    /**
     * Lógica de clasificación por Tiers
     */
    private function getEquipmentTier($equipment): string
    {
        // 1. Tanque 1: Certeza (Prioridad absoluta a la intención del usuario)
        if ($equipment->has_defined_pattern) {
            return 'certeza';
        }

        // 2. Tanque 2: Base Crítica (Refrigeración, Conectividad o uso continuo)
        if ($this->isCritical($equipment)) {
            return 'base_critica';
        }

        // 3. Tanque 3: Climatización (Sensible al clima)
        if ($equipment->type?->is_thermal_sensitive) {
            return 'climatizacion';
        }

        // 4. Tanque 4: Uso Variable (El resto)
        return 'uso_variable';
    }

    private function isCritical($equipment): bool
    {
        $criticalCategories = ['Refrigeración', 'Conectividad y Seguridad'];
        $categoryName = $equipment->category->name ?? $equipment->type?->category?->name ?? '';
        $hours = $equipment->avg_daily_use_hours ?? 0;
        
        return in_array($categoryName, $criticalCategories) || $hours >= 23.5;
    }

    private function getTopItemsForTank(array $items): array
    {
        usort($items, fn($a, $b) => ($b['kwh'] ?? 0) <=> ($a['kwh'] ?? 0));
        return array_slice($items, 0, 5);
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
