<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EquipmentType;
use App\Models\MaintenanceTask;

class MaintenanceSeeder extends Seeder
{
    public function run()
    {
        // 1. Aires Acondicionados
        $acTypes = EquipmentType::where('name', 'like', '%Aire Acondicionado%')->get();
        foreach ($acTypes as $type) {
            // Tarea común: Filtros
            MaintenanceTask::firstOrCreate(
                [
                    'equipment_type_id' => $type->id,
                    'title' => 'Limpieza de Filtros'
                ],
                [
                    'frequency_days' => 30, // Mensual
                    'season_month' => null,
                    'efficiency_impact' => 0.15
                ]
            );
            
            // Diferenciación: Portátil vs Split
            if (str_contains(strtolower($type->name), 'portátil') || str_contains(strtolower($type->name), 'portatil')) {
                // Aire Portátil: Drenaje
                MaintenanceTask::firstOrCreate(
                    [
                        'equipment_type_id' => $type->id,
                        'title' => 'Drenaje de Agua'
                    ],
                    [
                        'frequency_days' => 30, // Mensual (o según uso)
                        'season_month' => null,
                        'efficiency_impact' => 0.05 // Menor impacto, pero necesario
                    ]
                );
            } else {
                // Split/Ventana: Limpieza Profunda (Técnico)
                MaintenanceTask::firstOrCreate(
                    [
                        'equipment_type_id' => $type->id,
                        'title' => 'Limpieza Profunda (Unidad Exterior/Interior)'
                    ],
                    [
                        'frequency_days' => 365, // Anual
                        'season_month' => 11, // Noviembre
                        'efficiency_impact' => 0.20
                    ]
                );
            }
        }

        // 2. Termotanques (Revisión de Ánodo)
        $waterHeaterTypes = EquipmentType::where('name', 'like', '%Termotanque%')->get();
        foreach ($waterHeaterTypes as $type) {
            MaintenanceTask::firstOrCreate(
                [
                    'equipment_type_id' => $type->id,
                    'title' => 'Revisión de Ánodo de Sacrificio'
                ],
                [
                    'frequency_days' => 365,
                    'season_month' => null,
                    'efficiency_impact' => 0.10
                ]
            );
        }

        // 3. Heladeras (Limpieza de Condensador/Rejilla trasera)
        $fridgeTypes = EquipmentType::where('name', 'like', '%Heladera%')->orWhere('name', 'like', '%Freezer%')->get();
        foreach ($fridgeTypes as $type) {
            MaintenanceTask::firstOrCreate(
                [
                    'equipment_type_id' => $type->id,
                    'title' => 'Limpieza de Condensador (Rejilla trasera)'
                ],
                [
                    'frequency_days' => 180, // Semestral
                    'season_month' => null,
                    'efficiency_impact' => 0.15
                ]
            );
        }
    }
}
