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
        $categories = [
            'Climatización',
            'Iluminación',
            'Electrodomésticos',
            'Entretenimiento',
            'Cocina',
            'Oficina',
            'Portátiles',
            'Otros',
        ];
        foreach ($categories as $cat) {
            \App\Models\EquipmentCategory::firstOrCreate(['name' => $cat]);
        }
    }
}
