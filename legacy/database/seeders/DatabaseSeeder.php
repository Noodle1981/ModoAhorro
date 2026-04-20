<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
                // 1. Datos base
            ProvinceSeeder::class,
            LocalitySeeder::class,
            PlanSeeder::class,
            UtilityCompanySeeder::class,
            EquipmentCategorySeeder::class,
            EquipmentTypeSeeder::class,

            // 2. Datos de entidades (hogar, oficina, comercio)
            BackupCasa27Seeder::class,
            DatosOficinaSeeder::class,
            DatosComercioSeeder::class,

                // 3. Usuario de prueba (AL FINAL para asignar entidades)
            UserSeeder::class,
        ]);
    }
}
