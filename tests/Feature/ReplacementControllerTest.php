<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReplacementControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_replacements_index_screen_can_be_rendered()
    {
        // 1. Setup
        $user = \App\Models\User::factory()->create();
        $plan = \App\Models\Plan::create(['name' => 'Basic', 'slug' => 'basic', 'max_users' => 1, 'max_entities' => 1]);
        $entity = \App\Models\Entity::create(['name' => 'Test Home', 'plan_id' => $plan->id]);
        $user->entities()->attach($entity->id, ['plan_id' => $plan->id]);
        
        // Dependencies
        $province = \App\Models\Province::create(['name' => 'Buenos Aires']);
        $provider = \App\Models\Proveedor::create(['name' => 'Edenor', 'cuit' => '30-12345678-9', 'province_id' => $province->id]);
        $utility = \App\Models\UtilityCompany::create(['name' => 'Edenor', 'service_type' => 'electricity']);

        $contract = \App\Models\Contract::create([
            'entity_id' => $entity->id,
            'proveedor_id' => $provider->id,
            'utility_company_id' => $utility->id,
            'contract_number' => '123',
            'meter_number' => 'M123',
            'client_number' => 'C123',
            'supply_number' => 'S123',
            'rate_name' => 'T1-R1',
            'tariff_type' => 'residential',
            'start_date' => '2024-01-01',
        ]);

        // Invoice with high consumption
        $invoice = \App\Models\Invoice::create([
            'contract_id' => $contract->id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-02-01',
            'issue_date' => '2025-02-02',
            'total_energy_consumed_kwh' => 300,
            'total_amount' => 30000,
            'status' => 'paid'
        ]);

        // Equipment
        $category = \App\Models\EquipmentCategory::create(['name' => 'Climatización']);
        $type = \App\Models\EquipmentType::create(['name' => 'Aire Viejo', 'category_id' => $category->id]);
        $room = \App\Models\Room::create(['name' => 'Living', 'entity_id' => $entity->id]);
        
        $equipment = \App\Models\Equipment::create([
            'name' => 'Aire Sala',
            'type_id' => $type->id,
            'category_id' => $category->id,
            'room_id' => $room->id,
            'nominal_power_w' => 2000,
        ]);

        // Usage
        \App\Models\EquipmentUsage::create([
            'invoice_id' => $invoice->id,
            'equipment_id' => $equipment->id,
            'consumption_kwh' => 150,
            'usage_hours_per_day' => 8,
            'days_per_week' => 7,
        ]);

        // Benchmark
        \App\Models\EfficiencyBenchmark::create([
            'equipment_type_id' => $type->id,
            'efficiency_gain_factor' => 0.35,
            'average_market_price' => 800000,
            'meli_search_term' => 'Aire Inverter',
        ]);

        // 2. Act
        $response = $this->actingAs($user)->get(route('replacements.index', $entity));

        // 3. Assert
        $response->assertStatus(200);
        $response->assertViewIs('replacements.index');
        $response->assertSee('Aire Sala');
        $response->assertSee('Aire Inverter');
        $response->assertSee('Editar Datos');
        $response->assertSee(route('replacements.refine', $equipment->id));
    }
}
