<?php

namespace App\Services\Solar;

class SolarPowerService
{
    // Constantes Técnicas
    const PANEL_POWER_W = 550;
    const AREA_PER_PANEL = 2.0; // m²
    const PEAK_SUN_HOURS = 4.5;
    const SYSTEM_EFFICIENCY = 0.80;

    /**
     * Calcula la cobertura solar basada en espacio disponible y consumo.
     *
     * @param float $availableArea Espacio disponible en m²
     * @param float $maxConsumption Consumo mensual máximo (kWh)
     * @param float $avgConsumption Consumo mensual promedio (kWh)
     * @return array
     */
    public function calculateSolarCoverage($availableArea, $maxConsumption, $avgConsumption)
    {
        // Evitar división por cero
        if ($maxConsumption <= 0) $maxConsumption = 1;
        if ($avgConsumption <= 0) $avgConsumption = 1;

        // A. ¿Cuánto NECESITA? (Target)
        // KwP necesarios para cubrir el consumo máximo
        $targetKwp = $maxConsumption / (self::PEAK_SUN_HOURS * 30 * self::SYSTEM_EFFICIENCY);
        $targetPanels = ceil($targetKwp * 1000 / self::PANEL_POWER_W);
        $targetArea = $targetPanels * self::AREA_PER_PANEL;

        // B. ¿Cuánto CABE? (Limit)
        $maxPanelsFit = floor($availableArea / self::AREA_PER_PANEL);
        // $maxKwpFit = ($maxPanelsFit * self::PANEL_POWER_W) / 1000; // No se usa directamente

        // C. Comparación y Resultado
        $panelsToInstall = min($targetPanels, $maxPanelsFit);
        $kwpToInstall = ($panelsToInstall * self::PANEL_POWER_W) / 1000;
        
        // Generación Estimada del sistema resultante
        $monthlyGeneration = $kwpToInstall * self::PEAK_SUN_HOURS * 30 * self::SYSTEM_EFFICIENCY;

        // Porcentajes de Cobertura
        $coverageSummer = min(100, ($monthlyGeneration / $maxConsumption) * 100);
        $coverageWinter = min(100, ($monthlyGeneration / $avgConsumption) * 100); // Usamos promedio como proxy de invierno/media

        return [
            'scenario' => ($maxPanelsFit >= $targetPanels) ? 'FULL_COVERAGE' : 'PARTIAL_COVERAGE',
            'system_size_kwp' => $kwpToInstall,
            'panels_count' => $panelsToInstall,
            'target_area' => $targetArea, // Area necesaria para cubrir el 100%
            'area_used' => $panelsToInstall * self::AREA_PER_PANEL,
            'area_remaining' => $availableArea - ($panelsToInstall * self::AREA_PER_PANEL),
            'coverage_summer' => round($coverageSummer, 1),
            'coverage_winter' => round($coverageWinter, 1),
            'monthly_generation_kwh' => round($monthlyGeneration, 1)
        ];
    }
}
