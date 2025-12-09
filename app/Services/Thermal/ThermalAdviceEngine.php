<?php

namespace App\Services\Thermal;

class ThermalAdviceEngine
{
    /**
     * Generate recommendations based on thermal profile.
     *
     * @param array $profile
     * @param int $score
     * @return array
     */
    public function generateAdvice(array $profile, int $score): array
    {
        $advice = [];

        // A. Quick Wins (Low Cost)
        if (!empty($profile['drafts_detected'])) {
            $advice[] = [
                'title' => 'Detener Chifletes (Urgente)',
                'problem' => 'Entra aire exterior por rendijas en puertas o ventanas.',
                'solution' => 'Instalar Burletes Autoadhesivos.',
                'cost_level' => '$',
                'impact' => 'Alto',
                'icon' => 'fas fa-wind',
                'color' => 'danger'
            ];
        }

        // Check East/West orientation OR High Sun Exposure
        if ((in_array($profile['orientation'] ?? '', ['este_oeste', 'diagonal']) || ($profile['sun_exposure'] ?? '') === 'high') && ($profile['window_type'] ?? '') === 'single_glass') {
            $advice[] = [
                'title' => 'Escudo Solar en Ventanas',
                'problem' => 'El exceso de radiación solar directa sobrecalienta tu vivienda.',
                'solution' => 'Instalar Cortinas Blackout o un Toldo exterior.',
                'cost_level' => '$$',
                'impact' => 'Medio',
                'icon' => 'fas fa-sun',
                'color' => 'warning'
            ];
        }

        // B. Maintenance
        if (in_array($profile['roof_type'] ?? '', ['sheet_metal', 'concrete_slab']) && empty($profile['roof_insulation'])) {
            $advice[] = [
                'title' => 'Pintura Térmica en Techo',
                'problem' => 'Tu techo absorbe la radiación solar y la transmite al interior.',
                'solution' => 'Aplicar Membrana Líquida Blanca (Reflectiva).',
                'cost_level' => '$$',
                'impact' => 'Alto',
                'icon' => 'fas fa-paint-roller',
                'color' => 'orange'
            ];
        }

        // C. Upgrade (Investment)
        if (($profile['window_type'] ?? '') === 'single_glass' && $score < 60) {
            $advice[] = [
                'title' => 'Plan Canje de Ventanas',
                'problem' => 'El vidrio simple pierde hasta 4 veces más energía que un muro.',
                'solution' => 'Cambiar ventanas de dormitorios a Doble Vidrio (DVH).',
                'cost_level' => '$$$$',
                'impact' => 'Muy Alto',
                'icon' => 'fas fa-window-maximize',
                'color' => 'info'
            ];
        }

        return $advice;
    }
}
