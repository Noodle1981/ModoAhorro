<?php

namespace App\Services\Tanks;

use App\Models\Equipment;
use Illuminate\Support\Collection;

class Tank1BaseService
{
    /**
     * Procesa los equipos de base inmutable (Resto de fijos no T0)
     */
    public function process(Collection $equipments, float &$remainingKwh, array $opContext): array
    {
        $tankConsumption = 0;
        $logs = [];

        $targetEquipments = $equipments->filter(function ($eq) {
            return $eq->tank_assignment === null && (
                   $eq->type->consumption_logic === 'BASE_LOAD' || 
                   $eq->type->isBase() || 
                   ($eq->use_time_hours == 24 && !$eq->type->isClimate())
            );
        });

        foreach ($targetEquipments as $eq) {
            if (isset($eq->_theo_kwh)) {
                $periodKwh = $eq->_theo_kwh;
            } else {
                $hoursPerDay = ($eq->use_time_hours == 24) ? 24 : $opContext['daily_hours'];
                $activeDays = ($eq->use_time_hours == 24) ? $opContext['total_days'] : $opContext['work_days'];
                $dailyKwh = ($eq->type->default_power_watts * $hoursPerDay * $eq->type->load_factor) / 1000;
                $periodKwh = $dailyKwh * $activeDays;
            }
            
            $eq->calculated_consumption_kwh = $periodKwh;
            $eq->tank_assignment = 2;
            $eq->audit_logs = ["Fijado en " . number_format($periodKwh, 1) . " kWh (Base Crítica)"];
            
            $tankConsumption += $periodKwh;
            $remainingKwh -= $periodKwh;
            
            $logs[] = "[Tanque 2] {$eq->name}: " . number_format($periodKwh, 1) . " kWh";
        }

        return [
            'consumption' => $tankConsumption,
            'logs' => $logs,
            'processed_count' => $targetEquipments->count()
        ];
    }
}
