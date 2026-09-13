<?php

namespace App\Domain\Commercial\Profiles\Gastronomy;

use App\Domain\Commercial\Profiles\AbstractModularCommercialProfile;

abstract class GastronomyBaseProfile extends AbstractModularCommercialProfile
{
    public function getCategoryKey(): string
    {
        return 'gastronomia';
    }

    public function getCategoryLabel(): string
    {
        return 'Gastronomía';
    }

    public function getCriticalCategories(): array
    {
        return [
            'Refrigeración Comercial',
            'Conectividad y Seguridad',
        ];
    }

    public function getProcessCategories(): array
    {
        return [
            'Equipamiento Gastronómico',
            'Extracción y Ventilación',
        ];
    }

    public function getVisitorsSocialCoefficient(): float
    {
        return 0.15;
    }

    public function getVisitorsUnitLabel(): string
    {
        return 'comensales';
    }

    public function getStandbyMultiplier(): float
    {
        return 1.20;
    }

    public function calculateOperationalLoad(array $context): float
    {
        $turns = max(1, (int) ($context['service_turns'] ?? 1));
        $visitors = (int) ($context['visitors_count'] ?? 0);

        $trafficMultiplier = $visitors > 0 ? 1.5 : 1.0;

        return $turns * $trafficMultiplier;
    }
}
