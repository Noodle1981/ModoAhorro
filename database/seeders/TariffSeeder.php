<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TariffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Tariff Scheme
        $scheme = \App\Models\TariffScheme::firstOrCreate(
            ['name' => 'Residencial Doble Horario'],
            ['provider' => 'Edenor']
        );

        // 2. Create Bands
        // Pico: 18:00 - 23:00 ($180)
        \App\Models\TariffBand::updateOrCreate(
            ['tariff_scheme_id' => $scheme->id, 'name' => 'Pico'],
            [
                'start_time' => '18:00:00',
                'end_time' => '23:00:00',
                'price_per_kwh' => 180.00,
                'is_weekend_applicable' => true
            ]
        );

        // Valle: 23:00 - 08:00 ($90)
        \App\Models\TariffBand::updateOrCreate(
            ['tariff_scheme_id' => $scheme->id, 'name' => 'Valle'],
            [
                'start_time' => '23:00:00',
                'end_time' => '08:00:00',
                'price_per_kwh' => 90.00,
                'is_weekend_applicable' => true
            ]
        );

        // Resto: 08:00 - 18:00 ($100)
        \App\Models\TariffBand::updateOrCreate(
            ['tariff_scheme_id' => $scheme->id, 'name' => 'Resto'],
            [
                'start_time' => '08:00:00',
                'end_time' => '18:00:00',
                'price_per_kwh' => 100.00,
                'is_weekend_applicable' => true
            ]
        );

        // 3. Mark Shiftable Equipment
        $shiftables = [
            'Lavarropas',
            'Secarropas',
            'Lavavajillas',
            'Termotanque Eléctrico',
            'Bomba de Agua',
            'Bomba Piscina'
        ];

        foreach ($shiftables as $name) {
            \App\Models\EquipmentType::where('name', 'LIKE', "%$name%")
                ->update(['is_shiftable' => true]);
        }
    }
}
