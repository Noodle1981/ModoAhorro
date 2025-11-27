<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entity;
use App\Models\User;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Room;
use App\Models\Locality;
use App\Models\UtilityCompany;
use Carbon\Carbon;

class DatosHogarSeeder extends Seeder
{
    /**
     * Encuentra el tipo de equipo más apropiado basándose en el nombre del equipo.
     * 
     * @param string $equipmentName Nombre del equipo
     * @param string $categoryName Nombre de la categoría
     * @return int|null ID del tipo de equipo o null si no se encuentra
     */
    private function findEquipmentType(string $equipmentName, string $categoryName): ?int
    {
        // Mapeo de palabras clave → nombre de tipo de equipo
        $typeMapping = [
            // Climatización
            'Aire Grande' => 'Aire Acondicionado (3500 frigorías)',
            'Aire Portatil' => 'Aire Acondicionado Portátil',
            'Ventilador de Techo' => 'Ventilador de techo',
            'Ventilador de Pie' => 'Ventilador de pie',
            
            // Electrodomésticos
            'Heladera' => 'Heladera con Freezer',
            'Lavarropa' => 'Lavarropas Automático (Agua fría)',
            
            // Cocina
            'Microondas' => 'Microondas',
            
            // Entretenimiento
            'TV Grande' => 'Televisor LED 50" 4K',
            'TV Chico' => 'Televisor LED 32"',
            
            // Oficina/Computación
            'PC Gamer' => 'PC de Escritorio (CPU + Monitor)',
            'Monitor PC' => 'Monitor LED 24"',
            'Monitor' => 'Monitor LED 24"',
            'Notebook' => 'Notebook / Laptop',
            'Router' => 'Modem / Router WiFi',
            
            // Iluminación - Focos LED
            'Foco Ventilador' => 'Lámpara LED 5W (Eq. 40W)',
            'Foco Mesita' => 'Lámpara LED 5W (Eq. 40W)',
            'Foco Living' => 'Lámpara LED 5W (Eq. 40W)',
            'Focos Garage' => 'Lámpara LED 5W (Eq. 40W)',
            'Focos Ventilador' => 'Lámpara LED 5W (Eq. 40W)',
            'Mesita de Luz' => 'Lámpara LED 5W (Eq. 40W)',
            'Foco Baño' => 'Lámpara LED 12W (Eq. 75W)',
            'Foco Led Grande' => 'Lámpara LED 12W (Eq. 75W)',
            'Foco' => 'Lámpara LED 5W (Eq. 40W)', // Genérico
            'Tubo Led' => 'Tubo Fluorescente 36W',
            
            // Portátiles
            'Cargador' => 'Cargador de Celular',
            
            // Otros
            'Secador' => 'Secador de Pelo',
            'Maquina de Afeitar' => 'Afeitadora Eléctrica',
        ];
        
        // Buscar coincidencia exacta primero
        if (isset($typeMapping[$equipmentName])) {
            $type = \App\Models\EquipmentType::where('name', $typeMapping[$equipmentName])->first();
            if ($type) return $type->id;
        }
        
        // Buscar por palabras clave
        foreach ($typeMapping as $keyword => $typeName) {
            if (stripos($equipmentName, $keyword) !== false) {
                $type = \App\Models\EquipmentType::where('name', $typeName)->first();
                if ($type) return $type->id;
            }
        }
        
        return null; // Si no encuentra, deja NULL
    }

    public function run(): void
    {
        // Limpiar equipos antes de crear nuevos
        \App\Models\Equipment::truncate();
        // 1. Find or Create User
        $user = User::first();
        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // 2. Find or Create Locality (Capital de San Juan)
        $sanJuanProvince = \App\Models\Province::where('name', 'San Juan')->first();
        $locality = Locality::firstOrCreate(
            ['name' => 'Capital', 'province_id' => $sanJuanProvince->id],
            [
                'postal_code' => '5400',
                'latitude' => -31.5375,
                'longitude' => -68.5364
            ]
        );

        // 3. Create Entity (Hogar)
        $entity = Entity::firstOrCreate(
            ['name' => 'Casa 27'],
            [
                'address_street' => 'Calle Carlos Gardel Casa 27 B° Enoe Bravo',
                'address_postal_code' => '5300',
                'locality_id' => $locality->id,
                'description' => 'Casa de prueba',
                'square_meters' => 450,
                'people_count' => 4,
                'type' => 'hogar',
            ]
        );

        // Associate Entity with User
        if (!$user->entities()->where('entity_id', $entity->id)->exists()) {
            $user->entities()->attach($entity->id, ['plan_id' => 1, 'subscribed_at' => now()]);
        }

        // 4. Create Provider, Utility Company & Contract
        $provider = \App\Models\Proveedor::firstOrCreate(['name' => 'Naturgy']);
        $company = UtilityCompany::firstOrCreate(['name' => 'Naturgy']);

        $contract = Contract::firstOrCreate(
            ['contract_number' => '36697'],
            [
                'entity_id' => $entity->id,
                'proveedor_id' => $provider->id,
                'utility_company_id' => $company->id,
                'client_number' => '07182202700',
                'supply_number' => '07182202700',
                'meter_number' => '9618495',
                'tariff_type' => 'T1-R1',
                'rate_name' => 'T1-R1',
                'start_date' => now()->subYear(),
            ]
        );

        // 5. Create Invoices
        $invoicesData = [
            [
                'invoice_number' => '137756868',
                'issue_date' => '2025-03-28',
                'start_date' => '2025-01-15',
                'end_date' => '2025-03-20',
                'total_energy_consumed_kwh' => 624.00,
                'cost_for_energy' => 67876.86,
                'taxes' => 28078.31,
                'total_amount' => 95955.17,
            ],
            [
                'invoice_number' => '138579184',
                'issue_date' => '2025-06-25',
                'start_date' => '2025-03-21',
                'end_date' => '2025-05-15',
                'total_energy_consumed_kwh' => 123.00,
                'cost_for_energy' => 13784.62,
                'taxes' => 4743.40,
                'total_amount' => 18528.02,
            ],
            [
                'invoice_number' => '139151993',
                'issue_date' => '2025-08-27',
                'start_date' => '2025-05-14',
                'end_date' => '2025-07-15',
                'total_energy_consumed_kwh' => 83.00,
                'cost_for_energy' => 8503.49,
                'taxes' => 2452.22,
                'total_amount' => 10955.71,
            ],
            [
                'invoice_number' => '139459979',
                'issue_date' => '2025-09-26',
                'start_date' => '2025-07-16',
                'end_date' => '2025-09-07',
                'total_energy_consumed_kwh' => 78.00,
                'cost_for_energy' => 8293.53,
                'taxes' => 8293.53,
                'total_amount' => 10778.25,
            ],
        ];

        $createdInvoices = [];
        foreach ($invoicesData as $data) {
            $createdInvoices[] = Invoice::firstOrCreate(
                ['invoice_number' => $data['invoice_number']],
                [
                    'contract_id' => $contract->id,
                    'issue_date' => $data['issue_date'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'total_energy_consumed_kwh' => $data['total_energy_consumed_kwh'],
                    'cost_for_energy' => $data['cost_for_energy'],
                    'taxes' => $data['taxes'],
                    'total_amount' => $data['total_amount'],
                    'status' => 'paid',
                ]
            );
        }

        // 6. Create Rooms
        $roomsList = [
            'Cocina / Comedor',
            'Living',
            'Habitación Mamá',
            'Habitación Papa',
            'Habitación Hermanos',
            'Baño',
            'Fondo',
            'Garage',
            'Hall',
            'Frente / Vereda',
            'Lavadero',
            'Portátiles',
        ];

        $rooms = [];
        foreach ($roomsList as $roomName) {
            $rooms[$roomName] = Room::firstOrCreate(
                ['name' => $roomName, 'entity_id' => $entity->id],
                ['square_meters' => 0]
            );
        }

        // 7. Create Equipment
        $equipmentList = [
            ['name' => 'Aire Grande', 'category' => 'Climatización', 'room' => 'Cocina / Comedor', 'power' => 2400],
            ['name' => 'Ventilador de Techo', 'category' => 'Climatización', 'room' => 'Cocina / Comedor', 'power' => 60],
            ['name' => 'Microondas', 'category' => 'Cocina', 'room' => 'Cocina / Comedor', 'power' => 1000],
            ['name' => 'Focos Ventilador', 'category' => 'Iluminación', 'room' => 'Cocina / Comedor', 'power' => 5],
            ['name' => 'Focos Ventilador', 'category' => 'Iluminación', 'room' => 'Cocina / Comedor', 'power' => 5],
            ['name' => 'Focos Ventilador', 'category' => 'Iluminación', 'room' => 'Cocina / Comedor', 'power' => 5],
            ['name' => 'Tubo Led Cocina', 'category' => 'Iluminación', 'room' => 'Cocina / Comedor', 'power' => 5],
            ['name' => 'Ventilador de Techo', 'category' => 'Climatización', 'room' => 'Living', 'power' => 60],
            ['name' => 'TV Grande', 'category' => 'Entretenimiento', 'room' => 'Living', 'power' => 120],
            ['name' => 'Foco Living', 'category' => 'Iluminación', 'room' => 'Living', 'power' => 5],
            ['name' => 'Router Wifi', 'category' => 'Oficina', 'room' => 'Living', 'power' => 20],
            ['name' => 'Ventilador de Techo', 'category' => 'Climatización', 'room' => 'Habitación Mamá', 'power' => 60],
            ['name' => 'Foco Ventilador', 'category' => 'Iluminación', 'room' => 'Habitación Mamá', 'power' => 5],
            ['name' => 'Foco Mesita de Luz', 'category' => 'Iluminación', 'room' => 'Habitación Mamá', 'power' => 5],
            ['name' => 'Ventilador de Techo', 'category' => 'Climatización', 'room' => 'Habitación Papa', 'power' => 60],
            ['name' => 'Foco Ventilador', 'category' => 'Iluminación', 'room' => 'Habitación Papa', 'power' => 40],
            ['name' => 'Foco Mesita de Luz', 'category' => 'Iluminación', 'room' => 'Habitación Papa', 'power' => 40],
            ['name' => 'TV Chico', 'category' => 'Entretenimiento', 'room' => 'Habitación Papa', 'power' => 85],
            ['name' => 'PC Gamer', 'category' => 'Oficina', 'room' => 'Habitación Hermanos', 'power' => 600],
            ['name' => 'Monitor PC', 'category' => 'Oficina', 'room' => 'Habitación Hermanos', 'power' => 50],
            ['name' => 'Monitor PC', 'category' => 'Oficina', 'room' => 'Habitación Hermanos', 'power' => 50],
            ['name' => 'Ventilador de Techo', 'category' => 'Climatización', 'room' => 'Habitación Hermanos', 'power' => 60],
            ['name' => 'Foco Ventilador de Techo', 'category' => 'Iluminación', 'room' => 'Habitación Hermanos', 'power' => 5],
            ['name' => 'Mesita de Luz', 'category' => 'Iluminación', 'room' => 'Habitación Hermanos', 'power' => 5],
            ['name' => 'Aire Portatil', 'category' => 'Climatización', 'room' => 'Habitación Hermanos', 'power' => 1400],
            ['name' => 'Foco Baño', 'category' => 'Iluminación', 'room' => 'Baño', 'power' => 12], // Asumed 12W based on others
            ['name' => 'Secador de Pelo', 'category' => 'Otros', 'room' => 'Baño', 'power' => 1000],
            ['name' => 'Maquina de Afeitar', 'category' => 'Otros', 'room' => 'Baño', 'power' => 12],
            ['name' => 'Foco Led Grande', 'category' => 'Iluminación', 'room' => 'Fondo', 'power' => 12],
            ['name' => 'Focos Garage', 'category' => 'Iluminación', 'room' => 'Garage', 'power' => 5],
            ['name' => 'Focos Garage', 'category' => 'Iluminación', 'room' => 'Garage', 'power' => 5],
            ['name' => 'Heladera', 'category' => 'Electrodomésticos', 'room' => 'Garage', 'power' => 150],
            ['name' => 'Lavarropa', 'category' => 'Electrodomésticos', 'room' => 'Garage', 'power' => 2500],
            ['name' => 'Foco', 'category' => 'Iluminación', 'room' => 'Hall', 'power' => 5],
            ['name' => 'Foco', 'category' => 'Iluminación', 'room' => 'Frente / Vereda', 'power' => 5],
            ['name' => 'Cargadores de Celular', 'category' => 'Portátiles', 'room' => 'Portátiles', 'power' => 5],
            ['name' => 'Cargadores de Celular', 'category' => 'Portátiles', 'room' => 'Portátiles', 'power' => 5],
            ['name' => 'Cargadores de Celular', 'category' => 'Portátiles', 'room' => 'Portátiles', 'power' => 5],
            ['name' => 'Notebook', 'category' => 'Portátiles', 'room' => 'Portátiles', 'power' => 65],
        ];

        $createdEquipment = [];
        foreach ($equipmentList as $item) {
            $category = \App\Models\EquipmentCategory::firstOrCreate(['name' => $item['category']]);
            $room = $rooms[$item['room']] ?? null;

            if ($room) {
                $createdEquipment[] = \App\Models\Equipment::create([
                    'name' => $item['name'],
                    'room_id' => $room->id,
                    'category_id' => $category->id,
                    'type_id' => $this->findEquipmentType($item['name'], $item['category']), // ✅ NUEVO: Asigna type_id automáticamente
                    'nominal_power_w' => $item['power'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
