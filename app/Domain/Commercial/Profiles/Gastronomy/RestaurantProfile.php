<?php

namespace App\Domain\Commercial\Profiles\Gastronomy;

class RestaurantProfile extends GastronomyBaseProfile
{
    public function getSubcategoryKey(): string
    {
        return 'restaurante_general';
    }

    public function getSubcategoryLabel(): string
    {
        return 'Restaurante & Cocina Integral';
    }

    public function getDescription(): string
    {
        return 'Operación en turnos partidos (mediodía y noche), cocina completa con cámaras de frío, lavado automático, extracción y climatización de salón.';
    }

    public function getStandbyMultiplier(): float
    {
        return 1.25;
    }

    public function getThermalSensitivity(): float
    {
        return 1.25;
    }

    public function getSuggestedAppliances(): array
    {
        return [
            ['name' => 'Campana Extractora Central 3HP', 'category' => 'Extracción y Ventilación', 'typical_wattage' => 2200],
            ['name' => 'Lavavajillas Industrial de Cúpula', 'category' => 'Equipamiento Gastronómico', 'typical_wattage' => 4500],
            ['name' => 'Cámara Frigorífica Mixta', 'category' => 'Refrigeración Comercial', 'typical_wattage' => 2000],
            ['name' => 'Freidora Eléctrica Automática 18L', 'category' => 'Equipamiento Gastronómico', 'typical_wattage' => 5000],
            ['name' => 'Climatización Central Salón Comedor', 'category' => 'Climatización', 'typical_wattage' => 7500],
        ];
    }
}
