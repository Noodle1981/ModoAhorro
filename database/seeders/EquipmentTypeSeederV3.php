<?php

namespace Database\Seeders;

use App\Models\EquipmentType;
use App\Models\EquipmentCategory;
use Illuminate\Database\Seeder;

class EquipmentTypeSeederV3 extends Seeder
{
    public function run(): void
    {
        // Asegurar que las categorías existan
        $catClima = EquipmentCategory::firstOrCreate(['name' => 'Climatización']);
        $catLineaBlanca = EquipmentCategory::firstOrCreate(['name' => 'Línea Blanca']);
        $catIT = EquipmentCategory::firstOrCreate(['name' => 'Informática / IT']);
        $catCocina = EquipmentCategory::firstOrCreate(['name' => 'Cocina / Pequeños Electrodomésticos']);
        $catEntretenimiento = EquipmentCategory::firstOrCreate(['name' => 'Entretenimiento']);
        $catServicios = EquipmentCategory::firstOrCreate(['name' => 'Servicios / Bombas']);

        $types = [
            // [name, min, max, default, logic, unit, det, penalty, social, cat_id]
            ['Aire Split', 300, 3500, 2500, 'CLIMATE_DEPENDENT', 'hours', 0.4, 0.0, 0, $catClima->id],
            ['Aire de Ventana', 800, 2500, 1500, 'CLIMATE_DEPENDENT', 'hours', 0.4, 0.20, 0, $catClima->id],
            ['Aire Portátil', 1200, 4000, 2200, 'CLIMATE_INEFFICIENT', 'hours', 0.2, 0.4, 0, $catClima->id],
            ['Heladera c/Freezer', 80, 450, 150, 'BASE_LOAD', 'hours', 0.6, 0.1, 0, $catLineaBlanca->id],
            ['Freezer de Pozo', 100, 500, 200, 'BASE_LOAD', 'hours', 0.7, 0.05, 0, $catLineaBlanca->id],
            ['Lavarropas', 400, 2500, 600, 'BASE_LOAD', 'cycles', 0.9, -0.1, 0, $catLineaBlanca->id], // T0 candidate
            ['Secarropas (Calor)', 1500, 3000, 2000, 'CONSTANT_ELASTIC', 'cycles', 0.8, -0.3, 0, $catLineaBlanca->id],
            ['Lavavajillas', 1000, 2200, 1500, 'BASE_LOAD', 'cycles', 0.9, -0.2, 0, $catLineaBlanca->id], // T0 candidate
            ['Pava Eléctrica', 1500, 2400, 2000, 'BASE_LOAD', 'people_proportional', 0.95, 0, 0.12, $catCocina->id], // T0 candidate
            ['Microondas', 800, 1800, 1200, 'CONSTANT_ELASTIC', 'people_proportional', 0.85, 0, 0.08, $catCocina->id],
            ['Horno Eléctrico', 1500, 3500, 2200, 'CONSTANT_ELASTIC', 'hours', 0.5, -0.25, 0, $catCocina->id],
            ['Termotanque Eléctrico', 1500, 3000, 2000, 'BASE_LOAD', 'hours', 0.6, 0.15, 1.2, $catServicios->id],
            ['Router Wifi / ONT', 10, 40, 15, 'BASE_LOAD', 'hours', 1.0, 0, 0, $catIT->id], // T0 absolute
            ['Servidor / Rack', 150, 2000, 400, 'BASE_LOAD', 'hours', 0.98, 0, 0, $catIT->id], // T1 candidate
            ['PC de Escritorio', 150, 450, 250, 'CONSTANT_ELASTIC', 'hours', 0.7, 0, 0, $catIT->id],
            ['Notebook / Laptop', 30, 120, 65, 'CONSTANT_ELASTIC', 'hours', 0.6, 0, 0, $catIT->id],
            ['Monitor LED', 20, 80, 35, 'CONSTANT_ELASTIC', 'hours', 0.8, 0, 0, $catIT->id],
            ['PC Gamer / Workstation', 400, 1200, 600, 'CONSTANT_ELASTIC', 'hours', 0.4, 0, 0, $catIT->id],
            ['Iluminación LED (Kit)', 5, 100, 40, 'CONSTANT_ELASTIC', 'hours', 0.7, 0, 0, $catServicios->id],
            ['TV Grande (>50")', 100, 350, 180, 'CONSTANT_ELASTIC', 'hours', 0.5, 0, 0, $catEntretenimiento->id],
            ['Consola de Juegos', 150, 350, 200, 'CONSTANT_ELASTIC', 'hours', 0.3, 0, 0, $catEntretenimiento->id],
            ['Ventilador de Techo', 50, 120, 75, 'CONSTANT_ELASTIC', 'hours', 0.6, 0, 0, $catClima->id],
            ['Estufa de Cuarzo', 800, 2400, 1200, 'CLIMATE_DEPENDENT', 'hours', 0.5, 0, 0, $catClima->id],
            ['Bomba de Agua (1HP)', 750, 1100, 850, 'BASE_LOAD', 'cycles', 0.8, 0, 0.05, $catServicios->id],
            ['Cámaras de Seguridad', 5, 20, 10, 'BASE_LOAD', 'hours', 1.0, 0, 0, $catIT->id], // T0 absolute
        ];

        foreach ($types as $t) {
            EquipmentType::updateOrCreate(
                ['name' => $t[0]],
                [
                    'min_watts' => $t[1],
                    'max_watts' => $t[2],
                    'default_power_watts' => $t[3],
                    'consumption_logic' => $t[4],
                    'usage_unit' => $t[5],
                    'determinism_score' => $t[6],
                    'thermal_efficiency_penalty' => $t[7],
                    'social_coefficient' => $t[8],
                    'category_id' => $t[9],
                    'load_factor' => 1.0, // Default base
                    'is_climatization' => in_array($t[4], ['CLIMATE_DEPENDENT', 'CLIMATE_INEFFICIENT']),
                ]
            );
        }
    }
}
