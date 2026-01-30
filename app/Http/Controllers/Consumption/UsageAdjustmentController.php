<?php

namespace App\Http\Controllers\Consumption;

use App\Http\Controllers\Controller;
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

    // Muestra listado de facturas para una entidad específica
    public function indexForEntity($entityId)
    {
        $entity = \App\Models\Entity::findOrFail($entityId);
        $config = config("entity_types.{$entity->type}", []);
        
        // Obtener facturas solo de esta entidad
        $invoices = Invoice::with('usageAdjustment')
            ->whereHas('contract', function ($q) use ($entityId) {
                $q->where('entity_id', $entityId);
            })
            ->get();
        
        return view('usage_adjustments.index', compact('invoices', 'entity', 'config'));
    }

    // Muestra el formulario de ajuste para una factura
    public function edit($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $contract = $invoice->contract;
        $entity = $contract ? $contract->entity : null;
        $config = $entity ? config("entity_types.{$entity->type}", []) : [];
        
        // Verificar si está bloqueado
        if ($invoice->usage_locked) {
            $redirectRoute = $entity ? ($config['route_prefix'] . '.usage_adjustments') : 'usage_adjustments.index';
            return redirect()->route($redirectRoute, $entity ? $entity->id : null)->with('warning', 
                '⚠️ Este período está cerrado. Debes reabrirlo si deseas hacer cambios.'
            );
        }

        $usageAdjustment = $invoice->usageAdjustment;

        // Obtener los usos ya registrados para este periodo
        $usages = $invoice->equipmentUsages()->get()->keyBy('equipment_id');
        
        // --- REFINAMIENTO v3: Pre-poblar $usages con datos de la habitación si no existen ---
        if ($entity) {
            $rooms = $entity->rooms()->with(['equipment' => function($q) use ($invoice) {
                $q->where(function($query) use ($invoice) {
                    $query->where(function($q) use ($invoice) {
                        $q->whereNull('installed_at')
                          ->orWhere('installed_at', '<=', $invoice->end_date);
                    })
                    ->where(function($q) use ($invoice) {
                        $q->whereNull('removed_at')
                          ->orWhere('removed_at', '>=', $invoice->start_date);
                    });
                })->with(['type', 'room']);
            }])->get();

            foreach ($rooms as $room) {
                foreach ($room->equipment as $equipment) {
                    if (!isset($usages[$equipment->id])) {
                        // Crear un objeto usage "virtual" con los defaults de la habitación
                        $virtualUsage = new \App\Models\EquipmentUsage([
                            'equipment_id' => $equipment->id,
                            'avg_daily_use_hours' => $equipment->avg_daily_use_hours,
                            'usage_frequency' => $equipment->usage_frequency ?? 'diario',
                        ]);
                        $usages[$equipment->id] = $virtualUsage;
                    }
                }
            }
        }

        // Agrupar equipos por Tiers
        // Agrupar equipos por Tiers
        $equipmentTiers = [
            'base_critica' => ['label' => 'Tanque 1: Base Crítica', 'icon' => 'bi-shield-check', 'color' => 'red', 'desc' => 'Consumo continuo e indispensable (24hs).', 'items' => collect()],
            'base_pesada'  => ['label' => 'Tanque 2: Climatización', 'icon' => 'bi-thermometer-sun', 'color' => 'blue', 'desc' => 'Equipos de gestión climática.', 'items' => collect()],
            'ballenas'     => ['label' => 'Tanque 3: Elasticidad / Rutina', 'icon' => 'bi-controller', 'color' => 'purple', 'desc' => 'Uso variable dependiente del usuario.', 'items' => collect()],
        ];

        foreach ($rooms as $room) {
            foreach ($room->equipment as $equipment) {
                $tier = $this->getEquipmentTier($equipment);
                $equipmentTiers[$tier]['items']->push($equipment);
            }
        }

        return view('usage_adjustments.integral_motor', compact('invoice', 'usageAdjustment', 'rooms', 'usages', 'config', 'entity', 'equipmentTiers'));
    }

    private function getEquipmentTier($equipment)
    {
        // 1. Tanque 2: Climatización (Prioridad por tipo)
        if ($equipment->type && $equipment->type->is_climatization) {
            return 'base_pesada'; // Usamos este key para mantener compatibilidad con la vista por ahora
        }

        // 2. Tanque 1: Base Crítica (24hs y Diariamente)
        // Verificamos si tiene un uso asignado o usamos los defaults del tipo
        $hours = $equipment->avg_daily_use_hours ?? $equipment->type->default_avg_daily_use_hours;
        $freq = $equipment->usage_frequency ?? 'diariamente'; 
        
        // Si es 24hs (o cerca) y diario, es base crítica
        if ($hours >= 23 && ($freq == 'diariamente' || $freq == 'diario')) {
            return 'base_critica';
        }

        // 3. Tanque 3: Elasticidad / Rutina (Resto)
        return 'ballenas'; 
    }

    // Guarda el ajuste realizado
    public function update(Request $request, $invoiceId, \App\Services\ConsumptionAnalysisService $consumptionService)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $entity = $invoice->contract->entity;
        $config = config("entity_types.{$entity->type}", []);
        
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
            return redirect()->route($config['route_prefix'] . '.usage_adjustments', $entity->id)->with('success', 'Ajuste guardado y periodo CERRADO correctamente.');
        }

        return redirect()->route($config['route_prefix'] . '.usage_adjustments', $entity->id)->with('success', 'Ajuste guardado correctamente.');
    }

    // Desbloquear factura
    public function unlock($invoiceId)
    {
        $invoice = Invoice::with('contract.entity')->findOrFail($invoiceId);
        $invoice->usage_locked = false;
        $invoice->save();

        return redirect()->back()->with('success', 'Periodo reabierto correctamente. Ahora puedes editar los ajustes.');
    }

    // Muestra el detalle del ajuste de uso para una factura
    public function show($invoiceId, \App\Services\ConsumptionAnalysisService $consumptionService)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $entity = $invoice->contract->entity;
        $config = config("entity_types.{$entity->type}", []);
        
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

        return view('usage_adjustments.show', compact('invoice', 'groupedUsages', 'consumptionDetails', 'totalCalculatedConsumption', 'config', 'entity'));
    }
}
