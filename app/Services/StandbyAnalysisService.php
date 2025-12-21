<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\Equipment;

class StandbyAnalysisService
{
    /**
     * Calculate standby analysis for an entity
     */
    public function calculateStandbyAnalysis(Entity $entity): array
    {
        // Load equipment with types
        $entity->load(['rooms.equipment.type']);

        // Get all equipment that has standby power capability
        // EXCLUDING Infrastructure (Modems, Routers) as per user feedback
        $equipmentList = $entity->rooms
            ->flatMap(fn($room) => $room->equipment)
            ->filter(fn($eq) => ($eq->type->default_standby_power_w ?? 0) > 0)
            ->filter(fn($eq) => !str_contains(strtolower($eq->name), 'modem')
                && !str_contains(strtolower($eq->name), 'router')
                && !str_contains(strtolower($eq->type->name ?? ''), 'modem')
                && !str_contains(strtolower($eq->type->name ?? ''), 'router'));

        // Calculate totals
        $totalStandbyKwh = 0;
        $totalPotentialSavingsKwh = 0;
        $totalRealizedSavingsKwh = 0;

        foreach ($equipmentList as $eq) {
            $standbyPowerKw = ($eq->type->default_standby_power_w ?? 0) / 1000;
            $standbyHours = max(0, 24 - ($eq->avg_daily_use_hours ?? 0));
            $monthlyKwh = $standbyPowerKw * $standbyHours * 30;

            if ($eq->is_standby) {
                $totalStandbyKwh += $monthlyKwh;
                $totalPotentialSavingsKwh += $monthlyKwh;
            } else {
                $totalRealizedSavingsKwh += $monthlyKwh;
            }
        }

        // Estimate cost
        $averageTariff = 150;
        $totalStandbyCost = $totalStandbyKwh * $averageTariff;
        $totalPotentialSavings = $totalPotentialSavingsKwh * $averageTariff;
        $totalRealizedSavings = $totalRealizedSavingsKwh * $averageTariff;

        return [
            'equipmentList' => $equipmentList,
            'totalStandbyKwh' => $totalStandbyKwh,
            'totalStandbyCost' => $totalStandbyCost,
            'totalPotentialSavings' => $totalPotentialSavings,
            'totalRealizedSavings' => $totalRealizedSavings,
        ];
    }

    /**
     * Toggle standby status for an equipment
     */
    public function toggleEquipmentStandby(Equipment $equipment, Entity $entity): void
    {
        // Verify equipment belongs to entity (security check)
        if ($equipment->room->entity_id != $entity->id) {
            abort(403, 'Unauthorized action.');
        }

        $equipment->is_standby = !$equipment->is_standby;
        $equipment->save();
    }
}
