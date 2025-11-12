<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EquipmentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clima = \App\Models\EquipmentCategory::where('name', 'Climatización')->first();
        \App\Models\EquipmentType::create([
            'category_id' => $clima->id,
            'name' => 'Atma Aire Acondicionado',
            'default_power_watts' => 1420,
            'default_avg_daily_use_hours' => 8,
        ]);
        \App\Models\EquipmentType::create([
            'category_id' => $clima->id,
            'name' => 'Aire Acondicionado Split 3000 F',
            'default_power_watts' => 2300,
            'default_avg_daily_use_hours' => 8,
        ]);
        \App\Models\EquipmentType::create([
            'category_id' => $clima->id,
            'name' => 'Ventilador de Pie',
            'default_power_watts' => 60,
            'default_avg_daily_use_hours' => 6,
        ]);
        $elec = \App\Models\EquipmentCategory::where('name', 'Electrodoméstico')->first();
        \App\Models\EquipmentType::create([
            'category_id' => $elec->id,
            'name' => 'Heladera',
            'default_power_watts' => 150,
            'default_avg_daily_use_hours' => 24,
        ]);
        \App\Models\EquipmentType::create([
            'category_id' => $elec->id,
            'name' => 'Microondas',
            'default_power_watts' => 1200,
            'default_avg_daily_use_hours' => 0.5,
        ]);
    }
}
