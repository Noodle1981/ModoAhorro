<?php

namespace App\Services\Commercial;

use App\Models\Entity;
use App\Models\Equipment;

class OfficeEngineProfile implements CommercialEngineProfile
{
    public function getCriticalCategories(): array
    {
        return [
            'Conectividad y Seguridad',
            'Sistemas de Servidores'
        ];
    }

    public function getProcessCategories(): array
    {
        return [
            'Oficina y Computación',
            'Iluminación de Oficinas'
        ];
    }

    public function getVisitorsSocialCoefficient(): float
    {
        // En oficina, el "people_count" suele ser empleados. 
        // Si hay visitantes, su impacto es muy bajo.
        return 0.02;
    }

    public function getVisitorsUnitLabel(): string
    {
        return 'visitantes';
    }

    public function isThermalProcess(Equipment $equipment): bool
    {
        $category = $equipment->category?->name ?? '';
        return in_array($category, $this->getProcessCategories());
    }
}
