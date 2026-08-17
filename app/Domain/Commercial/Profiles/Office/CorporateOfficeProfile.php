<?php

namespace App\Domain\Commercial\Profiles\Office;

use App\Domain\Commercial\Profiles\AbstractModularCommercialProfile;

class CorporateOfficeProfile extends AbstractModularCommercialProfile
{
    public function getCategoryKey(): string
    {
        return 'oficina';
    }

    public function getCategoryLabel(): string
    {
        return 'Oficina / Corporativo';
    }

    public function getSubcategoryKey(): string
    {
        return 'oficina_servicios';
    }

    public function getSubcategoryLabel(): string
    {
        return 'Oficinas Profesionales & Coworking';
    }

    public function getDescription(): string
    {
        return 'Horario administrativo estricto (08:00 a 18:00), alta densidad de puestos de trabajo informáticos, servidores y climatización por zonas.';
    }

    public function getCriticalCategories(): array
    {
        return [
            'Conectividad y Seguridad',
            'Servidores y Telecomunicaciones',
        ];
    }

    public function getProcessCategories(): array
    {
        return [
            'Informática y Ofimática',
            'Climatización Central',
        ];
    }

    public function getStandbyMultiplier(): float
    {
        return 1.10;
    }

    public function getThermalSensitivity(): float
    {
        return 1.20;
    }

    public function getVisitorsSocialCoefficient(): float
    {
        return 0.08;
    }

    public function getVisitorsUnitLabel(): string
    {
        return 'visitantes';
    }

    public function calculateOperationalLoad(array $context): float
    {
        $staff = (int) ($context['staff_count'] ?? 0);
        $visitors = (int) ($context['visitors_count'] ?? 0);

        return max(1.0, 1.0 + ($staff * 0.05) + ($visitors * 0.02));
    }

    public function getSuggestedAppliances(): array
    {
        return [
            ['name' => 'Puestos de Trabajo (Notebook / Monitor / PC)', 'category' => 'Informática y Redes', 'typical_wattage' => 180],
            ['name' => 'Rack de Servidores y Switch', 'category' => 'Informática y Redes', 'typical_wattage' => 650],
            ['name' => 'Aire Acondicionado Central / VRF', 'category' => 'Climatización', 'typical_wattage' => 6000],
            ['name' => 'Dispensador de Agua Frío/Calor', 'category' => 'Línea Blanca', 'typical_wattage' => 500],
            ['name' => 'Impresora Multifunción Láser', 'category' => 'Informática y Redes', 'typical_wattage' => 450],
        ];
    }
}
