<?php

namespace App\Services\Commercial;

use App\Models\Entity;
use App\Models\Equipment;

class RetailEngineProfile extends AbstractCommercialProfile
{
    public function getCriticalCategories(): array
    {
        return [
            'Conectividad y Seguridad',
            'Refrigeración Comercial' // Por si es un mini-market
        ];
    }

    public function getProcessCategories(): array
    {
        return [
            'Iluminación Comercial',
            'Publicidad y Cartelería'
        ];
    }

    public function getVisitorsSocialCoefficient(): float
    {
        // Coeficiente por visitante (retail es menor impacto que gastronomía)
        return 0.05;
    }

    public function getVisitorsUnitLabel(): string
    {
        return 'clientes';
    }

    public function getStandbyMultiplier(): float
    {
        return 0.8; // Prácticamente todo apagado salvo cartelería/alarma
    }

    public function calculateOperationalLoad(array $context): float
    {
        // En retail los turnos no afectan tanto el consumo de los equipos,
        // ya que la iluminación comercial está encendida de todos modos.
        return 1.0;
    }
}
