<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener las habitaciones
        $rooms = [
            'Cocina / Comedor' => [
                ['name' => 'Aire Grande', 'type_name' => 'Aire Acondicionado Portátil', 'power' => 2500],
                ['name' => 'Ventilador de Techo', 'type_name' => 'Ventilador de techo', 'power' => 60],
                ['name' => 'Microondas', 'type_name' => 'Microondas', 'power' => 1000],
                ['name' => 'Focos Ventilador', 'type_name' => 'Lámpara LED 5W (Eq. 40W)', 'power' => 5],
                ['name' => 'Focos Ventilador', 'type_name' => 'Lámpara LED 5W (Eq. 40W)', 'power' => 5],
                ['name' => 'Focos Ventilador', 'type_name' => 'Lámpara LED 5W (Eq. 40W)', 'power' => 5],
                ['name' => 'Tubo Led Cocina', 'type_name' => 'Lámpara LED 5W (Eq. 40W)', 'power' => 5],
            ],
            'Living' => [
                ['name' => 'Ventilador de Techo', 'type_name' => 'Ventilador de techo', 'power' => 60],
                ['name' => 'TV Grande', 'type_name' => 'Televisor LED 50" 4K', 'power' => 120],
                ['name' => 'Foco Living', 'type_name' => 'Lámpara LED 5W (Eq. 40W)', 'power' => 5],
                ['name' => 'Router Wifi', 'type_name' => 'Modem / Router WiFi', 'power' => 20],
            ],
            'Habitación Mamá' => [
                ['name' => 'Ventilador de Techo', 'type_name' => 'Ventilador de techo', 'power' => 60],
                ['name' => 'Foco Ventilador', 'type_name' => 'Lámpara LED 5W (Eq. 40W)', 'power' => 5],
                ['name' => 'Foco Mesita de Luz', 'type_name' => 'Lámpara LED 5W (Eq. 40W)', 'power' => 5],
            ],
            'Habitación Papa' => [
                ['name' => 'Ventilador de Techo', 'type_name' => 'Ventilador de techo', 'power' => 60],
                ['name' => 'Foco Ventilador', 'type_name' => 'Lámpara Halógena 40W', 'power' => 40],
                ['name' => 'Foco Mesita de Luz', 'type_name' => 'Lámpara Halógena 40W', 'power' => 40],
                ['name' => 'TV Chico', 'type_name' => 'Televisor LED 32"', 'power' => 85],
            ],
            'Habitación Hermanos' => [
                ['name' => 'PC Gamer', 'type_name' => 'PC de Escritorio (CPU + Monitor)', 'power' => 600],
                ['name' => 'Monitor PC', 'type_name' => 'Monitor LED 24"', 'power' => 50],
                ['name' => 'Monitor PC', 'type_name' => 'Monitor LED 24"', 'power' => 50],
                ['name' => 'Ventilador de Techo', 'type_name' => 'Ventilador de techo', 'power' => 60],
                ['name' => 'Foco Ventilador de Techo', 'type_name' => 'Lámpara LED 5W (Eq. 40W)', 'power' => 5],
                ['name' => 'Mesita de Luz', 'type_name' => 'Lámpara LED 5W (Eq. 40W)', 'power' => 5],
                ['name' => 'Aire Portatil', 'type_name' => 'Aire Acondicionado Portátil', 'power' => 1400],
            ],
            'Baño' => [
                ['name' => 'Foco Baño', 'type_name' => 'Lámpara LED 12W (Eq. 75W)', 'power' => 12],
                ['name' => 'Secador de Pelo', 'type_name' => 'Secador de Pelo', 'power' => 1000],
                ['name' => 'Maquina de Afeitar', 'type_name' => 'Lámpara LED 12W (Eq. 75W)', 'power' => 12],
            ],
            'Fondo' => [
                ['name' => 'Foco Led Grande', 'type_name' => 'Lámpara LED 12W (Eq. 75W)', 'power' => 12],
            ],
            'Garage' => [
                ['name' => 'Focos Garage', 'type_name' => 'Lámpara LED 5W (Eq. 40W)', 'power' => 5],
                ['name' => 'Focos Garage', 'type_name' => 'Lámpara LED 5W (Eq. 40W)', 'power' => 5],
                ['name' => 'Heladera', 'type_name' => 'Heladera con Freezer', 'power' => 150],
                ['name' => 'Lavarropa', 'type_name' => 'Lavarropas Automático (Agua fría)', 'power' => 2500],
            ],
            'Hall' => [
                ['name' => 'Foco', 'type_name' => 'Lámpara LED 5W (Eq. 40W)', 'power' => 5],
            ],
            'Frente / Vereda' => [
                ['name' => 'Foco', 'type_name' => 'Lámpara LED 5W (Eq. 40W)', 'power' => 5],
            ],
            'Portátiles' => [
                ['name' => 'Cargadores de Celular', 'type_name' => 'Cargador de Celular', 'power' => 5],
                ['name' => 'Cargadores de Celular', 'type_name' => 'Cargador de Celular', 'power' => 5],
                ['name' => 'Cargadores de Celular', 'type_name' => 'Cargador de Celular', 'power' => 5],
            ],
        ];

        foreach ($rooms as $roomName => $equipments) {
            $room = \App\Models\Room::where('name', $roomName)->first();
            
            if (!$room) {
                echo "⚠️  Habitación '$roomName' no encontrada\n";
                continue;
            }

            foreach ($equipments as $equipData) {
                $type = \App\Models\EquipmentType::where('name', $equipData['type_name'])->first();
                
                if (!$type) {
                    echo "⚠️  Tipo '{$equipData['type_name']}' no encontrado\n";
                    continue;
                }

                \App\Models\Equipment::create([
                    'room_id' => $room->id,
                    'name' => $equipData['name'],
                    'type_id' => $type->id,
                    'category_id' => $type->category_id,
                    'nominal_power_w' => $equipData['power'],
                    'is_active' => true,
                ]);
            }
            
            echo "✅ {$roomName}: " . count($equipments) . " equipos creados\n";
        }
    }
}
