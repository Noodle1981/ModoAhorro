<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        try {
            $this->call([
                // 1. Datos base
                ProvinceSeeder::class,
                LocalitySeeder::class,
                PlanSeeder::class,
                UtilityCompanySeeder::class,
                
                // 2. Maestría de Categorías y Tipos (NUEVO)
                CategoryRefinementSeeder::class,

                // 3. Escenarios de prueba (Hogar Casa 27)
                Casa27Seeder::class,
            ]);
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
