<?php

namespace Tests\Feature\Mining;

use App\Domain\Commercial\Profiles\Mining\MiningCampProfile;
use App\Domain\Commercial\Registry\CommercialProfileRegistry;
use App\Models\Contract;
use App\Models\Entity;
use App\Models\Invoice;
use App\Models\Locality;
use App\Models\Proveedor;
use App\Models\Province;
use App\Models\UtilityCompany;
use Database\Seeders\MasterCleanCatalogueSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiningSprint1Test extends TestCase
{
    use RefreshDatabase;

    protected Province $province;

    protected Locality $locality;

    protected function setUp(): void
    {
        parent::setUp();

        $this->province = Province::create(['name' => 'San Juan']);
        $this->locality = Locality::create([
            'id' => 1,
            'province_id' => $this->province->id,
            'name' => 'Iglesia',
            'latitude' => -30.5000,
            'longitude' => -69.2000,
        ]);
    }

    public function test_pabellon_config_is_defined_with_expected_rooms_and_meta(): void
    {
        $config = config('entity_types.pabellon');

        $this->assertNotNull($config);
        $this->assertEquals('Pabellón de Campamento', $config['label']);
        $this->assertContains('Dormitorios', $config['default_rooms']);
        $this->assertContains('Baños y Vestuarios', $config['default_rooms']);
        $this->assertContains('Sala de Estar / Comedor', $config['default_rooms']);
        $this->assertArrayHasKey('recommendations', $config);
        $this->assertTrue($config['recommendations']['solar_panels']['enabled']);
        $this->assertTrue($config['recommendations']['replacements']['enabled']);
    }

    public function test_entity_creation_as_pabellon_creates_default_camp_rooms(): void
    {
        $entity = Entity::create([
            'name' => 'Pabellón Aconcagua 01',
            'type' => 'pabellon',
            'usage_type' => 'comercial',
            'locality_id' => $this->locality->id,
            'camp_shift_type' => '14x14',
            'camp_capacity' => 120,
            'module_type' => 'prefab',
        ]);

        $this->assertDatabaseHas('entities', [
            'id' => $entity->id,
            'type' => 'pabellon',
            'camp_shift_type' => '14x14',
            'camp_capacity' => 120,
            'module_type' => 'prefab',
        ]);

        $rooms = $entity->rooms->pluck('name')->toArray();
        $this->assertContains('Dormitorios', $rooms);
        $this->assertContains('Baños y Vestuarios', $rooms);
        $this->assertContains('Sala de Estar / Comedor', $rooms);
    }

    public function test_commercial_profile_registry_resolves_mining_camp_profile(): void
    {
        $entity = Entity::create([
            'name' => 'Módulo B - Veladero',
            'type' => 'pabellon',
            'usage_type' => 'comercial',
            'locality_id' => $this->locality->id,
            'camp_shift_type' => '7x7',
            'camp_capacity' => 80,
            'module_type' => 'container',
        ]);

        /** @var CommercialProfileRegistry $registry */
        $registry = app(CommercialProfileRegistry::class);

        $profile = $registry->resolveForEntity($entity);

        $this->assertNotNull($profile);
        $this->assertInstanceOf(MiningCampProfile::class, $profile);
        $this->assertEquals('campamento_pabellon', $profile->getSubcategoryKey());
        $this->assertEquals('pabellon', $profile->getCategoryKey());
        $this->assertEquals(1.50, $profile->getThermalSensitivity());
    }

    public function test_mining_camp_profile_operational_load_calculation(): void
    {
        $profile = new MiningCampProfile;

        $context = [
            'staff_count' => 80,
            'visitors_count' => 10,
        ];

        $load = $profile->calculateOperationalLoad($context);

        $this->assertIsFloat($load);
        // Base formula: max(1.0, 1.0 + (80 * 0.04) + (10 * 0.01)) = 1.0 + 3.2 + 0.1 = 4.3
        $this->assertEquals(4.3, $load);
        $this->assertGreaterThan(1.0, $load);
    }

    public function test_mining_equipment_catalogue_seeded_successfully(): void
    {
        $this->seed(MasterCleanCatalogueSeeder::class);

        $this->assertDatabaseHas('equipment_categories', [
            'name' => 'Calefacción Industrial',
        ]);
        $this->assertDatabaseHas('equipment_categories', [
            'name' => 'Agua y Bombeo Industrial',
        ]);
        $this->assertDatabaseHas('equipment_categories', [
            'name' => 'Compresores y Aire Industrial',
        ]);
        $this->assertDatabaseHas('equipment_categories', [
            'name' => 'Seguridad y Detección',
        ]);

        $this->assertDatabaseHas('equipment_types', [
            'name' => 'Convector Eléctrico de Pared',
            'consumption_logic' => 'CLIMATE_DEPENDENT',
        ]);
        $this->assertDatabaseHas('equipment_types', [
            'name' => 'Traceado Eléctrico Cañería',
            'consumption_logic' => 'BASE_LOAD',
        ]);
        $this->assertDatabaseHas('equipment_types', [
            'name' => 'Termotanque Industrial 300L',
            'consumption_logic' => 'BASE_THERMAL_LOSS',
        ]);
        $this->assertDatabaseHas('equipment_types', [
            'name' => 'Compresor de Tornillo',
            'consumption_logic' => 'CONTINUOUS_COMMERCIAL',
        ]);
    }

    public function test_invoice_supports_mining_specific_fields(): void
    {
        $entity = Entity::create([
            'name' => 'Módulo Tablero',
            'type' => 'pabellon',
            'locality_id' => $this->locality->id,
        ]);

        $utilityCompany = UtilityCompany::create([
            'province_id' => $this->province->id,
            'name' => 'Generación Minera S.A.',
            'type' => 'electricidad',
        ]);

        $proveedor = Proveedor::create([
            'name' => 'Generador Central Mina',
            'utility_company_id' => $utilityCompany->id,
            'province_id' => $this->province->id,
        ]);

        $contract = Contract::create([
            'entity_id' => $entity->id,
            'proveedor_id' => $proveedor->id,
            'contract_number' => 'MIN-2026-001',
        ]);

        $invoice = Invoice::create([
            'contract_id' => $contract->id,
            'invoice_number' => 'TAB-GEN-001',
            'issue_date' => now()->subDays(5),
            'start_date' => now()->subMonth(),
            'end_date' => now(),
            'total_energy_consumed_kwh' => 15200.50,
            'total_amount' => 4500000.00,
            'source_type' => 'generator',
            'shift_code' => 'TURNO_DIA_14X14',
            'demand_kw_peak' => 125.40,
            'generator_hours' => 360.50,
            'occupancy_count' => 95,
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'source_type' => 'generator',
            'shift_code' => 'TURNO_DIA_14X14',
            'occupancy_count' => 95,
        ]);

        $this->assertEquals(125.40, $invoice->demand_kw_peak);
        $this->assertEquals(360.50, $invoice->generator_hours);
        $this->assertEquals('generator', $invoice->source_type);
        $this->assertEquals(95, $invoice->occupancy_count);
    }
}
