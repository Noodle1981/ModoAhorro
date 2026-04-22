<?php

namespace Tests\Feature\Gestión_Física;

use App\Models\User;
use App\Models\Entity;
use App\Models\Locality;
use App\Models\Province;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThermalComfortTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $entity;
    protected $province;
    protected $locality;
    protected $plan;

    protected function setUp(): void
    {
        parent::setUp();

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
        $this->entity = Entity::factory()->create([
            'locality_id' => $this->locality->id,
            'type' => 'hogar'
        ]);

        $this->user->entities()->attach($this->entity->id, [
            'plan_id' => $this->plan->id,
            'subscribed_at' => now(),
        ]);
    }

    public function test_user_can_view_thermal_comfort_result_if_profile_exists()
    {
        // Mock a thermal profile
        $profile = [
            'roof_type' => 'techo_liviano',
            'window_type' => 'vidrio_simple',
            'window_frame' => 'aluminio',
            'orientation' => 'norte_sur',
            'sun_exposure' => 'alta',
            'roof_insulation' => true,
            'drafts_detected' => false,
            'south_window' => true,
            'thermal_score' => 65,
            'energy_label' => 'C'
        ];

        $this->entity->update(['thermal_profile' => $profile]);

        $response = $this->actingAs($this->user)
            ->get(route('gestion.thermal.result', $this->entity));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Thermal/Result')
            ->has('entity')
            ->has('profile')
            ->has('scoreResult')
            ->has('recommendations')
            ->where('entity.id', $this->entity->id)
        );
    }

    public function test_user_is_redirected_to_wizard_if_no_thermal_profile_exists()
    {
        // Ensure profile is null
        $this->entity->update(['thermal_profile' => null]);

        $response = $this->actingAs($this->user)
            ->get(route('gestion.thermal.result', $this->entity));

        $response->assertRedirect(route('gestion.thermal.wizard', $this->entity));
    }

    public function test_user_cannot_view_thermal_result_of_other_users_entity()
    {
        $otherUser = User::factory()->create();
        $otherEntity = Entity::factory()->create(['locality_id' => $this->locality->id]);

        $response = $this->actingAs($this->user) // Acting as User A
            ->get(route('gestion.thermal.result', $otherEntity)); // Trying to see User B's entity

        $response->assertStatus(403);
    }
}
