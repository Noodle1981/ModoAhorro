<?php

namespace App\Services\Commercial;

use App\Models\Entity;
use App\Models\Equipment;

class OfficeEngineProfile extends AbstractCommercialProfile
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

    public function getStandbyMultiplier(): float
    {
        return 1.2; // Alta carga nocturna por Servidores y Racks
    }

    public function calculateOperationalLoad(array $context): float
    {
        $turns = max(1, $context['service_turns'] ?? 1);
        $staff = $context['staff_count'] ?? 0;
        
        // El impacto del staff es mayor en oficinas, ya que cada empleado 
        // implica equipos encendidos y mayor uso de climatización.
        $staffMultiplier = $staff > 0 ? 1.2 : 1.0;
        
        return $turns * $staffMultiplier;
    }
}
