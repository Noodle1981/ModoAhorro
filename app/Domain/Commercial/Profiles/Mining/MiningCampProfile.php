<?php

namespace App\Domain\Commercial\Profiles\Mining;

use App\Domain\Commercial\Profiles\AbstractModularCommercialProfile;

class MiningCampProfile extends AbstractModularCommercialProfile
{
    public function getCategoryKey(): string
    {
        return 'pabellon';
    }

    public function getCategoryLabel(): string
    {
        return 'Campamento Minero';
    }

    public function getSubcategoryKey(): string
    {
        return 'campamento_pabellon';
    }

    public function getSubcategoryLabel(): string
    {
        return 'Pabellón de Alojamiento (Turno Faena/Descanso)';
    }

    public function getDescription(): string
    {
        return 'Pabellón modular de alta montaña (+3.500 msnm). Régimen 24/7 con turnos de faena (14x14 / 7x7), alta exigencia térmica en dormitorios y picos de agua caliente sanitaria en cambio de turno.';
    }

    public function getCriticalCategories(): array
    {
        return [
            'Conectividad y Seguridad',
            'Calefacción Industrial',
            'Agua y Bombeo Industrial',
            'Refrigeración',
        ];
    }

    public function getProcessCategories(): array
    {
        return [
            'Calefacción Industrial',
            'Traceado Eléctrico',
            'Agua y Bombeo Industrial',
        ];
    }

    public function getStandbyMultiplier(): float
    {
        return 1.05;
    }

    public function getThermalSensitivity(): float
    {
        // En alta montaña andina la sensibilidad térmica a frío extremo es máxima
        return 1.50;
    }

    public function getVisitorsSocialCoefficient(): float
    {
        return 0.02;
    }

    public function getVisitorsUnitLabel(): string
    {
        return 'visitas / contratistas';
    }

    public function calculateOperationalLoad(array $context): float
    {
        $occupancy = (int) ($context['staff_count'] ?? 0);
        $visitors = (int) ($context['visitors_count'] ?? 0);

        // La carga operativa base se calcula según la dotación de operarios en descanso o guardia
        return max(1.0, 1.0 + ($occupancy * 0.04) + ($visitors * 0.01));
    }

    public function getSuggestedAppliances(): array
    {
        return [
            ['name' => 'Convector Eléctrico de Pared 1500W', 'category' => 'Calefacción Industrial', 'typical_wattage' => 1500],
            ['name' => 'Panel Radiante Infrarrojo 900W', 'category' => 'Calefacción Industrial', 'typical_wattage' => 900],
            ['name' => 'Termotanque Industrial 300L 3000W', 'category' => 'Agua y Bombeo Industrial', 'typical_wattage' => 3000],
            ['name' => 'Traceado Eléctrico Anticongelamiento', 'category' => 'Calefacción Industrial', 'typical_wattage' => 450],
            ['name' => 'Luminaria LED Pasillo Industrial 40W', 'category' => 'Iluminación Industrial', 'typical_wattage' => 40],
            ['name' => 'Detector de Humo / Monóxido de Carbono', 'category' => 'Seguridad y Detección', 'typical_wattage' => 10],
        ];
    }
}
