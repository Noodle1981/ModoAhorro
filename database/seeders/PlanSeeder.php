<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Plan Gratuito - Solo hogar
        \App\Models\Plan::updateOrCreate([
            'name' => 'Gratuito'
        ], [
            'features' => 'Acceso a 1 entidad hogar',
            'price' => 0,
            'max_entities' => 1,
            'allowed_entity_types' => ['hogar'],
        ]);

        // Plan Premium - Hogar, Oficina, Comercio (hasta 3)
        \App\Models\Plan::updateOrCreate([
            'name' => 'Premium'
        ], [
            'features' => 'Hasta 3 entidades (hogar, oficina, comercio)',
            'price' => 15.00,
            'max_entities' => 3,
            'allowed_entity_types' => ['hogar', 'oficina', 'comercio'],
        ]);

        // Plan Enterprise - Ilimitado
        \App\Models\Plan::updateOrCreate([
            'name' => 'Enterprise'
        ], [
            'features' => 'Entidades ilimitadas, soporte prioritario',
            'price' => 50.00,
            'max_entities' => 999,
            'allowed_entity_types' => ['hogar', 'oficina', 'comercio'],
        ]);
    }
}
