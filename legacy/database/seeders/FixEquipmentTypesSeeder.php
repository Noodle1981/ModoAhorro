<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\EquipmentType;

class FixEquipmentTypesSeeder extends Seeder
{
    /**
     * Asigna type_id a los equipos existentes basándose en su nombre.
     * Esto corrige el problema de que los equipos fueron creados sin type_id.
     */
    public function run()
    {
        $this->command->info('🔧 Asignando type_id a equipos existentes...');
        
        // Mapeo de palabras clave (en nombre de equipo) → nombre de tipo
        $mappings = [
            // Climatización
            'Aire Grande' => 'Aire Acondicionado (3500 frigorías)',
            'Aire Portatil' => 'Aire Acondicionado Portátil',
            'Ventilador de Techo' => 'Ventilador de techo',
            'Ventilador de Pie' => 'Ventilador de pie',
            
            // Electrodomésticos
            'Heladera' => 'Heladera con Freezer',
            'Lavarropa' => 'Lavarropas Automático (Agua fría)',
            
            // Cocina
            'Microondas' => 'Microondas',
            
            // Entretenimiento
            'TV Grande' => 'Televisor LED 50" 4K',
            'TV Chico' => 'Televisor LED 32"',
            
            // Oficina/Computación
            'PC Gamer' => 'PC de Escritorio (Gamer)',
            'Monitor PC' => 'Monitor LED 24"',
            'Notebook' => 'Notebook / Laptop',
            'Router Wifi' => 'Router WiFi',
            
            // Iluminación
            'Foco Ventilador' => 'Lámpara LED 5W (Eq. 40W)',
            'Foco Mesita de Luz' => 'Lámpara LED 5W (Eq. 40W)',
            'Foco Living' => 'Lámpara LED 5W (Eq. 40W)',
            'Foco Baño' => 'Lámpara LED 12W (Eq. 75W)',
            'Foco Led Grande' => 'Lámpara LED 12W (Eq. 75W)',
            'Focos Garage' => 'Lámpara LED 5W (Eq. 40W)',
            'Focos Ventilador' => 'Lámpara LED 5W (Eq. 40W)',
            'Foco' => 'Lámpara LED 5W (Eq. 40W)', // Genérico
            'Tubo Led Cocina' => 'Tubo Fluorescente 36W',
            'Mesita de Luz' => 'Lámpara LED 5W (Eq. 40W)',
            
            // Portátiles
            'Cargadores de Celular' => 'Cargador de Celular',
            
            // Otros
            'Secador de Pelo' => 'Secador de Pelo',
            'Maquina de Afeitar' => 'Afeitadora Eléctrica',
        ];
        
        $updated = 0;
        $notFound = [];
        
        foreach ($mappings as $equipmentNamePattern => $typeName) {
            // Buscar el tipo de equipo
            $type = EquipmentType::where('name', $typeName)->first();
            
            if ($type) {
                // Actualizar equipos que coincidan con el patrón
                $count = Equipment::where('name', 'LIKE', "%{$equipmentNamePattern}%")
                    ->whereNull('type_id')
                    ->update(['type_id' => $type->id]);
                
                if ($count > 0) {
                    $updated += $count;
                    $this->command->info("  ✓ {$equipmentNamePattern} → {$typeName} ({$count} equipos)");
                }
            } else {
                $notFound[] = $typeName;
            }
        }
        
        $this->command->info("\n✅ Equipos actualizados: {$updated}");
        
        // Mostrar equipos que aún no tienen type_id
        $remaining = Equipment::whereNull('type_id')->count();
        if ($remaining > 0) {
            $this->command->warn("\n⚠️  Equipos sin type_id: {$remaining}");
            $equipmentWithoutType = Equipment::whereNull('type_id')->pluck('name')->unique();
            foreach ($equipmentWithoutType as $name) {
                $this->command->warn("  - {$name}");
            }
        }
        
        if (count($notFound) > 0) {
            $this->command->warn("\n⚠️  Tipos de equipo no encontrados en la base de datos:");
            foreach (array_unique($notFound) as $name) {
                $this->command->warn("  - {$name}");
            }
        }
    }
}
