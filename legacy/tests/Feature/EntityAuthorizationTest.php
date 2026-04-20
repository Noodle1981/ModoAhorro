<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{User, Entity, Plan};
use Illuminate\Foundation\Testing\RefreshDatabase;

class EntityAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ejecutar seeders necesarios
        $this->seed(\Database\Seeders\PlanSeeder::class);
        $this->seed(\Database\Seeders\EquipmentCategorySeeder::class);
    }

    /**
     * Test que usuario gratuito solo puede crear 1 hogar
     */
    public function test_free_user_can_create_one_home_only()
    {
        $user = User::factory()->create();
        $freePlan = Plan::where('name', 'Gratuito')->first();

        // Crear primera entidad hogar manualmente
        $firstEntity = Entity::create([
            'name' => 'Mi Casa',
            'type' => 'hogar',
            'locality_id' => 1,
            'square_meters' => 100,
        ]);

        // Asociar con usuario usando plan gratuito
        $user->entities()->attach($firstEntity->id, [
            'plan_id' => $freePlan->id,
            'subscribed_at' => now(),
        ]);

        $this->assertEquals(1, $user->entities()->count());

        // Verificar que NO puede crear segunda entidad (límite alcanzado)
        $canCreateSecond = $user->can('create', [Entity::class, 'hogar']);
        $this->assertFalse($canCreateSecond, 'Usuario gratuito NO debe poder crear segunda entidad');
    }

    /**
     * Test que usuario gratuito NO puede crear oficina
     */
    public function test_free_user_cannot_create_office()
    {
        $user = User::factory()->create();

        // Usuario nuevo tiene plan gratuito por defecto
        $this->assertEquals('Gratuito', $user->currentPlan()->name);

        // Verificar que NO puede crear oficina (tipo no permitido)
        $canCreateOffice = $user->can('create', [Entity::class, 'oficina']);
        $this->assertFalse($canCreateOffice, 'Usuario gratuito NO debe poder crear oficina');

        // Verificar que NO puede ver entidades de tipo oficina
        $canViewOffices = $user->can('viewAny', [Entity::class, 'oficina']);
        $this->assertFalse($canViewOffices, 'Usuario gratuito NO debe poder ver oficinas');
    }

    /**
     * Test que usuario premium puede crear hasta 3 entidades
     */
    public function test_premium_user_can_create_three_entities()
    {
        $user = User::factory()->create();
        $premiumPlan = Plan::where('name', 'Premium')->first();

        // Crear primera entidad y asociar con plan premium
        $entity1 = Entity::create([
            'name' => 'Mi Casa',
            'type' => 'hogar',
            'locality_id' => 1,
            'square_meters' => 100,
        ]);
        $user->entities()->attach($entity1->id, [
            'plan_id' => $premiumPlan->id,
            'subscribed_at' => now(),
        ]);

        // Verificar que puede crear oficina (tipo permitido)
        $canCreateOffice = $user->can('create', [Entity::class, 'oficina']);
        $this->assertTrue($canCreateOffice, 'Usuario premium DEBE poder crear oficina');

        // Crear segunda entidad (oficina)
        $entity2 = Entity::create([
            'name' => 'Mi Oficina',
            'type' => 'oficina',
            'locality_id' => 1,
            'square_meters' => 80,
        ]);
        $user->entities()->attach($entity2->id, [
            'plan_id' => $premiumPlan->id,
            'subscribed_at' => now(),
        ]);

        // Crear tercera entidad (comercio)
        $entity3 = Entity::create([
            'name' => 'Mi Comercio',
            'type' => 'comercio',
            'locality_id' => 1,
            'square_meters' => 120,
        ]);
        $user->entities()->attach($entity3->id, [
            'plan_id' => $premiumPlan->id,
            'subscribed_at' => now(),
        ]);

        $this->assertEquals(3, $user->entities()->count());

        // Verificar que NO puede crear cuarta entidad (límite alcanzado)
        $canCreateFourth = $user->can('create', [Entity::class, 'hogar']);
        $this->assertFalse($canCreateFourth, 'Usuario premium NO debe poder crear cuarta entidad (límite: 3)');
    }

    /**
     * Test que usuario de prueba (Enterprise) tiene acceso total
     */
    public function test_test_user_has_full_access()
    {
        // Ejecutar seeders necesarios (incluyendo dependencias)
        $this->seed(\Database\Seeders\ProvinceSeeder::class);
        $this->seed(\Database\Seeders\LocalitySeeder::class);
        $this->seed(\Database\Seeders\EquipmentTypeSeeder::class);
        $this->seed(\Database\Seeders\UserSeeder::class);
        $this->seed(\Database\Seeders\DatosHogarSeeder::class);
        $this->seed(\Database\Seeders\DatosOficinaSeeder::class);
        $this->seed(\Database\Seeders\DatosComercioSeeder::class);

        $testUser = User::where('email', 'test@modoahorro.com')->first();

        $this->assertNotNull($testUser, 'Usuario de prueba debe existir');
        $this->assertEquals('Enterprise', $testUser->currentPlan()->name);

        // Verificar que tiene acceso a las 3 entidades
        $this->assertEquals(3, $testUser->entities()->count());

        // Verificar acceso a cada tipo
        $homeEntity = Entity::where('type', 'hogar')->first();
        $officeEntity = Entity::where('type', 'oficina')->first();
        $commerceEntity = Entity::where('type', 'comercio')->first();

        $this->assertTrue($testUser->can('view', $homeEntity), 'Debe poder ver hogar');
        $this->assertTrue($testUser->can('view', $officeEntity), 'Debe poder ver oficina');
        $this->assertTrue($testUser->can('view', $commerceEntity), 'Debe poder ver comercio');

        // Verificar tipos permitidos
        $this->assertTrue($testUser->can('viewAny', [Entity::class, 'hogar']));
        $this->assertTrue($testUser->can('viewAny', [Entity::class, 'oficina']));
        $this->assertTrue($testUser->can('viewAny', [Entity::class, 'comercio']));
    }
}
