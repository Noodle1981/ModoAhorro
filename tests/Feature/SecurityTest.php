<?php

namespace Tests\Feature;

use App\Models\Entity;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentType;
use App\Models\Locality;
use App\Models\Plan;
use App\Models\Province;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $otherUser;
    protected $adminUser;
    protected $entity;
    protected $otherEntity;
    protected $room;
    protected $otherRoom;
    protected $equipment;
    protected $category;
    protected $type;

    protected function setUp(): void
    {
        parent::setUp();

        $province = Province::create(['name' => 'San Juan']);
        $locality = Locality::create([
            'id' => 1,
            'province_id' => $province->id,
            'name' => 'Capital',
            'latitude' => -31.5375,
            'longitude' => -68.5364,
        ]);

        $plan = Plan::create([
            'name' => 'Básico',
            'max_entities' => 5,
            'allowed_entity_types' => ['hogar'],
            'price' => 0,
        ]);

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();

        $this->adminUser = User::factory()->create();
        $this->adminUser->is_super_admin = true;
        $this->adminUser->save();

        $this->entity = Entity::factory()->create(['locality_id' => $locality->id]);
        $this->user->entities()->attach($this->entity->id, ['plan_id' => $plan->id, 'subscribed_at' => now()]);

        $this->otherEntity = Entity::factory()->create(['locality_id' => $locality->id]);
        $this->otherUser->entities()->attach($this->otherEntity->id, ['plan_id' => $plan->id, 'subscribed_at' => now()]);

        $this->room = Room::create([
            'entity_id' => $this->entity->id,
            'name' => 'Living',
            'square_meters' => 20,
        ]);

        $this->otherRoom = Room::create([
            'entity_id' => $this->otherEntity->id,
            'name' => 'Dormitorio Ajeno',
            'square_meters' => 15,
        ]);

        $this->category = EquipmentCategory::create([
            'name' => 'Climatización',
            'icon' => 'sun',
            'sort_order' => 1,
        ]);

        $this->type = EquipmentType::create([
            'name' => 'Aire Acondicionado',
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        $this->equipment = Equipment::create([
            'room_id' => $this->room->id,
            'category_id' => $this->category->id,
            'type_id' => $this->type->id,
            'name' => 'Aire Split',
            'nominal_power_w' => 1500,
            'is_standby' => false,
            'is_active' => true,
        ]);
    }

    #[Test]
    public function user_model_protects_is_super_admin_from_mass_assignment(): void
    {
        $createdUser = User::create([
            'name' => 'Hacker User',
            'email' => 'hacker@example.com',
            'password' => 'secret123',
            'is_super_admin' => true,
        ]);

        $this->assertFalse((bool) $createdUser->fresh()->is_super_admin);
    }

    #[Test]
    public function regular_user_cannot_access_administrative_panel(): void
    {
        $response = $this->actingAs($this->user)->get(route('sistema.admin'));
        $response->assertStatus(403);
    }

    #[Test]
    public function super_admin_can_access_administrative_panel(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('sistema.admin'));
        $response->assertStatus(200);
    }

    #[Test]
    public function regular_user_can_access_models_autocomplete(): void
    {
        $response = $this->actingAs($this->user)->get(route('sistema.models.autocomplete'));
        $response->assertStatus(200);
    }

    #[Test]
    public function user_cannot_toggle_standby_of_another_users_equipment(): void
    {
        $response = $this->actingAs($this->otherUser)
            ->post(route('recomendaciones.standby.toggle', $this->equipment));

        $response->assertStatus(403);
    }

    #[Test]
    public function user_can_toggle_standby_of_own_equipment(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('recomendaciones.standby.toggle', $this->equipment));

        $response->assertStatus(302);
    }

    #[Test]
    public function user_cannot_move_equipment_to_another_users_room(): void
    {
        $response = $this->actingAs($this->user)
            ->put(route('gestion.equipment.update', $this->equipment), [
                'room_id' => $this->otherRoom->id,
                'category_id' => $this->category->id,
                'type_id' => $this->type->id,
                'name' => 'Aire Trasladado',
                'nominal_power_w' => 1500,
                'is_active' => true,
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function login_enforces_rate_limiting_after_multiple_failed_attempts(): void
    {
        RateLimiter::clear('test_rate@example.com|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'test_rate@example.com',
                'password' => 'wrong_password',
            ]);
            $response->assertSessionHasErrors('email');
        }

        // El 6to intento debe fallar por throttle / demasiados intentos
        $response = $this->post('/login', [
            'email' => 'test_rate@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertSessionHasErrors('email');
        $errorMessage = session('errors')->first('email');
        $this->assertMatchesRegularExpression('/(Too many login attempts|Demasiados intentos)/i', $errorMessage);
    }
}
