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
        $usageAdjustment = $invoice->usageAdjustment;

        // Obtener la entidad asociada a la factura
        $contract = $invoice->contract;
        $entity = $contract ? $contract->entity : null;

        // Obtener equipos activos en las rooms de la entidad
        $equipments = collect();
        if ($entity) {
            $equipments = $entity->rooms()->with(['equipment' => function($q) {
                $q->where('is_active', true);
            }])->get()->pluck('equipment')->flatten();
        }

        // Obtener los usos ya registrados para este periodo
        $usages = $invoice->equipmentUsages()->get()->keyBy('equipment_id');

        return view('usage_adjustments.edit', compact('invoice', 'usageAdjustment', 'equipments', 'usages'));
    }

    // Guarda el ajuste realizado
    public function update(Request $request, $invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
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
        }

        return redirect()->route('usage_adjustments.index')->with('success', 'Ajuste guardado correctamente.');
    }
    // Muestra el detalle del ajuste de uso para una factura
    public function show($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        // Obtener todos los usos de equipos para la factura
        $equipmentUsages = $invoice->equipmentUsages()->with(['equipment.room'])->get();
        return view('usage_adjustments.show', compact('invoice', 'equipmentUsages'));
    }
}
