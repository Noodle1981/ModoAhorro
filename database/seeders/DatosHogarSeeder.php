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
    public function run(): void
    {
        // 1. Find or Create User
        $user = User::first();
        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // 2. Find or Create Locality (Capital)
        $locality = Locality::firstOrCreate(['name' => 'Capital'], ['province_id' => 1]); // Assuming province 1 exists

        // 3. Create Entity (Hogar)
        $entity = Entity::firstOrCreate(
            ['name' => 'Casa 27'],
            [
                'address_street' => 'Calle Carlos Gardel Casa 27 B° Enoe Bravo',
                'address_postal_code' => '5300', // Defaulting
                'locality_id' => $locality->id,
                'description' => 'Casa de prueba',
                'square_meters' => 450,
                'people_count' => 4,
            ]
        );

        // Associate Entity with User if not already associated
        if (!$user->entities()->where('entity_id', $entity->id)->exists()) {
            $user->entities()->attach($entity->id, ['plan_id' => 1, 'subscribed_at' => now()]);
        }

        // 4. Create Provider, Utility Company & Contract
        $provider = \App\Models\Proveedor::firstOrCreate(['name' => 'Naturgy']);
        $company = UtilityCompany::firstOrCreate(['name' => 'Naturgy']); // Assuming Naturgy for now based on text

        $contract = Contract::firstOrCreate(
            ['contract_number' => '36697'],
            [
                'entity_id' => $entity->id,
                'proveedor_id' => $provider->id,
                'utility_company_id' => $company->id,
                'client_number' => '07182202700', // N° de SUMINISTRO
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
                'end_date' => '2025-03-20', // Fixed from 20/30/2025
                'consumption_kwh' => 624.00,
                'energy_cost' => 67876.86,
                'taxes_cost' => 28078.31,
                'total_amount' => 95955.17,
            ],
            [
                'invoice_number' => '138579184',
                'issue_date' => '2025-06-25',
                'start_date' => '2025-03-21',
                'end_date' => '2025-05-15',
                'consumption_kwh' => 123.00,
                'energy_cost' => 13784.62,
                'taxes_cost' => 4743.40,
                'total_amount' => 18528.02,
            ],
            [
                'invoice_number' => '139151993',
                'issue_date' => '2025-08-27',
                'start_date' => '2025-05-14',
                'end_date' => '2025-07-15',
                'consumption_kwh' => 83.00,
                'energy_cost' => 8503.49,
                'taxes_cost' => 2452.22,
                'total_amount' => 10955.71,
            ],
            [
                'invoice_number' => '139459979',
                'issue_date' => '2025-09-26',
                'start_date' => '2025-07-16',
                'end_date' => '2025-09-07',
                'consumption_kwh' => 78.00,
                'energy_cost' => 8293.53,
                'taxes_cost' => 8293.53,
                'total_amount' => 10778.25,
            ],
        ];

        foreach ($invoicesData as $data) {
            Invoice::firstOrCreate(
                ['invoice_number' => $data['invoice_number']],
                [
                    'contract_id' => $contract->id,
                    'issue_date' => $data['issue_date'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'consumption_kwh' => $data['consumption_kwh'],
                    'energy_cost' => $data['energy_cost'],
                    'taxes_cost' => $data['taxes_cost'],
                    'total_amount' => $data['total_amount'],
                    'status' => 'paid',
                ]
            );
        }

        // 6. Create Rooms
        $rooms = [
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
        ];

        foreach ($rooms as $roomName) {
            Room::firstOrCreate(
                ['name' => $roomName, 'entity_id' => $entity->id],
                ['square_meters' => 0] // Default
            );
        }
    }
}
