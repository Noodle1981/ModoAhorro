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
        $totalBillKwh = $invoice->total_energy_consumed_kwh ?? $invoice->consumption_kwh ?? 0;
        $daysInPeriod = $invoice->days_in_period; 
        $logs = [];

        // Preparar equipos con metadatos v3
        // Asumiendo que vienen con 'type' cargado o lazy loading
        
        // --- TANQUE 1: BASE INMUTABLE ---
        $tank1Consumption = 0;
        $tank1Equipments = $equipments->filter(function ($eq) {
            // Lógica robusta: Si el tipo dice base, o si el uso es 24h
            return $eq->type->isBase() || ($eq->use_time_hours == 24 && !$eq->type->isClimate());
        });

        foreach ($tank1Equipments as $eq) {
            if (isset($eq->_theo_kwh)) {
                $periodKwh = $eq->_theo_kwh;
            } else {
                // Consumo = Potencia * 24h * Días * FactorCarga
                $dailyKwh = ($eq->type->default_power_watts * 24 * $eq->type->load_factor) / 1000;
                $periodKwh = $dailyKwh * $daysInPeriod;
            }
            
            $eq->calculated_consumption_kwh = $periodKwh;
            $eq->tank_assignment = 1;
            $eq->audit_logs = ["Fijado en " . number_format($periodKwh, 1) . " kWh (Base Crítica)"];
            $tank1Consumption += $periodKwh;
            $logs[] = "[Tanque 1] {$eq->name}: Fijado en " . number_format($periodKwh, 1) . " kWh";
        }

        // Remanente post-Tanque 1
        $remainingKwh = max(0, $totalBillKwh - $tank1Consumption);

        // --- TANQUE 2: CLIMATIZACIÓN ---
        $tank2Consumption = 0;
        $tank2Equipments = $equipments->filter(function ($eq) {
                return $eq->type->isClimate();
        });

        // Datos climáticos (SIEMPRE CARGARLOS para histórico)
        $locality = $invoice->contract->entity->locality;
        $climateStats = $this->climateService->getDegreeDaysForLocality(
            $locality, 
            $invoice->start_date, 
            $invoice->end_date
        );
        
        $this->lastClimateDays = $climateStats; // Store for retrieval
        
        if ($tank2Equipments->isNotEmpty()) {
            
            // Factor Térmico del Hogar (A-E)
            $thermalMultiplier = $this->thermalService->calculateMultiplier($invoice->contract->entity);

            foreach ($tank2Equipments as $eq) {
                if (isset($eq->_theo_kwh)) {
                    $periodKwh = $eq->_theo_kwh;
                    if ($periodKwh == 0) {
                        $logs[] = "[Tanque 2] {$eq->name}: 0 kWh (Sin Uso/Clima)";
                        $eq->audit_logs = ["0 kWh (Sin Uso/Clima)"];
                    } else {
                        $logMsg = number_format($periodKwh, 1) . " kWh (Ajuste Térmico)";
                        $logs[] = "[Tanque 2] {$eq->name}: " . $logMsg;
                        $eq->audit_logs = [$logMsg];
                    }
                } else {
                    // Heurística de nombre para decidir HDD vs CDD si no está explícito
                    $name = strtolower($eq->type->name);
                    $isCooling = str_contains($name, 'aire') || str_contains($name, 'ventilador') || str_contains($name, 'split');
                    
                    $degreeDays = $isCooling ? ($climateStats['cooling_days'] ?? 0) : ($climateStats['heating_days'] ?? 0);
                    
                    // Si no hubo grados día (ej: estufa en verano), consumo tiende a 0 (o a un standby mínimo?)
                    // Por ahora, asumimos que si no hace frío, la estufa no se prende.
                    if ($degreeDays <= 0) {
                         $periodKwh = 0;
                         $logs[] = "[Tanque 2] {$eq->name}: 0 kWh (Sin Grados-Día activos)";
                         $eq->audit_logs = ["0 kWh (Sin Grados-Día activos)"];
                    } else {
                        $avgDegreeDays = $degreeDays / $daysInPeriod;
                        // Factor Climático: Normalizamos con un umbral de 5°C
                        $climateMainFactor = min(1.0, ($avgDegreeDays / 5.0)); 
                        
                        // Carga Final
                        $finalLoadFactor = $eq->type->load_factor * $climateMainFactor * $thermalMultiplier;
                        $finalLoadFactor = min(1.0, $finalLoadFactor); // Cap en 100%
                        
                        // Usar horas declaradas en la instancia si existen, sino el default del tipo
                        $hours = $eq->avg_daily_use_hours ?? $eq->use_time_hours ?? $eq->type->default_avg_daily_use_hours;

                        // Cálculo
                        $dailyKwh = ($eq->type->default_power_watts * $hours * $finalLoadFactor) / 1000;
                        $periodKwh = $dailyKwh * $daysInPeriod;
                        
                        $logMsg = number_format($periodKwh, 1) . " kWh (CDD/HDD: $degreeDays, Load: " . number_format($finalLoadFactor, 2) . ")";
                        $logs[] = "[Tanque 2] {$eq->name}: " . $logMsg;
                        $eq->audit_logs = [$logMsg];
                    }
                }

                $eq->calculated_consumption_kwh = $periodKwh;
                $eq->tank_assignment = 2;
                $tank2Consumption += $periodKwh;
            }
        }

        // Remanente post-Tanque 2 (Eliminado, ahora se basa en el Cálculo Recomendado)

        // --- TANQUE 3: ELASTICIDAD (El Resto) ---
        $tank3Consumption = 0;
        $tank3Equipments = $equipments->filter(function ($eq) {
            return $eq->tank_assignment === null; // Los que no cayeron en T1 o T2
        });

        // 1. Calcular Teórico T3 Exacto
        $t3TheoreticalTotal = 0;
        foreach ($tank3Equipments as $eq) {
            if (isset($eq->_theo_kwh)) {
                $eq->theo_kwh = $eq->_theo_kwh;
            } else {
                $hours = $eq->avg_daily_use_hours ?? $eq->use_time_hours ?? $eq->type->default_avg_daily_use_hours ?? 1;
                $loadFactor = $eq->type->load_factor ?? 1.0;
                $dailyKwh = (($eq->type->default_power_watts ?? 0) * $hours * $loadFactor) / 1000;
                $eq->theo_kwh = $dailyKwh * $daysInPeriod;
            }
            $t3TheoreticalTotal += $eq->theo_kwh;
        }

        $totalTheoretical = $tank1Consumption + $tank2Consumption + $t3TheoreticalTotal;
        $recommendedTotalKwh = $totalTheoretical;
        $isDynamic = ($totalBillKwh > 0);

        if ($isDynamic) {
            // Lógica exacta: min(Facturado * 1.30, Calculado_Usuario)
            $recommendedTotalKwh = min($totalBillKwh * 1.30, $totalTheoretical);
            // No permitimos que caiga debajo de la factura para no crear falsos positivos
            $recommendedTotalKwh = max($totalBillKwh, $recommendedTotalKwh);
        }
        
        // Lo que el Tanque 3 TIENE que sumar para llegar al Recomendado
        // Notar: Si Recomendado es 107.9, y T1+T2 es 69.2, $idealT3 = 38.7.
        // Si T1+T2 > Recomendado, $idealT3 = 0.
        $idealT3 = max(0, $recommendedTotalKwh - $tank1Consumption - $tank2Consumption);

        if ($tank3Equipments->isNotEmpty()) {
            if ($idealT3 > 0) {
                // Mapa de Intensidad -> Puntos
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
                    $logs[] = "[Tanque 3] Objetivo de " . number_format($idealT3, 1) . " kWh alcanzado. Teórico original era " . number_format($t3TheoreticalTotal, 1) . " kWh.";
                }
            } else {
                foreach ($tank3Equipments as $eq) {
                    $eq->calculated_consumption_kwh = 0;
                    $eq->tank_assignment = 3;
                    $eq->audit_logs = ["Anulado (0 kWh). T1 y T2 absorben todo el límite recomendado."];
                }
                $logs[] = "[Tanque 3] Totalmente anulado (Límite superado por consumo rígido).";
            }
        }
        
        $totalAssigned = $tank1Consumption + $tank2Consumption + $tank3Consumption;

        // --- Novedad: Guardar el Cálculo Recomendado en la factura ---
        if ($isDynamic) {
            $invoice->update(['recommended_kwh' => $recommendedTotalKwh]);
        } else {
            // Si no hay factura, el recomendado es el teórico base
            $invoice->update(['recommended_kwh' => $totalTheoretical]);
        }

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

    public function getClimateDays(): array
    {
        return $this->lastClimateDays;
    }
}
