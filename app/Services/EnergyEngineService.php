<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Equipment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class EnergyEngineService
{
    protected $climateService;
    protected $thermalService;
    protected $tank0;
    protected $tank1;
    protected $tank2;
    protected $tank3;
    protected $lastClimateDays = [];
    protected $isFallbackMode = false;

    public function __construct(
        ClimateService $climateService, 
        ThermalProfileService $thermalService,
        \App\Services\Tanks\Tank0CertaintyService $tank0,
        \App\Services\Tanks\Tank1BaseService $tank1,
        \App\Services\Tanks\Tank2ClimateService $tank2,
        \App\Services\Tanks\Tank3ElasticityService $tank3
    ) {
        $this->climateService = $climateService;
        $this->thermalService = $thermalService;
        $this->tank0 = $tank0;
        $this->tank1 = $tank1;
        $this->tank2 = $tank2;
        $this->tank3 = $tank3;
    }

    /**
     * Distribuye el consumo de la factura en los 3 Tanques y ajusta el consumo unitario de los equipos.
     * Retorna un reporte detallado del proceso.
     */
    public function processInvoice(Invoice $invoice, Collection $equipments): array
    {
        $entity = $invoice->contract->entity;
        $totalBillKwh = $invoice->bimonthly_consumption_kwh ?? $invoice->total_energy_consumed_kwh ?? $invoice->consumption_kwh ?? 0;
        $logs = [];

        // 1. CONTEXTO OPERATIVO
        $opContext = $this->calculateOperationalContext($entity, $invoice->start_date, $invoice->end_date);
        $logs[] = "[Contexto] Entidad: {$entity->type}. Días: {$opContext['total_days']}. Bolsa Inicial: " . number_format($totalBillKwh, 1) . " kWh";

        $remainingKwh = $totalBillKwh;

        // --- PASO 0: TANQUE 0 (CERTEZA) ---
        $resT0 = $this->tank0->process($equipments, $remainingKwh);
        $logs = array_merge($logs, $resT0['logs']);

        // --- PASO 1: TANQUE 1 (BASE INMUTABLE) ---
        $resT1 = $this->tank1->process($equipments, $remainingKwh, $opContext);
        $logs = array_merge($logs, $resT1['logs']);

        // ⚠️ DETECCIÓN TEMPRANA DE ANOMALÍAS (NotebookLM Suggestion)
        if ($remainingKwh < -0.5) { 
            $logs[] = "❌ [ANOMALÍA] El consumo base declarado (" . number_format($totalBillKwh - $remainingKwh, 1) . " kWh) supera el total de la factura.";
        }

        // --- PASO 2: TANQUE 2 (CLIMATIZACIÓN) ---
        $resT2 = $this->tank2->process($equipments, $remainingKwh, $opContext, $invoice, $this->isFallbackMode);
        $logs = array_merge($logs, $resT2['logs']);
        $this->lastClimateDays = $resT2['climate_data'] ?? [];

        // --- PASO 3: TANQUE 3 (ELASTICIDAD/VARIABLE) ---
        $resT3 = $this->tank3->process($equipments, $remainingKwh, $opContext);
        $logs = array_merge($logs, $resT3['logs']);

        // --- CÁLCULO FINAL ---
        $totalTheoretical = $resT0['consumption'] + $resT1['consumption'] + $resT2['consumption'] + ($resT3['processed_count'] > 0 ? $remainingKwh : 0);
        $totalAssigned = $resT0['consumption'] + $resT1['consumption'] + $resT2['consumption'] + $resT3['consumption'];
        
        $recommendedTotalKwh = $totalTheoretical;
        if ($totalBillKwh > 0) {
            $recommendedTotalKwh = max($totalBillKwh, min($totalBillKwh * 1.30, $totalTheoretical));
        }

        $invoice->update(['recommended_kwh' => $recommendedTotalKwh]);

        return [
            'total_bill' => $totalBillKwh,
            'theoretical_total' => $totalTheoretical,
            'calibrated_total' => $totalAssigned,
            'recommended_total_kwh' => $recommendedTotalKwh,
            'tank_1_certainty' => $resT0['consumption'],
            'tank_2_base' => $resT1['consumption'],
            'tank_3_climate' => $resT2['consumption'],
            'tank_4_elasticity' => $resT3['consumption'],
            'unassigned_remainder' => $totalBillKwh - $totalAssigned,
            'equipments_processed' => $equipments->count(),
            'logs' => $logs,
            'climate_data' => $this->lastClimateDays
        ];
    }

    public function setFallbackMode(bool $isFallback): self
    {
        $this->isFallbackMode = $isFallback;
        return $this;
    }

    /**
     * Calcula el contexto operativo basado en horarios y días laborables de la entidad.
     */
    protected function calculateOperationalContext($entity, $startDate, $endDate): array
    {
        $dailyHours = 24;
        if ($entity->opens_at && $entity->closes_at) {
            $start = \Carbon\Carbon::parse($entity->opens_at);
            $end = \Carbon\Carbon::parse($entity->closes_at);
            $dailyHours = $start->diffInMinutes($end) / 60;
            if ($dailyHours <= 0) $dailyHours = 24;
        }

        $totalDays = \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1;
        $workDays = $totalDays;

        if ($entity->operating_days && is_array($entity->operating_days) && count($entity->operating_days) < 7) {
            $workDays = 0;
            $current = \Carbon\Carbon::parse($startDate);
            $last = \Carbon\Carbon::parse($endDate);
            
            // Optimización: si el periodo es largo, calcular por semanas completas
            while ($current->lte($last)) {
                if (in_array($current->dayOfWeek, $entity->operating_days)) {
                    $workDays++;
                }
                $current->addDay();
            }
        }

        return [
            'daily_hours' => $dailyHours,
            'work_days' => $workDays,
            'total_days' => $totalDays,
        ];
    }

    public function getClimateDays(): array
    {
        return $this->lastClimateDays;
    }
}
