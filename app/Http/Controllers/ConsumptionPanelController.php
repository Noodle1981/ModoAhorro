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
        $service = new \App\Services\ConsumptionAnalysisService($usageSuggestionService, $climateService);

        // Procesar métricas para cada factura
        $invoicesData = [];
        foreach ($invoices as $invoice) {
            // ✅ CALCULAR EN TIEMPO REAL con el nuevo algoritmo
            $consumos = [];
            foreach ($invoice->equipmentUsages as $usage) {
                // Usar el servicio para calcular con la nueva fórmula (sin efficiency + ajuste climático)
                $consumos[$usage->equipment_id] = $service->calculateEquipmentConsumption($usage, $invoice);
            }

            $totalEnergia = array_sum($consumos);
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

            $invoicesData[] = [
                'invoice' => $invoice,
                'totalEnergia' => $totalEnergia,
                'porcentaje' => $porcentaje,
                'color' => $color,
                'mensaje' => $mensaje,
                'isAdjusted' => $isAdjusted,
            ];
        }

        return view('consumption.panel', [
            'invoicesData' => $invoicesData,
        ]);
    }

    public function show($invoiceId)
    {
        // Vista detallada de una factura específica
        $invoice = Invoice::with(['contract.entity.locality', 'equipmentUsages.equipment.category', 'equipmentUsages.equipment.type', 'usageAdjustment'])
            ->findOrFail($invoiceId);
        
        $climateService = new \App\Services\Climate\ClimateDataService();
        $usageSuggestionService = new \App\Services\Climate\UsageSuggestionService($climateService);
        $service = new \App\Services\ConsumptionAnalysisService($usageSuggestionService, $climateService);

        // ✅ CALCULAR EN TIEMPO REAL con el nuevo algoritmo
        $consumos = [];
        foreach ($invoice->equipmentUsages as $usage) {
            // Usar el servicio para calcular con la nueva fórmula (sin efficiency + ajuste climático)
            $consumos[$usage->equipment_id] = $service->calculateEquipmentConsumption($usage, $invoice);
        }

        $totalPotencia = $invoice->equipmentUsages->sum(function($usage) {
            return $usage->equipment->nominal_power_w ?? 0;
        });

        $totalEnergia = array_sum($consumos);

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

        return view('consumption.show', [
            'invoice' => $invoice,
            'totalPotencia' => $totalPotencia,
            'totalEnergia' => $totalEnergia,
            'consumos' => $consumos,
            'consumoPorCategoria' => $consumoPorCategoria,
        ]);
    }
}
