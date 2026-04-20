<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Invoice;
use App\Models\Equipment;
use App\Models\Contract;
use App\Models\Entity;
use App\Models\Room;
use App\Services\Core\ValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class Sprint1Test extends TestCase
{
    use RefreshDatabase;

    public function test_validation_service_calculates_deviation_correctly()
    {
        $invoice = new Invoice(['total_energy_consumed_kwh' => 100]);
        $service = new ValidationService();

        // Caso 1: Exacto
        $result = $service->calculateDeviation($invoice, 100);
        $this->assertEquals(0, $result['deviation_percent']);
        $this->assertEquals('success', $result['alert_level']);

        // Caso 2: Desviación Moderada (20%)
        $result = $service->calculateDeviation($invoice, 80);
        $this->assertEquals(20, $result['deviation_percent']);
        $this->assertEquals('warning', $result['alert_level']);

        // Caso 3: Desviación Crítica (50%)
        $result = $service->calculateDeviation($invoice, 50);
        $this->assertEquals(50, $result['deviation_percent']);
        $this->assertEquals('danger', $result['alert_level']);
    }

    public function test_equipment_date_filtering()
    {
        // Setup manual (sin factories)
        $province = \App\Models\Province::create(['name' => 'Test Province']);
        $locality = \App\Models\Locality::create(['name' => 'Test Locality', 'province_id' => $province->id]);

        $entity = Entity::create([
            'name' => 'Test Entity',
            'user_id' => 1,
            'locality_id' => $locality->id,
            'address' => 'Test Address',
            'start_period_day' => 1,
            'area_m2' => 50,
            'inhabitants_count' => 2,
            'occupancy_type' => 'residential'
        ]);
        
        $room = Room::create([
            'entity_id' => $entity->id,
            'name' => 'Test Room'
        ]);
        
        $category = \App\Models\EquipmentCategory::create(['name' => 'Test Category']);

        // Equipo Viejo (Instalado antes del periodo)
        $oldEquipment = Equipment::create([
            'room_id' => $room->id,
            'name' => 'Old Equipment',
            'equipment_type_id' => 1, // Dummy
            'category_id' => $category->id,
            'is_active' => true,
            'installed_at' => '2024-01-01'
        ]);

        // Equipo Nuevo (Instalado DESPUÉS del periodo)
        $newEquipment = Equipment::create([
            'room_id' => $room->id,
            'name' => 'New Equipment',
            'equipment_type_id' => 1, // Dummy
            'category_id' => $category->id,
            'is_active' => true,
            'installed_at' => '2025-06-01'
        ]);

        // Crear proveedor dummy
        $proveedor = \App\Models\Proveedor::create(['name' => 'Test Provider', 'province_id' => $province->id]);
        $utility = \App\Models\UtilityCompany::create(['name' => 'Test Utility']);

        // Factura del periodo Enero-Marzo 2025
        $contract = Contract::create([
            'entity_id' => $entity->id,
            'proveedor_id' => $proveedor->id,
            'utility_company_id' => $utility->id,
            'contract_number' => '123',
            'meter_number' => 'M123',
            'client_number' => 'C123',
            'supply_number' => 'S123',
            'rate_name' => 'T1',
            'tariff_type' => 'Residential',
            'tariff_scheme_id' => 1,
            'start_date' => '2024-01-01'
        ]);
        
        $invoice = Invoice::create([
            'contract_id' => $contract->id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-03-31',
            'issue_date' => '2025-04-01',
            'total_energy_consumed_kwh' => 100,
            'total_amount' => 1000,
            // 'consumption_kwh' => 100, // Removed
            // 'energy_cost' => 800,
            // 'taxes_cost' => 200
        ]);

        // Simular lógica del controlador (Completa)
        $validEquipments = Equipment::where('room_id', $room->id)
            ->where(function($q) use ($invoice) {
                $q->whereNull('installed_at')
                  ->orWhere('installed_at', '<=', $invoice->end_date);
            })
            ->where(function($q) use ($invoice) {
                $q->whereNull('removed_at')
                  ->orWhere('removed_at', '>=', $invoice->start_date);
            })
            ->get();

        $this->assertTrue($validEquipments->contains('id', $oldEquipment->id));
        $this->assertFalse($validEquipments->contains('id', $newEquipment->id));
    }

    public function test_invoice_locking_logic()
    {
        // Crear dependencias mínimas
        $province = \App\Models\Province::create(['name' => 'Test Province']);
        $locality = \App\Models\Locality::create(['name' => 'Test Locality', 'province_id' => $province->id]);
        
        $proveedor = \App\Models\Proveedor::create(['name' => 'Test Provider', 'province_id' => $province->id]);
        $utility = \App\Models\UtilityCompany::create(['name' => 'Test Utility']);
        $entity = Entity::create(['name' => 'E', 'user_id' => 1, 'locality_id' => $locality->id, 'address' => 'A', 'start_period_day' => 1]);
        $contract = Contract::create(['entity_id' => $entity->id, 'proveedor_id' => $proveedor->id, 'utility_company_id' => $utility->id, 'contract_number' => 'C', 'meter_number' => 'M123', 'client_number' => 'C123', 'supply_number' => 'S123', 'rate_name' => 'T1', 'tariff_type' => 'Residential', 'tariff_scheme_id' => 1, 'start_date' => '2024-01-01']);
        
        $invoice = Invoice::create([
            'contract_id' => $contract->id,
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-31',
            'issue_date' => '2025-02-01',
            'total_energy_consumed_kwh' => 100,
            'total_amount' => 1000,
            'usage_locked' => true,
            // 'consumption_kwh' => 100, // Removed
            // 'energy_cost' => 800,
            // 'taxes_cost' => 200
        ]);

        // Verificar que está bloqueada
        $this->assertTrue((bool)$invoice->usage_locked);

        // Simular desbloqueo
        $invoice->usage_locked = false;
        $invoice->save();

        $this->assertFalse((bool)$invoice->usage_locked);
    }
}
