<?php

namespace Database\Seeders;

use App\Models\Entity;
use App\Models\Equipment;
use App\Models\EquipmentType;
use App\Models\Room;
use Illuminate\Database\Seeder;

/**
 * Equipos físicos de Casa 27 por ambiente.
 * Fuente de verdad: datoshogar.txt
 *
 * Depende de:
 *  - MasterCleanCatalogueSeeder (categorías y tipos deben existir)
 *  - Casa27Seeder (la entidad "Casa 27" debe existir)
 *
 * Campos:
 *  - watts   → potencia nominal real (fuente: datoshogar.txt)
 *  - hours   → uso diario promedio (0 = manual/estacional)
 *  - pattern → true si tiene hábito definido y reproducible
 *  - label   → etiqueta de eficiencia energética (null = no aplica o desconocida)
 *              A+++ | A++ | A+ | A | B | C | D
 *  - type    → nombre del EquipmentType creado en MasterCleanCatalogueSeeder
 */
class Casa27EquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $entity = Entity::where('name', 'Casa 27')->firstOrFail();

        $rooms = [

            'Cocina / Comedor' => [
                // Split de alta capacidad. Sin dato de marca/modelo → estimación B
                ['name' => 'Aire Grande',         'type' => 'Aire Split',          'watts' => 2500, 'hours' => 0,    'pattern' => false, 'label' => 'B'],
                // Ventiladores no tienen etiqueta EU formal → A por bajo consumo
                ['name' => 'Ventilador de Techo', 'type' => 'Ventilador de Techo', 'watts' => 40,   'hours' => 0,    'pattern' => false, 'label' => 'A'],
                // Microondas doméstico estándar sin marca → B
                ['name' => 'Microondas',           'type' => 'Microondas',          'watts' => 1000, 'hours' => 0.17, 'pattern' => true,  'label' => 'B'],
                // Tubo LED: tecnología intrínsecamente eficiente
                ['name' => 'Tubo Led Cocina',      'type' => 'Tubo LED',            'watts' => 10,   'hours' => 8,    'pattern' => true,  'label' => 'A+++'],
                ['name' => 'Foco Ventilador 1',    'type' => 'Foco LED',            'watts' => 10,   'hours' => 0.33, 'pattern' => true,  'label' => 'A+++'],
                ['name' => 'Foco Ventilador 2',    'type' => 'Foco LED',            'watts' => 10,   'hours' => 0.33, 'pattern' => true,  'label' => 'A+++'],
                ['name' => 'Foco Ventilador 3',    'type' => 'Foco LED',            'watts' => 10,   'hours' => 0.33, 'pattern' => true,  'label' => 'A+++'],
            ],

            'Living' => [
                // TV sin dato de marca. 120W para >50" → panel LED moderno → B
                ['name' => 'TV Grande',                  'type' => 'TV Grande (>50")',     'watts' => 120, 'hours' => 6,    'pattern' => true,  'label' => 'B'],
                ['name' => 'Foco Living',                'type' => 'Foco LED',             'watts' => 5,   'hours' => 0.17, 'pattern' => true,  'label' => 'A+++'],
                // Router 5W 24hs → sin etiqueta EU formal → A
                ['name' => 'Router Wifi',                'type' => 'Router Wifi / ONT',   'watts' => 5,   'hours' => 24,   'pattern' => true,  'label' => 'A'],
                ['name' => 'Ventilador de Techo Living', 'type' => 'Ventilador de Techo', 'watts' => 40,  'hours' => 0,    'pattern' => false, 'label' => 'A'],
            ],

            'Habitación Hermanos' => [
                // PC Gamer 150W (real medido). Mid-range sin cert. oficial → C
                ['name' => 'PC Gamer',            'type' => 'PC Gamer / Workstation', 'watts' => 150,  'hours' => 8,  'pattern' => true,  'label' => 'C'],
                // Monitor Philips LED IPS → B
                ['name' => 'Monitor PC PHILLPS',  'type' => 'Monitor LED',             'watts' => 40,   'hours' => 8,  'pattern' => true,  'label' => 'B'],
                // Aire portátil: siempre D por diseño termodinámico
                ['name' => 'Aire Portatil',        'type' => 'Aire Portátil',          'watts' => 1400, 'hours' => 0,  'pattern' => false, 'label' => 'D'],
                ['name' => 'Ventilador de Techo', 'type' => 'Ventilador de Techo',    'watts' => 40,   'hours' => 0,  'pattern' => false, 'label' => 'A'],
                ['name' => 'Mesita de Luz',        'type' => 'Foco LED',               'watts' => 9,    'hours' => 4,  'pattern' => true,  'label' => 'A+++'],
            ],

            'Habitación Mamá' => [
                ['name' => 'Ventilador de Techo', 'type' => 'Ventilador de Techo', 'watts' => 60, 'hours' => 0, 'pattern' => false, 'label' => 'A'],
                ['name' => 'Foco Ventilador',     'type' => 'Foco LED',            'watts' => 5,  'hours' => 0, 'pattern' => false, 'label' => 'A+++'],
                ['name' => 'Foco Mesita de Luz',  'type' => 'Foco LED',            'watts' => 5,  'hours' => 0, 'pattern' => false, 'label' => 'A+++'],
            ],

            'Habitación Papa' => [
                ['name' => 'Ventilador de Techo', 'type' => 'Ventilador de Techo',  'watts' => 60, 'hours' => 0, 'pattern' => false, 'label' => 'A'],
                // TV chico 85W → panel antiguo o baja gama → C
                ['name' => 'TV Chico',             'type' => 'TV Estándar / Chico', 'watts' => 85,  'hours' => 4, 'pattern' => true,  'label' => 'C'],
                // 40W = incandescente con seguridad → D
                ['name' => 'Foco Ventilador',     'type' => 'Foco Incandescente',   'watts' => 40, 'hours' => 0, 'pattern' => false, 'label' => 'D'],
                ['name' => 'Foco Mesita de Luz',  'type' => 'Foco Incandescente',   'watts' => 40, 'hours' => 0, 'pattern' => false, 'label' => 'D'],
            ],

            'Baño' => [
                ['name' => 'Foco Baño',          'type' => 'Foco LED',           'watts' => 10,   'hours' => 0, 'pattern' => false, 'label' => 'A+++'],
                // Secadores no tienen etiqueta EU estándar
                ['name' => 'Secador de Pelo',    'type' => 'Secador de Pelo',    'watts' => 1000, 'hours' => 0, 'pattern' => false, 'label' => null],
                // Afeitadora recargable → no aplica etiqueta de consumo
                ['name' => 'Maquina de Afeitar', 'type' => 'Máquina de Afeitar', 'watts' => 12,   'hours' => 0, 'pattern' => false, 'label' => null],
            ],

            'Fondo' => [
                ['name' => 'Foco Led Grande', 'type' => 'Foco LED', 'watts' => 12, 'hours' => 0, 'pattern' => false, 'label' => 'A+++'],
            ],

            'Garage' => [
                // Heladera sin marca. 150W nominales → modelo >10 años → C
                ['name' => 'Heladera',       'type' => 'Heladera con Freezer', 'watts' => 150,  'hours' => 8, 'pattern' => true,  'label' => 'C'],
                // Lavarropas 2500W (pico de centrifugado). Sin marca → B
                ['name' => 'Lavarropa',      'type' => 'Lavarropas',           'watts' => 2500, 'hours' => 0, 'pattern' => false, 'label' => 'B'],
                ['name' => 'Foco Garage 1',  'type' => 'Foco LED',             'watts' => 5,    'hours' => 0, 'pattern' => false, 'label' => 'A+++'],
                ['name' => 'Foco Garage 2',  'type' => 'Foco LED',             'watts' => 5,    'hours' => 0, 'pattern' => false, 'label' => 'A+++'],
            ],

            'Otros / Portátiles' => [
                // Cargadores USB: no tienen etiqueta formal
                ['name' => 'Celular 1',            'type' => 'Cargador Móvil', 'watts' => 15, 'hours' => 4, 'pattern' => true,  'label' => null],
                ['name' => 'Celular 2',            'type' => 'Cargador Móvil', 'watts' => 15, 'hours' => 4, 'pattern' => true,  'label' => null],
                ['name' => 'Celular 3',            'type' => 'Cargador Móvil', 'watts' => 15, 'hours' => 4, 'pattern' => true,  'label' => null],
                ['name' => 'Foco Frente / Vereda', 'type' => 'Foco LED',       'watts' => 5,  'hours' => 0, 'pattern' => false, 'label' => 'A+++'],
                ['name' => 'Foco Hall',            'type' => 'Foco LED',       'watts' => 5,  'hours' => 0, 'pattern' => false, 'label' => 'A+++'],
            ],
        ];

        foreach ($rooms as $roomName => $equipments) {
            $room = Room::updateOrCreate(
                ['name' => $roomName, 'entity_id' => $entity->id]
            );

            foreach ($equipments as $eq) {
                $type = EquipmentType::where('name', $eq['type'])->first();

                if (!$type) {
                    $this->command->warn("Tipo no encontrado: [{$eq['type']}] para equipo [{$eq['name']}]. Saltando.");
                    continue;
                }

                $equip = Equipment::firstOrNew(
                    ['name' => $eq['name'], 'room_id' => $room->id]
                );

                $equip->type_id             = $type->id;
                $equip->category_id         = $type->category_id;
                $equip->nominal_power_w     = $eq['watts'];
                $equip->avg_daily_use_hours = $eq['hours'];
                $equip->has_defined_pattern = $eq['pattern'];
                $equip->energy_label        = $eq['label'];
                $equip->is_active           = true;
                $equip->save();
            }
        }
    }
}
