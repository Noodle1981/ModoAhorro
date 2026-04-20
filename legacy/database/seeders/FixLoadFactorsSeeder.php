<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EquipmentType;

class FixLoadFactorsSeeder extends Seeder
{
    /**
     * Actualiza los factores de carga para incluir ciclos de trabajo (Duty Cycle).
     * Esto corrige la sobreestimación en heladeras, aires y PCs.
     * 
     * IMPORTANTE: El load_factor ahora representa el "Factor de Uso Real"
     * que combina:
     * - Duty Cycle: % del tiempo que el equipo está realmente encendido
     * - Load Factor: % de la potencia nominal que realmente consume
     */
    public function run()
    {
        $this->command->info('🔧 Actualizando factores de carga (load_factor) con duty cycles reales...');

        $adjustments = [
            // --- GRUPO MOTOR (Cíclicos) ---
            // Heladeras: El motor solo funciona ~35-40% del tiempo (ciclos de enfriamiento)
            // Heladeras: El motor solo funciona ~35-40% del tiempo (ciclos de enfriamiento)
            'Heladera con Freezer' => 0.35,
            'Heladera con Freezer Inverter' => 0.30, // Inverter es más eficiente
            'Freezer Horizontal' => 0.40,
            
            // Aires acondicionados: Inverter o Termostato cortan el compresor
            'Aire Acondicionado (2200 frigorías)' => 0.50,
            'Aire Acondicionado (3500 frigorías)' => 0.50,
            'Aire Acondicionado (4500 frigorías)' => 0.50,
            'Aire Acondicionado Portátil' => 0.50,
            'Aire Acondicionado Inverter (2200 frigorías)' => 0.40, // Inverter modula potencia
            
            // Lavarropas: Solo centrifuga a alta potencia brevemente
            // Ajuste fino: 0.30 para compensar potencia nominal vs uso real en agua fría (~300W)
            'Lavarropas Automático (Agua fría)' => 0.30,
            'Lavarropas Automático (Con calentamiento)' => 0.60, // Resistencia consume más
            
            // --- GRUPO MOTOR (Continuos) ---
            // Ventiladores: Si está ON, consume constante
            'Ventilador de techo' => 1.00,
            'Ventilador de pie' => 1.00,
            
            // Otros motores continuos
            'Aspiradora' => 1.00,
            'Licuadora' => 1.00,
            'Batidora de mano' => 1.00,
            'Procesadora de Alimentos' => 1.00,
            'Exprimidor Eléctrico' => 1.00,
            
            // --- GRUPO RESISTENCIA (Con Termostato) ---
            // Plancha: La luz prende y apaga para mantener temperatura
            'Plancha' => 0.60,
            'Plancha a Vapor' => 0.60,
            
            // Calefacción con termostato
            'Caloventor' => 0.70,
            'Radiador Eléctrico' => 0.70,
            'Panel Calefactor (Mica/Cerámico)' => 0.70,
            
            // Resistencias continuas (sin termostato)
            'Estufa de Cuarzo (2 velas)' => 1.00,
            'Estufa Halógena' => 1.00,
            'Horno Eléctrico' => 1.00,
            'Pava Eléctrica' => 1.00,
            'Tostadora' => 1.00,
            'Cafetera de Filtro' => 1.00,
            'Cafetera Expreso' => 1.00,
            'Sandwichera' => 1.00,
            'Freidora de Aire (Air Fryer)' => 1.00,
            'Anafe Eléctrico (1 hornalla)' => 1.00,
            
            // --- GRUPO ELECTRÓNICO (Carga Variable) ---
            // PCs: Fuentes de 600W rara vez pasan de 350W reales
            'PC de Escritorio (CPU + Monitor)' => 0.50, // Promedio entre oficina y gamer
            'Notebook / Laptop' => 0.40,
            
            // TVs y entretenimiento
            'Televisor LED 32"' => 0.90,
            'Televisor LED 50" 4K' => 0.90,
            'Consola de Videojuegos (PS5/Xbox)' => 0.70,
            'Decodificador TV Cable' => 1.00, // Siempre encendido
            'Equipo de Audio' => 0.80,
            
            // Networking (siempre encendidos)
            'Modem / Router WiFi' => 1.00,
            
            // --- GRUPO MAGNETRÓN ---
            // Microondas: Si se usa, es al 100%
            'Microondas' => 1.00,
            
            // --- GRUPO ILUMINACIÓN ---
            // LEDs: Consumo constante cuando están encendidos
            'Lámpara LED 5W (Eq. 40W)' => 1.00,
            'Lámpara LED 9W (Eq. 60W)' => 1.00,
            'Lámpara LED 12W (Eq. 75W)' => 1.00,
            'Lámpara Bajo Consumo 20W' => 1.00,
            'Lámpara Halógena 40W' => 1.00,
            'Tubo Fluorescente 36W' => 1.00,
            'Tira LED (por metro)' => 1.00,
            
            // --- OTROS ---
            'Lavavajillas' => 0.60,
            'Secarropas por calor' => 1.00,
            'Secarropas centrífugo' => 1.00,
            'Humidificador' => 1.00,
            'Deshumidificador' => 0.70,
        ];

        $updated = 0;
        $notFound = [];

        foreach ($adjustments as $name => $factor) {
            $result = EquipmentType::where('name', $name)
                ->update(['load_factor' => $factor]);
            
            if ($result > 0) {
                $updated++;
                $this->command->info("  ✓ {$name}: {$factor}");
            } else {
                $notFound[] = $name;
            }
        }

        $this->command->info("\n✅ Factores de carga actualizados: {$updated} tipos de equipo");
        
        if (count($notFound) > 0) {
            $this->command->warn("\n⚠️  No se encontraron los siguientes equipos:");
            foreach ($notFound as $name) {
                $this->command->warn("  - {$name}");
            }
        }
        
        $this->command->info("\n📊 Resumen de cambios:");
        $this->command->info("  • Heladeras: 0.35-0.40 (ciclo de enfriamiento)");
        $this->command->info("  • Aires acondicionados: 0.40-0.50 (termostato/inverter)");
        $this->command->info("  • Lavarropas: 0.30-0.60 (ciclos de lavado)");
        $this->command->info("  • PCs: 0.40-0.60 (carga variable)");
        $this->command->info("  • Planchas: 0.60 (termostato)");
        $this->command->info("  • Calefacción: 0.70 (termostato)");
        $this->command->info("  • Resto: 1.00 (consumo continuo)");
    }
}
