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

        // 3. Environment (Orientation & Ventilation - Southern Hemisphere)
        // Orientation
        switch ($profile['orientation'] ?? '') {
            case 'norte_sur':
                $score += 15; // Ideal: Sun in winter (North), fresh air in summer (South)
                break;
            case 'este_oeste':
                $score -= 10; // Hard to control: Low sun in morning/afternoon causing overheating
                break;
            // 'diagonal' or others get 0 change
        }

        // South Window (Cross Ventilation / Cooling)
        if (!empty($profile['south_window'])) {
            $score += 10;
        }

        // 4. Sun Exposure (Shadows)
        switch ($profile['sun_exposure'] ?? '') {
            case 'high':
                $score -= 10; // High overheating risk in summer
                break;
            case 'medium':
                $score += 5; // Good balance
                break;
            case 'low':
                // Neutral (Good for summer, bad for winter heating)
                break;
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
