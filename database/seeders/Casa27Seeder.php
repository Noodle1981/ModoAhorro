<?php

namespace Database\Seeders;

use App\Models\Contract;
use App\Models\Entity;
use App\Models\Invoice;
use App\Models\Locality;
use App\Models\Plan;
use App\Models\Province;
use App\Models\Proveedor;
use App\Models\User;
use App\Models\UtilityCompany;
use Illuminate\Database\Seeder;

/**
 * Infraestructura de Casa 27: usuario, entidad, contrato y facturas.
 * NO incluye equipos (ver Casa27EquipmentSeeder).
 * Fuente de verdad: datoshogar.txt
 */
class Casa27Seeder extends Seeder
{
    public function run(): void
    {
        // ── 1. PLAN Y USUARIO ─────────────────────────────────────────────────
        $plan = Plan::firstOrCreate(['name' => 'Premium'], [
            'max_entities'         => 5,
            'allowed_entity_types' => ['hogar'],
            'price'                => 0,
        ]);

        $user = User::firstOrCreate(['email' => 'casa27@modoahorro.com'], [
            'name'     => 'Usuario Casa 27',
            'password' => bcrypt('password'),
        ]);

        // ── 2. LOCALIDAD Y EMPRESA DISTRIBUIDORA ─────────────────────────────
        $province = Province::where('name', 'San Juan')->first() ?? Province::first();
        $locality = Locality::where('name', 'Capital')->first() ?? Locality::first();

        // Naturgy ID: 2 según datoshogar.txt
        $utility = UtilityCompany::updateOrCreate(['name' => 'Naturgy']);

        $proveedor = Proveedor::updateOrCreate(
            ['name' => 'Naturgy Distribuidora'],
            ['province_id' => $province?->id]
        );

        // ── 3. ENTIDAD (CASA 27) ──────────────────────────────────────────────
        $entity = Entity::updateOrCreate(['name' => 'Casa 27'], [
            'type'                => 'hogar',
            'locality_id'         => $locality?->id,
            'address_street'      => 'Calle Carlos Gardel Casa 27 B° Enoe Bravo',
            'address_postal_code' => '5300',
            'square_meters'       => 450,
            'people_count'        => 4,
            'description'         => 'Mi Hogar de Prueba - Gestión Física Optimizada',
            'thermal_profile'     => [
                'roof_type'        => 'concrete_slab',
                'window_type'      => 'single_glass',
                'window_frame'     => 'aluminum',
                'orientation'      => 'este_oeste',
                'sun_exposure'     => 'medium',
                'roof_insulation'  => true,
                'drafts_detected'  => false,
                'south_window'     => false,
                'thermal_score'    => 50,
                'energy_label'     => 'D',
            ],
        ]);

        if (!$user->entities()->where('entity_id', $entity->id)->exists()) {
            $user->entities()->attach($entity->id, [
                'plan_id'       => $plan->id,
                'subscribed_at' => now(),
            ]);
        }

        // ── 4. CONTRATO ───────────────────────────────────────────────────────
        $contract = Contract::updateOrCreate(['contract_number' => '36697'], [
            'entity_id'          => $entity->id,
            'proveedor_id'       => $proveedor->id,
            'utility_company_id' => $utility->id,
            'meter_number'       => '9618495',
            'supply_number'      => '07182202700',
            'rate_name'          => 'T1-R1',
            'is_active'          => true,
        ]);

        // ── 5. FACTURAS (fuente: datoshogar.txt) ─────────────────────────────
        // Cuotas bimestrales: dos cuotas por período de lectura (1/2 y 2/2)
        $invoices = [
            // Período: 15/11/2024 – 14/01/2025
            [
                'num'         => '137182126',
                'date'        => '2025-01-28',
                'start'       => '2024-11-15',
                'end'         => '2025-01-14',
                'kwh'         => 430,
                'energy'      => 44118.19,
                'taxes'       => 18841.80,
                'total'       => 62960.00,
                'installment' => 1,
                'of'          => 2,
                'tariff'      => 'T1-R3',
            ],
            [
                'num'         => '137423224',
                'date'        => '2025-01-28',
                'start'       => '2024-11-15',
                'end'         => '2025-01-14',
                'kwh'         => 429,
                'energy'      => 43987.21,
                'taxes'       => 18788.60,
                'total'       => 62775.81,
                'installment' => 2,
                'of'          => 2,
                'tariff'      => 'T1-R3',
            ],
            // Período: 15/01/2025 – 20/03/2025
            [
                'num'         => '137756868',
                'date'        => '2025-03-28',
                'start'       => '2025-01-15',
                'end'         => '2025-03-20',
                'kwh'         => 624,
                'energy'      => 67876.86,
                'taxes'       => 28078.31,
                'total'       => 95955.17,
                'installment' => 1,
                'of'          => 2,
                'tariff'      => 'T1-R3',
            ],
            [
                'num'         => '138004036',
                'date'        => '2025-04-25',
                'start'       => '2025-01-15',
                'end'         => '2025-03-20',
                'kwh'         => 623,
                'energy'      => 67748.37,
                'taxes'       => 28026.71,
                'total'       => 95775.08,
                'installment' => 2,
                'of'          => 2,
                'tariff'      => 'T1-R3',
            ],
            // Período: 21/03/2025 – 13/05/2025
            [
                'num'         => '138370044',
                'date'        => '2025-05-29',
                'start'       => '2025-03-21',
                'end'         => '2025-05-13',
                'kwh'         => 124,
                'energy'      => 13858.13,
                'taxes'       => 4768.66,
                'total'       => 18626.79,
                'installment' => 1,
                'of'          => 2,
                'tariff'      => 'T1-R2',
            ],
            [
                'num'         => '138579184',
                'date'        => '2025-07-17',
                'start'       => '2025-03-21',
                'end'         => '2025-05-13',
                'kwh'         => 123,
                'energy'      => 13784.62,
                'taxes'       => 4743.40,
                'total'       => 18528.02,
                'installment' => 2,
                'of'          => 2,
                'tariff'      => 'T1-R2',
            ],
            // Período: 14/05/2025 – 15/07/2025
            [
                'num'         => '13891203',
                'date'        => '2025-07-29',
                'start'       => '2025-05-14',
                'end'         => '2025-07-15',
                'kwh'         => 83,
                'energy'      => 8503.49,
                'taxes'       => 2452.22,
                'total'       => 10955.71,
                'installment' => 1,
                'of'          => 2,
                'tariff'      => 'T1-R1',
            ],
            [
                'num'         => '13915993',
                'date'        => '2025-08-27',
                'start'       => '2025-05-14',
                'end'         => '2025-07-15',
                'kwh'         => 83,
                'energy'      => 8503.49,
                'taxes'       => 2452.22,
                'total'       => 10955.71,
                'installment' => 2,
                'of'          => 2,
                'tariff'      => 'T1-R1',
            ],
            // Período: 16/07/2025 – 07/09/2025
            [
                'num'         => '139459979',
                'date'        => '2025-09-26',
                'start'       => '2025-07-16',
                'end'         => '2025-09-07',
                'kwh'         => 78,
                'energy'      => 8293.53,
                'taxes'       => 2484.72,
                'total'       => 10778.25,
                'installment' => 1,
                'of'          => 2,
                'tariff'      => 'T1-R1',
            ],
            [
                'num'         => '139674278',
                'date'        => '2025-10-23',
                'start'       => '2025-07-16',
                'end'         => '2025-09-07',
                'kwh'         => 78,
                'energy'      => 8293.53,
                'taxes'       => 2484.72,
                'total'       => 10778.25,
                'installment' => 2,
                'of'          => 2,
                'tariff'      => 'T1-R1',
            ],
            // Período: 08/09/2025 – 11/11/2025
            [
                'num'         => '140021493',
                'date'        => '2025-11-27',
                'start'       => '2025-09-08',
                'end'         => '2025-11-11',
                'kwh'         => 133,
                'energy'      => 15575.77,
                'taxes'       => 5680.47,
                'total'       => 21256.24,
                'installment' => 1,
                'of'          => 2,
                'tariff'      => 'T1-R2',
            ],
            [
                'num'         => '140232616',
                'date'        => '2025-12-23',
                'start'       => '2025-09-08',
                'end'         => '2025-11-11',
                'kwh'         => 132,
                'energy'      => 15494.76,
                'taxes'       => 5917.70,
                'total'       => 21412.46,
                'installment' => 2,
                'of'          => 2,
                'tariff'      => 'T1-R2',
            ],
            // Período: 12/11/2025 – 15/01/2026
            [
                'num'         => '14068867',
                'date'        => '2026-01-28',
                'start'       => '2025-11-12',
                'end'         => '2026-01-15',
                'kwh'         => 504,
                'energy'      => 58772.97,
                'taxes'       => 25241.47,
                'total'       => 84014.44,
                'installment' => 1,
                'of'          => 2,
                'tariff'      => 'T1-R2',
            ],
            [
                'num'         => '140937991',
                'date'        => '2026-02-27',
                'start'       => '2025-11-12',
                'end'         => '2026-01-15',
                'kwh'         => 503,
                'energy'      => 58631.43,
                'taxes'       => 25652.16,
                'total'       => 84283.59,
                'installment' => 2,
                'of'          => 2,
                'tariff'      => 'T1-R2',
            ],
        ];

        foreach ($invoices as $inv) {
            Invoice::updateOrCreate(['invoice_number' => $inv['num']], [
                'contract_id'               => $contract->id,
                'issue_date'                => $inv['date'],
                'start_date'                => $inv['start'],
                'end_date'                  => $inv['end'],
                'total_energy_consumed_kwh' => $inv['kwh'],
                'cost_for_energy'           => $inv['energy'],
                'taxes'                     => $inv['taxes'],
                'total_amount'              => $inv['total'],
                'installment_number'        => $inv['installment'],
                'total_installments'        => $inv['of'],
                'tariff'                    => $inv['tariff'],
                'source'                    => 'manual',
            ]);
        }
    }
}
