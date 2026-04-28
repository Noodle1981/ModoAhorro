<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ConsumptionAnalysisService;
use App\Models\Invoice;
use App\Models\Contract;
use App\Models\Entity;
use App\Models\Equipment;
use App\Models\EquipmentType;
use App\Models\EquipmentCategory;
use App\Models\EquipmentUsage;
use App\Models\Room;
use App\Models\Province;
use App\Models\Locality;
use App\Models\UtilityCompany;
use App\Models\Proveedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class ConsumptionAnalysisServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
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

        $entity = Entity::factory()->create(['locality_id' => $locality->id, 'people_count' => 3]);
        
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

        $contract = Contract::create([
            'entity_id' => $entity->id,
            'utility_company_id' => $utilityCompany->id,
            'proveedor_id' => $proveedor->id,
            'account_number' => '123456',
            'is_active' => true
        ]);

        $this->invoice = Invoice::create([
            'contract_id' => $contract->id,
            'invoice_number' => 'INV-001',
            'issue_date' => '2025-01-01',
            'start_date' => '2025-01-01',
            'end_date' => '2025-02-01',
            'total_energy_consumed_kwh' => 400,
            'total_amount' => 1000,
        ]);

        $category = EquipmentCategory::create(['name' => 'Limpieza']);
        $type = EquipmentType::create([
            'category_id' => $category->id,
            'name' => 'Lavarropas',
            'usage_unit' => 'cycles',
            'energy_per_cycle' => 0.5
        ]);

        $room = Room::create(['entity_id' => $entity->id, 'name' => 'Cocina']);

        $this->equipment = Equipment::create([
            'entity_id' => $entity->id,
            'category_id' => $category->id,
            'type_id' => $type->id,
            'room_id' => $room->id,
            'name' => 'Lavarropas Test',
            'nominal_power_w' => 500
        ]);

        EquipmentUsage::create([
            'invoice_id' => $this->invoice->id,
            'equipment_id' => $this->equipment->id,
            'cycles_per_period' => 10,
            'usage_frequency' => 'diario'
        ]);

        $this->service = app(ConsumptionAnalysisService::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_correct_structure_for_unified_calibration()
    {
        $result = $this->service->calibrateUnifiedPeriod(collect([$this->invoice]));

        $this->assertArrayHasKey('invoiced_kwh', $result);
        $this->assertArrayHasKey('declared_kwh', $result);
        $this->assertArrayHasKey('adjustment_kwh', $result);
        $this->assertArrayHasKey('tanks', $result);
        $this->assertArrayHasKey('unassigned_remainder', $result);
        $this->assertArrayHasKey('summary', $result);

        $this->assertCount(4, $result['tanks']);
        $this->assertEquals(400, $result['invoiced_kwh']);
        
        // El tanque 4 debería tener el lavarropas si no es determinista
        $tank4 = collect($result['tanks'])->firstWhere('key', 4);
        $this->assertNotNull($tank4);
        $this->assertNotEmpty($tank4['label']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calculates_equipment_consumption_by_cycles()
    {
        $usage = $this->invoice->equipmentUsages->first();
        $consumption = $this->service->calculateEquipmentConsumption($usage, $this->invoice);
        $this->assertEquals(5.0, $consumption);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calculates_equipment_consumption_by_people_proportional()
    {
        $type = EquipmentType::create([
            'category_id' => $this->equipment->category_id,
            'name' => 'Bombilla',
            'usage_unit' => 'people_proportional',
            'social_coefficient' => 0.1 // 0.1 kWh per person per day
        ]);

        $usage = EquipmentUsage::create([
            'invoice_id' => $this->invoice->id,
            'equipment_id' => Equipment::create([
                'entity_id' => $this->invoice->contract->entity_id,
                'category_id' => $type->category_id,
                'type_id' => $type->id,
                'room_id' => $this->equipment->room_id,
                'name' => 'Bombilla Test'
            ])->id,
            'use_days_in_period' => 30
        ]);

        $consumption = $this->service->calculateEquipmentConsumption($usage, $this->invoice);

        // 3 personas * 30 días * 0.1 coefficient = 9.0 kWh
        $this->assertEquals(9.0, $consumption);
    }
}
