<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EfficiencyBenchmark;
use App\Models\EquipmentType;

class EfficiencyBenchmarkSeeder extends Seeder
{
    /**
     * Benchmarks de eficiencia para recomendaciones de reemplazo.
     * 
     * efficiency_gain_factor: porcentaje de ahorro estimado (0.0 a 1.0)
     * average_market_price: precio promedio en ARS (actualizar periódicamente)
     * meli_search_term: término para buscar en Mercado Libre
     * 
     * Solo se crean benchmarks para equipos donde hay una alternativa
     * significativamente más eficiente. Los equipos ya eficientes se omiten.
     */
    public function run(): void
    {
        // [nombre exacto en DB => datos del benchmark]
        // null = ya es eficiente o no hay alternativa clara
        $benchmarks = [

            // ─── ILUMINACIÓN ──────────────────────────────────────────────
            'Tubo Fluorescente 36W' => [
                'efficiency_gain_factor' => 0.60,
                'average_market_price'   => 4500,
                'meli_search_term'       => 'Tubo LED 18W T8',
            ],
            'Foco Pequeño' => [
                'efficiency_gain_factor' => 0.80,
                'average_market_price'   => 2500,
                'meli_search_term'       => 'Lampara LED 9W E27',
            ],
            'Foco Ventilador' => [
                'efficiency_gain_factor' => 0.70,
                'average_market_price'   => 3000,
                'meli_search_term'       => 'Lampara LED ventilador de techo',
            ],
            // Tubo LED, Lámpara LED 9W, Foco LED Grande → ya son eficientes, se omiten

            // ─── CLIMATIZACIÓN ────────────────────────────────────────────
            'Aire Acondicionado (2200 frigorías)' => [
                'efficiency_gain_factor' => 0.40,
                'average_market_price'   => 380000,
                'meli_search_term'       => 'Aire Acondicionado Inverter 2200 frigorias',
            ],
            'Aire Acondicionado (3500 frigorías)' => [
                'efficiency_gain_factor' => 0.40,
                'average_market_price'   => 520000,
                'meli_search_term'       => 'Aire Acondicionado Inverter 3500 frigorias',
            ],
            'Aire Acondicionado Grande' => [
                'efficiency_gain_factor' => 0.40,
                'average_market_price'   => 700000,
                'meli_search_term'       => 'Aire Acondicionado Inverter 5000 frigorias',
            ],
            'Aire Acondicionado Mediano' => [
                'efficiency_gain_factor' => 0.40,
                'average_market_price'   => 520000,
                'meli_search_term'       => 'Aire Acondicionado Inverter 3500 frigorias',
            ],
            'Aire Acondicionado Portátil' => [
                'efficiency_gain_factor' => 0.35,
                'average_market_price'   => 420000,
                'meli_search_term'       => 'Aire Acondicionado Inverter split 2200 frigorias',
            ],
            'Caloventor' => [
                'efficiency_gain_factor' => 0.50,
                'average_market_price'   => 180000,
                'meli_search_term'       => 'Panel calefactor electrico bajo consumo',
            ],
            'Estufa Halógena' => [
                'efficiency_gain_factor' => 0.45,
                'average_market_price'   => 150000,
                'meli_search_term'       => 'Panel calefactor infrarrojo bajo consumo',
            ],
            'Ventilador de techo' => [
                'efficiency_gain_factor' => 0.30,
                'average_market_price'   => 120000,
                'meli_search_term'       => 'Ventilador de techo DC bajo consumo',
            ],

            // ─── ELECTRODOMÉSTICOS ────────────────────────────────────────
            'Heladera con Freezer' => [
                'efficiency_gain_factor' => 0.30,
                'average_market_price'   => 450000,
                'meli_search_term'       => 'Heladera No Frost A++ eficiencia energetica',
            ],
            'Lavarropas Automático' => [
                'efficiency_gain_factor' => 0.25,
                'average_market_price'   => 380000,
                'meli_search_term'       => 'Lavarropas inverter A+++ bajo consumo',
            ],
            'Termotanque Eléctrico' => [
                'efficiency_gain_factor' => 0.70,
                'average_market_price'   => 550000,
                'meli_search_term'       => 'Termotanque solar 150 litros',
            ],

            // ─── TECNOLOGÍA ───────────────────────────────────────────────
            'TV LED 50"' => [
                'efficiency_gain_factor' => 0.25,
                'average_market_price'   => 350000,
                'meli_search_term'       => 'Smart TV 50 pulgadas LED bajo consumo',
            ],
            'TV Grande (Smart 55+)' => [
                'efficiency_gain_factor' => 0.20,
                'average_market_price'   => 500000,
                'meli_search_term'       => 'Smart TV 55 pulgadas QLED bajo consumo',
            ],
            'TV Chico (32/Monitor)' => [
                'efficiency_gain_factor' => 0.20,
                'average_market_price'   => 180000,
                'meli_search_term'       => 'Monitor LED 32 pulgadas bajo consumo',
            ],
            'PC Gamer' => [
                'efficiency_gain_factor' => 0.20,
                'average_market_price'   => 250000,
                'meli_search_term'       => 'Fuente de poder 80 Plus Gold eficiencia',
            ],
            'Monitor PC' => [
                'efficiency_gain_factor' => 0.30,
                'average_market_price'   => 200000,
                'meli_search_term'       => 'Monitor LED IPS bajo consumo',
            ],
        ];

        foreach ($benchmarks as $typeName => $data) {
            $type = EquipmentType::where('name', $typeName)->first();

            if (!$type) {
                $this->command->warn("  ⚠ Tipo no encontrado: {$typeName}");
                continue;
            }

            EfficiencyBenchmark::updateOrCreate(
                ['equipment_type_id' => $type->id],
                $data
            );

            $this->command->info("  ✓ {$typeName}");
        }

        $this->command->info('');
        $this->command->info('Benchmarks cargados: ' . count($benchmarks));
    }
}
