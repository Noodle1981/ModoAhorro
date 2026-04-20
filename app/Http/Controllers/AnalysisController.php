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

class AnalysisController extends Controller
{
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
        $user = $request->user();
        $activeEntityId = session('active_entity_id');
        
        $entity = $user->entities()->with('contracts.invoices')->where('entities.id', $activeEntityId)->first();
        if (!$entity) return redirect()->route('dashboard');

        // 1. Desglose por Categoría (basado en la última factura calibrada o teórica si no hay)
        $latestInvoice = Invoice::whereHas('contract', function($q) use ($entity) {
            $q->where('entity_id', $entity->id);
        })->orderBy('end_date', 'desc')->first();

        $categoryBreakdown = [];
        if ($latestInvoice) {
            $categoryData = DB::table('equipment_usages')
                ->join('equipment', 'equipment_usages.equipment_id', '=', 'equipment.id')
                ->join('equipment_categories', 'equipment.category_id', '=', 'equipment_categories.id')
                ->where('equipment_usages.invoice_id', $latestInvoice->id)
                ->select('equipment_categories.name', DB::raw('SUM(COALESCE(kwh_reconciled, total_energy_consumed_kwh, 0)) as total_kwh'))
                ->groupBy('equipment_categories.name')
                ->get();
            
            foreach ($categoryData as $data) {
                $categoryBreakdown[] = [
                    'name' => $data->name,
                    'value' => (float)$data->total_kwh
                ];
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
            'history' => $history,
            'latestInvoice' => $latestInvoice
        ]);
    }

    /**
     * Gestión de Ajuste de Uso (Calibración)
     */
    public function usageAdjustment(Request $request)
    {
        $user = $request->user();
        $activeEntityId = session('active_entity_id');
        
        $entity = $user->entities()->where('entities.id', $activeEntityId)->first();
        if (!$entity) return redirect()->route('dashboard');

        $invoices = Invoice::whereHas('contract', function($q) use ($entity) {
            $q->where('entity_id', $entity->id);
        })
        ->orderBy('end_date', 'desc')
        ->get();

        return Inertia::render('Analisis/UsageAdjustment', [
            'entity' => $entity,
            'invoices' => $invoices
        ]);
    }

    /**
     * Ejecutar el motor de calibración para una factura
     */
    public function runAdjustment(Request $request, Invoice $invoice)
    {
        // Security check
        if ($request->user()->cannot('update', $invoice->contract->entity)) {
            abort(403);
        }

        try {
            $result = $this->analysisService->calibrateInvoiceConsumption($invoice);
            
            return redirect()->back()->with('success', 'Calibración finalizada con éxito. El Gemelo Digital se ha sincronizado.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en el motor de cálculo: ' . $e->getMessage());
        }
    }

    /**
     * Optimización de Horarios
     */
    public function gridOptimization(Request $request)
    {
        $user = $request->user();
        $activeEntityId = session('active_entity_id');
        
        $entity = $user->entities()->with('rooms.equipment.type')->where('entities.id', $activeEntityId)->first();
        if (!$entity) return redirect()->route('dashboard');

        // En esta fase, devolvemos una vista estática/dashboard que será poblada con lógica de desplazamiento
        return Inertia::render('Analisis/GridOptimization', [
            'entity' => $entity
        ]);
    }
}
