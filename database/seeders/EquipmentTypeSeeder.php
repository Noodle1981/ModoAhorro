<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EquipmentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Climatización', 'Iluminación', 'Electrodomésticos', 'Entretenimiento', 'Cocina', 'Oficina', 'Otros'
        ];
        $catIds = [];
        foreach ($categories as $cat) {
            $catIds[$cat] = \App\Models\EquipmentCategory::where('name', $cat)->first()->id;
        }
        $types = [
            // Climatización
            ['name' => 'Aire Acondicionado', 'category' => 'Climatización', 'default_power_watts' => 1420, 'default_avg_daily_use_hours' => 8],
            ['name' => 'Ventilador', 'category' => 'Climatización', 'default_power_watts' => 60, 'default_avg_daily_use_hours' => 6],
            ['name' => 'Calefactor', 'category' => 'Climatización', 'default_power_watts' => 2000, 'default_avg_daily_use_hours' => 6],
            // Iluminación
            ['name' => 'Lámpara LED', 'category' => 'Iluminación', 'default_power_watts' => 10, 'default_avg_daily_use_hours' => 5],
            ['name' => 'Lámpara Bajo Consumo', 'category' => 'Iluminación', 'default_power_watts' => 20, 'default_avg_daily_use_hours' => 5],
            ['name' => 'Lámpara Halógena', 'category' => 'Iluminación', 'default_power_watts' => 50, 'default_avg_daily_use_hours' => 5],
            // Electrodomésticos
            ['name' => 'Heladera', 'category' => 'Electrodomésticos', 'default_power_watts' => 150, 'default_avg_daily_use_hours' => 24],
            ['name' => 'Microondas', 'category' => 'Electrodomésticos', 'default_power_watts' => 1200, 'default_avg_daily_use_hours' => 0.5],
            ['name' => 'Lavarropas', 'category' => 'Electrodomésticos', 'default_power_watts' => 500, 'default_avg_daily_use_hours' => 1],
            ['name' => 'Secarropas', 'category' => 'Electrodomésticos', 'default_power_watts' => 2000, 'default_avg_daily_use_hours' => 1],
            ['name' => 'Aspiradora', 'category' => 'Electrodomésticos', 'default_power_watts' => 1200, 'default_avg_daily_use_hours' => 0.5],
            // Entretenimiento
            ['name' => 'Televisor', 'category' => 'Entretenimiento', 'default_power_watts' => 100, 'default_avg_daily_use_hours' => 4],
            ['name' => 'Equipo de Música', 'category' => 'Entretenimiento', 'default_power_watts' => 50, 'default_avg_daily_use_hours' => 2],
            ['name' => 'Consola de Videojuegos', 'category' => 'Entretenimiento', 'default_power_watts' => 70, 'default_avg_daily_use_hours' => 2],
            // Cocina
            ['name' => 'Horno Eléctrico', 'category' => 'Cocina', 'default_power_watts' => 2000, 'default_avg_daily_use_hours' => 1],
            ['name' => 'Anafe Eléctrico', 'category' => 'Cocina', 'default_power_watts' => 1500, 'default_avg_daily_use_hours' => 1],
            ['name' => 'Cafetera', 'category' => 'Cocina', 'default_power_watts' => 900, 'default_avg_daily_use_hours' => 0.5],
            // Oficina
            ['name' => 'Computadora', 'category' => 'Oficina', 'default_power_watts' => 120, 'default_avg_daily_use_hours' => 8],
            ['name' => 'Impresora', 'category' => 'Oficina', 'default_power_watts' => 30, 'default_avg_daily_use_hours' => 0.5],
            ['name' => 'Router', 'category' => 'Oficina', 'default_power_watts' => 10, 'default_avg_daily_use_hours' => 24],
            // Otros
            ['name' => 'Bomba de Agua', 'category' => 'Otros', 'default_power_watts' => 500, 'default_avg_daily_use_hours' => 1],
            ['name' => 'Freezer', 'category' => 'Otros', 'default_power_watts' => 180, 'default_avg_daily_use_hours' => 24],
        ];
        foreach ($types as $type) {
            \App\Models\EquipmentType::firstOrCreate([
                'category_id' => $catIds[$type['category']],
                'name' => $type['name'],
                'default_power_watts' => $type['default_power_watts'],
                'default_avg_daily_use_hours' => $type['default_avg_daily_use_hours'],
            ]);
        }
    }
}
