<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Services\ConsumptionAnalysisService;

class ConsumptionPanelController extends Controller
{
    public function index()
    {
        // Obtener todas las facturas con sus relaciones
        $invoices = Invoice::with(['contract.entity.locality', 'equipmentUsages.equipment.category', 'usageAdjustment'])
            ->orderBy('start_date', 'desc')
            ->get();

        $climateService = new \App\Services\Climate\ClimateDataService();
        $usageSuggestionService = new \App\Services\Climate\UsageSuggestionService($climateService);
        $consumptionCalibrator = new \App\Services\ConsumptionCalibrator();
        $maintenanceService = new \App\Services\MaintenanceService();
        $service = new \App\Services\ConsumptionAnalysisService($usageSuggestionService, $climateService, $consumptionCalibrator, $maintenanceService);

        // Procesar métricas para cada factura
        $invoicesData = [];
        foreach ($invoices as $invoice) {
            // ✅ CALCULAR EN TIEMPO REAL con el nuevo algoritmo (CALIBRADO)
            $calibratedUsages = $service->calibrateInvoiceConsumption($invoice);
            $totalEnergia = $calibratedUsages->sum('kwh_reconciled');
            $consumoFacturado = $invoice->total_energy_consumed_kwh ?? 0;
            $porcentaje = $consumoFacturado > 0 ? ($totalEnergia / $consumoFacturado) * 100 : 0;

            // Determinar color y mensaje según precisión
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

            // Verificar si está ajustado
            $isAdjusted = $invoice->usageAdjustment && $invoice->usageAdjustment->adjusted;

            // Calcular métricas adicionales
            $startDate = \Carbon\Carbon::parse($invoice->start_date);
            $endDate = \Carbon\Carbon::parse($invoice->end_date);
            $days = $startDate->diffInDays($endDate);
            $days = $days > 0 ? $days : 1; // Evitar división por cero

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

        // Preparar datos para el gráfico (Orden cronológico)
        $chartData = collect($invoicesData)->sortBy(function ($data) {
            return $data['invoice']->start_date;
        })->values()->map(function ($data) {
            return [
                'label' => \Carbon\Carbon::parse($data['invoice']->start_date)->format('M Y'),
                'consumption' => round($data['totalEnergia'], 2),
                'cost' => $data['invoice']->total_amount,
                'avg' => round($data['dailyAvg'], 2)
            ];
        });

        return view('consumption.panel', [
            'invoicesData' => $invoicesData,
            'chartData' => $chartData,
        ]);
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
