<?php

namespace Tests\Feature\Perfil_Entidad;

use App\Models\User;
use App\Models\Entity;
use App\Models\Locality;
use App\Models\Province;
use App\Models\Plan;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EntityProfileTest extends TestCase
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

        // Setup basic data
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
            'type' => 'hogar',
            'has_business_activity' => false
        ]);

        $this->user->entities()->attach($this->entity->id, [
            'plan_id' => $this->plan->id,
            'subscribed_at' => now(),
        ]);

        // Fake Http for ClimateService if needed
        Http::fake([
            'api.open-meteo.com/*' => Http::response(['current_weather' => ['temperature' => 25, 'windspeed' => 10, 'weathercode' => 0]], 200),
            'archive-api.open-meteo.com/*' => Http::response(['daily' => ['time' => []]], 200),
        ]);
    }

    /**
     * Data provider for entity types
     */
    public static function entityTypeProvider(): array
    {
        return [
            'Hogar' => ['hogar', 'residencial'],
            'Oficina' => ['oficina', 'oficina'],
            'Comercio' => ['comercio', 'comercial'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('entityTypeProvider')]
    public function can_access_entity_edit_page($type, $usage)
    {
        $entity = Entity::factory()->create([
            'locality_id' => $this->locality->id,
            'type' => $type,
            'usage_type' => $usage
        ]);
        $this->user->entities()->attach($entity->id, ['plan_id' => $this->plan->id, 'subscribed_at' => now()]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_entity_id' => $entity->id])
            ->get(route('gestion.entity.edit'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Entity/Edit')
            ->where('entity.type', $type)
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('entityTypeProvider')]
    public function can_update_entity_profile($type, $usage)
    {
        $entity = Entity::factory()->create([
            'locality_id' => $this->locality->id,
            'type' => $type,
            'usage_type' => $usage
        ]);
        $this->user->entities()->attach($entity->id, ['plan_id' => $this->plan->id, 'subscribed_at' => now()]);

        $updateData = [
            'name' => "Perfil Actualizado de $type",
            'usage_type' => $usage,
            'address_street' => 'Calle Secundaria 456',
            'square_meters' => 200,
            'people_count' => 2,
            'construction_year' => 2015,
            'has_gas' => false,
            'has_solar' => true,
            'has_business_activity' => false,
            'description' => 'Actualizando desde test',
        ];

        $response = $this->actingAs($this->user)
            ->withSession(['active_entity_id' => $entity->id])
            ->put(route('gestion.entity.update'), $updateData);

        $response->assertRedirect(route('home'));
        $this->assertDatabaseHas('entities', [
            'id' => $entity->id,
            'name' => "Perfil Actualizado de $type",
            'has_solar' => 1
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function entity_update_creates_business_room_when_enabled()
    {
        // Setup a Hogar entity without business
        $entity = Entity::factory()->create([
            'locality_id' => $this->locality->id,
            'type' => 'hogar',
            'has_business_activity' => false
        ]);
        $this->user->entities()->attach($entity->id, ['plan_id' => $this->plan->id, 'subscribed_at' => now()]);

        $updateData = [
            'name' => $entity->name,
            'usage_type' => 'residencial',
            'has_business_activity' => true, 
            'business_type' => 'venta',
            'square_meters' => 100,
        ];

        $this->actingAs($this->user)
            ->withSession(['active_entity_id' => $entity->id])
            ->put(route('gestion.entity.update'), $updateData);

        // Verify room created
        $this->assertDatabaseHas('rooms', [
            'entity_id' => $entity->id,
            'name' => 'Local / Venta',
            'description' => 'Ambiente autogenerado para soporte de actividad económica en el hogar.'
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function entity_update_synchronizes_room_name_when_business_type_changes()
    {
        $entity = Entity::factory()->create([
            'locality_id' => $this->locality->id,
            'type' => 'hogar',
            'has_business_activity' => true,
            'business_type' => 'venta'
        ]);
        $this->user->entities()->attach($entity->id, ['plan_id' => $this->plan->id, 'subscribed_at' => now()]);

        // Create the initial room
        $room = Room::create([
            'entity_id' => $entity->id,
            'name' => 'Local / Venta',
            'description' => 'Ambiente autogenerado para soporte de actividad económica en el hogar.'
        ]);

        $updateData = [
            'name' => $entity->name,
            'usage_type' => 'residencial',
            'has_business_activity' => true, 
            'business_type' => 'taller', // Changing to taller
            'square_meters' => 100,
        ];

        $this->actingAs($this->user)
            ->withSession(['active_entity_id' => $entity->id])
            ->put(route('gestion.entity.update'), $updateData);

        // Verify room name updated
        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'name' => 'Taller'
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function entity_update_deletes_business_room_when_activity_is_disabled()
    {
        $entity = Entity::factory()->create([
            'locality_id' => $this->locality->id,
            'type' => 'hogar',
            'has_business_activity' => true,
            'business_type' => 'venta'
        ]);
        $this->user->entities()->attach($entity->id, ['plan_id' => $this->plan->id, 'subscribed_at' => now()]);

        // Create the initial room
        Room::create([
            'entity_id' => $entity->id,
            'name' => 'Local / Venta',
            'description' => 'Ambiente autogenerado para soporte de actividad económica en el hogar.'
        ]);

        $updateData = [
            'name' => $entity->name,
            'usage_type' => 'residencial',
            'has_business_activity' => false, // Disabling
            'square_meters' => 100,
        ];

        $this->actingAs($this->user)
            ->withSession(['active_entity_id' => $entity->id])
            ->put(route('gestion.entity.update'), $updateData);

        // Verify room is deleted
        $this->assertDatabaseMissing('rooms', [
            'entity_id' => $entity->id,
            'description' => 'Ambiente autogenerado para soporte de actividad económica en el hogar.'
        ]);
    }

    public function test_unauthorized_user_cannot_access_other_entity_profile()
    {
        $otherUser = User::factory()->create();
        $otherEntity = Entity::factory()->create(['locality_id' => $this->locality->id]);

        $response = $this->actingAs($this->user)
            ->withSession(['active_entity_id' => $otherEntity->id])
            ->get(route('gestion.entity.edit'));

        $response->assertStatus(403);
    }
}
