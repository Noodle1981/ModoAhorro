<?php

namespace Tests\Feature\Gestión_Física;

use App\Models\User;
use App\Models\Entity;
use App\Models\Locality;
use App\Models\Province;
use App\Models\Plan;
use App\Models\Room;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InfrastructureTest extends TestCase
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
        $this->entity = Entity::factory()->create(['locality_id' => $this->locality->id]);
        $this->user->entities()->attach($this->entity->id, [
            'plan_id' => $this->plan->id,
            'subscribed_at' => now(),
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_access_infrastructure_index()
    {
        $response = $this->actingAs($this->user)
            ->withSession(['active_entity_id' => $this->entity->id])
            ->get(route('gestion.infrastructure'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Entities/Infrastructure/Index')
            ->has('rooms')
            ->has('categories')
            ->has('types')
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_manage_rooms()
    {
        // 1. Create Room
        $response = $this->actingAs($this->user)
            ->post(route('gestion.rooms.store'), [
                'entity_id' => $this->entity->id,
                'name' => 'Living Comedor',
                'description' => 'Ambiente principal'
            ]);

        $response->assertRedirect();
        $room = Room::first();
        $this->assertEquals('Living Comedor', $room->name);

        // 2. Update Room
        $this->actingAs($this->user)
            ->put(route('gestion.rooms.update', $room->id), [
                'name' => 'Living Master',
                'description' => 'Actualizado'
            ]);

        $this->assertEquals('Living Master', $room->fresh()->name);

        // 3. Delete Room
        $this->actingAs($this->user)
            ->delete(route('gestion.rooms.destroy', $room->id));

        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_manage_equipment_and_batch_create()
    {
        $room = Room::factory()->create(['entity_id' => $this->entity->id]);
        $category = EquipmentCategory::factory()->create();
        $type = EquipmentType::factory()->create(['category_id' => $category->id]);

        // 1. Batch creation of 3 lamps
        $equipmentData = [
            'room_id' => $room->id,
            'category_id' => $category->id,
            'type_id' => $type->id,
            'name' => 'Lámpara Led',
            'nominal_power_w' => 12,
            'avg_daily_use_hours' => 5,
            'is_standby' => false,
            'cantidad' => 3
        ];

        $response = $this->actingAs($this->user)
            ->post(route('gestion.equipment.store'), $equipmentData);

        $response->assertRedirect();
        $this->assertCount(3, Equipment::all());
        $this->assertDatabaseHas('equipment', ['name' => 'Lámpara Led 1']);
        $this->assertDatabaseHas('equipment', ['name' => 'Lámpara Led 3']);

        $equipment = Equipment::first();

        // 2. Update equipment
        $this->actingAs($this->user)
            ->put(route('gestion.equipment.update', $equipment->id), [
                'room_id' => $room->id,
                'category_id' => $category->id,
                'type_id' => $type->id,
                'name' => 'Lámpara Led Pro',
                'nominal_power_w' => 15,
                'avg_daily_use_hours' => 6,
                'is_standby' => true,
                'is_active' => false
            ]);

        $this->assertEquals('Lámpara Led Pro', $equipment->fresh()->name);
        $this->assertFalse($equipment->fresh()->is_active);

        // 3. Delete equipment
        $this->actingAs($this->user)
            ->delete(route('gestion.equipment.destroy', $equipment->id));

        $this->assertDatabaseMissing('equipment', ['id' => $equipment->id]);
    }
}
