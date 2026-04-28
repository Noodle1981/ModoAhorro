<?php

namespace Tests\Feature\Analisis_y_Ahorro;

use App\Models\User;
use App\Models\Entity;
use App\Models\Locality;
use App\Models\Province;
use App\Models\Plan;
use App\Models\Invoice;
use App\Models\Contract;
use App\Models\UtilityCompany;
use App\Models\Proveedor;
use App\Models\Room;
use App\Models\Equipment;
use App\Models\EquipmentType;
use App\Models\EquipmentCategory;
use App\Models\EquipmentUsage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdaptiveEnergyEngineTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $entity;
    protected $contract;
    protected $invoice;
    protected $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup base data
        $province = Province::create(['name' => 'San Juan']);
        $locality = Locality::create([
            'province_id' => $province->id,
            'name' => 'Santa Lucía',
            'latitude' => -31.5375,
            'longitude' => -68.5364
        ]);

        $plan = Plan::create([
            'name' => 'Premium',
            'max_entities' => 5,
            'allowed_entity_types' => ['hogar'],
            'price' => 0
        ]);

        $this->user = User::factory()->create();
        $this->entity = Entity::factory()->create(['locality_id' => $locality->id]);
        $this->user->entities()->attach($this->entity->id, [
            'plan_id' => $plan->id,
            'subscribed_at' => now(),
        ]);

        $utilityCompany = UtilityCompany::create([
            'province_id' => $province->id,
            'name' => 'Energía S.A.',
            'type' => 'electricidad'
        ]);

        $proveedor = Proveedor::create([
            'name' => 'Distribuidora S.A.',
            'utility_company_id' => $utilityCompany->id,
            'province_id' => $province->id
        ]);

        $this->contract = Contract::create([
            'entity_id' => $this->entity->id,
            'utility_company_id' => $utilityCompany->id,
            'proveedor_id' => $proveedor->id,
            'account_number' => '123456',
            'is_active' => true
        ]);

        $this->invoice = Invoice::create([
            'contract_id' => $this->contract->id,
            'invoice_number' => 'INV-001',
            'issue_date' => '2025-01-01',
            'start_date' => '2025-01-01',
            'end_date' => '2025-02-01',
            'total_energy_consumed_kwh' => 500,
            'total_amount' => 1000,
        ]);

        $category = EquipmentCategory::create(['name' => 'Limpieza']);
        $type = EquipmentType::create([
            'category_id' => $category->id,
            'name' => 'Lavarropas',
            'usage_unit' => 'cycles',
            'energy_per_cycle' => 0.5
        ]);

        $room = Room::create([
            'entity_id' => $this->entity->id,
            'name' => 'Cocina'
        ]);

        $this->equipment = Equipment::create([
            'entity_id' => $this->entity->id,
            'category_id' => $category->id,
            'type_id' => $type->id,
            'room_id' => $room->id,
            'name' => 'Lavarropas Test',
            'nominal_power_w' => 500
        ]);

        $this->actingAs($this->user);
        session(['active_entity_id' => $this->entity->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_save_usage_context_without_calibrating()
    {
        $payload = [
            'invoice_id' => $this->invoice->id,
            'usages' => [
                $this->equipment->id => [
                    'avg_daily_use_hours' => 0,
                    'usage_frequency' => 'frecuentemente',
                    'cycles_per_period' => 12,
                    'is_standby' => false,
                    'has_defined_pattern' => true
                ]
            ]
        ];

        $response = $this->post(route('analisis.usage.save'), $payload);

        $response->assertStatus(302);
        $this->assertDatabaseHas('equipment_usages', [
            'invoice_id' => $this->invoice->id,
            'equipment_id' => $this->equipment->id,
            'cycles_per_period' => 12,
            'usage_frequency' => 'frecuentemente'
        ]);

        $this->equipment->refresh();
        $this->assertTrue($this->equipment->has_defined_pattern);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_calibrate_and_redirect_to_results()
    {
        $payload = [
            'invoice_id' => $this->invoice->id,
            'usages' => [
                $this->equipment->id => [
                    'avg_daily_use_hours' => 0,
                    'usage_frequency' => 'frecuentemente',
                    'cycles_per_period' => 15,
                    'is_standby' => false,
                ]
            ]
        ];

        $response = $this->post(route('analisis.usage.calibrate'), $payload);

        $response->assertRedirect(route('analisis.usage.results', ['invoice' => $this->invoice->id]));
        
        $this->assertDatabaseHas('equipment_usages', [
            'invoice_id' => $this->invoice->id,
            'equipment_id' => $this->equipment->id,
            'cycles_per_period' => 15
        ]);

        $this->assertTrue(session()->has('engine_result'));
        $engineResult = session('engine_result');
        
        $this->assertArrayHasKey('tanks', $engineResult);
        $this->assertArrayHasKey('invoiced_kwh', $engineResult);
        $this->assertEquals(500, $engineResult['invoiced_kwh']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_show_engine_results_page()
    {
        $engineResult = [
            'invoiced_kwh' => 500,
            'declared_kwh' => 450,
            'adjustment_kwh' => 50,
            'unassigned_remainder' => 5,
            'tanks' => [
                ['key' => 1, 'label' => 'Certeza', 'total_kwh' => 100, 'top_items' => []],
                ['key' => 2, 'label' => 'Base', 'total_kwh' => 100, 'top_items' => []],
                ['key' => 3, 'label' => 'Clima', 'total_kwh' => 100, 'top_items' => []],
                ['key' => 4, 'label' => 'Variable', 'total_kwh' => 150, 'top_items' => []],
            ],
            'logs' => ['Log 1']
        ];

        session(['engine_result' => $engineResult]);

        $response = $this->get(route('analisis.usage.results', ['invoice' => $this->invoice->id]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Analisis/EngineResults')
            ->has('engine')
            ->where('engine.invoiced_kwh', 500)
            ->has('period')
        );
    }
}
