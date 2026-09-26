<?php

namespace Tests\Feature\Mining;

use App\Models\User;
use Database\Seeders\MasterCleanCatalogueSeeder;
use Database\Seeders\MiningCampDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MiningSprint3Test extends TestCase
{
    use RefreshDatabase;

    public function test_mining_camp_demo_seeder_populates_camp_entities_and_readings(): void
    {
        $this->seed(MasterCleanCatalogueSeeder::class);
        $this->seed(MiningCampDemoSeeder::class);

        // Verifica usuario supervisor
        $this->assertDatabaseHas('users', [
            'email' => 'mineria@modoahorro.com',
        ]);

        // Verifica pabellones creados
        $this->assertDatabaseHas('entities', [
            'name' => 'Pabellón A-01 (Dormitorios Turno A)',
            'type' => 'pabellon',
            'camp_shift_type' => '14x14',
            'camp_capacity' => 40,
        ]);

        $this->assertDatabaseHas('entities', [
            'name' => 'Pabellón B-02 (Sustentable / Piloto Infrarrojo)',
            'type' => 'pabellon',
            'camp_shift_type' => '14x14',
        ]);

        $this->assertDatabaseHas('entities', [
            'name' => 'Pabellón C-03 (Contratistas Turno 7x7)',
            'type' => 'pabellon',
            'camp_shift_type' => '7x7',
        ]);

        $this->assertDatabaseHas('entities', [
            'name' => 'Pabellón Administrativo y Supervisión Mina',
            'type' => 'oficina',
        ]);

        // Verifica lecturas de tablero (invoices)
        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'TAB-A01-OCT2026',
            'source_type' => 'generator',
            'shift_code' => 'TURNO_A_14X14',
        ]);

        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'TAB-B02-OCT2026',
            'source_type' => 'solar_hybrid',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_mining_dashboard(): void
    {
        $response = $this->get('/mineria/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_mining_dashboard_and_receive_inertia_props(): void
    {
        $this->seed(MasterCleanCatalogueSeeder::class);
        $this->seed(MiningCampDemoSeeder::class);

        $user = User::where('email', 'mineria@modoahorro.com')->first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user)->get('/mineria/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Mining/Dashboard')
            ->has('kpis')
            ->has('pavilions')
            ->where('kpis.camp_name', 'Campamento Base Veladero - Cordillera de San Juan')
            ->has('pavilions', 4)
            ->where('pavilions.0.deviation.verdict', 'CRITICAL') // Pabellón A-01
            ->has('pavilions.0.outputs.training')
            ->has('pavilions.0.outputs.replacement')
            ->where('pavilions.1.deviation.verdict', 'OK') // Pabellón B-02
            ->has('pavilions.1.outputs.green_wave')
            ->where('pavilions.2.deviation.verdict', 'CRITICAL') // Pabellón C-03 (reincidente)
            ->has('pavilions.2.outputs.penalty')
        );
    }
}
