<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\UsageAdjustment;
use Carbon\Carbon;

class UsageAdjustmentController extends Controller
{
    // Muestra listado de facturas y su estado de ajuste
    public function index()
    {
        $invoices = Invoice::with('usageAdjustment')->get();
        return view('usage_adjustments.index', compact('invoices'));
    }

    // Muestra el formulario de ajuste para una factura
    public function edit($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        
        // Verificar si está bloqueado
        if ($invoice->usage_locked) {
            return redirect()->route('usage_adjustments.index')->with('warning', 
                '⚠️ Este período está cerrado. Debes reabrirlo si deseas hacer cambios.'
            );
        }

        $usageAdjustment = $invoice->usageAdjustment;

        // Obtener la entidad asociada a la factura
        $contract = $invoice->contract;
        $entity = $contract ? $contract->entity : null;

        // Obtener los usos ya registrados para este periodo
        $usages = $invoice->equipmentUsages()->get()->keyBy('equipment_id');
        
        // Obtener habitaciones con sus equipos activos O que tengan uso en esta factura
        // Y FILTRAR por fechas de instalación/retiro
        $rooms = collect();
        if ($entity) {
            $rooms = $entity->rooms()->with(['equipment' => function($q) use ($invoice) {
                $q->where(function($query) use ($invoice) {
                    // Equipo instalado antes o durante el período
                    $query->where(function($q) use ($invoice) {
                        $q->whereNull('installed_at')
                          ->orWhere('installed_at', '<=', $invoice->end_date);
                    })
                    // Y no removido antes del inicio del período
                    ->where(function($q) use ($invoice) {
                        $q->whereNull('removed_at')
                          ->orWhere('removed_at', '>=', $invoice->start_date);
                    });
                });
            }])->get();
        }

        return view('usage_adjustments.edit', compact('invoice', 'usageAdjustment', 'rooms', 'usages'));
    }

    // Guarda el ajuste realizado
    public function update(Request $request, $invoiceId, \App\Services\ConsumptionAnalysisService $consumptionService)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        
        if ($invoice->usage_locked) {
            return redirect()->back()->with('error', 'El periodo está cerrado.');
        }

        $usageAdjustment = $invoice->usageAdjustment;
        if (!$usageAdjustment) {
            $usageAdjustment = new UsageAdjustment();
            $usageAdjustment->invoice_id = $invoice->id;
        }
        $usageAdjustment->adjusted = true;
        $usageAdjustment->adjusted_at = Carbon::now();
        $usageAdjustment->notes = $request->input('notes');
        $usageAdjustment->save();

        // Guardar ajustes de uso por equipo
        $usages = $request->input('usages', []);
        foreach ($usages as $equipmentId => $data) {
            $usage = $invoice->equipmentUsages()->where('equipment_id', $equipmentId)->first();
            if (!$usage) {
                $usage = new \App\Models\EquipmentUsage();
                $usage->invoice_id = $invoice->id;
                $usage->equipment_id = $equipmentId;
            }
            $usage->is_standby = isset($data['is_standby']) ? 1 : 0;
            $usage->usage_frequency = $data['usage_frequency'] ?? 'diario';
            $usage->usage_count = $data['usage_count'] ?? null;
            $usage->avg_use_duration = $data['avg_use_duration'] ?? null;
            $usage->avg_daily_use_hours = $data['avg_daily_use_hours'] ?? null;
            // Guardar días de la semana seleccionados
            $daysOfWeek = isset($data['use_days_of_week']) ? implode(',', $data['use_days_of_week']) : '';
            $usage->use_days_of_week = $daysOfWeek;
            // Calcular cantidad de días en el periodo solo si es diario/semanal
            if (in_array($usage->usage_frequency, ['diario', 'semanal'])) {
                $weeks = max(1, ceil((strtotime($invoice->end_date) - strtotime($invoice->start_date)) / (60*60*24*7)));
                $usage->use_days_in_period = count($data['use_days_of_week'] ?? []) * $weeks;
            } else {
                $usage->use_days_in_period = null;
            }

            $usage->save();
            // Recalcular consumo automáticamente después de guardar
            $usage->consumption_kwh = $consumptionService->calculateEquipmentConsumption($usage, $invoice);
            $usage->save();
        }

        // Bloquear la factura si se solicita
        if ($request->has('lock_invoice')) {
            $invoice->usage_locked = true;
            $invoice->save();
            return redirect()->route('usage_adjustments.index')->with('success', 'Ajuste guardado y periodo CERRADO correctamente.');
        }

        return redirect()->route('usage_adjustments.index')->with('success', 'Ajuste guardado correctamente.');
    }

    // Desbloquear factura
    public function unlock($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $invoice->usage_locked = false;
        $invoice->save();

        return redirect()->back()->with('success', 'Periodo reabierto correctamente. Ahora puedes editar los ajustes.');
    }

    // Muestra el detalle del ajuste de uso para una factura
    public function show($invoiceId, \App\Services\ConsumptionAnalysisService $consumptionService)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        
        // Obtener todos los usos de equipos para la factura
        $equipmentUsages = $invoice->equipmentUsages()->with(['equipment.room'])->get();

        // Calcular consumo por equipo
        $consumptionDetails = [];
        $totalCalculatedConsumption = 0;

        foreach ($equipmentUsages as $usage) {
            $kwh = $consumptionService->calculateEquipmentConsumption($usage, $invoice);
            $consumptionDetails[$usage->equipment_id] = $kwh;
            $totalCalculatedConsumption += $kwh;
        }

        // Agrupar por habitación
        $groupedUsages = $equipmentUsages->groupBy(function($usage) {
            return $usage->equipment->room->name ?? 'Sin habitación';
        });

        return view('usage_adjustments.show', compact('invoice', 'groupedUsages', 'consumptionDetails', 'totalCalculatedConsumption'));
    }
}
