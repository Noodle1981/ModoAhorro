<?php

namespace App\Services\Thermal;

class ThermalScoreService
{
    /**
     * Calculate thermal score (0-100) and label (A-G).
     *
     * @param array $profile
     * @return array
     */
    public function calculate(array $profile): array
    {
        $score = 50; // Base score

        // 1. Roof (30%)
        switch ($profile['roof_type'] ?? '') {
            case 'sheet_metal': $score -= 20; break;
            case 'concrete_slab': $score -= 10; break;
            case 'wood_tiles': $score += 5; break;
        }

        if (!empty($profile['roof_insulation'])) {
            $score += 15;
        }

        // 2. Windows (40%)
        if (($profile['window_type'] ?? '') === 'dvh') {
            $score += 20;
        }

        if (in_array($profile['window_frame'] ?? '', ['pvc', 'wood'])) {
            $score += 5;
        }

        if (!empty($profile['drafts_detected'])) {
            $score -= 15;
        }

        // 3. Environment (30%)
        if (($profile['sun_exposure'] ?? '') === 'high') {
            // Penalty mainly if windows are single glass or no shading, simplifying here
            $score -= 10;
        }

        // Clamp score 0-100
        $score = max(0, min(100, $score));

        return [
            'score' => $score,
            'label' => $this->getLabel($score),
            'color' => $this->getColor($score),
        ];
    }

    private function getLabel(int $score): string
    {
        if ($score >= 90) return 'A';
        if ($score >= 75) return 'B';
        if ($score >= 60) return 'C';
        if ($score >= 40) return 'D';
        return 'E';
    }

    private function getColor(int $score): string
    {
        if ($score >= 90) return 'success'; // Green
        if ($score >= 75) return 'success'; // Light Green
        if ($score >= 60) return 'warning'; // Yellow
        if ($score >= 40) return 'warning'; // Orange
        return 'danger';  // Red
    }
}
