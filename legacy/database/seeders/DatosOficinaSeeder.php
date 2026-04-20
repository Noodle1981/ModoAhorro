<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Entity, User, Contract, Invoice, Room, Equipment, EquipmentUsage, EquipmentType, EquipmentCategory, Locality};
use App\Models\UtilityCompany;

class DatosOficinaSeeder extends Seeder
{
    /**
     * Crear entidad de prueba tipo Oficina
     * Oficina de 10 empleados en San Juan
     */
    public function run(): void
    {
        $this->command->info('🏢 Creando Oficina de prueba...');

        // 1. Locality (San Juan Capital)
        $sanJuanProvince = \App\Models\Province::where('name', 'San Juan')->first();
        $locality = Locality::firstOrCreate(
            ['name' => 'Capital', 'province_id' => $sanJuanProvince->id],
            ['postal_code' => '5400', 'latitude' => -31.5375, 'longitude' => -68.5364]
        );

        // 2. Entity Oficina
        $entity = Entity::firstOrCreate(
            ['name' => 'Oficina Tecno Solutions'],
            [
                'type' => 'oficina',
                'address_street' => 'Av. Libertador 450, Piso 3',
                'address_postal_code' => '5400',
                'locality_id' => $locality->id,
                'description' => 'Oficina de desarrollo de software - 10 empleados',
                'square_meters' => 150,
                'people_count' => 10,
            ]
        );

        // 3. Contract & Provider
        $provider = \App\Models\Proveedor::firstOrCreate(['name' => 'Naturgy']);
        $company = UtilityCompany::firstOrCreate(['name' => 'Naturgy']);

        $contract = Contract::firstOrCreate(
            ['contract_number' => 'OF-98765'],
            [
                'entity_id' => $entity->id,
                'proveedor_id' => $provider->id,
                'utility_company_id' => $company->id,
                'client_number' => '08293847500',
                'supply_number' => '08293847500',
                'meter_number' => '8765432',
                'tariff_type' => 'T2-G2',
                'rate_name' => 'T2-G2 Comercios',
                'start_date' => now()->subYear(),
            ]
        );

        // 4. Invoices (3 bimestrales: verano, otoño, invierno)
        $invoicesData = [
            // Verano (ene-mar): aires + equipos
            [
                'invoice_number' => 'OF-001-2025',
                'issue_date' => '2025-03-28',
                'start_date' => '2025-01-15',
                'end_date' => '2025-03-20',
                'total_energy_consumed_kwh' => 1150.00, // Alto por aires
                'cost_for_energy' => 125000.00,
                'taxes' => 45000.00,
                'total_amount' => 170000.00,
            ],
            // Otoño (mar-may): sin aires
            [
                'invoice_number' => 'OF-002-2025',
                'issue_date' => '2025-05-25',
                'start_date' => '2025-03-21',
                'end_date' => '2025-05-15',
                'total_energy_consumed_kwh' => 820.00,
                'cost_for_energy' => 89000.00,
                'taxes' => 32000.00,
                'total_amount' => 121000.00,
            ],
            // Invierno (jul-sep): sin aires
            [
                'invoice_number' => 'OF-003-2025',
                'issue_date' => '2025-09-26',
                'start_date' => '2025-07-16',
                'end_date' => '2025-09-07',
                'total_energy_consumed_kwh' => 780.00,
                'cost_for_energy' => 85000.00,
                'taxes' => 30000.00,
                'total_amount' => 115000.00,
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
            'Sala Principal' => Room::firstOrCreate(
                ['name' => 'Sala Principal', 'entity_id' => $entity->id],
                ['description' => '10 escritorios', 'square_meters' => 80]
            ),
            'Sala de Reuniones' => Room::firstOrCreate(
                ['name' => 'Sala de Reuniones', 'entity_id' => $entity->id],
                ['description' => 'Proyector y pantalla', 'square_meters' => 25]
            ),
            'Cocina' => Room::firstOrCreate(
                ['name' => 'Cocina', 'entity_id' => $entity->id],
                ['description' => 'Microondas, heladera', 'square_meters' => 15]
            ),
            'Recepción' => Room::firstOrCreate(
                ['name' => 'Recepción', 'entity_id' => $entity->id],
                ['description' => 'Lobby', 'square_meters' => 20]
            ),
            'Baño' => Room::firstOrCreate(
                ['name' => 'Baño', 'entity_id' => $entity->id],
                ['description' => '2 baños', 'square_meters' => 10]
            ),
        ];

        // 6. Equipment (equipos típicos de oficina)
        $equipmentList = [
            // PCs y Monitores (10 puestos)
            ['room' => 'Sala Principal', 'category' => 'Oficina', 'type' => 'PC de Escritorio (CPU + Monitor)', 'name' => 'PC Puesto 1', 'power' => 250, 'cantidad' => 1],
            ['room' => 'Sala Principal', 'category' => 'Oficina', 'type' => 'PC de Escritorio (CPU + Monitor)', 'name' => 'PC Puesto 2', 'power' => 250, 'cantidad' => 1],
            ['room' => 'Sala Principal', 'category' => 'Oficina', 'type' => 'PC de Escritorio (CPU + Monitor)', 'name' => 'PC Puesto 3', 'power' => 250, 'cantidad' => 1],
            ['room' => 'Sala Principal', 'category' => 'Oficina', 'type' => 'PC de Escritorio (CPU + Monitor)', 'name' => 'PC Puestos 4-10', 'power' => 250, 'cantidad' => 7], // Agrupados

            // Monitores adicionales duales
            ['room' => 'Sala Principal', 'category' => 'Oficina', 'type' => 'Monitor LED 24"', 'name' => 'Monitores Duales', 'power' => 30, 'cantidad' => 5],

            // Impresoras
            ['room' => 'Sala Principal', 'category' => 'Oficina', 'type' => 'Impresora Láser', 'name' => 'Impresora Principal', 'power' => 600, 'cantidad' => 1],

            // Aires Acondicionados
            ['room' => 'Sala Principal', 'category' => 'Climatización', 'type' => 'Aire Acondicionado (4500 frigorías)', 'name' => 'Aire Sala Principal', 'power' => 1800, 'cantidad' => 1],
            ['room' => 'Sala de Reuniones', 'category' => 'Climatización', 'type' => 'Aire Acondicionado (2200 frigorías)', 'name' => 'Aire Reuniones', 'power' => 900, 'cantidad' => 1],

            // Cocina
            ['room' => 'Cocina', 'category' => 'Electrodomésticos', 'type' => 'Heladera pequeña', 'name' => 'Heladera Oficina', 'power' => 150, 'cantidad' => 1],
            ['room' => 'Cocina', 'category' => 'Cocina', 'type' => 'Microondas', 'name' => 'Microondas', 'power' => 1000, 'cantidad' => 1],
            ['room' => 'Cocina', 'category' => 'Cocina', 'type' => 'Cafetera', 'name' => 'Cafetera Oficina', 'power' => 1200, 'cantidad' => 1],

            // Iluminación LED
            ['room' => 'Sala Principal', 'category' => 'Iluminación', 'type' => 'Tubo LED 18W', 'name' => 'Tubos Sala Principal', 'power' => 18, 'cantidad' => 12],
            ['room' => 'Sala de Reuniones', 'category' => 'Iluminación', 'type' => 'Lámpara LED 12W (Eq. 75W)', 'name' => 'Luces Reuniones', 'power' => 12, 'cantidad' => 4],
            ['room' => 'Recepción', 'category' => 'Iluminación', 'type' => 'Lámpara LED 12W (Eq. 75W)', 'name' => 'Luces Recepción', 'power' => 12, 'cantidad' => 3],
            ['room' => 'Cocina', 'category' => 'Iluminación', 'type' => 'Tubo LED 9W', 'name' => 'Tubo Cocina', 'power' => 9, 'cantidad' => 2],
            ['room' => 'Baño', 'category' => 'Iluminación', 'type' => 'Lámpara LED 5W (Eq. 40W)', 'name' => 'Luces Baño', 'power' => 5, 'cantidad' => 2],

            // Electrónicos adicionales
            ['room' => 'Sala de Reuniones', 'category' => 'Oficina', 'type' => 'Proyector', 'name' => 'Proyector Reuniones', 'power' => 200, 'cantidad' => 1],
            ['room' => 'Recepción', 'category' => 'Oficina', 'type' => 'Router Wifi', 'name' => 'Router Principal', 'power' => 12, 'cantidad' => 1],
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

        $this->command->info('✅ Oficina creada: ' . $entity->name);
        $this->command->info('   Equipos: ' . Equipment::where('room_id', $rooms['Sala Principal']->id)->count());
        $this->command->info('   Facturas: ' . count($createdInvoices));
    }
}
