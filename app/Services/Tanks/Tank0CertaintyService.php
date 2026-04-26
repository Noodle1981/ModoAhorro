<?php

namespace App\Services\Tanks;

use App\Models\Equipment;
use Illuminate\Support\Collection;

class Tank0CertaintyService
{
    /**
     * Procesa los equipos con altísimo determinismo (Certeza Matemática)
     * Estos equipos se restan primero de la bolsa de energía.
     */
    public function process(Collection $equipments, float &$remainingKwh): array
    {
        $tankConsumption = 0;
        $logs = [];

        $targetEquipments = $equipments->filter(function ($eq) {
            return $eq->type->determinism_score >= 0.9;
        });

        foreach ($targetEquipments as $eq) {
            $periodKwh = $eq->_theo_kwh ?? 0;
            $eq->calculated_consumption_kwh = $periodKwh;
            $eq->tank_assignment = 1;
            $eq->audit_logs = ["Fijado en " . number_format($periodKwh, 1) . " kWh (Tanque 1 - Certeza)"];
            
            $tankConsumption += $periodKwh;
            $remainingKwh -= $periodKwh;
            
            $logs[] = "[Tanque 1] {$eq->name}: " . number_format($periodKwh, 1) . " kWh";
        }

        return [
            'consumption' => $tankConsumption,
            'logs' => $logs,
            'processed_count' => $targetEquipments->count()
        ];
    }
}
