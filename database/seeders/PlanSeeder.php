<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Plan Gratuito - Solo hogar
        Plan::updateOrCreate([
            'name' => 'Gratuito',
        ], [
            'features' => 'Acceso a 1 entidad hogar',
            'price' => 0,
            'max_entities' => 1,
            'allowed_entity_types' => ['hogar'],
        ]);

        // Plan Premium - Hogar, Oficina, Comercio, Pabellón (hasta 5)
        Plan::updateOrCreate([
            'name' => 'Premium',
        ], [
            'features' => 'Hasta 5 entidades (hogar, oficina, comercio, pabellón)',
            'price' => 15.00,
            'max_entities' => 5,
            'allowed_entity_types' => ['hogar', 'oficina', 'comercio', 'pabellon'],
        ]);

        // Plan Enterprise - Ilimitado
        Plan::updateOrCreate([
            'name' => 'Enterprise',
        ], [
            'features' => 'Entidades ilimitadas, soporte prioritario',
            'price' => 50.00,
            'max_entities' => 999,
            'allowed_entity_types' => ['hogar', 'oficina', 'comercio', 'pabellon'],
        ]);
    }
}
