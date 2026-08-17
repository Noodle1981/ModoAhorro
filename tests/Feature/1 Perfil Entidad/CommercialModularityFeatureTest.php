<?php

namespace Tests\Feature\Perfil_Entidad;

use App\Domain\Commercial\Profiles\Gastronomy\IceCreamShopProfile;
use App\Models\Entity;
use App\Models\Locality;
use App\Models\Plan;
use App\Models\Province;
use App\Models\User;
use App\Services\EnergyEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommercialModularityFeatureTest extends TestCase
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
            'longitude' => -68.5364,
        ]);

        $this->plan = Plan::create([
            'name' => 'Comercial Pro',
            'max_entities' => 5,
            'allowed_entity_types' => ['comercio'],
            'price' => 0,
        ]);

        $this->user = User::factory()->create();
        $this->entity = Entity::factory()->create([
            'locality_id' => $this->locality->id,
            'type' => 'comercio',
            'usage_type' => 'comercial',
            'comercio_type' => 'gastronomia',
            'has_business_activity' => false,
        ]);

        $this->user->entities()->attach($this->entity->id, [
            'plan_id' => $this->plan->id,
        ]);
    }

    public function test_commercial_catalog_is_passed_to_inertia_edit_page(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['active_entity_id' => $this->entity->id])
            ->get(route('gestion.entity.edit'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Entity/Edit')
            ->has('commercialCatalog')
            ->where('commercialCatalog.0.key', 'gastronomia')
        );
    }

    public function test_user_can_update_commercial_entity_with_modular_category_and_subcategory(): void
    {
        $updateData = [
            'name' => 'Heladería Artesanal Vía Trento',
            'usage_type' => 'comercial',
            'address_street' => 'Av. Libertador 1234',
            'address_postal_code' => '5400',
            'locality_id' => $this->locality->id,
            'square_meters' => 120,
            'people_count' => 5,
            'construction_year' => 2015,
            'has_gas' => true,
            'has_solar' => false,
            'has_business_activity' => false,
            'comercio_type' => 'gastronomia',
            'business_category' => 'gastronomia',
            'business_subcategory' => 'heladeria_artesanal',
            'staff_count' => 4,
            'visitors_count' => 180,
            'service_turns' => 2,
            'opens_at' => '12:00',
            'closes_at' => '01:00',
        ];

        $response = $this->actingAs($this->user)
            ->withSession(['active_entity_id' => $this->entity->id])
            ->put(route('gestion.entity.update'), $updateData);

        $response->assertRedirect();

        $this->assertDatabaseHas('entities', [
            'id' => $this->entity->id,
            'name' => 'Heladería Artesanal Vía Trento',
            'business_category' => 'gastronomia',
            'business_subcategory' => 'heladeria_artesanal',
            'staff_count' => 4,
            'visitors_count' => 180,
            'service_turns' => 2,
        ]);
    }

    public function test_energy_engine_resolves_ice_cream_shop_profile(): void
    {
        $this->entity->update([
            'business_category' => 'gastronomia',
            'business_subcategory' => 'heladeria_artesanal',
        ]);

        $engine = app(EnergyEngineService::class);
        $registry = app(\App\Domain\Commercial\Registry\CommercialProfileRegistry::class);

        $resolvedProfile = $registry->resolveForEntity($this->entity);

        $this->assertInstanceOf(IceCreamShopProfile::class, $resolvedProfile);
        $this->assertEquals(1.35, $resolvedProfile->getStandbyMultiplier());
        $this->assertEquals(1.45, $resolvedProfile->getThermalSensitivity());
    }
}
