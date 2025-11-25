<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Services\ConsumptionAnalysisService;

class ConsumptionPanelController extends Controller
{
    public function index()
    {
        // Usamos la factura id 1 como ejemplo
        $invoice = Invoice::with(['contract.entity.locality', 'equipmentUsages.equipment.category', 'equipmentUsages.equipment.type'])->find(1);
        $climateService = new \App\Services\Climate\ClimateDataService();
        $usageSuggestionService = new \App\Services\Climate\UsageSuggestionService($climateService);
        $service = new \App\Services\ConsumptionAnalysisService($usageSuggestionService, $climateService);

        $consumos = [];
        foreach ($invoice->equipmentUsages as $usage) {
            $consumos[$usage->equipment_id] = $usage->consumption_kwh ?? 0;
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

        return view('consumption.panel', [
            'invoice' => $invoice,
            'totalPotencia' => $totalPotencia,
            'totalEnergia' => $totalEnergia,
            'consumos' => $consumos,
            'consumoPorCategoria' => $consumoPorCategoria,
        ]);
    }
}
