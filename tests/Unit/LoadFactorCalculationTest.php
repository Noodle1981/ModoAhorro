<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\EquipmentType;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Room;
use App\Models\Entity;
use App\Models\EquipmentUsage;
use App\Services\ConsumptionAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoadFactorCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear seeders necesarios
        $this->seed(\Database\Seeders\EquipmentCategorySeeder::class);
    }

    /**
     * Test que verifica la fórmula de consumo con load_factor
     * Fórmula: E (kWh) = P (kW) × h × d × load_factor
     */
    public function test_consumption_calculation_with_load_factor()
    {
        // Crear entidad y room
        $entity = Entity::factory()->create();
        $room = Room::factory()->create(['entity_id' => $entity->id]);

        // Crear categoría
        $category = EquipmentCategory::where('name', 'Electrodomésticos')->first();

        // Crear tipo de equipo: Heladera con load_factor 0.35 (duty cycle)
        $type = EquipmentType::create([
            'name' => 'Heladera Test',
            'category_id' => $category->id,
            'process_type' => 'motor',
            'load_factor' => 0.35,
            'efficiency' => 1.0,
        ]);

        $equipment = Equipment::create([
            'name' => 'Heladera Prueba',
            'type_id' => $type->id,
            'category_id' => $category->id,
            'room_id' => $room->id,
            'nominal_power_w' => 300,
            'is_active' => true,
        ]);

        // Uso: 24h/día × 60 días
        $usage = new EquipmentUsage([
            'equipment_id' => $equipment->id,
            'avg_daily_use_hours' => 24,
            'use_days_in_period' => 60,
        ]);

        $service = app(ConsumptionAnalysisService::class);

        // Calcular consumo estimado
        $consumption = $service->calculateEquipmentConsumption($usage, $equipment);

        // Verificar fórmula: (300W / 1000) × 24h × 60d × 0.35 = 151.2 kWh
        $expected = (300 / 1000) * 24 * 60 * 0.35;

        $this->assertEquals($expected, $consumption, 'El consumo debe coincidir con la fórmula P × h × d × load_factor');
    }

    /**
     * Test que verifica que equipos sin load_factor usan 1.0 por defecto
     */
    public function test_equipment_without_load_factor_uses_default()
    {
        $entity = Entity::factory()->create();
        $room = Room::factory()->create(['entity_id' => $entity->id]);
        $category = EquipmentCategory::where('name', 'Iluminación')->first();

        $type = EquipmentType::create([
            'name' => 'LED Test',
            'category_id' => $category->id,
            'process_type' => 'iluminacion',
            'load_factor' => 1.0, // LEDs consumen constante
            'efficiency' => 1.0,
        ]);

        $equipment = Equipment::create([
            'name' => 'LED Prueba',
            'type_id' => $type->id,
            'category_id' => $category->id,
            'room_id' => $room->id,
            'nominal_power_w' => 5,
            'is_active' => true,
        ]);

        $usage = new EquipmentUsage([
            'equipment_id' => $equipment->id,
            'avg_daily_use_hours' => 3,
            'use_days_in_period' => 30,
        ]);

        $service = app(ConsumptionAnalysisService::class);

        $consumption = $service->calculateEquipmentConsumption($usage, $equipment);

        // Verificar: (5W / 1000) × 3h × 30d × 1.0 = 0.45 kWh
        $expected = (5 / 1000) * 3 * 30 * 1.0;

        $this->assertEquals($expected, $consumption);
    }

    /**
     * Test que verifica load_factors específicos después del seeder
     */
    public function test_load_factors_after_seeder()
    {
        // Primero ejecutar EquipmentTypeSeeder para tener tipos
        $this->seed(\Database\Seeders\EquipmentTypeSeeder::class);

        // Luego ejecutar FixLoadFactorsSeeder
        $this->seed(\Database\Seeders\FixLoadFactorsSeeder::class);

        // Verificar algunos load_factors clave
        $this->assertDatabaseHas('equipment_types', [
            'name' => 'Heladera con Freezer',
            'load_factor' => 0.35,
        ]);

        $this->assertDatabaseHas('equipment_types', [
            'name' => 'Aire Acondicionado (2200 frigorías)',
            'load_factor' => 0.50,
        ]);

        $this->assertDatabaseHas('equipment_types', [
            'name' => 'PC de Escritorio (CPU + Monitor)',
            'load_factor' => 0.50,
        ]);

        $this->assertDatabaseHas('equipment_types', [
            'name' => 'Lámpara LED 5W (Eq. 40W)',
            'load_factor' => 1.00,
        ]);
    }
}
