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
        \App\Models\Plan::updateOrCreate([
            'name' => 'Gratuito'
        ], [
            'features' => 'Acceso a 1 entidad hogar',
            'price' => 0,
            'max_entities' => 1,
        ]);
    }
}
