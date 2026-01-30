<?php

namespace Database\Seeders;

use App\Models\EquipmentCategory;
use App\Models\EquipmentType;
use Illuminate\Database\Seeder;

class EquipmentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Climatización', 'Iluminación', 'Electrodomésticos', 'Entretenimiento', 'Cocina', 'Oficina', 'Otros'
        ];

        $catIds = [];
        foreach ($categories as $cat) {
            $catIds[$cat] = EquipmentCategory::firstOrCreate(['name' => $cat])->id;
        }

        $types = [
            // --- CLIMATIZACIÓN (Tanque 2) ---
            ['name' => 'Aire Acondicionado (2200 frigorías)', 'category' => 'Climatización', 'watts' => 1013, 'use_h' => 6, 'process' => 'Motor', 'load' => 0.7, 'eff' => 0.9, 'int' => 'excesivo', 'clima' => true],
            ['name' => 'Aire Acondicionado (3500 frigorías)', 'category' => 'Climatización', 'watts' => 1613, 'use_h' => 6, 'process' => 'Motor', 'load' => 0.7, 'eff' => 0.9, 'int' => 'excesivo', 'clima' => true],
            ['name' => 'Aire Acondicionado Portátil', 'category' => 'Climatización', 'watts' => 1400, 'use_h' => 6, 'process' => 'Motor', 'load' => 0.7, 'eff' => 0.9, 'int' => 'excesivo', 'clima' => true],
            ['name' => 'Ventilador de techo', 'category' => 'Climatización', 'watts' => 75, 'use_h' => 8, 'process' => 'Motor', 'load' => 0.8, 'eff' => 0.9, 'int' => 'medio', 'clima' => true],
            ['name' => 'Caloventor', 'category' => 'Climatización', 'watts' => 2000, 'use_h' => 4, 'process' => 'Resistencia', 'load' => 1.0, 'eff' => 0.7, 'int' => 'excesivo', 'clima' => true],
            ['name' => 'Estufa Halógena', 'category' => 'Climatización', 'watts' => 1200, 'use_h' => 4, 'process' => 'Resistencia', 'load' => 1.0, 'eff' => 0.7, 'int' => 'excesivo', 'clima' => true],

            // --- ILUMINACIÓN (Tanque 3 - Elasticidad Baja) ---
            ['name' => 'Lámpara LED 9W', 'category' => 'Iluminación', 'watts' => 9, 'use_h' => 6, 'process' => 'Electroluminiscencia', 'load' => 1.0, 'eff' => 0.9, 'int' => 'bajo', 'clima' => false],
            ['name' => 'Tubo Fluorescente 36W', 'category' => 'Iluminación', 'watts' => 36, 'use_h' => 8, 'process' => 'Gas', 'load' => 1.0, 'eff' => 0.8, 'int' => 'bajo', 'clima' => false],

            // --- ELECTRODOMÉSTICOS (Tanque 1 si es 24h, sino Tanque 3) ---
            ['name' => 'Heladera con Freezer', 'category' => 'Electrodomésticos', 'watts' => 150, 'use_h' => 24, 'process' => 'Motor', 'load' => 0.4, 'eff' => 0.9, 'int' => 'critico', 'clima' => false], 
            ['name' => 'Lavarropas Automático', 'category' => 'Electrodomésticos', 'watts' => 500, 'use_h' => 1, 'process' => 'Motor', 'load' => 0.7, 'eff' => 0.9, 'int' => 'alto', 'clima' => false],
            ['name' => 'Termotanque Eléctrico', 'category' => 'Otros', 'watts' => 1500, 'use_h' => 4, 'process' => 'Resistencia', 'load' => 1.0, 'eff' => 0.7, 'int' => 'excesivo', 'clima' => false],

            // --- ENTRETENIMIENTO / OFICINA (Tanque 3) ---
            // --- ENTRETENIMIENTO / OFICINA (Tanque 3) ---
            ['name' => 'TV LED 50"', 'category' => 'Entretenimiento', 'watts' => 100, 'use_h' => 5, 'process' => 'Electrónico', 'load' => 1.0, 'eff' => 0.9, 'int' => 'medio', 'clima' => false],
            ['name' => 'PC Gamer', 'category' => 'Oficina', 'watts' => 500, 'use_h' => 4, 'process' => 'Electrónico', 'load' => 0.6, 'eff' => 0.8, 'int' => 'alto', 'clima' => false],
            ['name' => 'Router WiFi', 'category' => 'Oficina', 'watts' => 15, 'use_h' => 24, 'process' => 'Electrónico', 'load' => 1.0, 'eff' => 0.9, 'int' => 'critico', 'clima' => false],
            ['name' => 'Cargador de Celular', 'category' => 'Oficina', 'watts' => 10, 'use_h' => 4, 'process' => 'Electrónico', 'load' => 1.0, 'eff' => 0.8, 'int' => 'bajo', 'clima' => false],

            // --- DE DATOSHOGAR.TXT ---
            ['name' => 'Aire Acondicionado Grande', 'category' => 'Climatización', 'watts' => 2500, 'use_h' => 6, 'process' => 'Motor', 'load' => 0.7, 'eff' => 0.9, 'int' => 'excesivo', 'clima' => true],
            ['name' => 'Aire Acondicionado Mediano', 'category' => 'Climatización', 'watts' => 1800, 'use_h' => 6, 'process' => 'Motor', 'load' => 0.7, 'eff' => 0.9, 'int' => 'excesivo', 'clima' => true],
            ['name' => 'Microondas', 'category' => 'Cocina', 'watts' => 1000, 'use_h' => 0.5, 'process' => 'Resistencia', 'load' => 1.0, 'eff' => 0.6, 'int' => 'alto', 'clima' => false],
            ['name' => 'Foco Ventilador', 'category' => 'Iluminación', 'watts' => 5, 'use_h' => 6, 'process' => 'Electroluminiscencia', 'load' => 1.0, 'eff' => 0.9, 'int' => 'bajo', 'clima' => false],
            ['name' => 'Tubo LED', 'category' => 'Iluminación', 'watts' => 18, 'use_h' => 6, 'process' => 'Electroluminiscencia', 'load' => 1.0, 'eff' => 0.9, 'int' => 'bajo', 'clima' => false],
            ['name' => 'TV Grande (Smart 55+)', 'category' => 'Entretenimiento', 'watts' => 120, 'use_h' => 4, 'process' => 'Electrónico', 'load' => 1.0, 'eff' => 0.9, 'int' => 'medio', 'clima' => false],
            ['name' => 'TV Chico (32/Monitor)', 'category' => 'Entretenimiento', 'watts' => 85, 'use_h' => 4, 'process' => 'Electrónico', 'load' => 1.0, 'eff' => 0.9, 'int' => 'medio', 'clima' => false],
            ['name' => 'Monitor PC', 'category' => 'Oficina', 'watts' => 50, 'use_h' => 6, 'process' => 'Electrónico', 'load' => 1.0, 'eff' => 0.9, 'int' => 'bajo', 'clima' => false],
            ['name' => 'Notebook', 'category' => 'Oficina', 'watts' => 65, 'use_h' => 6, 'process' => 'Electrónico', 'load' => 1.0, 'eff' => 0.9, 'int' => 'medio', 'clima' => false],
            ['name' => 'Secador de Pelo', 'category' => 'Otros', 'watts' => 1000, 'use_h' => 0.5, 'process' => 'Resistencia', 'load' => 1.0, 'eff' => 0.8, 'int' => 'alto', 'clima' => false],
            ['name' => 'Máquina de Afeitar', 'category' => 'Otros', 'watts' => 12, 'use_h' => 0.2, 'process' => 'Motor', 'load' => 1.0, 'eff' => 0.8, 'int' => 'bajo', 'clima' => false],
            ['name' => 'Foco LED Grande', 'category' => 'Iluminación', 'watts' => 12, 'use_h' => 6, 'process' => 'Electroluminiscencia', 'load' => 1.0, 'eff' => 0.9, 'int' => 'bajo', 'clima' => false],
            ['name' => 'Foco Pequeño', 'category' => 'Iluminación', 'watts' => 5, 'use_h' => 6, 'process' => 'Electroluminiscencia', 'load' => 1.0, 'eff' => 0.9, 'int' => 'bajo', 'clima' => false],
            
            // --- NUEVOS PEDIDOS ---
            ['name' => 'Cortadora de Fiambre', 'category' => 'Electrodomésticos', 'watts' => 200, 'use_h' => 0.5, 'process' => 'Motor', 'load' => 0.8, 'eff' => 0.85, 'int' => 'medio', 'clima' => false],
        ];

        foreach ($types as $type) {
            EquipmentType::updateOrCreate(
                ['name' => $type['name']],
                [
                    'category_id'                => $catIds[$type['category']],
                    'default_power_watts'        => $type['watts'],
                    'default_avg_daily_use_hours'=> $type['use_h'],
                    'process_type'               => $type['process'],
                    'load_factor'                => $type['load'],
                    'efficiency'                 => $type['eff'],
                    'intensity'                  => $type['int'], // AHORA SÍ LO GUARDAMOS
                    'is_climatization'           => $type['clima'], // PARA EL TANQUE 2
                    'default_standby_power_w'    => ($type['int'] == 'bajo' || $type['int'] == 'medio') ? 1.5 : 0, // Consumo vampiro opcional
                ]
            );
        }
    }
}