<?php

namespace Database\Seeders;

use App\Models\EquipmentCategory;
use App\Models\EquipmentType;
use Illuminate\Database\Seeder;

class CommercialCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        // 1. CATEGORÍAS COMERCIALES
        $cats = [
            'GASTRO' => EquipmentCategory::firstOrCreate(['name' => 'Equipamiento Gastronómico'], [
                'description' => 'Equipos industriales para cocción y preparación en restaurantes y bares.'
            ]),
            'REFRIG_COMM' => EquipmentCategory::firstOrCreate(['name' => 'Refrigeración Comercial'], [
                'description' => 'Cámaras de frío, exhibidoras y mesas refrigeradas industriales.'
            ]),
            'EXTRACT' => EquipmentCategory::firstOrCreate(['name' => 'Extracción y Ventilación'], [
                'description' => 'Sistemas de extracción de humos y ventilación forzada para cocinas industriales.'
            ]),
        ];

        // 2. TIPOS DE EQUIPO
        // [nombre, minW, maxW, defW, logic, unit, det, catKey, isCritical]
        $types = [
            // --- EQUIPAMIENTO GASTRONÓMICO ---
            ['Horno Convector Industrial', 2000, 8000, 4000, 'TURNS_BASED', 'hours', 0.85, 'GASTRO', false],
            ['Horno a Cuarzo / Salamandra', 1000, 3000, 2000, 'TURNS_BASED', 'hours', 0.80, 'GASTRO', false],
            ['Freidora Eléctrica', 2000, 7000, 5000, 'TURNS_BASED', 'hours', 0.90, 'GASTRO', false],
            ['Plancha / Grill Eléctrico', 1500, 5000, 3500, 'TURNS_BASED', 'hours', 0.85, 'GASTRO', false],
            ['Microondas Industrial', 1200, 2500, 1800, 'CONSTANT_ELASTIC', 'cycles', 0.95, 'GASTRO', false],
            ['Cafetera Espresso Profesional', 1500, 4000, 2500, 'CONSTANT_ELASTIC', 'cycles', 0.95, 'GASTRO', false],
            ['Lavavajillas Industrial', 2000, 5000, 3000, 'CONSTANT_ELASTIC', 'cycles', 0.90, 'GASTRO', false],

            // --- REFRIGERACIÓN COMERCIAL ---
            ['Cámara de Frío (-18°C)', 1500, 5000, 2500, 'CONTINUOUS_COMMERCIAL', 'hours', 0.98, 'REFRIG_COMM', true],
            ['Cámara de Frío (+4°C)', 800, 3000, 1200, 'CONTINUOUS_COMMERCIAL', 'hours', 0.98, 'REFRIG_COMM', true],
            ['Exhibidor Refrigerado Vertical', 400, 1500, 800, 'CONTINUOUS_COMMERCIAL', 'hours', 0.95, 'REFRIG_COMM', true],
            ['Mesa Refrigerada', 300, 1000, 500, 'CONTINUOUS_COMMERCIAL', 'hours', 0.95, 'REFRIG_COMM', true],
            ['Máquina de Hielo', 300, 1200, 600, 'CONTINUOUS_COMMERCIAL', 'hours', 0.90, 'REFRIG_COMM', true],

            // --- EXTRACCIÓN Y VENTILACIÓN ---
            ['Campana Extractora', 300, 1500, 750, 'SERVICE_HOURS', 'hours', 0.90, 'EXTRACT', false],
            ['Extractor de Techo', 150, 800, 400, 'SERVICE_HOURS', 'hours', 0.90, 'EXTRACT', false],
        ];

        foreach ($types as $t) {
            EquipmentType::firstOrCreate(['name' => $t[0]], [
                'min_watts' => $t[1],
                'max_watts' => $t[2],
                'default_power_watts' => $t[3],
                'consumption_logic' => $t[4],
                'usage_unit' => $t[5],
                'determinism_score' => $t[6],
                'category_id' => $cats[$t[7]]->id,
                'load_factor' => 1.0,
                'is_climatization' => false,
                'is_thermal_sensitive' => ($t[7] === 'GASTRO' || $t[7] === 'REFRIG_COMM'),
            ]);
        }
    }
}
