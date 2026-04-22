<?php

namespace Tests\Feature\Gestión_de_Consumo;

use App\Models\User;
use App\Models\Entity;
use App\Models\Contract;
use App\Models\Proveedor;
use App\Models\Invoice;
use App\Models\Locality;
use App\Models\Province;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnificationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $entity;
    protected $contract;

    protected function setUp(): void
    {
        parent::setUp();

        $province = Province::create(['name' => 'San Juan']);
        $locality = Locality::create([
            'id' => 1,
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

        $provider = Proveedor::factory()->create(['province_id' => $province->id]);
        $this->contract = Contract::factory()->create([
            'entity_id' => $this->entity->id,
            'proveedor_id' => $provider->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function unifications_index_groups_multiple_installments_correctly()
    {
        // Create 2 invoices for the same bimonthly period
        Invoice::factory()->create([
            'contract_id' => $this->contract->id,
            'start_date' => '2024-11-01',
            'end_date' => '2024-11-30',
            'installment_number' => 1,
            'total_energy_consumed_kwh' => 100,
            'total_amount' => 2000,
        ]);

        Invoice::factory()->create([
            'contract_id' => $this->contract->id,
            'start_date' => '2024-11-01',
            'end_date' => '2024-11-30',
            'installment_number' => 2,
            'total_energy_consumed_kwh' => 150,
            'total_amount' => 3000,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_entity_id' => $this->entity->id])
            ->get(route('gestion.unifications'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Entities/Unifications/Index')
            ->has('unifications', 1)
            ->where('unifications.0.total_kwh', 250)
            ->where('unifications.0.total_amount', 5000)
            ->where('unifications.0.status', 'complete')
            ->has('unifications.0.invoices', 2)
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function unifications_index_shows_pending_status_for_single_installment()
    {
        Invoice::factory()->create([
            'contract_id' => $this->contract->id,
            'start_date' => '2024-11-01',
            'end_date' => '2024-11-30',
            'installment_number' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_entity_id' => $this->entity->id])
            ->get(route('gestion.unifications'));

        $response->assertInertia(fn ($page) => $page
            ->where('unifications.0.status', 'pending')
        );
    }
}
