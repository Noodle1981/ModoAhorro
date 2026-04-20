<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Entity;
use App\Models\Locality;
use App\Models\Province;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntityProfileTest extends TestCase
{
    use RefreshDatabase;

    protected $locality;
    protected $plan;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear una provincia y localidad para las pruebas
        $province = Province::create(['name' => 'San Juan']);
        
        $this->locality = new Locality();
        $this->locality->forceFill([
            'id' => 1,
            'province_id' => $province->id,
            'name' => 'Capital',
            'latitude' => -31.5375,
            'longitude' => -68.5364
        ]);
        $this->locality->save();

        // Crear un plan para el pivote entity_user
        $this->plan = Plan::create([
            'name' => 'Premium',
            'max_entities' => 5,
            'allowed_entity_types' => ['hogar'],
            'price' => 0
        ]);
    }

    protected function createEntityForUser($user)
    {
        $entity = Entity::factory()->create(['locality_id' => $this->locality->id]);
        $user->entities()->attach($entity->id, [
            'plan_id' => $this->plan->id,
            'subscribed_at' => now(),
        ]);
        return $entity;
    }

    public function test_user_can_view_the_entity_profile_edit_page()
    {
        $user = User::factory()->create();
        $entity = $this->createEntityForUser($user);

        $response = $this->actingAs($user)
            ->withSession(['active_entity_id' => $entity->id])
            ->get(route('gestion.entity.edit'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Entity/Edit')
            ->has('entity')
            ->has('provinces')
            ->has('localities')
        );
    }

    public function test_user_can_update_the_entity_profile_and_basic_fields()
    {
        $user = User::factory()->create();
        $entity = $this->createEntityForUser($user);

        $newData = [
            'name' => 'Casa Renovada',
            'usage_type' => 'residencial',
            'address_street' => 'Nueva Calle 456',
            'address_postal_code' => '5401',
            'locality_id' => $this->locality->id,
            'square_meters' => 250.5,
            'people_count' => 5,
            'construction_year' => 2010,
            'has_gas' => true,
            'has_solar' => false,
            'description' => 'Test description'
        ];

        $response = $this->actingAs($user)
            ->withSession(['active_entity_id' => $entity->id])
            ->put(route('gestion.entity.update'), $newData);

        $response->assertRedirect(route('home'));
        
        $this->assertDatabaseHas('entities', [
            'id' => $entity->id,
            'name' => 'Casa Renovada',
            'square_meters' => 250.5,
            'people_count' => 5,
            'has_gas' => 1
        ]);
    }

    public function test_switching_to_business_activity_injects_specific_rooms()
    {
        $user = User::factory()->create();
        $entity = $this->createEntityForUser($user);
        
        // Resetear actividad comercial
        $entity->update(['has_business_activity' => false]);

        // Activar actividad comercial tipo 'taller'
        $newData = [
            'name' => $entity->name,
            'usage_type' => 'residencial', 
            'address_street' => $entity->address_street,
            'locality_id' => $this->locality->id,
            'has_business_activity' => true,
            'business_type' => 'taller',
            'square_meters' => 100,
            'people_count' => 4
        ];

        $this->actingAs($user)
            ->withSession(['active_entity_id' => $entity->id])
            ->put(route('gestion.entity.update'), $newData);

        $this->assertTrue($entity->fresh()->has_business_activity);
        $this->assertEquals('taller', $entity->fresh()->business_type);

        $this->assertDatabaseHas('rooms', [
            'entity_id' => $entity->id,
            'name' => 'Taller / Producción'
        ]);
    }

    public function test_multiple_updates_with_business_active_do_not_duplicate_rooms()
    {
        $user = User::factory()->create();
        $entity = $this->createEntityForUser($user);

        $data = [
            'name' => $entity->name,
            'usage_type' => 'residencial',
            'locality_id' => $this->locality->id,
            'has_business_activity' => true,
            'business_type' => 'almacen',
            'square_meters' => 100,
            'people_count' => 4
        ];

        // Primera vez (Inyecta)
        $this->actingAs($user)->withSession(['active_entity_id' => $entity->id])->put(route('gestion.entity.update'), $data);
        $this->assertEquals(1, $entity->rooms()->where('name', 'like', '%Almacén%')->count());

        // Segunda vez (No debe duplicar)
        $this->actingAs($user)->withSession(['active_entity_id' => $entity->id])->put(route('gestion.entity.update'), $data);
        $this->assertEquals(1, $entity->rooms()->where('name', 'like', '%Almacén%')->count());
    }

    public function test_thermal_index_redirects_correctly()
    {
        $user = User::factory()->create();
        $entity = $this->createEntityForUser($user);

        // Sin diagnóstico
        $response = $this->actingAs($user)
            ->withSession(['active_entity_id' => $entity->id])
            ->get(route('gestion.thermal.index', ['entity' => $entity->id]));
        
        $response->assertRedirect(route('gestion.thermal.wizard', ['entity' => $entity->id]));

        // Con diagnóstico (Mockeamos el perfil térmico)
        $entity->update(['thermal_profile' => ['status' => 'complete']]);
        
        $response = $this->actingAs($user)
            ->withSession(['active_entity_id' => $entity->id])
            ->get(route('gestion.thermal.index', ['entity' => $entity->id]));
        
        $response->assertRedirect(route('gestion.thermal.result', ['entity' => $entity->id]));
    }
}
