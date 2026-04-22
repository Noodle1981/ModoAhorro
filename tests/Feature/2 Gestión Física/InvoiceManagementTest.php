<?php

namespace Tests\Feature\Gestión_de_Consumo;

use App\Models\User;
use App\Models\Entity;
use App\Models\Locality;
use App\Models\Province;
use App\Models\Plan;
use App\Models\Contract;
use App\Models\Proveedor;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $province;
    protected $locality;
    protected $plan;
    protected $user;
    protected $entity;
    protected $contract;

    protected function setUp(): void
    {
        parent::setUp();

        // Basic setup for testing environment
        $this->province = Province::create(['name' => 'San Juan']);
        $this->locality = Locality::create([
            'id' => 1,
            'province_id' => $this->province->id,
            'name' => 'Santa Lucía',
            'latitude' => -31.5375,
            'longitude' => -68.5364
        ]);
        
        $this->plan = Plan::create([
            'name' => 'Premium',
            'max_entities' => 5,
            'allowed_entity_types' => ['hogar'],
            'price' => 0
        ]);

        $this->user = User::factory()->create();
        $this->entity = Entity::factory()->create(['locality_id' => $this->locality->id]);
        $this->user->entities()->attach($this->entity->id, [
            'plan_id' => $this->plan->id,
            'subscribed_at' => now(),
        ]);

        $provider = Proveedor::factory()->create(['province_id' => $this->province->id]);
        $this->contract = Contract::factory()->create([
            'entity_id' => $this->entity->id,
            'proveedor_id' => $provider->id,
        ]);
    }

    public function test_user_can_access_invoices_index()
    {
        $response = $this->actingAs($this->user)
            ->withSession(['active_entity_id' => $this->entity->id])
            ->get(route('gestion.invoices'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Entities/Invoices/Index')
            ->has('invoices')
            ->has('contracts')
        );
    }

    public function test_invoice_creation_validates_dates_emission_must_be_after_period()
    {
        $invoiceData = [
            'contract_id' => $this->contract->id,
            'invoice_number' => 'INV-001',
            'tariff' => 'T1R2',
            'invoice_date' => '2024-12-01',
            'issue_date' => '2024-11-20', // Error: issue_date < end_date
            'start_date' => '2024-11-01',
            'end_date' => '2024-11-30',
            'total_energy_consumed_kwh' => 250,
            'total_amount' => 5000,
            'installment_number' => 1,
            'total_installments' => 2,
        ];

        $response = $this->actingAs($this->user)
            ->withSession(['active_entity_id' => $this->entity->id])
            ->from(route('gestion.invoices'))
            ->post(route('gestion.invoices.store'), $invoiceData);

        $response->assertSessionHasErrors(['issue_date']);
        $this->assertEquals(
            'La fecha de emisión debe ser posterior al cierre del período.', 
            session('errors')->get('issue_date')[0]
        );
    }

    public function test_invoice_creation_validates_year_difference_limit()
    {
        $invoiceData = [
            'contract_id' => $this->contract->id,
            'invoice_number' => 'INV-OLD',
            'tariff' => 'T1R2',
            'invoice_date' => '2026-01-01',
            'issue_date' => '2026-01-01', 
            'start_date' => '2024-12-01', // Error: 2026 - 2024 = 2 years difference
            'end_date' => '2024-12-31',
            'total_energy_consumed_kwh' => 250,
            'total_amount' => 5000,
            'installment_number' => 1,
            'total_installments' => 2,
        ];

        $response = $this->actingAs($this->user)
            ->withSession(['active_entity_id' => $this->entity->id])
            ->from(route('gestion.invoices'))
            ->post(route('gestion.invoices.store'), $invoiceData);

        $response->assertSessionHasErrors(['issue_date']);
        $this->assertEquals(
            'El año de la factura no puede ser más de un año posterior al período de consumo.', 
            session('errors')->get('issue_date')[0]
        );
    }

    public function test_guided_installment_2_creation_prefills_and_saves_correctly()
    {
        // 1. Create installment 1
        $invoice1 = Invoice::factory()->create([
            'contract_id' => $this->contract->id,
            'installment_number' => 1,
            'start_date' => '2024-11-01',
            'end_date' => '2024-11-30',
        ]);

        // 2. Try to create installment 2 (mimicking the guided flow)
        $invoice2Data = [
            'contract_id' => $this->contract->id,
            'invoice_number' => 'INV-002',
            'invoice_date' => '2024-12-15',
            'issue_date' => '2024-12-15',
            'start_date' => '2024-11-01', // Same period
            'end_date' => '2024-11-30',   // Same period
            'installment_number' => 2,
            'total_installments' => 2,
            'total_energy_consumed_kwh' => 260,
            'total_amount' => 5200,
        ];

        $response = $this->actingAs($this->user)
            ->withSession(['active_entity_id' => $this->entity->id])
            ->post(route('gestion.invoices.store'), $invoice2Data);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'INV-002',
            'installment_number' => 2,
        ]);
    }
}
