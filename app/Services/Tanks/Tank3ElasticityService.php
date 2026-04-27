<?php

namespace App\Services\Tanks;

use App\Models\Equipment;
use Illuminate\Support\Collection;

class Tank3ElasticityService
{
    /**
     * Procesa el remanente final (Elasticidad) distribuyéndolo entre equipos variables.
     */
    public function process(Collection $equipments, float $remainingKwh, array $opContext): array
    {
        $tankConsumption = 0;
        $logs = [];

        $targetEquipments = $equipments->filter(function ($eq) {
            // T4: Elasticidad y Hábitos
            // Todo lo que sobró y NO sea un consumo puramente de Standby (se calcula aparte)
            return $eq->tank_assignment === null && ($eq->is_standby === false || $eq->is_standby === null);
        });

        if ($targetEquipments->isEmpty()) {
            return ['consumption' => 0, 'logs' => []];
        }

        // 1. Calcular consumos teóricos para ponderación
        $totalTheoreticalT3 = 0;
        foreach ($targetEquipments as $eq) {
            if (isset($eq->_theo_kwh)) {
                $eq->theo_kwh = $eq->_theo_kwh;
            } else {
                $hours = $eq->avg_daily_use_hours ?? $eq->use_time_hours ?? $opContext['daily_hours'];
                $activeDays = ($eq->use_time_hours == 24) ? $opContext['total_days'] : $opContext['work_days'];
                
                // --- LÓGICA SEASONAL_HABIT (Ventiladores) ---
                $isSeasonal = $eq->type?->consumption_logic === 'SEASONAL_HABIT';
                $coolingDays = $opContext['cooling_days'] ?? 0;
                
                if ($isSeasonal && $coolingDays <= 0) {
                    $eq->theo_kwh = 0;
                    $eq->audit_logs = ["Anulado (0 kWh). Fuera de temporada estacional."];
                } else {
                    $loadFactor = $eq->type->load_factor ?? 1.0;
                    $dailyKwh = (($eq->type->default_power_watts ?? 0) * $hours * $loadFactor) / 1000;
                    $eq->theo_kwh = $dailyKwh * $activeDays;
                }
            }
            $totalTheoreticalT3 += $eq->theo_kwh;
        }

        // 2. Distribuir el remanente (si hay)
        if ($remainingKwh > 0) {
            $intensityMap = ['Bajo' => 1, 'Medio' => 2, 'Alto' => 3, 'Excesivo' => 5, 'Critico' => 5];
            $totalPoints = 0;
            
            foreach ($targetEquipments as $eq) {
                $intensityStr = ucfirst(strtolower($eq->type->intensity ?? 'Medio'));
                $points = $intensityMap[$intensityStr] ?? 2;
                $powerWeight = sqrt((float)($eq->type->default_power_watts ?? 1)); 
                $eq->elasticity_points = $points * $powerWeight;
                $totalPoints += $eq->elasticity_points;
            }

            if ($totalPoints > 0) {
                foreach ($targetEquipments as $eq) {
                    $share = $eq->elasticity_points / $totalPoints;
                    $periodKwh = $remainingKwh * $share;
                    $eq->calculated_consumption_kwh = $periodKwh;
                    $eq->tank_assignment = 4;
                    $tankConsumption += $periodKwh;
                    
                    $percentChange = $eq->theo_kwh > 0 ? (($periodKwh - $eq->theo_kwh) / $eq->theo_kwh * 100) : 0;
                    $actionSign = $percentChange >= 0 ? "+" : "";
                    $eq->audit_logs = [number_format($periodKwh, 1) . " kWh (Teórico: ".number_format($eq->theo_kwh, 1).", Ajuste: {$actionSign}".number_format($percentChange, 1)."%)"];
                }
                $logs[] = "[Tanque 4] Distribuido remanente de " . number_format($remainingKwh, 1) . " kWh.";
            }
        } else {
            foreach ($targetEquipments as $eq) {
                $eq->calculated_consumption_kwh = 0;
                $eq->tank_assignment = 4;
                $eq->audit_logs = ["Anulado (0 kWh). Sin remanente en la bolsa."];
            }
            $logs[] = "[Tanque 4] Anulado por falta de remanente.";
        }

        return [
            'consumption' => $tankConsumption,
            'logs' => $logs,
            'processed_count' => $targetEquipments->count()
        ];
    }
}
