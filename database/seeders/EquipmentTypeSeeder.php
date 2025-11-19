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
            ['name' => 'Aire Acondicionado (2200 frigorías)', 'category' => 'Climatización', 'default_power_watts' => 1013, 'default_avg_daily_use_hours' => 6],
            ['name' => 'Aire Acondicionado (3500 frigorías)', 'category' => 'Climatización', 'default_power_watts' => 1613, 'default_avg_daily_use_hours' => 6],
            ['name' => 'Aire Acondicionado (4500 frigorías)', 'category' => 'Climatización', 'default_power_watts' => 2153, 'default_avg_daily_use_hours' => 6],
            ['name' => 'Aire Acondicionado Inverter (2200 frigorías)', 'category' => 'Climatización', 'default_power_watts' => 658, 'default_avg_daily_use_hours' => 6],
            ['name' => 'Ventilador de techo', 'category' => 'Climatización', 'default_power_watts' => 75, 'default_avg_daily_use_hours' => 6],
            ['name' => 'Ventilador de pie', 'category' => 'Climatización', 'default_power_watts' => 60, 'default_avg_daily_use_hours' => 6],
            ['name' => 'Caloventor', 'category' => 'Climatización', 'default_power_watts' => 2000, 'default_avg_daily_use_hours' => 4],
            ['name' => 'Estufa de Cuarzo (2 velas)', 'category' => 'Climatización', 'default_power_watts' => 1200, 'default_avg_daily_use_hours' => 4],
            ['name' => 'Estufa Halógena', 'category' => 'Climatización', 'default_power_watts' => 1400, 'default_avg_daily_use_hours' => 4],
            ['name' => 'Radiador Eléctrico', 'category' => 'Climatización', 'default_power_watts' => 1500, 'default_avg_daily_use_hours' => 6],
            ['name' => 'Panel Calefactor (Mica/Cerámico)', 'category' => 'Climatización', 'default_power_watts' => 1000, 'default_avg_daily_use_hours' => 6],
            ['name' => 'Humidificador', 'category' => 'Climatización', 'default_power_watts' => 35, 'default_avg_daily_use_hours' => 8],
            ['name' => 'Deshumidificador', 'category' => 'Climatización', 'default_power_watts' => 300, 'default_avg_daily_use_hours' => 8],

            // Iluminación
            ['name' => 'Lámpara LED 5W (Eq. 40W)', 'category' => 'Iluminación', 'default_power_watts' => 5, 'default_avg_daily_use_hours' => 6],
            ['name' => 'Lámpara LED 9W (Eq. 60W)', 'category' => 'Iluminación', 'default_power_watts' => 9, 'default_avg_daily_use_hours' => 6],
            ['name' => 'Lámpara LED 12W (Eq. 75W)', 'category' => 'Iluminación', 'default_power_watts' => 12, 'default_avg_daily_use_hours' => 6],
            ['name' => 'Lámpara Bajo Consumo 20W', 'category' => 'Iluminación', 'default_power_watts' => 20, 'default_avg_daily_use_hours' => 6],
            ['name' => 'Lámpara Halógena 40W', 'category' => 'Iluminación', 'default_power_watts' => 40, 'default_avg_daily_use_hours' => 6],
            ['name' => 'Tubo Fluorescente 36W', 'category' => 'Iluminación', 'default_power_watts' => 36, 'default_avg_daily_use_hours' => 8],
            ['name' => 'Tira LED (por metro)', 'category' => 'Iluminación', 'default_power_watts' => 14, 'default_avg_daily_use_hours' => 4],

            // Electrodomésticos (Línea Blanca)
            ['name' => 'Heladera con Freezer', 'category' => 'Electrodomésticos', 'default_power_watts' => 150, 'default_avg_daily_use_hours' => 24], // Ciclo intermitente
            ['name' => 'Heladera con Freezer Inverter', 'category' => 'Electrodomésticos', 'default_power_watts' => 100, 'default_avg_daily_use_hours' => 24],
            ['name' => 'Freezer Horizontal', 'category' => 'Electrodomésticos', 'default_power_watts' => 180, 'default_avg_daily_use_hours' => 24],
            ['name' => 'Lavarropas Automático (Agua fría)', 'category' => 'Electrodomésticos', 'default_power_watts' => 500, 'default_avg_daily_use_hours' => 1],
            ['name' => 'Lavarropas Automático (Con calentamiento)', 'category' => 'Electrodomésticos', 'default_power_watts' => 2500, 'default_avg_daily_use_hours' => 1],
            ['name' => 'Secarropas por calor', 'category' => 'Electrodomésticos', 'default_power_watts' => 2500, 'default_avg_daily_use_hours' => 1],
            ['name' => 'Secarropas centrífugo', 'category' => 'Electrodomésticos', 'default_power_watts' => 300, 'default_avg_daily_use_hours' => 0.5],
            ['name' => 'Lavavajillas', 'category' => 'Electrodomésticos', 'default_power_watts' => 1500, 'default_avg_daily_use_hours' => 1.5],
            ['name' => 'Plancha', 'category' => 'Electrodomésticos', 'default_power_watts' => 1500, 'default_avg_daily_use_hours' => 0.5],
            ['name' => 'Plancha a Vapor', 'category' => 'Electrodomésticos', 'default_power_watts' => 2000, 'default_avg_daily_use_hours' => 0.5],
            ['name' => 'Aspiradora', 'category' => 'Electrodomésticos', 'default_power_watts' => 1200, 'default_avg_daily_use_hours' => 0.5],

            // Cocina
            ['name' => 'Horno Eléctrico', 'category' => 'Cocina', 'default_power_watts' => 2000, 'default_avg_daily_use_hours' => 1],
            ['name' => 'Microondas', 'category' => 'Cocina', 'default_power_watts' => 800, 'default_avg_daily_use_hours' => 0.3],
            ['name' => 'Anafe Eléctrico (1 hornalla)', 'category' => 'Cocina', 'default_power_watts' => 1500, 'default_avg_daily_use_hours' => 1],
            ['name' => 'Pava Eléctrica', 'category' => 'Cocina', 'default_power_watts' => 2000, 'default_avg_daily_use_hours' => 0.3],
            ['name' => 'Tostadora', 'category' => 'Cocina', 'default_power_watts' => 800, 'default_avg_daily_use_hours' => 0.2],
            ['name' => 'Cafetera de Filtro', 'category' => 'Cocina', 'default_power_watts' => 900, 'default_avg_daily_use_hours' => 0.5],
            ['name' => 'Cafetera Expreso', 'category' => 'Cocina', 'default_power_watts' => 1300, 'default_avg_daily_use_hours' => 0.2],
            ['name' => 'Licuadora', 'category' => 'Cocina', 'default_power_watts' => 300, 'default_avg_daily_use_hours' => 0.1],
            ['name' => 'Batidora de mano', 'category' => 'Cocina', 'default_power_watts' => 200, 'default_avg_daily_use_hours' => 0.2],
            ['name' => 'Procesadora de Alimentos', 'category' => 'Cocina', 'default_power_watts' => 600, 'default_avg_daily_use_hours' => 0.2],
            ['name' => 'Freidora de Aire (Air Fryer)', 'category' => 'Cocina', 'default_power_watts' => 1500, 'default_avg_daily_use_hours' => 0.5],
            ['name' => 'Sandwichera', 'category' => 'Cocina', 'default_power_watts' => 800, 'default_avg_daily_use_hours' => 0.2],
            ['name' => 'Exprimidor Eléctrico', 'category' => 'Cocina', 'default_power_watts' => 150, 'default_avg_daily_use_hours' => 0.1],

            // Entretenimiento y Electrónica
            ['name' => 'Televisor LED 32"', 'category' => 'Entretenimiento', 'default_power_watts' => 40, 'default_avg_daily_use_hours' => 4],
            ['name' => 'Televisor LED 50" 4K', 'category' => 'Entretenimiento', 'default_power_watts' => 100, 'default_avg_daily_use_hours' => 4],
            ['name' => 'Consola de Videojuegos (PS5/Xbox)', 'category' => 'Entretenimiento', 'default_power_watts' => 200, 'default_avg_daily_use_hours' => 2],
            ['name' => 'Decodificador TV Cable', 'category' => 'Entretenimiento', 'default_power_watts' => 20, 'default_avg_daily_use_hours' => 24],
            ['name' => 'Equipo de Audio', 'category' => 'Entretenimiento', 'default_power_watts' => 50, 'default_avg_daily_use_hours' => 2],
            ['name' => 'Proyector', 'category' => 'Entretenimiento', 'default_power_watts' => 250, 'default_avg_daily_use_hours' => 2],

            // Oficina / Home Office
            ['name' => 'Notebook / Laptop', 'category' => 'Oficina', 'default_power_watts' => 50, 'default_avg_daily_use_hours' => 8],
            ['name' => 'PC de Escritorio (CPU + Monitor)', 'category' => 'Oficina', 'default_power_watts' => 250, 'default_avg_daily_use_hours' => 8],
            ['name' => 'Monitor LED 24"', 'category' => 'Oficina', 'default_power_watts' => 30, 'default_avg_daily_use_hours' => 8],
            ['name' => 'Impresora Láser', 'category' => 'Oficina', 'default_power_watts' => 400, 'default_avg_daily_use_hours' => 0.1],
            ['name' => 'Impresora Inyección de Tinta', 'category' => 'Oficina', 'default_power_watts' => 30, 'default_avg_daily_use_hours' => 0.2],
            ['name' => 'Escáner', 'category' => 'Oficina', 'default_power_watts' => 20, 'default_avg_daily_use_hours' => 0.2],
            ['name' => 'Modem / Router WiFi', 'category' => 'Oficina', 'default_power_watts' => 10, 'default_avg_daily_use_hours' => 24],
            ['name' => 'Cargador de Celular', 'category' => 'Oficina', 'default_power_watts' => 5, 'default_avg_daily_use_hours' => 4],

            // Otros (Agua y Exteriores)
            ['name' => 'Termotanque Eléctrico (80-100L)', 'category' => 'Otros', 'default_power_watts' => 1500, 'default_avg_daily_use_hours' => 4],
            ['name' => 'Bomba de Agua (1/2 HP)', 'category' => 'Otros', 'default_power_watts' => 375, 'default_avg_daily_use_hours' => 1],
            ['name' => 'Bomba de Agua (3/4 HP)', 'category' => 'Otros', 'default_power_watts' => 550, 'default_avg_daily_use_hours' => 1],
            ['name' => 'Secador de Pelo', 'category' => 'Otros', 'default_power_watts' => 1800, 'default_avg_daily_use_hours' => 0.2],
            ['name' => 'Planchita de Pelo', 'category' => 'Otros', 'default_power_watts' => 40, 'default_avg_daily_use_hours' => 0.2],
        ];
        foreach ($types as $type) {
            \App\Models\EquipmentType::updateOrCreate(
                ['name' => $type['name']],
                [
                    'category_id' => $catIds[$type['category']],
                    'default_power_watts' => $type['default_power_watts'],
                    'default_avg_daily_use_hours' => $type['default_avg_daily_use_hours'],
                ]
            );
        }
    }
}
