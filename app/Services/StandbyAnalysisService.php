<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\Equipment;

class StandbyAnalysisService
{
    /**
     * Categorías que NO tienen consumo standby relevante.
     * Iluminación se apaga completamente. Portátiles tienen batería propia.
     */
    const EXCLUDED_CATEGORY_NAMES = [
        'Iluminación',
        'Portátiles',
    ];

    /**
     * Calculate standby analysis for an entity.
     * Shows all equipment from relevant categories with their standby status.
     */
    public function calculateStandbyAnalysis(Entity $entity): array
    {
        $entity->load(['rooms.equipment.type', 'rooms.equipment.category']);

        // Obtener TODOS los equipos para la auditoría de 3 estados
        $equipmentList = $entity->rooms
            ->flatMap(fn($room) => $room->equipment)
            ->filter(fn($eq) => $eq->is_active !== false)
            ->values();

        // Calcular totales
        $totalStandbyKwh      = 0;
        $totalStandbyCost     = 0;
        $totalPotentialSavingsKwh = 0;
        $totalRealizedSavingsKwh  = 0;

        $averageTariff = 150; // ARS/kWh

        foreach ($equipmentList as $eq) {
            // Lógica de potencia standby (con fallback de 5W)
            $dbStandby = $eq->type->default_standby_power_w;
            $standbyPowerW = ($dbStandby && $dbStandby > 0) ? $dbStandby : 5;
            
            $standbyPowerKw = $standbyPowerW / 1000;
            $activeHours    = $eq->avg_daily_use_hours ?? 2;
            $standbyHours   = max(0, 24 - $activeHours);
            $monthlyKwh     = $standbyPowerKw * $standbyHours * 30;

            // ESTADOS:
            // 1 (True) -> Enchufado (Consume)
            // 0 (False) -> Desenchufado/No tiene (Ahorra)
            // null -> Pendiente (Gris)
            
            if ($eq->is_standby === 1 || $eq->is_standby === true) {
                $totalStandbyKwh  += $monthlyKwh;
                $totalStandbyCost += $monthlyKwh * $averageTariff;
                $totalPotentialSavingsKwh += $monthlyKwh;
            } elseif ($eq->is_standby === 0 || $eq->is_standby === false) {
                // Si explícitamente es 0, cuenta como ahorro realizado
                $totalRealizedSavingsKwh += $monthlyKwh;
            }
            // Si es NULL, no suma ni a gasto ni a ahorro (está en gris)
        }

        return [
            'equipmentList'          => $equipmentList,
            'totalStandbyKwh'        => round($totalStandbyKwh, 1),
            'totalStandbyCost'       => round($totalStandbyCost, 0),
            'totalPotentialSavings'  => round($totalPotentialSavingsKwh * $averageTariff, 0),
            'totalRealizedSavings'   => round($totalRealizedSavingsKwh * $averageTariff, 0),
            'averageTariff'          => $averageTariff,
        ];
    }

    /**
     * Toggle standby status for an equipment.
     */
    public function toggleEquipmentStandby(string $equipmentId): void
    {
        $equipment = Equipment::findOrFail($equipmentId);
        
        // Ciclo: null (pendiente) -> 1 (enchufado) -> 0 (desenchufado)
        if (is_null($equipment->is_standby)) {
            $equipment->is_standby = 1;
        } else {
            $equipment->is_standby = $equipment->is_standby ? 0 : 1;
        }
        
        $equipment->save();
    }
}
