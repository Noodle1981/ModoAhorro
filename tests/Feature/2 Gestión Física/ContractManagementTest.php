<?php

namespace Tests\Feature\Gestión_Física;

use App\Models\User;
use App\Models\Entity;
use App\Models\Locality;
use App\Models\Province;
use App\Models\Plan;
use App\Models\Proveedor;
use App\Models\Contract;
use App\Models\UtilityCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $provinceSJ;
    protected $provinceMZ;
    protected $localitySJ;
    protected $plan;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear provincias
        $this->provinceSJ = Province::create(['name' => 'San Juan']);
        $this->provinceMZ = Province::create(['name' => 'Mendoza']);
        
        // Crear localidad en San Juan
        $this->localitySJ = new Locality();
        $this->localitySJ->forceFill([
            'id' => 1,
            'province_id' => $this->provinceSJ->id,
            'name' => 'Santa Lucía',
            'latitude' => -31.5375,
            'longitude' => -68.5364
        ]);
        $this->localitySJ->save();

        // Crear Plan
        $this->plan = Plan::create([
            'name' => 'Premium',
            'max_entities' => 5,
            'allowed_entity_types' => ['hogar'],
            'price' => 0
        ]);
    }

    protected function createEntityForUser($user)
    {
        $entity = Entity::factory()->create(['locality_id' => $this->localitySJ->id]);
        $user->entities()->attach($entity->id, [
            'plan_id' => $this->plan->id,
            'subscribed_at' => now(),
        ]);
        return $entity;
    }

    public function test_user_can_access_contracts_index_with_context()
    {
        $user = User::factory()->create();
        $entity = $this->createEntityForUser($user);

        $response = $this->actingAs($user)
            ->withSession(['active_entity_id' => $entity->id])
            ->get(route('gestion.contracts'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Entities/Contracts/Index')
            ->has('contracts')
            ->has('entities')
            ->has('proveedores')
            ->where('active_entity_id', $entity->id)
        );
    }

    public function test_providers_are_filtered_by_active_entity_province()
    {
        $user = User::factory()->create();
        $entity = $this->createEntityForUser($user); // SJ

        // Crear proveedores en diferentes provincias
        $provSJ = Proveedor::factory()->create(['province_id' => $this->provinceSJ->id, 'name' => 'Energía San Juan']);
        $provMZ = Proveedor::factory()->create(['province_id' => $this->provinceMZ->id, 'name' => 'Edemsa']);

        $response = $this->actingAs($user)
            ->withSession(['active_entity_id' => $entity->id])
            ->get(route('gestion.contracts'));

        $response->assertInertia(fn ($page) => $page
            ->has('proveedores', 1)
            ->where('proveedores.0.name', 'Energía San Juan')
        );
    }

    public function test_user_can_store_contract_with_full_technical_details()
    {
        $user = User::factory()->create();
        $entity = $this->createEntityForUser($user);
        $provider = Proveedor::factory()->create(['province_id' => $this->provinceSJ->id]);

        $contractData = [
            'entity_id' => $entity->id,
            'proveedor_id' => $provider->id,
            'supply_number' => 'NIU-12345',
            'meter_number' => 'SERIE-999',
            'contract_number' => 'CONT-777',
            'rate_name' => 'T1-R1',
            'start_date' => '2024-01-01',
            'is_three_phase' => true,
            'contracted_power_kw_p1' => 10.5,
            'contracted_power_kw_p2' => 5.0,
            'contracted_power_kw_p3' => 5.0,
            'is_active' => true,
        ];

        $response = $this->actingAs($user)
            ->withSession(['active_entity_id' => $entity->id])
            ->post(route('gestion.contracts.store'), $contractData);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('contracts', [
            'contract_number' => 'CONT-777',
            'meter_number' => 'SERIE-999',
            'supply_number' => 'NIU-12345',
            'is_three_phase' => 1
        ]);
    }

    public function test_contract_number_must_be_unique_across_system()
    {
        $user = User::factory()->create();
        $entity = $this->createEntityForUser($user);
        $provider = Proveedor::factory()->create();
        
        // Crear un contrato previo
        Contract::factory()->create(['contract_number' => 'DUPLICADO-123']);

        $contractData = [
            'entity_id' => $entity->id,
            'proveedor_id' => $provider->id,
            'supply_number' => 'NIU-999',
            'contract_number' => 'DUPLICADO-123', // Mismo numero
            'rate_name' => 'T1',
            'is_three_phase' => false,
            'contracted_power_kw_p1' => 5,
            'is_active' => true,
        ];

        $response = $this->actingAs($user)
            ->withSession(['active_entity_id' => $entity->id])
            ->post(route('gestion.contracts.store'), $contractData);

        $response->assertSessionHasErrors(['contract_number']);
        $this->assertEquals(
            'Este número de contrato ya se encuentra registrado en el sistema.', 
            session('errors')->get('contract_number')[0]
        );
    }

    public function test_storing_active_contract_deactivates_others_for_same_entity()
    {
        $user = User::factory()->create();
        $entity = $this->createEntityForUser($user);
        $provider = Proveedor::factory()->create();
        
        // Crear un contrato activo previo
        $oldContract = Contract::factory()->create([
            'entity_id' => $entity->id,
            'is_active' => true
        ]);

        $newContractData = [
            'entity_id' => $entity->id,
            'proveedor_id' => $provider->id,
            'supply_number' => 'NIU-NEW',
            'contract_number' => 'CONT-NEW',
            'rate_name' => 'T1',
            'is_three_phase' => false,
            'contracted_power_kw_p1' => 5,
            'is_active' => true, // Este nuevo será el activo
        ];

        $this->actingAs($user)
            ->withSession(['active_entity_id' => $entity->id])
            ->post(route('gestion.contracts.store'), $newContractData);

        $this->assertFalse($oldContract->fresh()->is_active);
        $this->assertTrue(Contract::where('contract_number', 'CONT-NEW')->first()->is_active);
    }

    public function test_user_can_toggle_contract_active_status()
    {
        $user = User::factory()->create();
        $entity = $this->createEntityForUser($user);
        
        $contract1 = Contract::factory()->create(['entity_id' => $entity->id, 'is_active' => true]);
        $contract2 = Contract::factory()->create(['entity_id' => $entity->id, 'is_active' => false]);

        // Activar el 2 (deberia desactivar el 1)
        $response = $this->actingAs($user)
            ->withSession(['active_entity_id' => $entity->id])
            ->patch(route('gestion.contracts.toggle', $contract2->id));

        $response->assertRedirect();
        $this->assertFalse($contract1->fresh()->is_active);
        $this->assertTrue($contract2->fresh()->is_active);
    }

    public function test_user_can_update_contract()
    {
        $user = User::factory()->create();
        $entity = $this->createEntityForUser($user);
        $provider = Proveedor::factory()->create(['province_id' => $this->provinceSJ->id]);
        $contract = Contract::factory()->create(['entity_id' => $entity->id]);

        $updateData = [
            'entity_id' => $entity->id,
            'proveedor_id' => $provider->id,
            'supply_number' => 'NIU-UPDATED',
            'contract_number' => 'CONT-UPDATED',
            'rate_name' => 'T1-R2',
            'is_three_phase' => false,
            'contracted_power_kw_p1' => 7.5,
            'is_active' => true,
        ];

        $response = $this->actingAs($user)
            ->withSession(['active_entity_id' => $entity->id])
            ->put(route('gestion.contracts.update', $contract->id), $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('contracts', [
            'id' => $contract->id,
            'supply_number' => 'NIU-UPDATED',
            'rate_name' => 'T1-R2'
        ]);
    }

    public function test_user_can_delete_contract()
    {
        $user = User::factory()->create();
        $entity = $this->createEntityForUser($user);
        $contract = Contract::factory()->create(['entity_id' => $entity->id]);

        $response = $this->actingAs($user)
            ->withSession(['active_entity_id' => $entity->id])
            ->delete(route('gestion.contracts.destroy', $contract->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('contracts', ['id' => $contract->id]);
    }
}
