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
        ProvinceSeeder::class,
        LocalitySeeder::class,
        PlanSeeder::class,
        UserSeeder::class,
        // Agrega aquí otros seeders que necesites
    ]);
    }
}
