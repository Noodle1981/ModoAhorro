<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EfficiencyBenchmarkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $benchmarks = [
            [
                'type' => 'Aire Acondicionado',
                'gain' => 0.35, // 35% ahorro Inverter
                'price' => 850000,
                'term' => 'Aire Acondicionado Inverter A++',
            ],
            [
                'type' => 'Heladera',
                'gain' => 0.40, // 40% ahorro Inverter
                'price' => 950000,
                'term' => 'Heladera Inverter No Frost',
            ],
            [
                'type' => 'Lavarropas',
                'gain' => 0.30, // 30% ahorro Inverter
                'price' => 780000,
                'term' => 'Lavarropas Inverter A+++',
            ],
            [
                'type' => 'Lámpara', // O Iluminación
                'gain' => 0.85, // 85% ahorro LED
                'price' => 4500,
                'term' => 'Pack Lamparas LED E27',
            ],
            [
                'type' => 'Termotanque Eléctrico',
                'gain' => 0.75, // 75% ahorro Solar
                'price' => 650000,
                'term' => 'Termotanque Solar 200 Litros',
            ],
            [
                'type' => 'Calefón', // Gas
                'gain' => 0.80, // 80% ahorro Solar
                'price' => 650000,
                'term' => 'Termotanque Solar 200 Litros',
            ],
        ];

        foreach ($benchmarks as $data) {
            $type = \App\Models\EquipmentType::where('name', 'LIKE', '%' . $data['type'] . '%')->first();

            if ($type) {
                \App\Models\EfficiencyBenchmark::updateOrCreate(
                    ['equipment_type_id' => $type->id],
                    [
                        'efficiency_gain_factor' => $data['gain'],
                        'average_market_price' => $data['price'],
                        'meli_search_term' => $data['term'],
                    ]
                );
            }
        }
    }
}
