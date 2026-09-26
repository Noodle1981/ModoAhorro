<?php

namespace Tests\Feature\Mining;

use App\Models\Entity;
use App\Models\Equipment;
use App\Models\EquipmentBenchmark;
use App\Models\EquipmentCategory;
use App\Models\EquipmentType;
use App\Models\Locality;
use App\Models\Province;
use App\Models\Room;
use App\Services\Mining\BaselineEngine;
use App\Services\Mining\DeviationOutputService;
use App\Services\Solar\SolarPowerService;
use App\Services\Solar\SolarWaterService;
use Database\Seeders\MasterCleanCatalogueSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiningSprint2Test extends TestCase
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
            'name' => 'Calingasta',
            'latitude' => -31.3300,
            'longitude' => -69.4100,
        ]);
    }

    public function test_solar_power_service_supports_andean_high_altitude_parameters(): void
    {
        $service = new SolarPowerService;

        $urbanResult = $service->calculateSolarCoverage(100.0, 1500.0, 1200.0, false);
        $andeanResult = $service->calculateSolarCoverage(100.0, 1500.0, 1200.0, true);

        $this->assertGreaterThan(
            $urbanResult['monthly_generation_kwh'],
            $andeanResult['monthly_generation_kwh']
        );
        $this->assertGreaterThanOrEqual($urbanResult['coverage_summer'], $andeanResult['coverage_summer']);
    }

    public function test_solar_water_service_calculates_mining_camp_diesel_roi(): void
    {
        $service = new SolarWaterService;

        $roi = $service->calculateMiningROI(
            peopleCount: 40,
            dieselCostPerLiter: 1.35,
            minTemp: -5.0,
            solarFraction: 0.75
        );

        $this->assertEquals(40, $roi['people_count']);
        $this->assertEquals(2000, $roi['daily_liters']); // 40 * 50
        $this->assertGreaterThan(0, $roi['saved_kwh_monthly']);
        $this->assertGreaterThan(0, $roi['diesel_liters_avoided_monthly']);
        $this->assertGreaterThan(0, $roi['monthly_savings_usd']);
        $this->assertGreaterThan(0, $roi['annual_savings_usd']);
        $this->assertGreaterThan(0, $roi['co2_avoided_kg_monthly']);
    }

    public function test_equipment_benchmarks_for_mining_camp_seeded_successfully(): void
    {
        $this->seed(MasterCleanCatalogueSeeder::class);

        $convectorBenchmark = EquipmentBenchmark::where('name', 'like', '%Panel Radiante Infrarrojo%')->first();
        $this->assertNotNull($convectorBenchmark);
        $this->assertEquals(0.40, $convectorBenchmark->efficiency_gain_factor);
        $this->assertEquals(900, $convectorBenchmark->watts);

        $solarWaterBenchmark = EquipmentBenchmark::where('name', 'like', '%Colector Solar Tubos de Vacío%')->first();
        $this->assertNotNull($solarWaterBenchmark);
        $this->assertEquals(0.75, $solarWaterBenchmark->efficiency_gain_factor);

        $compresorBenchmark = EquipmentBenchmark::where('name', 'like', '%Compresor de Tornillo con Variador%')->first();
        $this->assertNotNull($compresorBenchmark);
        $this->assertEquals(0.30, $compresorBenchmark->efficiency_gain_factor);
    }

    public function test_baseline_engine_calculates_expected_kwh_and_breakdowns(): void
    {
        $this->seed(MasterCleanCatalogueSeeder::class);

        $entity = Entity::create([
            'name' => 'Pabellón Los Azules 01',
            'type' => 'pabellon',
            'usage_type' => 'comercial',
            'locality_id' => $this->locality->id,
            'camp_shift_type' => '14x14',
            'camp_capacity' => 30,
        ]);

        $room = Room::create([
            'entity_id' => $entity->id,
            'name' => 'Dormitorio A',
        ]);

        $typeConvector = EquipmentType::where('name', 'Convector Eléctrico de Pared')->first();
        $catCalef = EquipmentCategory::where('name', 'Calefacción Industrial')->first();

        Equipment::create([
            'room_id' => $room->id,
            'equipment_type_id' => $typeConvector?->id,
            'category_id' => $catCalef?->id,
            'name' => 'Convector Habitación 1',
            'power_watts' => 1500,
            'quantity' => 4,
        ]);

        $engine = new BaselineEngine;
        $baseline = $engine->calculateBaseline($entity, [], 30, 30);

        $this->assertArrayHasKey('baseline_kwh', $baseline);
        $this->assertArrayHasKey('daily_baseline_kwh', $baseline);
        $this->assertArrayHasKey('by_equipment', $baseline);
        $this->assertArrayHasKey('by_room', $baseline);
        $this->assertArrayHasKey('by_category', $baseline);
        $this->assertGreaterThan(0, $baseline['baseline_kwh']);
        $this->assertEquals(30, $baseline['occupancy_count']);
    }

    public function test_baseline_engine_calculates_deviation_and_verdicts(): void
    {
        $engine = new BaselineEngine;

        // Caso 1: Desvío menor al 5% -> OK
        $okResult = $engine->calculateDeviation(1000.0, 1030.0);
        $this->assertEquals('OK', $okResult['verdict']);
        $this->assertEquals(3.0, $okResult['delta_pct']);
        $this->assertGreaterThan(0, $okResult['liters_wasted']);

        // Caso 2: Desvío entre 5% y 20% -> WARN
        $warnResult = $engine->calculateDeviation(1000.0, 1150.0);
        $this->assertEquals('WARN', $warnResult['verdict']);
        $this->assertEquals(15.0, $warnResult['delta_pct']);

        // Caso 3: Desvío mayor al 20% -> CRITICAL
        $criticalResult = $engine->calculateDeviation(1000.0, 1600.0);
        $this->assertEquals('CRITICAL', $criticalResult['verdict']);
        $this->assertEquals(60.0, $criticalResult['delta_pct']);
        $this->assertEquals(168.0, $criticalResult['liters_wasted']); // 600 * 0.28 = 168
        $this->assertGreaterThan(0, $criticalResult['usd_wasted']);
    }

    public function test_deviation_output_service_generates_four_value_outputs_appropriately(): void
    {
        $service = new DeviationOutputService;
        $engine = new BaselineEngine;

        $pabellon = Entity::create([
            'name' => 'Módulo B - Veladero',
            'type' => 'pabellon',
            'locality_id' => $this->locality->id,
            'camp_shift_type' => '14x14',
        ]);

        // 1. Escenario Cumplidor (Onda Verde):
        $compliantDev = $engine->calculateDeviation(1000.0, 980.0); // -2% de consumo
        $outputs1 = $service->generate($compliantDev, 1, $pabellon);

        $this->assertNotNull($outputs1['training']);
        $this->assertNotNull($outputs1['green_wave']);
        $this->assertEquals('Onda Verde Minera', $outputs1['green_wave']['badge']);
        $this->assertNull($outputs1['penalty']);
        $this->assertNull($outputs1['replacement']);

        // 2. Escenario Crítico con Reincidencia (Penalización + Reemplazo):
        $criticalDev = $engine->calculateDeviation(1000.0, 1650.0); // +65% de consumo
        $outputs2 = $service->generate($criticalDev, 3, $pabellon);

        $this->assertNotNull($outputs2['training']);
        $this->assertNull($outputs2['green_wave']);
        $this->assertNotNull($outputs2['penalty']);
        $this->assertNotNull($outputs2['replacement']);
        $this->assertStringStartsWith('ACTA-DESVIO-', $outputs2['penalty']['code']);
        $this->assertEquals('CRÍTICA', $outputs2['penalty']['severity']);
        $this->assertEquals(40.0, $outputs2['replacement']['estimated_savings_pct']);
        $this->assertGreaterThan(0, $outputs2['replacement']['payback_months']);
    }
}
