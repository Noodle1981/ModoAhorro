<?php
namespace App\Http\Controllers\Consumption;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Services\ConsumptionAnalysisService;

class PanelController extends Controller
{
    public function index()
    {
        // Obtener todas las facturas para el gráfico (Historical Evolution)
        $allInvoices = Invoice::with(['contract.entity.locality', 'equipmentUsages.equipment.category', 'usageAdjustment'])
            ->orderBy('start_date', 'asc') // Chronological for chart
            ->get();

        // Obtener facturas paginadas para la tabla (Reverse chronological)
        $paginatedInvoices = Invoice::with(['contract.entity.locality', 'equipmentUsages.equipment.category', 'usageAdjustment'])
            ->orderBy('start_date', 'desc')
            ->paginate(5); // 5 per page as requested

        // Initialize Services
        $climateService = new \App\Services\Climate\ClimateDataService();
        $usageSuggestionService = new \App\Services\Climate\UsageSuggestionService($climateService);
        $consumptionCalibrator = new \App\Services\ConsumptionCalibrator();
        $maintenanceService = new \App\Services\MaintenanceService();
        $service = new \App\Services\ConsumptionAnalysisService($usageSuggestionService, $climateService, $consumptionCalibrator, $maintenanceService);

        // --- Process Chart Data (Full History) ---
        // --- Process Chart Data (Full History) ---
        $chartData = $allInvoices->map(function ($invoice) use ($service, $climateService) {
            $calibratedUsages = $service->calibrateInvoiceConsumption($invoice);
            $totalEnergia = $calibratedUsages->sum('kwh_reconciled');
            
            $startDate = \Carbon\Carbon::parse($invoice->start_date);
            $endDate = \Carbon\Carbon::parse($invoice->end_date);
            $days = max(1, $startDate->diffInDays($endDate));
            $dailyAvg = $totalEnergia / $days;

            // Fetch Climate Data for visual correlation
            $locality = $invoice->contract->entity->locality ?? null;
            $avgTemp = null;
            $hotDays = 0;
            $coldDays = 0;
            
            if ($locality && $locality->latitude && $locality->longitude) {
                 // Ensure data is loaded (this checks cache/DB first)
                 $climateService->loadDataForInvoice($invoice);
                 
                 $stats = $climateService->getClimateStats(
                     $locality->latitude, 
                     $locality->longitude, 
                     $startDate, 
                     $endDate
                 );
                 $avgTemp = $stats['avg_temp_avg'] ?? null;
                 $hotDays = $stats['hot_days_count'] ?? 0;
                 $coldDays = $stats['cold_days_count'] ?? 0;
            }

            // Financial Metrics
            $dailyCost = $invoice->total_amount / $days;
            $costPerKwh = $totalEnergia > 0 ? ($invoice->total_amount / $totalEnergia) : 0;

            return [
                'label' => $startDate->format('M Y'),
                'consumption' => round($totalEnergia, 2),
                'cost' => $invoice->total_amount,
                'avg' => round($dailyAvg, 2),
                'avg_temp' => $avgTemp, 
                'daily_cost' => round($dailyCost, 2),
                'cost_per_kwh' => round($costPerKwh, 2),
                'hot_days' => $hotDays, // New
                'cold_days' => $coldDays // New
            ];
        });

        // --- Process Table Data (Paginated) ---
        // We will process the processed metrics on the fly in the view or here.
        // Let's process here to pass clean objects to view.
        // Note: Pagination returns a LengthAwarePaginator. We can transform items but must keep it as paginator.
        
        $paginatedInvoices->getCollection()->transform(function ($invoice) use ($service, $climateService) {
             $calibratedUsages = $service->calibrateInvoiceConsumption($invoice);
             $totalEnergia = $calibratedUsages->sum('kwh_reconciled');
             $consumoFacturado = $invoice->total_energy_consumed_kwh ?? 0;
             $porcentaje = $consumoFacturado > 0 ? ($totalEnergia / $consumoFacturado) * 100 : 0;
             
             // Metrics
             $startDate = \Carbon\Carbon::parse($invoice->start_date);
             $endDate = \Carbon\Carbon::parse($invoice->end_date);
             $days = max(1, $startDate->diffInDays($endDate));
             $dailyAvg = $totalEnergia / $days;
             $costPerKwh = $totalEnergia > 0 ? ($invoice->total_amount / $totalEnergia) : 0;

             // Fetch Climate Data for Table
             $locality = $invoice->contract->entity->locality ?? null;
             $hotDays = 0;
             
             if ($locality && $locality->latitude && $locality->longitude) {
                 // Ensure data is loaded (this checks cache/DB first)
                 $climateService->loadDataForInvoice($invoice);
                 
                 $stats = $climateService->getClimateStats(
                     $locality->latitude, 
                     $locality->longitude, 
                     $startDate, 
                     $endDate
                 );
                 $hotDays = $stats['hot_days_count'] ?? 0;
             }
             
             // Status Logic
             $isAdjusted = $invoice->usageAdjustment && $invoice->usageAdjustment->adjusted;
             $status = 'exact'; // default
             if ($porcentaje > 130 || $porcentaje < 70) $status = 'critical';
             elseif ($porcentaje > 110 || $porcentaje < 90) $status = 'warning';
             elseif ($porcentaje >= 90 && $porcentaje <= 110) $status = 'exact';
             
             // Attach simplified data to invoice object for easy access in view
             $invoice->calculated_metrics = (object) [
                 'total_kwh_calculated' => $totalEnergia,
                 'total_kwh_billed' => $consumoFacturado,
                 'deviation_percent' => $porcentaje,
                 'status' => $status,
                 'is_adjusted' => $isAdjusted,
                 'daily_avg' => $dailyAvg,
                 'cost_per_kwh' => $costPerKwh,
                 'days' => $days,
                 'hot_days' => $hotDays
             ];
             
             return $invoice;
        });

        return view('consumption.panel', [
            'invoices' => $paginatedInvoices, // Updated variable name for clarity
            'chartData' => $chartData,
        ]);
    }

    // Muestra panel de consumo para una entidad específica
    public function showForEntity($entityId)
    {
        $entity = \App\Models\Entity::findOrFail($entityId);
        $config = config("entity_types.{$entity->type}", []);

        // Obtener facturas solo de esta entidad
        $paginatedInvoices = Invoice::with(['contract.entity.locality', 'equipmentUsages.equipment.category', 'usageAdjustment'])
            ->whereHas('contract', function ($q) use ($entityId) {
                $q->where('entity_id', $entityId);
            })
            ->orderBy('start_date', 'desc')
            ->paginate(5);

        // Initialize Services
        $climateService = new \App\Services\Climate\ClimateDataService();
        $usageSuggestionService = new \App\Services\Climate\UsageSuggestionService($climateService);
        $consumptionCalibrator = new \App\Services\ConsumptionCalibrator();
        $maintenanceService = new \App\Services\MaintenanceService();
        $service = new \App\Services\ConsumptionAnalysisService($usageSuggestionService, $climateService, $consumptionCalibrator, $maintenanceService);

        // Process paginated invoices
        $paginatedInvoices->getCollection()->transform(function ($invoice) use ($service, $climateService) {
            $calibratedUsages = $service->calibrateInvoiceConsumption($invoice);
            $totalEnergia = $calibratedUsages->sum('kwh_reconciled');
            $consumoFacturado = $invoice->total_energy_consumed_kwh ?? 0;
            $porcentaje = $consumoFacturado > 0 ? ($totalEnergia / $consumoFacturado) * 100 : 0;
            
            $startDate = \Carbon\Carbon::parse($invoice->start_date);
            $endDate = \Carbon\Carbon::parse($invoice->end_date);
            $days = max(1, $startDate->diffInDays($endDate));
            $dailyAvg = $totalEnergia / $days;
            $costPerKwh = $totalEnergia > 0 ? ($invoice->total_amount / $totalEnergia) : 0;

            $isAdjusted = $invoice->usageAdjustment && $invoice->usageAdjustment->adjusted;
            $status = 'exact';
            if ($porcentaje > 130 || $porcentaje < 70) $status = 'critical';
            elseif ($porcentaje > 110 || $porcentaje < 90) $status = 'warning';
            
            $invoice->calculated_metrics = (object) [
                'total_kwh_calculated' => $totalEnergia,
                'total_kwh_billed' => $consumoFacturado,
                'deviation_percent' => $porcentaje,
                'status' => $status,
                'is_adjusted' => $isAdjusted,
                'daily_avg' => $dailyAvg,
                'cost_per_kwh' => $costPerKwh,
                'days' => $days,
            ];
            
            return $invoice;
        });

        // Chart data for this entity
        $allInvoices = Invoice::with(['contract.entity.locality', 'equipmentUsages.equipment.category'])
            ->whereHas('contract', function ($q) use ($entityId) {
                $q->where('entity_id', $entityId);
            })
            ->orderBy('start_date', 'asc')
            ->get();

        $chartData = $allInvoices->map(function ($invoice) use ($service) {
            $calibratedUsages = $service->calibrateInvoiceConsumption($invoice);
            $totalEnergia = $calibratedUsages->sum('kwh_reconciled');
            $startDate = \Carbon\Carbon::parse($invoice->start_date);
            $endDate = \Carbon\Carbon::parse($invoice->end_date);
            $days = max(1, $startDate->diffInDays($endDate));
            
            return [
                'label' => $startDate->format('M Y'),
                'consumption' => round($totalEnergia, 2),
                'cost' => $invoice->total_amount,
                'avg' => round($totalEnergia / $days, 2),
            ];
        });

        return view('consumption.panel', [
            'invoices' => $paginatedInvoices,
            'chartData' => $chartData,
            'entity' => $entity,
            'config' => $config,
        ]);
    }

    public function cards()
    {
        // Full list for cards view (or paginated too? Let's assume full or larger pagination)
        // User asked to "move" the cards.
        $invoices = Invoice::with(['contract.entity.locality', 'equipmentUsages.equipment.category', 'usageAdjustment'])
            ->orderBy('start_date', 'desc')
            ->get();

        $climateService = new \App\Services\Climate\ClimateDataService();
        $usageSuggestionService = new \App\Services\Climate\UsageSuggestionService($climateService);
        $consumptionCalibrator = new \App\Services\ConsumptionCalibrator();
        $maintenanceService = new \App\Services\MaintenanceService();
        $service = new \App\Services\ConsumptionAnalysisService($usageSuggestionService, $climateService, $consumptionCalibrator, $maintenanceService);

        $invoicesData = [];
        foreach ($invoices as $invoice) {
            $calibratedUsages = $service->calibrateInvoiceConsumption($invoice);
            $totalEnergia = $calibratedUsages->sum('kwh_reconciled');
            $consumoFacturado = $invoice->total_energy_consumed_kwh ?? 0;
            $porcentaje = $consumoFacturado > 0 ? ($totalEnergia / $consumoFacturado) * 100 : 0;

            // Determinar color y mensaje según precisión (Logic as before)
            $color = 'secondary';
            $mensaje = 'Sin datos';
            if ($porcentaje >= 90 && $porcentaje <= 110) {
                $color = 'success'; 
                $mensaje = 'Excelente precisión';
            } elseif ($porcentaje >= 70 && $porcentaje < 90) {
                $color = 'warning'; 
                $mensaje = 'Diferencia aceptable';
            } elseif ($porcentaje > 110 && $porcentaje <= 130) {
                $color = 'warning'; 
                $mensaje = 'Diferencia aceptable';
            } elseif ($porcentaje > 0) {
                $color = 'danger'; 
                $mensaje = 'Revisar ajustes';
            }

            $isAdjusted = $invoice->usageAdjustment && $invoice->usageAdjustment->adjusted;
            $startDate = \Carbon\Carbon::parse($invoice->start_date);
            $endDate = \Carbon\Carbon::parse($invoice->end_date);
            $days = max(1, $startDate->diffInDays($endDate));
            $dailyAvg = $totalEnergia / $days;
            $costPerKwh = $totalEnergia > 0 ? ($invoice->total_amount / $totalEnergia) : 0;

            $invoicesData[] = [
                'invoice' => $invoice,
                'totalEnergia' => $totalEnergia,
                'porcentaje' => $porcentaje,
                'color' => $color,
                'mensaje' => $mensaje,
                'isAdjusted' => $isAdjusted,
                'dailyAvg' => $dailyAvg,
                'costPerKwh' => $costPerKwh,
                'days' => $days,
            ];
        }

        return view('consumption.cards', compact('invoicesData'));
    }

    public function show($invoiceId)
    {
        // Vista detallada de una factura específica
        $invoice = Invoice::with(['contract.entity.locality', 'equipmentUsages.equipment.category', 'equipmentUsages.equipment.type', 'usageAdjustment'])
            ->findOrFail($invoiceId);
        
        $climateService = new \App\Services\Climate\ClimateDataService();
        $usageSuggestionService = new \App\Services\Climate\UsageSuggestionService($climateService);
        $consumptionCalibrator = new \App\Services\ConsumptionCalibrator();
        $maintenanceService = new \App\Services\MaintenanceService();
        $service = new \App\Services\ConsumptionAnalysisService($usageSuggestionService, $climateService, $consumptionCalibrator, $maintenanceService);
        $validationService = new \App\Services\Core\ValidationService();

        // ✅ CALCULAR EN TIEMPO REAL con el nuevo algoritmo (CALIBRADO)
        $calibratedUsages = $service->calibrateInvoiceConsumption($invoice);
        
        // Mapear para la vista: [equipment_id => kwh_reconciled]
        $consumos = $calibratedUsages->pluck('kwh_reconciled', 'equipment_id')->toArray();

        $totalPotencia = $invoice->equipmentUsages->sum(function($usage) {
            return $usage->equipment->nominal_power_w ?? 0;
        });

        $totalEnergia = $calibratedUsages->sum('kwh_reconciled');

        // Validación de Desviación
        $validation = $validationService->calculateDeviation($invoice, $totalEnergia);
        $suggestions = $validationService->getSuggestions($invoice, $totalEnergia);

        // Agrupar consumo por categoría
        $consumoPorCategoria = [];
        foreach ($invoice->equipmentUsages as $usage) {
            $catName = $usage->equipment->category->name ?? 'Sin Categoría';
            if (!isset($consumoPorCategoria[$catName])) {
                $consumoPorCategoria[$catName] = 0;
            }
            $consumoPorCategoria[$catName] += $consumos[$usage->equipment_id] ?? 0;
        }

        // Ordenar categorías por consumo descendente
        arsort($consumoPorCategoria);

        // Obtener datos climáticos
        $locality = $invoice->contract->entity->locality ?? null;
        $climateStats = null;
        
        if ($locality && $locality->latitude && $locality->longitude) {
            $startDate = \Carbon\Carbon::parse($invoice->start_date);
            $endDate = \Carbon\Carbon::parse($invoice->end_date);
            
            // Asegurar que los datos estén cargados
            $climateService->loadDataForInvoice($invoice);
            
            $climateStats = $climateService->getClimateStats(
                $locality->latitude,
                $locality->longitude,
                $startDate,
                $endDate
            );
        }

        return view('consumption.show', [
            'invoice' => $invoice,
            'totalPotencia' => $totalPotencia,
            'totalEnergia' => $totalEnergia,
            'consumos' => $consumos,
            'consumoPorCategoria' => $consumoPorCategoria,
            'calibratedUsages' => $calibratedUsages, // Pasar la colección completa para acceder a status
            'climateStats' => $climateStats,
            'validation' => $validation,
            'suggestions' => $suggestions,
        ]);
    }
}
