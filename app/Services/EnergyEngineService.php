<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Equipment;
use App\Models\Entity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class EnergyEngineService
{
    protected ClimateService $climateService;
    protected ThermalProfileService $thermalService;
    protected \App\Services\Tanks\Tank0CertaintyService $tank0;
    protected \App\Services\Tanks\Tank1BaseService $tank1;
    protected \App\Services\Tanks\Tank2ClimateService $tank2;
    protected \App\Services\Tanks\Tank3ElasticityService $tank3;
    protected ?\App\Services\Commercial\CommercialEngineProfile $commercialProfile = null;
    protected array $lastClimateDays = [];
    protected bool $isFallbackMode = false;

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
        
        // Cargar perfil comercial si aplica
        $this->commercialProfile = $this->getCommercialProfile($entity);
        
        // Priorizamos el consumo de la factura (cuota) sobre el bimensual si estamos en un proceso de unificación
        $totalBillKwh = $invoice->total_energy_consumed_kwh ?? $invoice->consumption_kwh ?? $invoice->bimonthly_consumption_kwh ?? 0;
        $logs = [];

        // 1. CONTEXTO OPERATIVO
        $opContext = $this->calculateOperationalContext($entity, $invoice->start_date, $invoice->end_date);
        $logs[] = "[Contexto] Entidad: {$entity->type}. Días: {$opContext['total_days']}. Bolsa Inicial: " . number_format($totalBillKwh, 1) . " kWh";

        $remainingKwh = $totalBillKwh;

        // --- PASO PREVIO: CONSUMO STANDBY (VAMPIRO) ---
        $standbyKwh = 0;
        foreach ($equipments->where('is_standby', true) as $eq) {
            $stbyPower = $eq->type->default_standby_power_w ?? 0;
            $hoursActive = $eq->avg_daily_use_hours ?? 0;
            $hoursStandby = max(0, 24 - $hoursActive);
            $dailyStbyKwh = ($stbyPower * $hoursStandby) / 1000;
            $periodStbyKwh = $dailyStbyKwh * $opContext['total_days'];
            
            $eq->calculated_consumption_kwh = $periodStbyKwh;
            $eq->tank_assignment = 0; // Tanque especial 0 para Standby
            $eq->audit_logs = ["Consumo Vampiro (Standby) estimado en " . number_format($periodStbyKwh, 1) . " kWh"];
            $standbyKwh += $periodStbyKwh;
            $remainingKwh -= $periodStbyKwh;
        }
        if ($standbyKwh > 0) {
            $logs[] = "[🧛 Standby] Consumo parásito total: " . number_format($standbyKwh, 1) . " kWh. (Restado de la bolsa)";
        }

        // --- PASO 0: TANQUE 0 (CERTEZA) ---
        $resT0 = $this->tank0->process($equipments->where('is_standby', false), $remainingKwh, $opContext);
        $logs = array_merge($logs, $resT0['logs']);

        // --- PASO 1: TANQUE 1 (BASE INMUTABLE) ---
        $resT1 = $this->tank1->process($equipments->where('is_standby', false), $remainingKwh, $opContext);
        $logs = array_merge($logs, $resT1['logs']);

        // ⚠️ DETECCIÓN TEMPRANA DE ANOMALÍAS (NotebookLM Suggestion)
        if ($remainingKwh < -0.5) { 
            $logs[] = "❌ [ANOMALÍA] El consumo base declarado (" . number_format($totalBillKwh - $remainingKwh, 1) . " kWh) supera el total de la factura.";
        }

        // --- PASO 2: TANQUE 2 (CLIMATIZACIÓN) ---
        $resT2 = $this->tank2->process($equipments->where('is_standby', false), $remainingKwh, $opContext, $invoice, $this->isFallbackMode);
        $logs = array_merge($logs, $resT2['logs']);
        $this->lastClimateDays = $resT2['climate_data'] ?? [];

        // --- PASO 3: TANQUE 3 (ELASTICIDAD/VARIABLE) ---
        // Se calcula el Teórico Puro basado en las horas declaradas. Sin distribución artificial.
        $resT3 = $this->tank3->process($equipments->where('is_standby', false), $opContext);
        $logs = array_merge($logs, $resT3['logs']);

        // --- CÁLCULO FINAL (TEÓRICO PURO) ---
        $totalAssigned = $resT0['consumption'] + $resT1['consumption'] + $resT2['consumption'] + $resT3['consumption'] + $standbyKwh;
        $totalTheoretical = $totalAssigned; // En la nueva filosofía, el asignado ES el teórico puro.
        
        $unassignedRemainder = $totalBillKwh - $totalTheoretical;
        
        if (abs($unassignedRemainder) > 0) {
            $logs[] = "[Energía Residual] " . number_format($unassignedRemainder, 1) . " kWh (Diferencia entre Facturado y Teórico Puro).";
        }

        $recommendedTotalKwh = $totalTheoretical;
        if ($totalBillKwh > 0) {
            $recommendedTotalKwh = max($totalBillKwh, min($totalBillKwh * 1.30, $totalTheoretical));
        }

        return [
            'total_bill' => $totalBillKwh,
            'theoretical_total' => $totalTheoretical,
            'calibrated_total' => $totalAssigned,
            'recommended_total_kwh' => $recommendedTotalKwh,
            'tank_1_certainty' => $resT0['consumption'],
            'tank_2_base' => $resT1['consumption'],
            'tank_3_climate' => $resT2['consumption'],
            'tank_4_elasticity' => $resT3['consumption'],
            'unassigned_remainder' => $unassignedRemainder,
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

    protected function getCommercialProfile(Entity $entity): ?\App\Services\Commercial\CommercialEngineProfile
    {
        if ($entity->type === 'oficina') {
            return new \App\Services\Commercial\OfficeEngineProfile();
        }

        if ($entity->type !== 'comercio') return null;

        return match ($entity->comercio_type) {
            'gastronomia' => new \App\Services\Commercial\GastronomyEngineProfile(),
            'retail'      => new \App\Services\Commercial\RetailEngineProfile(),
            default       => null,
        };
    }

    /**
     * Calcula el contexto operativo basado en horarios y días laborables de la entidad.
     */
    protected function calculateOperationalContext(mixed $entity, string $startDate, string $endDate): array
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
            'work_days'   => $workDays,
            'total_days'  => $totalDays,
            'people_count' => $entity->people_count ?? 1,
            'staff_count' => $entity->staff_count ?? 0,
            'visitors_count' => $entity->visitors_count ?? 0,
            'service_turns' => $entity->service_turns ?? 1,
            'is_commercial' => $entity->type === 'comercio',
            'commercial_profile' => $this->commercialProfile,
        ];
    }

    public function getClimateDays(): array
    {
        return $this->lastClimateDays;
    }

    public function setClimateDays(array $days): self
    {
        $this->lastClimateDays = $days;
        return $this;
    }
}
