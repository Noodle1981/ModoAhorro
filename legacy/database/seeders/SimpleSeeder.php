<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Entity;
use App\Models\Plan;
use App\Models\Province;
use App\Models\Locality;

class SimpleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Basic Province & Locality
        $province = \App\Models\Province::where('name', 'San Juan')->first() 
                    ?: \App\Models\Province::firstOrCreate(['name' => 'San Juan']);
        $locality = Locality::firstOrCreate([
            'name' => 'La Rioja Capital',
            'province_id' => $province->id,
            'postal_code' => '5300'
        ]);

        // 2. Basic Plan
        $plan = Plan::firstOrCreate(['name' => 'Gratuito'], [
            'price' => 0,
            'max_entities' => 1,
            'allowed_entity_types' => ['hogar'],
            'features' => 'Plan básico'
        ]);

        // 3. User
        $user = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Usuario Admin',
                'password' => bcrypt('password'),
                'is_super_admin' => true,
            ]
        );

        // 4. Entity
        $entity = Entity::create([
            'name' => 'Mi Hogar',
            'type' => 'hogar',
            'locality_id' => $locality->id,
            'square_meters' => 100,
            'people_count' => 3
        ]);

        // 5. Attach
        $user->entities()->attach($entity->id, [
            'plan_id' => $plan->id,
            'subscribed_at' => now()
        ]);

        $this->command->info('✅ Base de datos lista con acceso mínimo.');
        $this->command->info('📧 Email: admin@admin.com');
        $this->command->info('🔑 Password: password');
    }
}
