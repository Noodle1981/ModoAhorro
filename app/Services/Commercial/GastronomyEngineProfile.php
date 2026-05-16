<?php

namespace App\Services\Commercial;

use App\Models\Entity;
use App\Models\Equipment;

class GastronomyEngineProfile extends AbstractCommercialProfile
{
    public function getCriticalCategories(): array
    {
        return [
            'Refrigeración Comercial',
            'Conectividad y Seguridad'
        ];
    }

    public function getProcessCategories(): array
    {
        return [
            'Equipamiento Gastronómico',
            'Extracción y Ventilación'
        ];
    }

    public function getVisitorsSocialCoefficient(): float
    {
        // Coeficiente por comensal (gastronomía)
        return 0.15;
    }

    public function getVisitorsUnitLabel(): string
    {
        return 'comensales';
    }

    public function getStandbyMultiplier(): float
    {
        return 1.2; // Alta carga térmica por heladeras que nunca cortan
    }

    public function calculateOperationalLoad(array $context): float
    {
        $turns = max(1, $context['service_turns'] ?? 1);
        $visitors = $context['visitors_count'] ?? 0;
        
        // Multiplicador base por turnos, con un plus si hay mucho tráfico de comensales.
        $trafficMultiplier = $visitors > 0 ? 1.5 : 1.0;
        
        return $turns * $trafficMultiplier;
    }
}
