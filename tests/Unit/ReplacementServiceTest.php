<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\Recommendations\ReplacementService;
use App\Models\Invoice;
use App\Models\Equipment;
use App\Models\EquipmentType;
use App\Models\EquipmentCategory;
use App\Models\EquipmentUsage;
use App\Models\EfficiencyBenchmark;
use App\Models\Contract;
use App\Models\Entity;
use App\Models\Plan;
use App\Models\User;

class ReplacementServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_opportunities_for_inefficient_equipment()
    {
        // 1. Setup Data
        $user = User::factory()->create();
        $plan = Plan::create(['name' => 'Basic', 'slug' => 'basic', 'max_users' => 1, 'max_entities' => 1]);
        $entity = Entity::create(['name' => 'Test Home', 'plan_id' => $plan->id]);
        // Create dependencies
        $province = \App\Models\Province::create(['name' => 'Buenos Aires']);
        $provider = \App\Models\Proveedor::create(['name' => 'Edenor', 'cuit' => '30-12345678-9', 'province_id' => $province->id]);
        $utility = \App\Models\UtilityCompany::create(['name' => 'Edenor', 'service_type' => 'electricity']);

        $contract = Contract::create([
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
        
        $invoice = Invoice::create([
            'contract_id' => $contract->id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-02-01',
            'issue_date' => '2025-02-02',
            'total_energy_consumed_kwh' => 300,
            'total_amount' => 30000, // $100/kWh
            'status' => 'paid'
        ]);

        $category = EquipmentCategory::create(['name' => 'Climatización']);
        $type = EquipmentType::create(['name' => 'Aire Viejo', 'category_id' => $category->id]);
        
        $room = \App\Models\Room::create(['name' => 'Living', 'entity_id' => $entity->id]);

        $equipment = Equipment::create([
            'name' => 'Aire Sala',
            'type_id' => $type->id,
            'category_id' => $category->id,
            'room_id' => $room->id,
            'nominal_power_w' => 2000,
        ]);

        // Create Usage (High consumption)
        EquipmentUsage::create([
            'invoice_id' => $invoice->id,
            'equipment_id' => $equipment->id,
            'consumption_kwh' => 150, // 50% of total
            'usage_hours_per_day' => 8,
            'days_per_week' => 7,
        ]);

        // Create Benchmark
        EfficiencyBenchmark::create([
            'equipment_type_id' => $type->id,
            'efficiency_gain_factor' => 0.35, // 35% savings
            'average_market_price' => 800000,
            'meli_search_term' => 'Aire Inverter',
        ]);

        // 2. Execute Service
        $service = new ReplacementService();
        $opportunities = $service->generateOpportunities($invoice);

        // 3. Assertions
        $this->assertCount(1, $opportunities);
        $op = $opportunities[0];

        $this->assertEquals('Aire Sala', $op['equipment_name']);
        $this->assertEquals(150, $op['current_consumption_kwh']);
        
        // Savings: 150 * 0.35 = 52.5 kWh
        $this->assertEquals(52.5, $op['potential_savings_kwh']);
        
        // Money: 52.5 kWh * $100/kWh = $5250
        $this->assertEquals(5250, $op['monthly_savings_amount']);
        
        // ROI: 800000 / 5250 = 152.38 months
        $this->assertEquals(152.4, $op['payback_months']);
        $this->assertEquals('📈 Ahorro a Largo Plazo', $op['verdict']['label']);
    }
}
