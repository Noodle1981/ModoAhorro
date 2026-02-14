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
            'base_critica' => ['label' => 'Base Crítica y 24hs', 'icon' => 'bi-shield-check', 'color' => 'red', 'desc' => 'Consumo continuo e indispensable (Heladeras, Wifi, Seguridad).', 'items' => collect()],
            'base_pesada'  => ['label' => 'Climatización y Gestión Térmica', 'icon' => 'bi-thermometer-sun', 'color' => 'blue', 'desc' => 'Equipos de gestión climática (Aires, Estufas, Ventiladores).', 'items' => collect()],
            'ballenas'     => ['label' => 'Uso Diario y Variable', 'icon' => 'bi-controller', 'color' => 'purple', 'desc' => 'Uso variable dependiente del usuario (TV, Lavarropas, Luces).', 'items' => collect()],
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
            
            // Normalizar frecuencia
            $freq = $data['usage_frequency'] ?? 'diario';
            if ($freq === 'diariamente') $freq = 'diario';
            $usage->usage_frequency = $freq;

            $usage->usage_count = $data['usage_count'] ?? null;
            $usage->avg_use_duration = $data['avg_use_duration'] ?? null;
            $usage->avg_daily_use_hours = $data['avg_daily_use_hours'] ?? null;
            
            // Guardar días de la semana seleccionados
            $daysOfWeek = isset($data['use_days_of_week']) ? implode(',', $data['use_days_of_week']) : '';
            $usage->use_days_of_week = $daysOfWeek;
            
            // Calcular cantidad de días en el periodo
            $daysInPeriod = Carbon::parse($invoice->start_date)->diffInDays(Carbon::parse($invoice->end_date));
            $daysInPeriod = max(1, $daysInPeriod);

            if (in_array($usage->usage_frequency, ['diario', 'semanal', 'diariamente'])) {
                if (!empty($data['use_days_of_week'])) {
                    $weeks = max(1, ceil($daysInPeriod / 7));
                    $usage->use_days_in_period = count($data['use_days_of_week']) * $weeks;
                } else {
                    $usage->use_days_in_period = $daysInPeriod;
                }
            } else {
                // Para otras frecuencias, aplicar ponderación usando la misma lógica que el Service (getDaysByFrequency)
                $factor = match($usage->usage_frequency) {
                    'casi_frecuentemente' => 0.85,
                    'frecuentemente'      => 0.60,
                    'ocasionalmente'      => 0.30,
                    'raramente'           => 0.10,
                    'nunca'               => 0.0,
                    default               => 0.60,
                };
                $usage->use_days_in_period = floor($daysInPeriod * $factor);
            }

            $usage->save();
            // Recalcular consumo automáticamente después de guardar
            $usage->consumption_kwh = $consumptionService->calculateEquipmentConsumption($usage, $invoice);
            $usage->save();
        }

        // Ejecutar Motor de Calibración v3 (Jerarquía de Confianza)
        $calibrationResult = $consumptionService->calibrateInvoiceConsumption($invoice);
        $summaryMsg = $calibrationResult['summary']['message'] ?? '';

        // Bloquear la factura si se solicita
        if ($request->has('lock_invoice')) {
            $invoice->usage_locked = true;
            $invoice->save();
            return redirect()->route($config['route_prefix'] . '.usage_adjustments', $entity->id)
                ->with('success', 'Ajuste guardado y periodo CERRADO. ' . $summaryMsg);
        }

        return redirect()->route($config['route_prefix'] . '.usage_adjustments', $entity->id)
            ->with('success', 'Ajuste guardado. ' . $summaryMsg);
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
    // Muestra el detalle del ajuste de uso para una factura
    public function show($invoiceId, \App\Services\ConsumptionAnalysisService $consumptionService)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $entity = $invoice->contract->entity;
        $config = config("entity_types.{$entity->type}", []);

        // 1. Ejecutar calibración/carga de datos climáticos PRIMERO para inicializar el motor
        $calibrationResult = $consumptionService->calibrateInvoiceConsumption($invoice);
        $apiSummary = $calibrationResult['summary'] ?? [];
        $climateData = $calibrationResult['climate_data'] ?? [];
        
        // Obtener todos los usos de equipos para la factura
        // 1. Obtener Usages
        // IMPORTANT: This relationship MUST include inactive equipment to preserve history.
        // Since we use a manual 'is_active' flag and not Laravel's SoftDeletes trait on the Equipment model,
        // the relationship naturally includes all records unless explicitly filtered.
        $equipmentUsages = $invoice->equipmentUsages()->with(['equipment.category', 'equipment.type'])->get();

        // Calcular consumo por equipo y totales
        $consumptionDetails = [];
        $totalCalculatedConsumption = 0;

        // Estructuras para resumen
        $tierStats = [
            'base_critica' => ['label' => 'Base Crítica', 'kwh' => 0, 'count' => 0, 'color' => 'red', 'icon' => 'bi-shield-check'],
            'base_pesada'  => ['label' => 'Climatización', 'kwh' => 0, 'count' => 0, 'color' => 'blue', 'icon' => 'bi-thermometer-sun'],
            'ballenas'     => ['label' => 'Consumo Variable', 'kwh' => 0, 'count' => 0, 'color' => 'purple', 'icon' => 'bi-controller'],
        ];

        foreach ($equipmentUsages as $usage) {
            // Ahora el Service ya tiene los datos climáticos cargados en el motor, 
            // por lo que calculateEquipmentConsumption usará los días efectivos correctos.
            $kwh = $consumptionService->calculateEquipmentConsumption($usage, $invoice);
            $consumptionDetails[$usage->equipment_id] = $kwh;
            $totalCalculatedConsumption += $kwh;

            // Clasificar por Tier
            $tier = $this->getEquipmentTier($usage->equipment);
            
            // Inyectar Tier al objeto usage para la vista
            $usage->tier = $tier; 
            $usage->tier_label = $tierStats[$tier]['label'];
            $usage->tier_color = $tierStats[$tier]['color'];

            // Acumular estadísticas
            if (isset($tierStats[$tier])) {
                $tierStats[$tier]['kwh'] += $kwh;
                $tierStats[$tier]['count']++;
            }

            // Mapear reconciliación desde el resultado de calibración previo
            $calibrado = collect($calibrationResult['usages'])->firstWhere('id', $usage->id);
            if ($calibrado) {
                $usage->kwh_reconciled = $calibrado->kwh_reconciled ?? null;
            }
        }

        // Agrupar por habitación
        $groupedUsages = $equipmentUsages->groupBy(function($usage) {
            return $usage->equipment->room->name ?? 'Sin habitación';
        });

        return view('usage_adjustments.show', compact('invoice', 'groupedUsages', 'consumptionDetails', 'totalCalculatedConsumption', 'config', 'entity', 'tierStats', 'apiSummary', 'climateData'));
    }

    // --- Métodos "ForEntity" para manejar las rutas anidadas con el parámetro {entity} ---

    public function editForEntity($entityId, $invoiceId)
    {
        // Podríamos validar que la factura pertenezca a la entidad si fuera necesario
        return $this->edit($invoiceId);
    }

    public function updateForEntity(Request $request, $entityId, $invoiceId, \App\Services\ConsumptionAnalysisService $consumptionService)
    {
        return $this->update($request, $invoiceId, $consumptionService);
    }

    public function unlockForEntity($entityId, $invoiceId)
    {
        return $this->unlock($invoiceId);
    }

    public function showForEntity($entityId, $invoiceId, \App\Services\ConsumptionAnalysisService $consumptionService)
    {
        return $this->show($invoiceId, $consumptionService);
    }
}
