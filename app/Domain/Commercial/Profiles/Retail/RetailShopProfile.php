<?php

namespace App\Domain\Commercial\Profiles\Retail;

use App\Domain\Commercial\Profiles\AbstractModularCommercialProfile;

class RetailShopProfile extends AbstractModularCommercialProfile
{
    public function getCategoryKey(): string
    {
        return 'retail';
    }

    public function getCategoryLabel(): string
    {
        return 'Retail / Comercio al Público';
    }

    public function getSubcategoryKey(): string
    {
        return 'retail_general';
    }

    public function getSubcategoryLabel(): string
    {
        return 'Tienda, Local Comercial & Showroom';
    }

    public function getDescription(): string
    {
        return 'Dominado por iluminación de vidriera y salón (marquesinas continuas), climatización de confort para clientes y sistemas POS/seguridad.';
    }

    public function getCriticalCategories(): array
    {
        return [
            'Conectividad y Seguridad',
            'Iluminación de Vidriera',
        ];
    }

    public function getProcessCategories(): array
    {
        return [
            'Iluminación Comercial',
            'Sistemas POS y Cajas',
        ];
    }

    public function getStandbyMultiplier(): float
    {
        return 1.05;
    }

    public function getThermalSensitivity(): float
    {
        return 1.15;
    }

    public function getVisitorsSocialCoefficient(): float
    {
        return 0.05;
    }

    public function getVisitorsUnitLabel(): string
    {
        return 'clientes';
    }

    public function calculateOperationalLoad(array $context): float
    {
        $turns = max(1, (int) ($context['service_turns'] ?? 1));
        $visitors = (int) ($context['visitors_count'] ?? 0);

        return $turns * ($visitors > 50 ? 1.2 : 1.0);
    }

    public function getSuggestedAppliances(): array
    {
        return [
            ['name' => 'Iluminación Riel LED y Spots Salón', 'category' => 'Iluminación', 'typical_wattage' => 800],
            ['name' => 'Marquesina y Cartel Luminoso Exterior', 'category' => 'Iluminación', 'typical_wattage' => 400],
            ['name' => 'Aire Acondicionado Salón Comercial', 'category' => 'Climatización', 'typical_wattage' => 4500],
            ['name' => 'Terminal Punto de Venta (POS / PC Cajas)', 'category' => 'Informática y Redes', 'typical_wattage' => 250],
            ['name' => 'Cortina de Aire en Acceso', 'category' => 'Climatización', 'typical_wattage' => 1500],
        ];
    }
}
