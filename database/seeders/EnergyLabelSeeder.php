<?php

namespace Database\Seeders;

use App\Models\EnergyLabelCoefficient;
use App\Models\EquipmentCategory;
use Illuminate\Database\Seeder;

class EnergyLabelSeeder extends Seeder
{
    public function run(): void
    {
        $catClima = EquipmentCategory::where('name', 'Climatización')->first();
        $catLineaBlanca = EquipmentCategory::where('name', 'Línea Blanca')->first();
        $catCocina = EquipmentCategory::where('name', 'Cocina / Pequeños Electrodomésticos')->first();
        $catServicios = EquipmentCategory::where('name', 'Servicios / Bombas')->first();

        $matrix = [
            'Climatización' => [
                'A+++' => 0.60, 'A++' => 0.75, 'A+' => 0.85, 'A' => 1.00, 'B' => 1.18, 'C' => 1.40, 'D' => 1.80, 'E' => 2.00, 'F' => 2.20, 'G' => 2.50
            ],
            'Línea Blanca' => [
                'A+++' => 0.65, 'A++' => 0.75, 'A+' => 0.85, 'A' => 1.00, 'B' => 1.15, 'C' => 1.35, 'D' => 1.60, 'E' => 1.80, 'F' => 2.00, 'G' => 2.30
            ],
            'Cocina / Pequeños Electrodomésticos' => [
                'A+++' => 0.80, 'A' => 1.00, 'B' => 1.12, 'C' => 1.30, 'D' => 1.55
            ],
            'Servicios / Bombas' => [
                'A+++' => 0.40, 'A' => 1.00, 'B' => 1.20, 'C' => 1.50, 'D' => 2.00
            ]
        ];

        foreach ($matrix as $catName => $labels) {
            $cat = EquipmentCategory::where('name', $catName)->first();
            if ($cat) {
                foreach ($labels as $label => $coeff) {
                    EnergyLabelCoefficient::updateOrCreate(
                        ['category_id' => $cat->id, 'label' => $label, 'equipment_type_id' => null],
                        ['coefficient' => $coeff]
                    );
                }
            }
        }

        // --- EXCEPCIÓN ESPECÍFICA: AIRE DE VENTANA ---
        $typeVentana = \App\Models\EquipmentType::where('name', 'Aire de Ventana')->first();
        if ($typeVentana && $catClima) {
            $ventanaMatrix = ['A+++' => 0.75, 'A++' => 0.85, 'A+' => 0.95, 'A' => 1.00, 'B' => 1.15, 'C' => 1.30, 'D' => 1.65];
            foreach ($ventanaMatrix as $label => $coeff) {
                EnergyLabelCoefficient::updateOrCreate(
                    ['category_id' => $catClima->id, 'label' => $label, 'equipment_type_id' => $typeVentana->id],
                    ['coefficient' => $coeff]
                );
            }
        }
    }
}
