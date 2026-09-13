<?php

namespace App\Domain\Commercial\Profiles\Gastronomy;

class CoffeeShopProfile extends GastronomyBaseProfile
{
    public function getSubcategoryKey(): string
    {
        return 'cafeteria';
    }

    public function getSubcategoryLabel(): string
    {
        return 'Cafetería, Bar & Pastelería';
    }

    public function getDescription(): string
    {
        return 'Operación diurna continua con cafeteras express de caldera presurizada, molinos, vitrinas de frío positivo y climatización constante.';
    }

    public function getProcessCategories(): array
    {
        return [
            'Equipamiento Gastronómico',
            'Cafetería y Bebidas',
            'Extracción y Ventilación',
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

    public function getSuggestedAppliances(): array
    {
        return [
            ['name' => 'Cafetera Express 2 Grupos (Caldera Continua)', 'category' => 'Equipamiento Gastronómico', 'typical_wattage' => 3500],
            ['name' => 'Molino de Café Automático', 'category' => 'Equipamiento Gastronómico', 'typical_wattage' => 450],
            ['name' => 'Vitrina Refrigerada de Pastelería (+4°C)', 'category' => 'Refrigeración Comercial', 'typical_wattage' => 600],
            ['name' => 'Licuadora Industrial / Frappera', 'category' => 'Equipamiento Gastronómico', 'typical_wattage' => 1200],
            ['name' => 'Aire Acondicionado Salón', 'category' => 'Climatización', 'typical_wattage' => 4500],
        ];
    }
}
