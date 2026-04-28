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
            // Período: 16/07/2025 – 07/09/2025
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
                'tariff'      => null,
            ],
            // Período: 08/09/2025 – 11/11/2025 (cuota 1)
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
                'tariff'      => null,
            ],
            // Período: 08/09/2025 – 11/11/2025 (cuota 2)
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
                'tariff'      => null,
            ],
            // Período: 12/11/2025 – 15/01/2026 (cuota 1)
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
                'tariff'      => 'T1-R3',
            ],
            // Período: 12/11/2025 – 15/01/2026 (cuota 2)
            [
                'num'         => '140937991',
                'date'        => '2026-02-27',
                'start'       => '2025-11-12',
                'end'         => '2026-01-15',
                'kwh'         => 503,
                'energy'      => 58631.43,
                'taxes'       => 25952.16,
                'total'       => 84583.59,
                'installment' => 2,
                'of'          => 2,
                'tariff'      => 'T1-R3',
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
                'status'                    => 'paid',
                'source'                    => 'manual',
            ]);
        }
    }
}
