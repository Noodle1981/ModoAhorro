<?php

namespace App\Domain\Commercial\Profiles\Gastronomy;

class PizzeriaProfile extends GastronomyBaseProfile
{
    public function getSubcategoryKey(): string
    {
        return 'pizzeria';
    }

    public function getSubcategoryLabel(): string
    {
        return 'Pizzería, Empanadas & Rotisería';
    }

    public function getDescription(): string
    {
        return 'Consumo dominado por calor intensivo en hornos pizzeros/convectores, alta extracción de aire y frío comercial para ingredientes.';
    }

    public function getProcessCategories(): array
    {
        return [
            'Equipamiento Gastronómico',
            'Cocción y Horneado',
            'Extracción y Ventilación',
        ];
    }

    public function getStandbyMultiplier(): float
    {
        return 1.15;
    }

    public function getThermalSensitivity(): float
    {
        return 1.10;
    }

    public function getSuggestedAppliances(): array
    {
        return [
            ['name' => 'Horno Pizzero Eléctrico Continuo', 'category' => 'Equipamiento Gastronómico', 'typical_wattage' => 6000],
            ['name' => 'Campana Extractora Industrial 2HP', 'category' => 'Extracción y Ventilación', 'typical_wattage' => 1500],
            ['name' => 'Heladera Mostrador Pizzera con Granito', 'category' => 'Refrigeración Comercial', 'typical_wattage' => 750],
            ['name' => 'Amasadora / Sobadora Rápida', 'category' => 'Equipamiento Gastronómico', 'typical_wattage' => 1200],
            ['name' => 'Aire Acondicionado Salón', 'category' => 'Climatización', 'typical_wattage' => 4500],
        ];
    }
}
