<?php

namespace App\Services\Commercial;

use App\Models\Entity;
use App\Models\Equipment;

class GastronomyEngineProfile implements CommercialEngineProfile
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

    public function isThermalProcess(Equipment $equipment): bool
    {
        $category = $equipment->category?->name ?? '';
        return in_array($category, $this->getProcessCategories());
    }
}
