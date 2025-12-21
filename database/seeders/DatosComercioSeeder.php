<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Entity, User, Contract, Invoice, Room, Equipment, EquipmentUsage, EquipmentType, EquipmentCategory, Locality, UtilityCompany};

class DatosComercioSeeder extends Seeder
{
    /**
     * Crear entidad de prueba tipo Comercio
     * Cafetería/Restaurant en San Juan
     */
    public function run(): void
    {
        $this->command->info('☕ Creando Comercio de prueba...');

        // 1. Locality (San Juan Capital)
        $sanJuanProvince = \App\Models\Province::where('name', 'San Juan')->first();
        $locality = Locality::firstOrCreate(
            ['name' => 'Capital', 'province_id' => $sanJuanProvince->id],
            ['postal_code' => '5400', 'latitude' => -31.5375, 'longitude' => -68.5364]
        );

        // 2. Entity Comercio
        $entity = Entity::firstOrCreate(
            ['name' => 'Cafetería La Esquina'],
            [
                'type' => 'comercio',
                'address_street' => 'Av. San Martín 890',
                'address_postal_code' => '5400',
                'locality_id' => $locality->id,
                'description' => 'Cafetería y Restaurant - Abierto 7 días',
                'square_meters' => 120,
                'people_count' => 8,
            ]
        );

        // 3. Contract & Provider
        $provider = \App\Models\Proveedor::firstOrCreate(['name' => 'Naturgy']);
        $company = UtilityCompany::firstOrCreate(['name' => 'Naturgy']);

        $contract = Contract::firstOrCreate(
            ['contract_number' => 'CO-55444'],
            [
                'entity_id' => $entity->id,
                'proveedor_id' => $provider->id,
                'utility_company_id' => $company->id,
                'client_number' => '09384756300',
                'supply_number' => '09384756300',
                'meter_number' => '7654321',
                'tariff_type' => 'T3-G3',
                'rate_name' => 'T3-G3 Comercios Grandes',
                'start_date' => now()->subYear(),
            ]
        );

        // 4. Invoices (3 bimestrales: alto consumo comercial)
        $invoicesData = [
            // Verano (ene-mar): aires + cocina full
            [
                'invoice_number' => 'CO-001-2025',
                'issue_date' => '2025-03-28',
                'start_date' => '2025-01-15',
                'end_date' => '2025-03-20',
                'total_energy_consumed_kwh' => 2350.00, // Alto: heladeras comerciales + aires + hornos
                'cost_for_energy' => 255000.00,
                'taxes' => 92000.00,
                'total_amount' => 347000.00,
            ],
            // Otoño (mar-may): sin aires
            [
                'invoice_number' => 'CO-002-2025',
                'issue_date' => '2025-05-25',
                'start_date' => '2025-03-21',
                'end_date' => '2025-05-15',
                'total_energy_consumed_kwh' => 1680.00,
                'cost_for_energy' => 182000.00,
                'taxes' => 65000.00,
                'total_amount' => 247000.00,
            ],
            // Invierno (jul-sep): sin aires, menos afluencia
            [
                'invoice_number' => 'CO-003-2025',
                'issue_date' => '2025-09-26',
                'start_date' => '2025-07-16',
                'end_date' => '2025-09-07',
                'total_energy_consumed_kwh' => 1520.00,
                'cost_for_energy' => 165000.00,
                'taxes' => 59000.00,
                'total_amount' => 224000.00,
            ],
        ];

        $createdInvoices = [];
        foreach ($invoicesData as $data) {
            $createdInvoices[] = Invoice::firstOrCreate(
                ['invoice_number' => $data['invoice_number']],
                array_merge($data, ['contract_id' => $contract->id, 'status' => 'paid'])
            );
        }

        // 5. Rooms
        $rooms = [
            'Salón Principal' => Room::firstOrCreate(
                ['name' => 'Salón Principal', 'entity_id' => $entity->id],
                ['description' => '15 mesas', 'square_meters' => 60]
            ),
            'Cocina' => Room::firstOrCreate(
                ['name' => 'Cocina', 'entity_id' => $entity->id],
                ['description' => 'Cocina profesional', 'square_meters' => 30]
            ),
            'Barra' => Room::firstOrCreate(
                ['name' => 'Barra', 'entity_id' => $entity->id],
                ['description' => 'Barra de café y tragos', 'square_meters' => 15]
            ),
            'Depósito' => Room::firstOrCreate(
                ['name' => 'Depósito', 'entity_id' => $entity->id],
                ['description' => 'Almacén y freezers', 'square_meters' => 10]
            ),
            'Baños' => Room::firstOrCreate(
                ['name' => 'Baños', 'entity_id' => $entity->id],
                ['description' => 'Baños públicos', 'square_meters' => 5]
            ),
        ];

        // 6. Equipment (equipos comerciales)
        $equipmentList = [
            // Heladeras y Freezers Comerciales
            ['room' => 'Cocina', 'category' => 'Electrodomésticos', 'type' => 'Heladera con Freezer', 'name' => 'Heladera Comercial Grande', 'power' => 500, 'cantidad' => 1],
            ['room' => 'Barra', 'category' => 'Electrodomésticos', 'type' => 'Heladera pequeña', 'name' => 'Heladera Bebidas', 'power' => 200, 'cantidad' => 1],
            ['room' => 'Depósito', 'category' => 'Electrodomésticos', 'type' => 'Freezer Horizontal', 'name' => 'Freezer Congelados', 'power' => 350, 'cantidad' => 1],

            // Equipos de Cocina
            ['room' => 'Cocina', 'category' => 'Cocina', 'type' => 'Horno Eléctrico', 'name' => 'Horno Industrial', 'power' => 3000, 'cantidad' => 1],
            ['room' => 'Cocina', 'category' => 'Cocina', 'type' => 'Microondas', 'name' => 'Microondas Cocina', 'power' => 1200, 'cantidad' => 1],
            ['room' => 'Cocina', 'category' => 'Cocina', 'type' => 'Anafe Eléctrico (4 hornallas)', 'name' => 'Anafe Principal', 'power' => 4000, 'cantidad' => 1],
            ['room' => 'Cocina', 'category' => 'Electrodomésticos', 'type' => 'Campana Extractora', 'name' => 'Campana Cocina', 'power' => 300, 'cantidad' => 1],

            // Barra - Equipos de Café
            ['room' => 'Barra', 'category' => 'Cocina', 'type' => 'Cafetera', 'name' => 'Cafetera Profesional', 'power' => 2000, 'cantidad' => 1],
            ['room' => 'Barra', 'category' => 'Cocina', 'type' => 'Licuadora', 'name' => 'Licuadora Industrial', 'power' => 600, 'cantidad' => 1],

            // Aires Acondicionados
            ['room' => 'Salón Principal', 'category' => 'Climatización', 'type' => 'Aire Acondicionado (4500 frigorías)', 'name' => 'Aire Salón 1', 'power' => 1800, 'cantidad' => 1],
            ['room' => 'Salón Principal', 'category' => 'Climatización', 'type' => 'Aire Acondicionado (4500 frigorías)', 'name' => 'Aire Salón 2', 'power' => 1800, 'cantidad' => 1],
            ['room' => 'Cocina', 'category' => 'Climatización', 'type' => 'Aire Acondicionado (2200 frigorías)', 'name' => 'Aire Cocina', 'power' => 900, 'cantidad' => 1],

            // Iluminación Comercial (muchas luces)
            ['room' => 'Salón Principal', 'category' => 'Iluminación', 'type' => 'Lámpara LED 12W (Eq. 75W)', 'name' => 'Luces Salón', 'power' => 12, 'cantidad' => 20],
            ['room' => 'Salón Principal', 'category' => 'Iluminación', 'type' => 'Tira LED (5m)', 'name' => 'Tiras Decorativas', 'power' => 36, 'cantidad' => 4],
            ['room' => 'Barra', 'category' => 'Iluminación', 'type' => 'Tubo LED 18W', 'name' => 'Tubos Barra', 'power' => 18, 'cantidad' => 6],
            ['room' => 'Cocina', 'category' => 'Iluminación', 'type' => 'Tubo LED 18W', 'name' => 'Tubos Cocina', 'power' => 18, 'cantidad' => 4],
            ['room' => 'Baños', 'category' => 'Iluminación', 'type' => 'Lámpara LED 5W (Eq. 40W)', 'name' => 'Luces Baños', 'power' => 5, 'cantidad' => 3],

            // Electrónicos
            ['room' => 'Barra', 'category' => 'Oficina', 'type' => 'PC de Escritorio (CPU + Monitor)', 'name' => 'PC Caja', 'power' => 200, 'cantidad' => 1],
            ['room' => 'Salón Principal', 'category' => 'Entretenimiento', 'type' => 'TV LED 40"', 'name' => 'TV Salón', 'power' => 80, 'cantidad' => 1],
            ['room' => 'Barra', 'category' => 'Oficina', 'type' => 'Router Wifi', 'name' => 'Router Comercio', 'power' => 12, 'cantidad' => 1],

            // Lavavajillas Industrial
            ['room' => 'Cocina', 'category' => 'Electrodomésticos', 'type' => 'Lavavajillas', 'name' => 'Lavavajillas Industrial', 'power' => 2400, 'cantidad' => 1],
        ];

        foreach ($equipmentList as $item) {
            $type = EquipmentType::where('name', $item['type'])->first();
            $category = EquipmentCategory::where('name', $item['category'])->first();
            $room = $rooms[$item['room']];

            if ($type && $category && $room) {
                Equipment::firstOrCreate(
                    ['name' => $item['name'], 'room_id' => $room->id],
                    [
                        'type_id' => $type->id,
                        'category_id' => $category->id,
                        'nominal_power_w' => $item['power'],
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command->info('✅ Comercio creado: ' . $entity->name);
        $this->command->info('   Equipos: ' . Equipment::whereIn('room_id', array_column($rooms, 'id'))->count());
        $this->command->info('   Facturas: ' . count($createdInvoices));
    }
}
