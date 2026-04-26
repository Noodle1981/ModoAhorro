<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\EquipmentType;
use App\Models\EquipmentCategory;
use App\Models\EnergyLabelCoefficient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MasterCleanCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Desactivar checks de FK para limpiar sin dolor
        Schema::disableForeignKeyConstraints();
        
        // Guardar equipos actuales para intentar re-mapearlos
        $oldEquipments = Equipment::all();
        
        // Limpiar tablas
        DB::table('energy_label_coefficients')->truncate();
        DB::table('equipment_benchmarks')->truncate();
        DB::table('equipment_types')->truncate();
        DB::table('equipment_categories')->truncate();

        // 2. Crear Categorías Maestras (Alineadas con Tanques de NotebookLM)
        $cats = [
            'T0' => EquipmentCategory::create(['name' => '0. Conectividad y Seguridad (T0)']),
            'T1' => EquipmentCategory::create(['name' => '1. Refrigeración y Servidores (T1)']),
            'T2' => EquipmentCategory::create(['name' => '2. Climatización y Agua (T2)']),
            'T3_L' => EquipmentCategory::create(['name' => '3. Línea Blanca y Limpieza (T3)']),
            'T3_I' => EquipmentCategory::create(['name' => '4. Informática y Ocio (T3)']),
            'T3_C' => EquipmentCategory::create(['name' => '5. Cocina y Pequeños (T3)']),
            'T3_O' => EquipmentCategory::create(['name' => '6. Iluminación y Otros (T3)']),
        ];

        // 3. Definir Tipos Definativos
        // Formato: [Nombre, MinW, MaxW, DefW, Logic, Unit, DetScore, Penalty, SocialCoeff, CatKey]
        $typesData = [
            // TANQUE 0 (Certeza / Determinísticos)
            ['Router Wifi / ONT', 10, 40, 15, 'BASE_LOAD', 'hours', 1.0, 0, 0, 'T0'],
            ['Cámaras de Seguridad', 5, 20, 10, 'BASE_LOAD', 'hours', 1.0, 0, 0, 'T0'],
            ['Alarma Monitoreada', 5, 15, 8, 'BASE_LOAD', 'hours', 1.0, 0, 0, 'T0'],
            ['Central Telefónica', 20, 100, 40, 'BASE_LOAD', 'hours', 0.95, 0, 0, 'T0'],

            // TANQUE 1 (Base / Pulso Vital)
            ['Heladera con Freezer', 80, 450, 150, 'BASE_LOAD', 'hours', 0.7, 0.1, 0, 'T1'],
            ['Freezer de Pozo', 100, 500, 200, 'BASE_LOAD', 'hours', 0.8, 0.05, 0, 'T1'],
            ['Cava de Vinos', 50, 150, 80, 'BASE_LOAD', 'hours', 0.6, 0.1, 0, 'T1'],
            ['Servidor / Rack IT', 150, 2000, 400, 'BASE_LOAD', 'hours', 0.98, 0, 0, 'T1'],

            // TANQUE 2 (Clima / Agua Caliente)
            ['Aire Split', 300, 3500, 1500, 'CLIMATE_DEPENDENT', 'hours', 0.4, 0, 0, 'T2'],
            ['Aire de Ventana', 800, 2500, 1500, 'CLIMATE_DEPENDENT', 'hours', 0.4, 0.20, 0, 'T2'],
            ['Aire Portátil', 1200, 4000, 2200, 'CLIMATE_INEFFICIENT', 'hours', 0.2, 0.45, 0, 'T2'],
            ['Estufa Eléctrica / Panel', 500, 2000, 1000, 'CLIMATE_DEPENDENT', 'hours', 0.6, 0, 0, 'T2'],
            ['Termotanque Eléctrico', 1500, 3000, 2000, 'BASE_LOAD', 'hours', 0.5, 0.15, 1.2, 'T2'],
            ['Bomba de Agua / Riego', 400, 1500, 750, 'BASE_LOAD', 'cycles', 0.8, 0, 0.05, 'T2'],

            // TANQUE 3 (Elásticos / Uso Puntual)
            ['Lavarropas', 400, 2500, 600, 'BASE_LOAD', 'cycles', 0.9, -0.1, 0, 'T3_L'],
            ['Secarropas', 1500, 3000, 2000, 'CONSTANT_ELASTIC', 'cycles', 0.8, -0.2, 0, 'T3_L'],
            ['Lavavajillas', 1000, 2200, 1500, 'BASE_LOAD', 'cycles', 0.9, -0.2, 0, 'T3_L'],
            
            ['TV Grande (>50")', 100, 350, 180, 'CONSTANT_ELASTIC', 'hours', 0.5, 0, 0, 'T3_I'],
            ['TV Estándar', 40, 120, 80, 'CONSTANT_ELASTIC', 'hours', 0.6, 0, 0, 'T3_I'],
            ['Consola de Juegos', 150, 350, 220, 'CONSTANT_ELASTIC', 'hours', 0.3, 0, 0, 'T3_I'],
            ['PC de Escritorio', 150, 500, 250, 'CONSTANT_ELASTIC', 'hours', 0.7, 0, 0, 'T3_I'],
            ['Notebook / Laptop', 30, 120, 65, 'CONSTANT_ELASTIC', 'hours', 0.6, 0, 0, 'T3_I'],

            ['Microondas', 800, 1800, 1200, 'CONSTANT_ELASTIC', 'people_proportional', 0.85, 0, 0.08, 'T3_C'],
            ['Pava Eléctrica', 1500, 2400, 2000, 'BASE_LOAD', 'people_proportional', 0.95, 0, 0.12, 'T3_C'],
            ['Horno Eléctrico', 1500, 3500, 2200, 'CONSTANT_ELASTIC', 'hours', 0.5, 0, 0, 'T3_C'],
            ['Cafetera / Nespresso', 800, 1500, 1200, 'CONSTANT_ELASTIC', 'cycles', 0.9, 0, 0, 'T3_C'],

            ['Iluminación LED (Kit)', 5, 100, 30, 'CONSTANT_ELASTIC', 'hours', 0.7, 0, 0, 'T3_O'],
            ['Plancha', 1000, 2200, 1500, 'CONSTANT_ELASTIC', 'hours', 0.4, 0, 0, 'T3_O'],
            ['Secador de Pelo', 1000, 2200, 1800, 'CONSTANT_ELASTIC', 'hours', 0.3, 0, 0, 'T3_O'],
        ];

        foreach ($typesData as $t) {
            EquipmentType::create([
                'name' => $t[0],
                'min_watts' => $t[1],
                'max_watts' => $t[2],
                'default_power_watts' => $t[3],
                'consumption_logic' => $t[4],
                'usage_unit' => $t[5],
                'determinism_score' => $t[6],
                'thermal_efficiency_penalty' => $t[7],
                'social_coefficient' => $t[8],
                'category_id' => $cats[$t[9]]->id,
                'is_climatization' => in_array($t[4], ['CLIMATE_DEPENDENT', 'CLIMATE_INEFFICIENT']),
                'load_factor' => 1.0
            ]);
        }

        // 4. Re-mapear equipos existentes si es posible
        foreach ($oldEquipments as $oldEq) {
            $newType = EquipmentType::where('name', 'LIKE', '%' . $oldEq->name . '%')->first();
            if ($newType) {
                $oldEq->update([
                    'type_id' => $newType->id,
                    'category_id' => $newType->category_id
                ]);
            } else {
                // Fallback a algo genérico
                $fallbackType = EquipmentType::first();
                $oldEq->update([
                    'type_id' => $fallbackType->id,
                    'category_id' => $fallbackType->category_id
                ]);
            }
        }

        // 5. Cargar Coeficientes de Eficiencia por Categoría
        $matrix = [
            'T0' => ['A+++' => 0.80, 'A' => 1.00],
            'T1' => ['A+++' => 0.65, 'A++' => 0.75, 'A+' => 0.85, 'A' => 1.00, 'B' => 1.15, 'C' => 1.35, 'D' => 1.60],
            'T2' => ['A+++' => 0.60, 'A++' => 0.75, 'A+' => 0.85, 'A' => 1.00, 'B' => 1.18, 'C' => 1.40, 'D' => 1.80],
            'T3_L' => ['A+++' => 0.75, 'A' => 1.00, 'D' => 1.45],
            'T3_C' => ['A+++' => 0.80, 'A' => 1.00, 'D' => 1.55],
        ];

        foreach ($matrix as $catKey => $labels) {
            foreach ($labels as $label => $coeff) {
                EnergyLabelCoefficient::create([
                    'category_id' => $cats[$catKey]->id,
                    'label' => $label,
                    'coefficient' => $coeff
                ]);
            }
        }

        // 6. Excepción específica para Aire de Ventana
        $typeVentana = EquipmentType::where('name', 'Aire de Ventana')->first();
        if ($typeVentana) {
            $ventanaMatrix = ['A+++' => 0.75, 'A++' => 0.85, 'A+' => 0.95, 'A' => 1.00, 'B' => 1.15, 'C' => 1.30, 'D' => 1.65];
            foreach ($ventanaMatrix as $label => $coeff) {
                EnergyLabelCoefficient::create([
                    'category_id' => $typeVentana->category_id,
                    'equipment_type_id' => $typeVentana->id,
                    'label' => $label,
                    'coefficient' => $coeff
                ]);
            }
        }

        Schema::enableForeignKeyConstraints();
    }
}
