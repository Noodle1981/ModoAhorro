<?php

namespace Database\Seeders;

use App\Models\EquipmentCategory;
use App\Models\EquipmentType;
use App\Models\EnergyLabelCoefficient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MasterCleanCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        // Limpiar catálogo e instancias (se repoblan con Casa27EquipmentSeeder)
        DB::table('energy_label_coefficients')->truncate();
        DB::table('equipment_benchmarks')->truncate();
        DB::table('equipment')->truncate();
        DB::table('equipment_types')->truncate();
        DB::table('equipment_categories')->truncate();

        // ── 1. CATEGORÍAS VISUALES (12 - centradas en necesidad humana) ──────
        $cats = [
            'CLIMA'       => EquipmentCategory::create(['name' => 'Climatización y Ambiente',     'description' => 'Aires, estufas, ventiladores y todo lo que modifica la temperatura o mueve el aire.']),
            'REFRIG'      => EquipmentCategory::create(['name' => 'Refrigeración',                'description' => 'Conservación de alimentos 24hs: heladeras, freezers, cavas de vino.']),
            'AGUA'        => EquipmentCategory::create(['name' => 'Agua y Bombeo',                'description' => 'Producción de agua caliente sanitaria y sistemas hídricos: termotanques, bombas, riego.']),
            'COCINA'      => EquipmentCategory::create(['name' => 'Cocina y Preparación',         'description' => 'Equipos para cocinar y preparar alimentos y bebidas.']),
            'LAVADO'      => EquipmentCategory::create(['name' => 'Lavado y Limpieza',            'description' => 'Tratamiento de ropa y vajilla: lavarropas, secarropas, lavavajillas.']),
            'ILUM'        => EquipmentCategory::create(['name' => 'Iluminación',                  'description' => 'Todo tipo de luminarias: LED, incandescentes, tubos, tiras.']),
            'ENTRET'      => EquipmentCategory::create(['name' => 'Entretenimiento y Multimedia', 'description' => 'TVs, consolas, audio, home theatre.']),
            'IT'          => EquipmentCategory::create(['name' => 'Informática y Oficina',        'description' => 'PCs, notebooks, monitores, impresoras, servidores.']),
            'REDES'       => EquipmentCategory::create(['name' => 'Conectividad y Seguridad',     'description' => 'Routers, cámaras, alarmas, sistemas 24/7 de baja potencia.']),
            'SALUD'       => EquipmentCategory::create(['name' => 'Salud y Cuidado Personal',     'description' => 'Secadores de pelo, afeitadoras, nebulizadores, equipos de salud.']),
            'MANT'        => EquipmentCategory::create(['name' => 'Mantenimiento y Bombas',       'description' => 'Bombas de riego, filtrado de piscina, herramientas eléctricas.']),
            'CARGADORES'  => EquipmentCategory::create(['name' => 'Cargadores',                   'description' => 'Cargadores de celulares, tablets, notebooks y dispositivos portátiles.']),
        ];

        // ── 2. TIPOS DE EQUIPO ────────────────────────────────────────────────
        // [nombre, minW, maxW, defW, logic, unit, det, penalty, social, catKey, isClimatization, isThermal, energyPerCycle]
        $types = [
            // --- CONECTIVIDAD Y SEGURIDAD (Tank 0 - Certeza Absoluta) ---
            ['Router Wifi / ONT',        5,   40,   5,    'BASE_LOAD',          'hours',              1.00,  0,     0,    'REDES',     false, false, null],
            ['Cámaras de Seguridad',     5,   20,   10,   'BASE_LOAD',          'hours',              1.00,  0,     0,    'REDES',     false, false, null],
            ['Alarma Monitoreada',       5,   15,   8,    'BASE_LOAD',          'hours',              1.00,  0,     0,    'REDES',     false, false, null],

            // --- REFRIGERACIÓN (Tank 1 - Base Inmutable) ---
            ['Heladera con Freezer',     80,  450,  150,  'BASE_LOAD',          'hours',              0.70,  0.10,  0,    'REFRIG',    false, false, null],
            ['Freezer de Pozo',          100, 500,  200,  'BASE_LOAD',          'hours',              0.80,  0.05,  0,    'REFRIG',    false, false, null],
            ['Cava de Vinos',            50,  150,  80,   'BASE_LOAD',          'hours',              0.60,  0.10,  0,    'REFRIG',    false, false, null],

            // --- CLIMATIZACIÓN (Tank 2 - Sensibilidad Climática) ---
            ['Aire Split',               300, 3500, 1500, 'CLIMATE_DEPENDENT',  'hours',              0.40,  0,     0,    'CLIMA',     true,  true,  null],
            ['Aire de Ventana',          800, 2500, 1500, 'CLIMATE_DEPENDENT',  'hours',              0.40,  0.20,  0,    'CLIMA',     true,  true,  null],
            ['Aire Portátil',            1200,4000, 1400, 'CLIMATE_INEFFICIENT','hours',              0.20,  0.45,  0,    'CLIMA',     true,  true,  null],
            ['Estufa Eléctrica / Panel', 500, 2000, 1000, 'CLIMATE_DEPENDENT',  'hours',              0.60,  0,     0,    'CLIMA',     true,  true,  null],
            ['Ventilador de Techo',      40,  120,  60,   'SEASONAL_HABIT',     'hours',              0.50,  0,     0,    'CLIMA',     false, false, null],
            ['Ventilador de Pie',        30,  90,   50,   'SEASONAL_HABIT',     'hours',              0.40,  0,     0,    'CLIMA',     false, false, null],

            // --- AGUA Y BOMBEO ---
            ['Termotanque Eléctrico',    1500,3000, 2000, 'BASE_THERMAL_LOSS',  'hours',              0.50,  0.15,  1.2,  'AGUA',      false, true,  null],
            ['Bomba de Agua',            400, 1500, 750,  'BASE_LOAD',          'cycles',             0.80,  0,     0.05, 'AGUA',      false, false, 0.10],
            ['Bomba de Riego',           400, 1500, 750,  'BASE_LOAD',          'cycles',             0.80,  0,     0.05, 'MANT',      false, false, 0.15],

            // --- COCINA Y PREPARACIÓN (Tank 3 - Elásticos) ---
            ['Microondas',               800, 1800, 1000, 'CONSTANT_ELASTIC',   'people_proportional',0.85,  0,     0.08, 'COCINA',    false, false, null],
            ['Pava Eléctrica',           1500,2400, 2000, 'BASE_LOAD',          'people_proportional',0.95,  0,     0.12, 'COCINA',    false, false, null],
            ['Horno Eléctrico',          1500,3500, 2200, 'CONSTANT_ELASTIC',   'hours',              0.50,  0,     0,    'COCINA',    false, false, null],
            ['Cafetera / Nespresso',     800, 1500, 1200, 'CONSTANT_ELASTIC',   'cycles',             0.90,  0,     0,    'COCINA',    false, false, 0.03],
            ['Tostadora',                700, 1200, 900,  'CONSTANT_ELASTIC',   'cycles',             0.85,  0,     0,    'COCINA',    false, false, 0.04],

            // --- LAVADO Y LIMPIEZA ---
            ['Lavarropas',               400, 2500, 2500, 'BASE_LOAD',          'cycles',             0.90, -0.10,  1.5,  'LAVADO',    false, false, 1.50],
            ['Secarropas',               1500,3000, 2000, 'CONSTANT_ELASTIC',   'cycles',             0.80, -0.20,  1.0,  'LAVADO',    false, false, 2.50],
            ['Lavavajillas',             1000,2200, 1500, 'BASE_LOAD',          'cycles',             0.90, -0.20,  1.2,  'LAVADO',    false, false, 1.20],

            // --- ILUMINACIÓN ---
            ['Foco LED',                 3,   15,   8,    'CONSTANT_ELASTIC',   'hours',              0.70,  0,     0,    'ILUM',      false, false, null],
            ['Tubo LED',                 8,   20,   10,   'CONSTANT_ELASTIC',   'hours',              0.70,  0,     0,    'ILUM',      false, false, null],
            ['Foco Incandescente',       40,  100,  60,   'CONSTANT_ELASTIC',   'hours',              0.60,  0,     0,    'ILUM',      false, false, null],
            ['Tira LED',                 5,   50,   20,   'CONSTANT_ELASTIC',   'hours',              0.70,  0,     0,    'ILUM',      false, false, null],

            // --- ENTRETENIMIENTO Y MULTIMEDIA ---
            ['TV Grande (>50")',          100, 350,  120,  'CONSTANT_ELASTIC',   'hours',              0.50,  0,     0,    'ENTRET',    false, false, null],
            ['TV Estándar / Chico',      40,  100,  85,   'CONSTANT_ELASTIC',   'hours',              0.60,  0,     0,    'ENTRET',    false, false, null],
            ['Consola de Juegos',        150, 350,  220,  'CONSTANT_ELASTIC',   'hours',              0.30,  0,     0,    'ENTRET',    false, false, null],

            // --- INFORMÁTICA Y OFICINA ---
            ['PC Gamer / Workstation',   150, 1200, 150,  'CONSTANT_ELASTIC',   'hours',              0.40,  0,     0,    'IT',        false, false, null],
            ['PC de Escritorio',         150, 500,  250,  'CONSTANT_ELASTIC',   'hours',              0.70,  0,     0,    'IT',        false, false, null],
            ['Monitor LED',              20,  80,   40,   'CONSTANT_ELASTIC',   'hours',              0.80,  0,     0,    'IT',        false, false, null],
            ['Notebook / Laptop',        30,  120,  65,   'CONSTANT_ELASTIC',   'hours',              0.60,  0,     0,    'IT',        false, false, null],
            ['Servidor / Rack IT',       150, 2000, 400,  'BASE_LOAD',          'hours',              0.98,  0,     0,    'IT',        false, false, null],
            ['Impresora',                10,  400,  30,   'CONSTANT_ELASTIC',   'cycles',             0.30,  0,     0,    'IT',        false, false, 0.01],

            // --- SALUD Y CUIDADO PERSONAL ---
            ['Secador de Pelo',          1000,2200, 1000, 'CONSTANT_ELASTIC',   'hours',              0.30,  0,     0,    'SALUD',     false, false, null],
            ['Máquina de Afeitar',       5,   25,   12,   'CONSTANT_ELASTIC',   'hours',              0.40,  0,     0,    'SALUD',     false, false, null],
            ['Plancha de Ropa',          1000,2200, 1500, 'CONSTANT_ELASTIC',   'hours',              0.40,  0,     0,    'SALUD',     false, false, null],

            // --- CARGADORES ---
            ['Cargador Móvil',           5,   25,   15,   'BASE_LOAD',          'hours',              0.90,  0,     0,    'CARGADORES',false, false, null],
        ];

        foreach ($types as $t) {
            EquipmentType::create([
                'name'                       => $t[0],
                'min_watts'                  => $t[1],
                'max_watts'                  => $t[2],
                'default_power_watts'        => $t[3],
                'consumption_logic'          => $t[4],
                'usage_unit'                 => $t[5],
                'determinism_score'          => $t[6],
                'thermal_efficiency_penalty' => $t[7],
                'social_coefficient'         => $t[8],
                'category_id'               => $cats[$t[9]]->id,
                'is_climatization'           => $t[10],
                'is_thermal_sensitive'       => $t[11],
                'energy_per_cycle'           => $t[12],
                'load_factor'                => 1.0,
            ]);
        }

        // ── 3. COEFICIENTES DE ETIQUETA ENERGÉTICA (por categoría) ───────────
        $matrix = [
            'CLIMA'  => ['A+++' => 0.60, 'A++' => 0.75, 'A+' => 0.85, 'A' => 1.00, 'B' => 1.18, 'C' => 1.40, 'D' => 1.80],
            'REFRIG' => ['A+++' => 0.65, 'A++' => 0.75, 'A+' => 0.85, 'A' => 1.00, 'B' => 1.15, 'C' => 1.35, 'D' => 1.60],
            'LAVADO' => ['A+++' => 0.75, 'A++' => 0.85, 'A+' => 0.92, 'A' => 1.00, 'B' => 1.12, 'C' => 1.30, 'D' => 1.45],
            'COCINA' => ['A+++' => 0.80, 'A'   => 1.00, 'B' => 1.12,  'C' => 1.30, 'D' => 1.55],
        ];

        foreach ($matrix as $catKey => $labels) {
            foreach ($labels as $label => $coeff) {
                EnergyLabelCoefficient::create([
                    'category_id' => $cats[$catKey]->id,
                    'label'       => $label,
                    'coefficient' => $coeff,
                ]);
            }
        }

        // Excepción: Aire Portátil tiene penalización mayor
        $typePortatil = EquipmentType::where('name', 'Aire Portátil')->first();
        if ($typePortatil) {
            $portMatriz = ['A+++' => 0.75, 'A++' => 0.85, 'A+' => 0.95, 'A' => 1.00, 'B' => 1.20, 'C' => 1.45, 'D' => 1.90];
            foreach ($portMatriz as $label => $coeff) {
                EnergyLabelCoefficient::create([
                    'category_id'       => $cats['CLIMA']->id,
                    'equipment_type_id' => $typePortatil->id,
                    'label'             => $label,
                    'coefficient'       => $coeff,
                ]);
            }
        }

        Schema::enableForeignKeyConstraints();
    }
}
