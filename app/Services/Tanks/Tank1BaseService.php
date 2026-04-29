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
            // Tank Crítico: Entra cualquier equipo que funcione 24hs
            // o pertenezca a categorías inamovibles (Refrigeración, Conectividad).
            // El usuario NO necesita marcar Patrón Fijo: es un criterio técnico.
            // La categoría determina el algoritmo de cálculo dentro del tanque.
            return $eq->tank_assignment === null
                && $this->isCritical($eq);
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

            // --- SPLIT 70/30 para BASE_THERMAL_LOSS (Termotanques) ---
            if ($eq->type?->consumption_logic === 'BASE_THERMAL_LOSS') {
                $periodKwh = $periodKwh * 0.70;
                $eq->audit_logs = ["Asignado 70% como Base Inmutable (" . number_format($periodKwh, 1) . " kWh)"];
                $eq->tank_assignment = null; // No lo bloqueamos, T3 debe procesar el resto
            } else {
                $eq->tank_assignment = 2;
                $eq->audit_logs = ["Fijado en " . number_format($periodKwh, 1) . " kWh (Tank Crítico (Fijo + 24h ó Refrigeración/Conectividad))"];
            }
            
            $tankConsumption += $periodKwh;
            $remainingKwh -= $periodKwh;
            $eq->calculated_consumption_kwh = ($eq->calculated_consumption_kwh ?? 0) + $periodKwh;
            
            $logs[] = "[Tanque 2] {$eq->name}: " . number_format($periodKwh, 1) . " kWh";
        }

        return [
            'consumption' => $tankConsumption,
            'logs' => $logs,
            'processed_count' => $targetEquipments->count()
        ];
    }

    private function isCritical(Equipment $eq): bool
    {
        // Crítico = 24hs continuas Y todos los días (diario).
        // Un servidor que se apaga los fines de semana tiene 24h
        // pero NO es diario: va a Certeza (si es Patrón Fijo) o Variable.
        $hours = $eq->avg_daily_use_hours ?? 0;
        $frequency = $eq->usage_frequency ?? 'diario';
        $isDailyOrAlways = in_array($frequency, ['diario', 'diariamente']);

        return $hours >= 23.5 && $isDailyOrAlways;
    }
}
