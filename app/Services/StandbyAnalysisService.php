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

        // Obtener todos los equipos de categorías que SÍ tienen standby
        $equipmentList = $entity->rooms
            ->flatMap(fn($room) => $room->equipment)
            ->filter(function ($eq) {
                // 1. Excluir categorías irrelevantes
                $categoryName = $eq->category->name ?? '';
                if (in_array($categoryName, self::EXCLUDED_CATEGORY_NAMES)) {
                    return false;
                }

                // 2. Excluir equipos con 0W de standby (ej: Ventilador de techo, Caloventor)
                // Si el tipo no tiene dato, asumimos 5W (fallback).
                // Pero si tiene dato explícito de 0W, lo respetamos y ocultamos.
                $standbyPowerW = $eq->type->default_standby_power_w;
                
                if (!is_null($standbyPowerW) && $standbyPowerW == 0) {
                    return false;
                }

                return true;
            })
            ->filter(fn($eq) => $eq->is_active !== false)
            ->values();

        // Calcular totales: solo los que TIENEN standby activo
        $totalStandbyKwh      = 0;
        $totalStandbyCost     = 0;
        $totalPotentialSavingsKwh = 0;
        $totalRealizedSavingsKwh  = 0;

        $averageTariff = 150; // ARS/kWh

        foreach ($equipmentList as $eq) {
            $standbyPowerW  = $eq->type->default_standby_power_w ?? 5; // 5W default si no hay dato
            $standbyPowerKw = $standbyPowerW / 1000;
            $activeHours    = $eq->avg_daily_use_hours ?? 2;
            $standbyHours   = max(0, 24 - $activeHours);
            $monthlyKwh     = $standbyPowerKw * $standbyHours * 30;

            if ($eq->is_standby) {
                // Equipo enchufado en standby → está consumiendo
                $totalStandbyKwh  += $monthlyKwh;
                $totalStandbyCost += $monthlyKwh * $averageTariff;
                $totalPotentialSavingsKwh += $monthlyKwh;
            } else {
                // Equipo desenchufado → ya está ahorrando
                $totalRealizedSavingsKwh += $monthlyKwh;
            }
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
        $equipment->is_standby = !$equipment->is_standby;
        $equipment->save();
    }
}
