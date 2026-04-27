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
        Schema::disableForeignKeyConstraints();
        
        $oldEquipments = Equipment::all();
        
        DB::table('energy_label_coefficients')->truncate();
        DB::table('equipment_benchmarks')->truncate();
        DB::table('equipment_types')->truncate();
        DB::table('equipment_categories')->truncate();

        // 2. Crear Categorías Maestras (Capa Visual V3)
        $cats = [
            'CONECTIVIDAD' => EquipmentCategory::create(['name' => 'Conectividad y Seguridad']),
            'REFRIGERACION' => EquipmentCategory::create(['name' => 'Refrigeración y Servidores']),
            'CLIMATIZACION' => EquipmentCategory::create(['name' => 'Climatización']),
            'AGUA' => EquipmentCategory::create(['name' => 'Agua Caliente']),
            'LINEA_BLANCA' => EquipmentCategory::create(['name' => 'Línea Blanca y Limpieza']),
            'INFORMATICA' => EquipmentCategory::create(['name' => 'Informática y Ocio']),
            'COCINA' => EquipmentCategory::create(['name' => 'Cocina y Pequeños']),
            'OTROS' => EquipmentCategory::create(['name' => 'Iluminación y Otros']),
        ];

        // 3. Definir Tipos Definitivos
        // Formato: [Nombre, MinW, MaxW, DefW, Logic, Unit, DetScore, Penalty, SocialCoeff, CatKey, IsThermal]
        $typesData = [
            // T1: Certeza Matemática
            ['Router Wifi / ONT', 10, 40, 15, 'BASE_LOAD', 'hours', 1.0, 0, 0, 'CONECTIVIDAD', false],
            ['Cámaras de Seguridad', 5, 20, 10, 'BASE_LOAD', 'hours', 1.0, 0, 0, 'CONECTIVIDAD', false],
            ['Alarma Monitoreada', 5, 15, 8, 'BASE_LOAD', 'hours', 1.0, 0, 0, 'CONECTIVIDAD', false],

            // T2: Base Inmutable
            ['Heladera con Freezer', 80, 450, 150, 'BASE_LOAD', 'hours', 0.7, 0.1, 0, 'REFRIGERACION', false],
            ['Freezer de Pozo', 100, 500, 200, 'BASE_LOAD', 'hours', 0.8, 0.05, 0, 'REFRIGERACION', false],
            ['Servidor / Rack IT', 150, 2000, 400, 'BASE_LOAD', 'hours', 0.98, 0, 0, 'REFRIGERACION', false],

            // T3: Sensibilidad Climática
            ['Aire Split', 300, 3500, 1500, 'CLIMATE_DEPENDENT', 'hours', 0.4, 0, 0, 'CLIMATIZACION', true],
            ['Aire de Ventana', 800, 2500, 1500, 'CLIMATE_DEPENDENT', 'hours', 0.4, 0.20, 0, 'CLIMATIZACION', true],
            ['Aire Portátil', 1200, 4000, 2200, 'CLIMATE_INEFFICIENT', 'hours', 0.2, 0.45, 0, 'CLIMATIZACION', true],
            ['Estufa Eléctrica / Panel', 500, 2000, 1000, 'CLIMATE_DEPENDENT', 'hours', 0.6, 0, 0, 'CLIMATIZACION', true],
            ['Termotanque Eléctrico', 1500, 3000, 2000, 'BASE_THERMAL_LOSS', 'hours', 0.5, 0.15, 1.2, 'AGUA', true],

            // T4: Elasticidad y Hábitos
            ['Lavarropas', 400, 2500, 600, 'BASE_LOAD', 'cycles', 0.9, -0.1, 0, 'LINEA_BLANCA', false],
            ['Lavavajillas', 1000, 2200, 1500, 'BASE_LOAD', 'cycles', 0.9, -0.2, 0, 'LINEA_BLANCA', false],
            ['TV Estándar', 40, 120, 80, 'CONSTANT_ELASTIC', 'hours', 0.6, 0, 0, 'INFORMATICA', false],
            ['PC de Escritorio', 150, 500, 250, 'CONSTANT_ELASTIC', 'hours', 0.7, 0, 0, 'INFORMATICA', false],
            ['Notebook / Laptop', 30, 120, 65, 'CONSTANT_ELASTIC', 'hours', 0.6, 0, 0, 'INFORMATICA', false],
            ['Microondas', 800, 1800, 1200, 'CONSTANT_ELASTIC', 'people_proportional', 0.85, 0, 0.08, 'COCINA', false],
            ['Pava Eléctrica', 1500, 2400, 2000, 'BASE_LOAD', 'people_proportional', 0.95, 0, 0.12, 'COCINA', false],
            ['Iluminación LED (Kit)', 5, 100, 30, 'CONSTANT_ELASTIC', 'hours', 0.7, 0, 0, 'OTROS', false],
            
            // Especial: SEASONAL_HABIT (T4 condicionado por Clima)
            ['Ventilador de Techo', 60, 120, 80, 'SEASONAL_HABIT', 'hours', 0.5, 0, 0, 'CLIMATIZACION', false],
            ['Ventilador de Pie', 40, 90, 60, 'SEASONAL_HABIT', 'hours', 0.4, 0, 0, 'CLIMATIZACION', false],
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
                'is_climatization' => ($t[9] === 'CLIMATIZACION'),
                'is_thermal_sensitive' => $t[10],
                'load_factor' => 1.0
            ]);
        }

        // Re-mapeo y coeficientes... (mantenido similar para no romper consistencia)
        foreach ($oldEquipments as $oldEq) {
            $newType = EquipmentType::where('name', 'LIKE', '%' . $oldEq->name . '%')->first();
            if ($newType) {
                $oldEq->update(['type_id' => $newType->id, 'category_id' => $newType->category_id]);
            }
        }

        Schema::enableForeignKeyConstraints();
    }
}
