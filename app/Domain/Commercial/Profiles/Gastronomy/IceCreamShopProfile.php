<?php

namespace App\Domain\Commercial\Profiles\Gastronomy;

class IceCreamShopProfile extends GastronomyBaseProfile
{
    public function getSubcategoryKey(): string
    {
        return 'heladeria_artesanal';
    }

    public function getSubcategoryLabel(): string
    {
        return 'Heladería Artesanal & Fábrica de Frío';
    }

    public function getDescription(): string
    {
        return 'Carga continua 24/7 de frío negativo (-18°C a -24°C) en pozos/cámaras, con picos intensivos de potencia en mantecado y pasteurización.';
    }

    public function getCriticalCategories(): array
    {
        return [
            'Refrigeración Comercial',
            'Cadena de Frío Negativo',
            'Conectividad y Seguridad',
        ];
    }

    public function getProcessCategories(): array
    {
        return [
            'Equipamiento Gastronómico',
            'Elaboración y Mantecado',
            'Extracción y Ventilación',
        ];
    }

    public function getStandbyMultiplier(): float
    {
        // Factor de carga continua alto debido a que las cámaras y pozos nunca cortan
        return 1.35;
    }

    public function getThermalSensitivity(): float
    {
        // En días de ola de calor (>30°C), los compresores de vitrinas y pozos aumentan drásticamente su ciclo de trabajo
        return 1.45;
    }

    public function getVisitorsSocialCoefficient(): float
    {
        return 0.12;
    }

    public function getVisitorsUnitLabel(): string
    {
        return 'clientes';
    }

    public function getSuggestedAppliances(): array
    {
        return [
            ['name' => 'Mantecadora de Helado Trifásica', 'category' => 'Equipamiento Gastronómico', 'typical_wattage' => 4500],
            ['name' => 'Pasteurizador Continuo 60L', 'category' => 'Equipamiento Gastronómico', 'typical_wattage' => 5000],
            ['name' => 'Cámara Frigorífica de Congelados (-20°C)', 'category' => 'Refrigeración Comercial', 'typical_wattage' => 2500],
            ['name' => 'Pozo Heladero Glicolado (12 a 24 tachas)', 'category' => 'Refrigeración Comercial', 'typical_wattage' => 1800],
            ['name' => 'Vitrina Exhibidora Panorámica', 'category' => 'Refrigeración Comercial', 'typical_wattage' => 1200],
            ['name' => 'Aire Acondicionado Salón (Frío/Calor)', 'category' => 'Climatización', 'typical_wattage' => 3500],
        ];
    }
}
