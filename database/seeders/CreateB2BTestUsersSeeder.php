<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Entity;
use App\Models\Plan;
use Illuminate\Support\Facades\Hash;

class CreateB2BTestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Creando usuarios test B2B (Solo cuentas)...');

        $password = Hash::make('password');

        // --- Usuario OFICINA ---
        User::updateOrCreate(
            ['email' => 'test_oficina@modoahorro.com'],
            ['name' => 'Tester Oficina', 'password' => $password]
        );

        // --- Usuario COMERCIO ---
        User::updateOrCreate(
            ['email' => 'test_comercio@modoahorro.com'],
            ['name' => 'Tester Comercio', 'password' => $password]
        );

        $this->command->info('✅ Usuarios creados:');
        $this->command->info('   - test_oficina@modoahorro.com (password)');
        $this->command->info('   - test_comercio@modoahorro.com (password)');
    }
}
