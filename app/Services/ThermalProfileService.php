<?php

namespace App\Services;

class ThermalProfileService
{
    /**
     * Calcula el Energy Label (A-E) y el Score Térmico basado en las respuestas del usuario.
     * 
     * @param array $profile Datos del thermal_profile de la entidad.
     * @return array ['score' => int, 'label' => string]
     */
    public function calculate(array $profile)
    {
        $score = 50; // Base: Categoría C

        // 1. Techo (Pilar Crítico)
        $roofImpact = [
            'insulated_panel' => 15,
            'roof_tiles_with_insulation' => 10,
            'concrete_slab' => 0,
            'sheet_metal_no_insulation' => -15,
        ];
        $score += $roofImpact[$profile['roof_type'] ?? 'concrete_slab'] ?? 0;

        if ($profile['roof_insulation'] ?? false) {
            $score += 10;
        }

        // 2. Ventanas y Aberturas
        if (($profile['window_type'] ?? 'single_glass') === 'double_glass') {
            $score += 15;
        }

        $frameImpact = [
            'pvc' => 5,
            'wood' => 3,
            'aluminum_tb' => 5, // Thermal Bridge
            'aluminum' => 0,
            'iron' => -5,
        ];
        $score += $frameImpact[$profile['window_frame'] ?? 'aluminum'] ?? 0;

        // 3. Entorno e Infiltraciones
        if ($profile['drafts_detected'] ?? false) {
            $score -= 15;
        }

        if ($profile['south_window'] ?? false) {
            $score -= 5; // Pérdida de calor en invierno en hemisferio sur
        }

        $sunImpact = [
            'low' => -5,
            'medium' => 0,
            'high' => 5,
        ];
        $score += $sunImpact[$profile['sun_exposure'] ?? 'medium'] ?? 0;

        // Normalización (0-100)
        $score = max(0, min(100, $score));

        // Asignación de Etiqueta (Energy Label)
        $label = $this->getLabelFromScore($score);

        return [
            'thermal_score' => $score,
            'energy_label' => $label
        ];
    }

    protected function getLabelFromScore($score)
    {
        if ($score >= 85) return 'A';
        if ($score >= 70) return 'B';
        if ($score >= 50) return 'C';
        if ($score >= 30) return 'D';
        return 'E';
    }

    /**
     * Obtiene el multiplicador de ineficiencia para el Motor v3 (Tanque 2).
     * A = 1.0 (Sin penalización)
     * E = 1.8 (Penalización severa)
     */
    public function getMultiplierForScore(string $label): float
    {
        $map = [
            'A' => 1.0,
            'B' => 1.2,
            'C' => 1.4,
            'D' => 1.6,
            'E' => 1.8,
        ];
        return $map[strtoupper($label)] ?? 1.4;
    }

    /**
     * Helper para obtener el multiplicador directamente de una entidad.
     */
    public function calculateMultiplier(\App\Models\Entity $entity): float
    {
        $profile = $entity->thermal_profile ?? [];
        // Si ya tiene etiqueta calculada, usala
        if (isset($profile['energy_label'])) {
            return $this->getMultiplierForScore($profile['energy_label']);
        }
        
        // Si no, calcula al vuelo
        $result = $this->calculate($profile);
        return $this->getMultiplierForScore($result['energy_label']);
    }
}
