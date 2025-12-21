<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario existente de prueba básico
        \App\Models\User::updateOrCreate(
            ['email' => 'demo@modoahorro.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('12345'),
            ]
        );

        // Nuevo usuario de prueba con plan Enterprise y acceso a las 3 entidades
        $testUser = \App\Models\User::updateOrCreate(
            ['email' => 'test@modoahorro.com'],
            [
                'name' => 'Usuario de Prueba Enterprise',
                'password' => bcrypt('password'),
            ]
        );

        // Obtener plan Enterprise
        $enterprisePlan = \App\Models\Plan::where('name', 'Enterprise')->first();

        if ($enterprisePlan) {
            // Obtener las 3 entidades (hogar, oficina, comercio)
            $hogar = \App\Models\Entity::where('type', 'hogar')->first();
            $oficina = \App\Models\Entity::where('type', 'oficina')->first();
            $comercio = \App\Models\Entity::where('type', 'comercio')->first();

            // Asociar usuario con las 3 entidades usando plan Enterprise
            if ($hogar && !$testUser->entities()->where('entity_id', $hogar->id)->exists()) {
                $testUser->entities()->attach($hogar->id, [
                    'plan_id' => $enterprisePlan->id,
                    'subscribed_at' => now(),
                    'expires_at' => null, // Sin expiración
                ]);
            }

            if ($oficina && !$testUser->entities()->where('entity_id', $oficina->id)->exists()) {
                $testUser->entities()->attach($oficina->id, [
                    'plan_id' => $enterprisePlan->id,
                    'subscribed_at' => now(),
                    'expires_at' => null,
                ]);
            }

            if ($comercio && !$testUser->entities()->where('entity_id', $comercio->id)->exists()) {
                $testUser->entities()->attach($comercio->id, [
                    'plan_id' => $enterprisePlan->id,
                    'subscribed_at' => now(),
                    'expires_at' => null,
                ]);
            }

            $this->command->info('✅ Usuario de prueba creado: test@modoahorro.com (password)');
            $this->command->info('   Plan: Enterprise');
            $this->command->info('   Acceso a: ' . $testUser->entities()->count() . ' entidades');
        }
    }
}
