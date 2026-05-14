<?php

namespace App\Services\Tanks;

use App\Models\Equipment;
use Illuminate\Support\Collection;

class Tank3ElasticityService
{
    /**
     * Procesa el remanente final (Elasticidad) distribuyéndolo entre equipos variables.
     */
    public function process(Collection $equipments, array $opContext): array
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

        foreach ($targetEquipments as $eq) {
            if (isset($eq->_theo_kwh)) {
                $periodKwh = $eq->_theo_kwh;
            } else {
                $hours = $eq->avg_daily_use_hours ?? $eq->use_time_hours ?? $opContext['daily_hours'];
                $activeDays = ($eq->use_time_hours == 24) ? $opContext['total_days'] : $opContext['work_days'];
                
                $isSeasonal = $eq->type?->consumption_logic === 'SEASONAL_HABIT';
                $coolingDays = $opContext['cooling_days'] ?? 0;
                
                if ($isSeasonal && $coolingDays <= 0) {
                    $periodKwh = 0;
                    $eq->audit_logs = ["Anulado (0 kWh). Fuera de temporada estacional."];
                } else {
                    $loadFactor = $eq->type->load_factor ?? 1.0;
                    $logic = $eq->type?->consumption_logic ?? '';
                    $powerW = $eq->nominal_power_w ?? $eq->type->default_power_watts ?? 0;

                    if ($logic === 'TURNS_BASED') {
                        $turns = $opContext['service_turns'] ?? 1;
                        $dailyKwh = ($powerW * $turns * $hours * $loadFactor) / 1000;
                    } elseif ($logic === 'SERVICE_HOURS') {
                        $dailyHours = $opContext['daily_hours'] ?? 12;
                        $dailyKwh = ($powerW * $dailyHours * $loadFactor) / 1000;
                    } else {
                        $dailyKwh = ($powerW * $hours * $loadFactor) / 1000;
                    }

                    $periodKwh = $dailyKwh * $activeDays;
                }
            }

            // Asignación Directa del Teórico Puro
            $eq->calculated_consumption_kwh = $periodKwh;
            $eq->tank_assignment = 4;
            
            // Mensaje en Log dependiendo del tipo de unidad
            if ($eq->type?->usage_unit === 'cycles') {
                $cyclesUsed = $eq->cycles_per_period ?? 0;
                $eq->audit_logs = ["Asignación Teórica: " . number_format($periodKwh, 1) . " kWh ($cyclesUsed ciclos declarados)"];
            } else {
                $eq->audit_logs = ["Asignación Teórica: " . number_format($periodKwh, 1) . " kWh (Uso Variable Calculado)"];
            }

            $tankConsumption += $periodKwh;
        }

        $logs[] = "[Tanque 4] Se han procesado " . $targetEquipments->count() . " equipos variables, totalizando " . number_format($tankConsumption, 1) . " kWh según horas declaradas.";

        return [
            'consumption' => $tankConsumption,
            'logs' => $logs,
            'processed_count' => $targetEquipments->count()
        ];
    }
}
