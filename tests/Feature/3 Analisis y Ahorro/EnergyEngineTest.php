<?php

namespace Tests\Feature\Gestión_de_Consumo;

use App\Models\User;
use App\Models\Entity;
use App\Models\Locality;
use App\Models\Province;
use App\Models\Plan;
use App\Models\Room;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentType;
use App\Models\Invoice;
use App\Services\EnergyEngineService;
use App\Services\ClimateService;
use App\Services\ThermalProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnergyEngineTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $entity;
    protected $invoice;
    protected $service;

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

        $utilityCompany = \App\Models\UtilityCompany::create([
            'id' => 1,
            'province_id' => $province->id,
            'name' => 'Naturgy',
            'type' => 'gas'
        ]);

        $proveedor = \App\Models\Proveedor::create([
            'name' => 'Distribuidora S.A.',
            'utility_company_id' => $utilityCompany->id,
            'province_id' => $province->id
        ]);

        $contract = $this->entity->contracts()->create([
            'utility_company_id' => $utilityCompany->id,
            'proveedor_id' => $proveedor->id,
            'account_number' => '123456',
            'is_active' => true
        ]);

        $this->invoice = Invoice::create([
            'contract_id' => $contract->id,
            'invoice_number' => 'INV-001',
            'issue_date' => '2025-03-25',
            'start_date' => '2025-01-01',
            'end_date' => '2025-02-28',
            'total_energy_consumed_kwh' => 500,
            'total_amount' => 10000,
        ]);

        $this->service = app(EnergyEngineService::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function engine_correctly_assigns_equipment_to_tanks_based_on_new_categories()
    {
        // 0. Asegurar que las categorías existan antes de empezar
        $this->seed(\Database\Seeders\CategoryRefinementSeeder::class);

        $room = Room::factory()->create(['entity_id' => $this->entity->id]);

        // 1. Heladera (Refrigeración) -> Should be Tank 2
        $catRef = EquipmentCategory::where('name', 'Refrigeración')->first();
        $typeRef = EquipmentType::factory()->create([
            'category_id' => $catRef->id, 
            'name' => 'Heladera con Freezer', 
            'default_power_watts' => 150,
            'consumption_logic' => 'BASE_LOAD',
            'is_climatization' => false
        ]);
        $eqRef = Equipment::create([
            'room_id' => $room->id,
            'category_id' => $catRef->id,
            'type_id' => $typeRef->id,
            'name' => 'Mi Heladera',
            'nominal_power_w' => 150,
            'avg_daily_use_hours' => 24, // Heladeras son 24h
            'is_active' => true
        ]);

        // 2. Aire Acondicionado (Climatización) -> Should be Tank 3
        $catCli = EquipmentCategory::where('name', 'Climatización')->first();
        $typeCli = EquipmentType::factory()->create([
            'category_id' => $catCli->id, 
            'name' => 'Aire Split', 
            'consumption_logic' => 'CLIMATE_DEPENDENT',
            'is_thermal_sensitive' => true,
            'is_climatization' => true
        ]);
        $eqCli = Equipment::create([
            'room_id' => $room->id,
            'category_id' => $catCli->id,
            'type_id' => $typeCli->id,
            'name' => 'Aire Living',
            'nominal_power_w' => 2000,
            'avg_daily_use_hours' => 5,
            'is_active' => true
        ]);

        // 3. Lavarropas (Lavado y Limpieza) -> Should be Tank 4
        $catLav = EquipmentCategory::where('name', 'Lavado y Limpieza')->first();
        $typeLav = EquipmentType::factory()->create([
            'category_id' => $catLav->id, 
            'name' => 'Lavarropas Automático',
            'consumption_logic' => 'CONSTANT_ELASTIC',
            'is_climatization' => false
        ]);
        $eqLav = Equipment::create([
            'room_id' => $room->id,
            'category_id' => $catLav->id,
            'type_id' => $typeLav->id,
            'name' => 'Mi Lavarropas',
            'nominal_power_w' => 2500,
            'avg_daily_use_hours' => 1,
            'is_active' => true
        ]);

        // Run the engine
        $equipments = Equipment::with('type.category')->get();
        $result = $this->service->processInvoice($this->invoice, $equipments);

        // Assertions
        $processedRef = $equipments->where('name', 'Mi Heladera')->first();
        $processedCli = $equipments->where('name', 'Aire Living')->first();
        $processedLav = $equipments->where('name', 'Mi Lavarropas')->first();

        $this->assertEquals(2, $processedRef->tank_assignment, 'La heladera debería ser Tanque 2 (Base Inmutable)');
        $this->assertEquals(3, $processedCli->tank_assignment, 'El aire debería ser Tanque 3 (Sensibilidad Climática)');
        $this->assertEquals(4, $processedLav->tank_assignment, 'El lavarropas debería ser Tanque 4 (Elasticidad y Hábitos)');
    }
}
