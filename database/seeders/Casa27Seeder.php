<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Entity;
use App\Models\Room;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentType;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Locality;
use App\Models\Province;
use App\Models\Plan;
use App\Models\Proveedor;
use App\Models\UtilityCompany;
use Illuminate\Database\Seeder;

class Casa27Seeder extends Seeder
{
    public function run(): void
    {
        // 1. Asegurar Plan y Usuario Base
        $plan = Plan::firstOrCreate(['name' => 'Premium'], [
            'max_entities' => 5,
            'allowed_entity_types' => ['hogar'],
            'price' => 0
        ]);

        $user = User::firstOrCreate(['email' => 'casa27@modoahorro.com'], [
            'name' => 'Usuario Casa 27',
            'password' => bcrypt('password'),
        ]);

        // 2. Localidad y Empresa (Naturgy - San Juan como ejemplo)
        $province = Province::where('name', 'San Juan')->first() ?? Province::first();
        $locality = Locality::where('name', 'Capital')->first() ?? Locality::first();
        
        $utility = UtilityCompany::updateOrCreate(['name' => 'Naturgy']);

        $proveedor = Proveedor::updateOrCreate(['name' => 'Naturgy Distribuidora'], [
            'province_id' => $province->id
        ]);

        // 3. La Entidad (Casa 27) con su Perfil Térmico
        $entity = Entity::updateOrCreate(['name' => 'Casa 27'], [
            'type' => 'hogar',
            'locality_id' => $locality->id,
            'address_street' => 'Calle Carlos Gardel Casa 27 B° Enoe Bravo',
            'address_postal_code' => '5300',
            'square_meters' => 450,
            'people_count' => 4,
            'description' => 'Mi Hogar de Prueba - Gestión Física Optimizada',
            'thermal_profile' => [
                'roof_type' => 'concrete_slab',
                'window_type' => 'single_glass',
                'window_frame' => 'aluminum',
                'orientation' => 'este_oeste',
                'sun_exposure' => 'medium',
                'roof_insulation' => true,
                'drafts_detected' => false,
                'south_window' => false,
                'thermal_score' => 50,
                'energy_label' => 'D',
            ]
        ]);

        if (!$user->entities()->where('entity_id', $entity->id)->exists()) {
            $user->entities()->attach($entity->id, ['plan_id' => $plan->id, 'subscribed_at' => now()]);
        }

        // 4. El Contrato
        $contract = Contract::updateOrCreate(['contract_number' => '36697'], [
            'entity_id' => $entity->id,
            'proveedor_id' => $proveedor->id,
            'utility_company_id' => $utility->id,
            'meter_number' => '9618495',
            'supply_number' => '07182202700',
            'rate_name' => 'T1-R1',
            'is_active' => true,
        ]);

        // 5. Facturas Históricas
        $invoices = [
            ['num' => '137756868', 'date' => '2025-03-28', 'start' => '2025-01-15', 'end' => '2025-03-20', 'kwh' => 624, 'amt' => 95955.17],
            ['num' => '138579184', 'date' => '2025-06-25', 'start' => '2025-03-21', 'end' => '2025-05-15', 'kwh' => 123, 'amt' => 18528.02],
            ['num' => '139151993', 'date' => '2025-08-27', 'start' => '2025-05-14', 'end' => '2025-07-15', 'kwh' => 83, 'amt' => 10955.71],
            ['num' => '139459979', 'date' => '2025-09-26', 'start' => '2025-07-16', 'end' => '2025-09-07', 'kwh' => 78, 'amt' => 10778.25],
        ];

        foreach ($invoices as $inv) {
            Invoice::updateOrCreate(['invoice_number' => $inv['num']], [
                'contract_id' => $contract->id,
                'issue_date' => $inv['date'],
                'start_date' => $inv['start'],
                'end_date' => $inv['end'],
                'total_energy_consumed_kwh' => $inv['kwh'],
                'total_amount' => $inv['amt'],
                'status' => 'paid',
                'source' => 'manual',
            ]);
        }

        // 6. Habitaciones y Equipos (Nueva Taxonomía)
        $roomsData = [
            'Cocina / Comedor' => [
                ['name' => 'Aire Grande', 'cat' => 'Climatización', 'watts' => 2500, 'hours' => 8],
                ['name' => 'Ventilador de Techo', 'cat' => 'Climatización', 'watts' => 60, 'hours' => 5],
                ['name' => 'Microondas', 'cat' => 'Cocina y Preparación', 'watts' => 1000, 'hours' => 0.2],
                ['name' => 'Tubo Led Cocina', 'cat' => 'Iluminación', 'watts' => 10, 'hours' => 4],
            ],
            'Living' => [
                ['name' => 'TV Grande', 'cat' => 'Entretenimiento y Multimedia', 'watts' => 120, 'hours' => 4],
                ['name' => 'Foco Living', 'cat' => 'Iluminación', 'watts' => 5, 'hours' => 3],
                ['name' => 'Router Wifi', 'cat' => 'Seguridad y Redes', 'watts' => 20, 'hours' => 24],
            ],
            'Habitación Hermanos' => [
                ['name' => 'PC Gamer', 'cat' => 'Informática y Oficina', 'watts' => 600, 'hours' => 5],
                ['name' => 'Monitor PC', 'cat' => 'Informática y Oficina', 'watts' => 50, 'hours' => 5],
                ['name' => 'Aire Portatil', 'cat' => 'Climatización', 'watts' => 1400, 'hours' => 4],
            ],
            'Baño' => [
                ['name' => 'Secador de Pelo', 'cat' => 'Salud y Cuidado Personal', 'watts' => 1000, 'hours' => 0.1],
                ['name' => 'Foco Baño', 'cat' => 'Iluminación', 'watts' => 5, 'hours' => 1],
            ],
            'Garage' => [
                ['name' => 'Heladera', 'cat' => 'Refrigeración', 'watts' => 150, 'hours' => 24],
                ['name' => 'Lavarropa', 'cat' => 'Lavado y Limpieza', 'watts' => 2500, 'hours' => 1],
            ],
        ];

        foreach ($roomsData as $roomName => $equipments) {
            $room = Room::updateOrCreate([
                'name' => $roomName, 
                'entity_id' => $entity->id
            ]);

            foreach ($equipments as $eq) {
                // Buscar categoría por nombre para evitar IDs estáticos
                $category = EquipmentCategory::where('name', $eq['cat'])->first();
                
                // Determinar el nombre del tipo (Taxonomía)
                $typeName = $eq['type'] ?? $eq['name'];
                if ($eq['cat'] === 'Iluminación') {
                    $typeName = 'Foco Estandar';
                }

                // Buscar o Crear el Tipo de Equipo
                $type = EquipmentType::updateOrCreate(['name' => $typeName], [
                    'category_id' => $category->id,
                    'default_power_watts' => $eq['watts'],
                    'default_avg_daily_use_hours' => $eq['hours'],
                    'is_climatization' => ($eq['cat'] === 'Climatización')
                ]);

                Equipment::updateOrCreate(['name' => $eq['name'], 'room_id' => $room->id], [
                    'category_id' => $category->id,
                    'type_id' => $type->id,
                    'nominal_power_w' => $eq['watts'],
                    'avg_daily_use_hours' => $eq['hours'],
                    'is_active' => true
                ]);
            }
        }
    }
}
