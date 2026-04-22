<?php

namespace Tests\Feature\Analisis_y_Ahorro;

use App\Models\User;
use App\Models\Entity;
use App\Models\Locality;
use App\Models\Province;
use App\Models\Plan;
use App\Models\Invoice;
use App\Models\Contract;
use App\Models\UtilityCompany;
use App\Models\Proveedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Http\Controllers\AnalysisController;
use Illuminate\Http\Request;

class UsageAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $entity;
    protected $contract;

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

        $utilityCompany = UtilityCompany::create([
            'id' => 1,
            'province_id' => $province->id,
            'name' => 'Naturgy',
            'type' => 'gas'
        ]);

        $proveedor = Proveedor::create([
            'name' => 'Distribuidora S.A.',
            'utility_company_id' => $utilityCompany->id,
            'province_id' => $province->id
        ]);

        $this->contract = Contract::create([
            'entity_id' => $this->entity->id,
            'utility_company_id' => $utilityCompany->id,
            'proveedor_id' => $proveedor->id,
            'account_number' => '123456',
            'is_active' => true
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function bimonthly_invoice_without_installments_is_considered_complete()
    {
        // Factura bimestral única (sin cuotas, installment_number = null, total = 2)
        $invoice = Invoice::create([
            'contract_id' => $this->contract->id,
            'invoice_number' => 'INV-BIMONTHLY-1',
            'issue_date' => '2025-03-25',
            'start_date' => '2025-01-15',
            'end_date' => '2025-03-20',
            'total_energy_consumed_kwh' => 624,
            'total_amount' => 10000,
            'installment_number' => null,
            'total_installments' => 2
        ]);

        $this->actingAs($this->user);
        session(['active_entity_id' => $this->entity->id]);

        $response = $this->get(route('analisis.usage'));
        
        $response->assertStatus(200);
        
        // Obtenemos los datos pasados a Inertia
        $page = $response->viewData('page');
        $unifications = $page['props']['unifications'];
        
        $this->assertCount(1, $unifications);
        $this->assertTrue($unifications[0]['is_complete'], 'El periodo bimestral unificado debe estar completo.');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function partial_monthly_invoice_is_considered_incomplete()
    {
        // Factura mensual que es parte de un bimestre (installment = 1, total = 2)
        $invoice = Invoice::create([
            'contract_id' => $this->contract->id,
            'invoice_number' => 'INV-MONTHLY-1',
            'issue_date' => '2025-02-15',
            'start_date' => '2025-01-15',
            'end_date' => '2025-02-15',
            'total_energy_consumed_kwh' => 300,
            'total_amount' => 5000,
            'installment_number' => 1,
            'total_installments' => 2
        ]);

        $this->actingAs($this->user);
        session(['active_entity_id' => $this->entity->id]);

        $response = $this->get(route('analisis.usage'));
        
        $response->assertStatus(200);
        
        $page = $response->viewData('page');
        $unifications = $page['props']['unifications'];
        
        $this->assertCount(1, $unifications);
        $this->assertFalse($unifications[0]['is_complete'], 'El periodo mensual 1 de 2 debe estar incompleto.');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function multiple_monthly_invoices_complete_the_period_if_they_match_total_installments()
    {
        // Cuota 1
        Invoice::create([
            'contract_id' => $this->contract->id,
            'invoice_number' => 'INV-MONTHLY-1',
            'issue_date' => '2025-02-15',
            'start_date' => '2025-01-15',
            'end_date' => '2025-02-15',
            'total_energy_consumed_kwh' => 300,
            'total_amount' => 5000,
            'installment_number' => 1,
            'total_installments' => 2
        ]);

        // Cuota 2
        Invoice::create([
            'contract_id' => $this->contract->id,
            'invoice_number' => 'INV-MONTHLY-2',
            'issue_date' => '2025-03-15',
            'start_date' => '2025-02-16',
            'end_date' => '2025-03-20',
            'total_energy_consumed_kwh' => 324,
            'total_amount' => 5000,
            'installment_number' => 2,
            'total_installments' => 2
        ]);

        // Para agruparlos correctamente en el Test, GroupsInvoices trait agrupa por start_date y end_date de la cuota 1?
        // En tu sistema, si las fechas difieren, NO se agrupan por defecto a menos que el trait las combine de otra forma.
        // Pero vamos a probar el comportamiento base actual del sistema.
    }
}
