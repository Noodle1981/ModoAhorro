<?php

namespace App\Services;

class SolarPowerService
{
    // Constantes Técnicas
    const PANEL_POWER_W = 550;
    const AREA_PER_PANEL = 2.0; // m²
    const PEAK_SUN_HOURS = 4.5;
    const SYSTEM_EFFICIENCY = 0.80;

    /**
     * Calcula la cobertura solar basada en espacio disponible y consumo.
     */
    public function calculateSolarCoverage($availableArea, $maxConsumption, $avgConsumption)
    {
        // Evitar división por cero
        if ($maxConsumption <= 0) $maxConsumption = 1;
        if ($avgConsumption <= 0) $avgConsumption = 1;

        // KwP necesarios para cubrir el consumo máximo
        $targetKwp = $maxConsumption / (self::PEAK_SUN_HOURS * 30 * self::SYSTEM_EFFICIENCY);
        $targetPanels = (int) ceil($targetKwp * 1000 / self::PANEL_POWER_W);
        $targetArea = $targetPanels * self::AREA_PER_PANEL;

        // ¿Cuánto CABE?
        $maxPanelsFit = (int) floor($availableArea / self::AREA_PER_PANEL);

        // Selección final
        $panelsToInstall = min($targetPanels, $maxPanelsFit);
        $kwpToInstall = ($panelsToInstall * self::PANEL_POWER_W) / 1000;
        
        $monthlyGeneration = $kwpToInstall * self::PEAK_SUN_HOURS * 30 * self::SYSTEM_EFFICIENCY;

        $coverageSummer = min(100, ($monthlyGeneration / $maxConsumption) * 100);
        $coverageWinter = min(100, ($monthlyGeneration / $avgConsumption) * 100);

        return [
            'scenario' => ($maxPanelsFit >= $targetPanels) ? 'FULL_COVERAGE' : 'PARTIAL_COVERAGE',
            'system_size_kwp' => round($kwpToInstall, 2),
            'panels_count' => $panelsToInstall,
            'target_area' => $targetArea,
            'area_used' => $panelsToInstall * self::AREA_PER_PANEL,
            'area_remaining' => max(0, $availableArea - ($panelsToInstall * self::AREA_PER_PANEL)),
            'coverage_summer' => round($coverageSummer, 1),
            'coverage_winter' => round($coverageWinter, 1),
            'monthly_generation_kwh' => round($monthlyGeneration, 1),
            'investment_estimate' => $panelsToInstall * 600, // Estimación base USD (Suministro + Inst)
        ];
    }
}
