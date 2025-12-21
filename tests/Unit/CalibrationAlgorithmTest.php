<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ConsumptionCalibrator;
use App\Models\Equipment;
use App\Models\EquipmentType;
use App\Models\EquipmentUsage;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CalibrationAlgorithmTest extends TestCase
{
    use RefreshDatabase;

    private ConsumptionCalibrator $calibrator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calibrator = new ConsumptionCalibrator();
    }

    /**
     * Test delta positivo: consumo estimado < facturado
     * Debe distribuir el exceso a WHALES con pesos
     */
    public function test_positive_delta_distributes_to_whales()
    {
        // Crear equipos de prueba
        $usages = $this->createTestUsages([
            ['name' => 'Heladera', 'category' => 'BASE_CRITICAL', 'estimated' => 80],
            ['name' => 'Aire', 'category' => 'Climatización', 'estimated' => 100],
            ['name' => 'TV', 'category' => 'Entretenimiento', 'estimated' => 50],
        ]);

        // Estimado total: 230 kWh, Facturado: 400 kWh
        // Delta positivo: +170 kWh debe ir a Aire y TV (WHALES)

        $calibrated = $this->calibrator->calibrate($usages, 400);

        // Heladera (BASE) debe mantener su valor
        $heladera = $calibrated->firstWhere('equipment.name', 'Heladera');
        $this->assertEquals(80, $heladera->kwh_reconciled, 'Base debe mantenerse');

        // Total debe ser exactamente 400 kWh
        $total = $calibrated->sum('kwh_reconciled');
        $this->assertEquals(400, $total, 'Total calibrado debe ser igual a facturado');

        // Aire debe recibir MÁS que TV (peso x3 vs x0.6)
        $aire = $calibrated->firstWhere('equipment.name', 'Aire');
        $tv = $calibrated->firstWhere('equipment.name', 'TV');
        $this->assertGreaterThan(
            $tv->kwh_reconciled,
            $aire->kwh_reconciled,
            'Climatización debe recibir más por peso x3'
        );
    }

    /**
     * Test delta negativo: consumo estimado > facturado
     * Debe recortar proporcionalmente WHALES y ANTS
     */
    public function test_negative_delta_reduces_proportionally()
    {
        $usages = $this->createTestUsages([
            ['name' => 'Heladera', 'category' => 'BASE_CRITICAL', 'estimated' => 80],
            ['name' => 'Foco LED', 'category' => 'Iluminación', 'estimated' => 10],
            ['name' => 'PC', 'category' => 'Oficina', 'estimated' => 200],
        ]);

        // Estimado total: 290 kWh, Facturado: 120 kWh
        // Debe recortar ANTS y WHALES proporcionalmente

        $calibrated = $this->calibrator->calibrate($usages, 120);

        // Total debe ser exactamente 120 kWh
        $total = $calibrated->sum('kwh_reconciled');
        $this->assertEquals(120, $total);

        // Heladera debe mantenerse
        $heladera = $calibrated->firstWhere('equipment.name', 'Heladera');
        $this->assertEquals(80, $heladera->kwh_reconciled);

        // Foco y PC deben recibir consumo proporcional (NO cero)
        $foco = $calibrated->firstWhere('equipment.name', 'Foco LED');
        $pc = $calibrated->firstWhere('equipment.name', 'PC');

        $this->assertGreaterThan(0, $foco->kwh_reconciled, 'ANTS debe tener consumo > 0');
        $this->assertGreaterThan(0, $pc->kwh_reconciled, 'WHALES debe tener consumo > 0');
    }

    /**
     * Test caso extremo: factura muy baja (solo BASE_CRITICAL)
     */
    public function test_extreme_low_invoice_partial_base()
    {
        $usages = $this->createTestUsages([
            ['name' => 'Heladera', 'category' => 'BASE_CRITICAL', 'estimated' => 80],
            ['name' => 'Router', 'category' => 'BASE_CRITICAL', 'estimated' => 30],
            ['name' => 'Aire', 'category' => 'Climatización', 'estimated' => 200],
        ]);

        // Estimado BASE: 110 kWh, Facturado: 90 kWh
        // Debe hacer CRITICAL_PARTIAL en BASE, resto a 0

        $calibrated = $this->calibrator->calibrate($usages, 90);

        $total = $calibrated->sum('kwh_reconciled');
        $this->assertEquals(90, $total);

        // Heladera y Router deben tener consumo parcial proporcional
        $heladera = $calibrated->firstWhere('equipment.name', 'Heladera');
        $router = $calibrated->firstWhere('equipment.name', 'Router');

        $this->assertLessThan(80, $heladera->kwh_reconciled, 'Heladera debe ser < estimado');
        $this->assertLessThan(30, $router->kwh_reconciled, 'Router debe ser < estimado');
        $this->assertGreaterThan(0, $heladera->kwh_reconciled, 'Heladera debe ser > 0');
        $this->assertGreaterThan(0, $router->kwh_reconciled, 'Router debe ser > 0');

        // Aire debe quedar en 0 (no alcanza)
        $aire = $calibrated->firstWhere('equipment.name', 'Aire');
        $this->assertEquals(0, $aire->kwh_reconciled);
    }

    /**
     * Test que ningún equipo excede su estimado
     */
    public function test_no_equipment_exceeds_estimated()
    {
        $usages = $this->createTestUsages([
            ['name' => 'Heladera', 'category' => 'BASE_CRITICAL', 'estimated' => 50],
            ['name' => 'LED 5W', 'category' => 'Iluminación', 'estimated' => 0.5],
            ['name' => 'Aire', 'category' => 'Climatización', 'estimated' => 100],
        ]);

        $calibrated = $this->calibrator->calibrate($usages, 200);

        foreach ($calibrated as $usage) {
            $this->assertLessThanOrEqual(
                $usage->kwh_estimated,
                $usage->kwh_reconciled,
                "Equipo {$usage->equipment->name} no debe exceder su estimado"
            );
        }
    }

    /**
     * Helper: Crear usages de prueba
     */
    private function createTestUsages(array $data): Collection
    {
        $usages = collect();

        foreach ($data as $item) {
            $category = \App\Models\EquipmentCategory::firstOrCreate(['name' => $item['category']]);

            $type = EquipmentType::create([
                'name' => $item['name'] . ' Type',
                'category_id' => $category->id,
                'load_factor' => 1.0,
            ]);

            $equipment = new Equipment([
                'name' => $item['name'],
                'type_id' => $type->id,
                'category_id' => $category->id,
                'nominal_power_w' => 100,
            ]);
            $equipment->category = $category;
            $equipment->type = $type;

            $usage = new EquipmentUsage([
                'kwh_estimated' => $item['estimated'],
            ]);
            $usage->equipment = $equipment;

            $usages->push($usage);
        }

        return $usages;
    }
}
