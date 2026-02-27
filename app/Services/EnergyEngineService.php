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
    protected $lastClimateDays = []; // Store the last calculated climate days

    public function __construct(ClimateService $climateService, ThermalProfileService $thermalService)
    {
        $this->climateService = $climateService;
        $this->thermalService = $thermalService;
    }

    /**
     * Distribuye el consumo de la factura en los 3 Tanques y ajusta el consumo unitario de los equipos.
     * Retorna un reporte detallado del proceso.
     */
    public function processInvoice(Invoice $invoice, Collection $equipments): array
    {
        $entity = $invoice->contract->entity;
        $totalBillKwh = $invoice->total_energy_consumed_kwh ?? $invoice->consumption_kwh ?? 0;
        $daysInPeriod = $invoice->days_in_period; 
        $logs = [];

        // CONTEXTO OPERATIVO (Oficinas/Comercios vs Hogares)
        $opContext = $this->calculateOperationalContext($entity, $invoice->start_date, $invoice->end_date);
        $logs[] = "[Contexto] Entidad: {$entity->type}. Horas/Día: " . number_format($opContext['daily_hours'], 1) . ". Días laborables: {$opContext['work_days']}/{$opContext['total_days']}";

        // --- TANQUE 1: BASE INMUTABLE ---
        $tank1Consumption = 0;
        $tank1Equipments = $equipments->filter(function ($eq) {
            return $eq->type->isBase() || ($eq->use_time_hours == 24 && !$eq->type->isClimate());
        });

        foreach ($tank1Equipments as $eq) {
            if (isset($eq->_theo_kwh)) {
                $periodKwh = $eq->_theo_kwh;
            } else {
                // Si el equipo es 24h pero la oficina cierra, hay un conflicto.
                // Respetamos la definición del equipo si es explícitamente 24h, 
                // pero si es base genérica, sigue el horario de la oficina.
                $hoursPerDay = ($eq->use_time_hours == 24) ? 24 : $opContext['daily_hours'];
                $activeDays = ($eq->use_time_hours == 24) ? $opContext['total_days'] : $opContext['work_days'];
                
                $dailyKwh = ($eq->type->default_power_watts * $hoursPerDay * $eq->type->load_factor) / 1000;
                $periodKwh = $dailyKwh * $activeDays;
            }
            
            $eq->calculated_consumption_kwh = $periodKwh;
            $eq->tank_assignment = 1;
            $eq->audit_logs = ["Fijado en " . number_format($periodKwh, 1) . " kWh (Base Crítica)"];
            $tank1Consumption += $periodKwh;
            $logs[] = "[Tanque 1] {$eq->name}: " . number_format($periodKwh, 1) . " kWh" . ($activeDays < $opContext['total_days'] ? " (Días: $activeDays)" : "");
        }

        // Remanente post-Tanque 1
        $remainingKwh = max(0, $totalBillKwh - $tank1Consumption);

        // --- TANQUE 2: CLIMATIZACIÓN ---
        $tank2Consumption = 0;
        $tank2Equipments = $equipments->filter(function ($eq) {
                return $eq->type->isClimate();
        });

        // Datos climáticos
        $locality = $entity->locality;
        $climateStats = $this->climateService->getDegreeDaysForLocality(
            $locality, 
            $invoice->start_date, 
            $invoice->end_date
        );
        
        $this->lastClimateDays = $climateStats;
        
        if ($tank2Equipments->isNotEmpty()) {
            $thermalMultiplier = $this->thermalService->calculateMultiplier($entity);

            foreach ($tank2Equipments as $eq) {
                if (isset($eq->_theo_kwh)) {
                    $periodKwh = $eq->_theo_kwh;
                    $eq->audit_logs = [number_format($periodKwh, 1) . " kWh (Ajuste Térmico)"];
                } else {
                    $name = strtolower($eq->type->name);
                    $isCooling = str_contains($name, 'aire') || str_contains($name, 'ventilador') || str_contains($name, 'split');
                    $degreeDays = $isCooling ? ($climateStats['cooling_days'] ?? 0) : ($climateStats['heating_days'] ?? 0);
                    
                    if ($degreeDays <= 0) {
                         $periodKwh = 0;
                         $eq->audit_logs = ["0 kWh (Sin Grados-Día activos)"];
                    } else {
                        $avgDegreeDays = $degreeDays / $opContext['total_days'];
                        $climateMainFactor = min(1.0, ($avgDegreeDays / 5.0)); 
                        $finalLoadFactor = min(1.0, $eq->type->load_factor * $climateMainFactor * $thermalMultiplier);
                        
                        // Si no especificó horas, usa las de la oficina
                        $hours = $eq->avg_daily_use_hours ?? $eq->use_time_hours ?? $opContext['daily_hours'];
                        $activeDays = ($eq->use_time_hours == 24) ? $opContext['total_days'] : $opContext['work_days'];

                        $dailyKwh = ($eq->type->default_power_watts * $hours * $finalLoadFactor) / 1000;
                        $periodKwh = $dailyKwh * $activeDays;
                        
                        $eq->audit_logs = [number_format($periodKwh, 1) . " kWh (Load: " . number_format($finalLoadFactor, 2) . ")"];
                    }
                }

                $eq->calculated_consumption_kwh = $periodKwh;
                $eq->tank_assignment = 2;
                $tank2Consumption += $periodKwh;
                $logs[] = "[Tanque 2] {$eq->name}: " . number_format($periodKwh, 1) . " kWh";
            }
        }

        // --- TANQUE 3: ELASTICIDAD ---
        $tank3Consumption = 0;
        $tank3Equipments = $equipments->filter(function ($eq) {
            return $eq->tank_assignment === null;
        });

        $t3TheoreticalTotal = 0;
        foreach ($tank3Equipments as $eq) {
            if (isset($eq->_theo_kwh)) {
                $eq->theo_kwh = $eq->_theo_kwh;
            } else {
                $hours = $eq->avg_daily_use_hours ?? $eq->use_time_hours ?? $opContext['daily_hours'];
                $activeDays = ($eq->use_time_hours == 24) ? $opContext['total_days'] : $opContext['work_days'];
                
                $loadFactor = $eq->type->load_factor ?? 1.0;
                $dailyKwh = (($eq->type->default_power_watts ?? 0) * $hours * $loadFactor) / 1000;
                $eq->theo_kwh = $dailyKwh * $activeDays;
            }
            $t3TheoreticalTotal += $eq->theo_kwh;
        }

        $totalTheoretical = $tank1Consumption + $tank2Consumption + $t3TheoreticalTotal;
        $recommendedTotalKwh = $totalTheoretical;
        $isDynamic = ($totalBillKwh > 0);

        if ($isDynamic) {
            $recommendedTotalKwh = max($totalBillKwh, min($totalBillKwh * 1.30, $totalTheoretical));
        }
        
        $idealT3 = max(0, $recommendedTotalKwh - $tank1Consumption - $tank2Consumption);

        if ($tank3Equipments->isNotEmpty()) {
            if ($idealT3 > 0) {
                $intensityMap = ['Bajo' => 1, 'Medio' => 2, 'Alto' => 3, 'Excesivo' => 5, 'Critico' => 5];
                $totalPoints = 0;
                foreach ($tank3Equipments as $eq) {
                    $intensityStr = ucfirst(strtolower($eq->type->intensity ?? 'Medio'));
                    $points = $intensityMap[$intensityStr] ?? 2;
                    $powerWeight = sqrt((float)($eq->type->default_power_watts ?? 1)); 
                    $eq->elasticity_points = $points * $powerWeight;
                    $totalPoints += $eq->elasticity_points;
                }

                if ($totalPoints > 0) {
                    foreach ($tank3Equipments as $eq) {
                        $share = $eq->elasticity_points / $totalPoints;
                        $periodKwh = $idealT3 * $share;
                        $eq->calculated_consumption_kwh = $periodKwh;
                        $eq->tank_assignment = 3;
                        $tank3Consumption += $periodKwh;
                        
                        $percentChange = $eq->theo_kwh > 0 ? (($periodKwh - $eq->theo_kwh) / $eq->theo_kwh * 100) : 0;
                        $actionSign = $percentChange >= 0 ? "+" : "";
                        $eq->audit_logs = [number_format($periodKwh, 1) . " kWh (Teórico: ".number_format($eq->theo_kwh, 1).", Ajuste: {$actionSign}".number_format($percentChange, 1)."%)"];
                    }
                    $logs[] = "[Tanque 3] Objetivo de " . number_format($idealT3, 1) . " kWh alcanzado.";
                }
            } else {
                foreach ($tank3Equipments as $eq) {
                    $eq->calculated_consumption_kwh = 0;
                    $eq->tank_assignment = 3;
                    $eq->audit_logs = ["Anulado (0 kWh). Consumo rígido superado."];
                }
                $logs[] = "[Tanque 3] Anulado.";
            }
        }
        
        $totalAssigned = $tank1Consumption + $tank2Consumption + $tank3Consumption;
        $invoice->update(['recommended_kwh' => $recommendedTotalKwh]);

        return [
            'total_bill' => $totalBillKwh,
            'theoretical_total' => $totalTheoretical,
            'calibrated_total' => $totalAssigned,
            'tank_1_base' => $tank1Consumption,
            'tank_2_climate' => $tank2Consumption,
            'tank_3_elasticity' => $tank3Consumption,
            'unassigned_remainder' => $totalBillKwh - $totalAssigned,
            'equipments_processed' => $equipments->count(),
            'logs' => $logs,
            'climate_data' => $this->lastClimateDays
        ];
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
