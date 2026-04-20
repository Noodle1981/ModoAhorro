<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Entity;
use App\Models\Plan;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear el usuario de prueba
        $user = User::updateOrCreate([
            'email' => 'user@modoahorro.com'
        ], [
            'name' => 'Usuario de Prueba',
            'password' => Hash::make('password'),
            'is_super_admin' => false,
        ]);

        // 2. Obtener el Plan Gratuito
        $plan = Plan::where('name', 'Gratuito')->first();

        if (!$plan) {
            $this->command->error('Plan Gratuito no encontrado. Asegúrate de correr PlanSeeder primero.');
            return;
        }

        // 3. Crear una Entidad de prueba (Casa)
        $entity = Entity::updateOrCreate([
            'name' => 'Mi Casa en Córdoba'
        ], [
            'type' => 'hogar',
            'description' => 'Vivienda principal para pruebas térmicas',
            'thermal_profile' => null, // Empezar de cero
        ]);

        // 4. Vincular Usuario + Entidad + Plan en la tabla pivote
        $user->entities()->syncWithPivotValues([$entity->id], [
            'plan_id' => $plan->id,
            'subscribed_at' => now(),
        ]);

        $this->command->info('Usuario [user@modoahorro.com] creado con éxito (Psw: password)');
    }
}
