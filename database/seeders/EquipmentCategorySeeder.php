<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EquipmentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    \App\Models\EquipmentCategory::create(['name' => 'Climatización']);
    \App\Models\EquipmentCategory::create(['name' => 'Electrodoméstico']);
    \App\Models\EquipmentCategory::create(['name' => 'Iluminación']);
    }
}
