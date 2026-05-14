<?php

namespace App\Services\Commercial;

use App\Models\Entity;
use App\Models\Equipment;

class RetailEngineProfile implements CommercialEngineProfile
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

    public function isThermalProcess(Equipment $equipment): bool
    {
        $category = $equipment->category?->name ?? '';
        return in_array($category, $this->getProcessCategories());
    }
}
