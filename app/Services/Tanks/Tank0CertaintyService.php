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
    public function process(Collection $equipments, float &$remainingKwh, array $opContext = []): array
    {
        $tankConsumption = 0;
        $logs = [];

        $targetEquipments = $equipments->filter(function ($eq) use ($opContext) {
            // NUEVO (CORRECTO):
            // Solo entran equipos que el USUARIO marcó como Patrón Fijo
            // Y que NO sean ni de Refrigeración/Conectividad (-> Tank Crítico)
            // NI de Climatización (-> Tank Climático)
            return $eq->has_defined_pattern === true
                && !$this->isCritical($eq, $opContext)
                && !($eq->type?->is_thermal_sensitive);
        });

        foreach ($targetEquipments as $eq) {
            $periodKwh = $eq->_theo_kwh ?? 0;
            $eq->calculated_consumption_kwh = $periodKwh;
            $eq->tank_assignment = 1;
            $eq->audit_logs = ["Fijado en " . number_format($periodKwh, 1) . " kWh (Tanque 1 - Certeza (Patrón Fijo declarado))"];
            
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

    private function isCritical(Equipment $eq, array $opContext): bool
    {
        $criticalCategories = ['Refrigeración', 'Conectividad y Seguridad'];

        if (isset($opContext['commercial_profile'])) {
            $criticalCategories = $opContext['commercial_profile']->getCriticalCategories();
        }

        $categoryName = $eq->type?->category?->name ?? '';
        $hours = $eq->avg_daily_use_hours ?? 0;

        return in_array($categoryName, $criticalCategories)
            || $hours >= 23.5;
    }
}
