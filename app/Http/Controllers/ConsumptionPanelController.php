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
        $invoice = Invoice::with(['contract', 'equipmentUsages.equipment'])->find(1);
        $service = new ConsumptionAnalysisService();
        $consumos = $service->calculateInvoiceConsumption($invoice);
        
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
