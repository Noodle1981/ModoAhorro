<?php

namespace Database\Seeders;

use App\Models\EquipmentCategory;
use App\Models\EquipmentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryRefinementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Definir y asegurar las nuevas categorías profesionales
        $categories = [
            'Climatización' => 'Control de temperatura ambiente (Aire, Estufas, Ventiladores).',
            'Refrigeración' => 'Conservación de alimentos 24hs (Heladeras, Freezers, Cavas).',
            'Agua Caliente (ACS)' => 'Producción de agua caliente sanitaria (Termotanques, Calderas).',
            'Cocina y Preparación' => 'Equipos para cocción y preparación de alimentos.',
            'Lavado y Limpieza' => 'Tratamiento de ropa y vajilla (Lavarropas, Lavavajillas, Secarropas).',
            'Iluminación' => 'Todo tipo de luminarias (LED, Incandescentes, Tubos).',
            'Entretenimiento y Multimedia' => 'TV, Consolas, Audio, Home Theatre.',
            'Informática y Oficina' => 'PCs, Monitores, Impresoras, Notebooks.',
            'Seguridad y Redes' => 'Modems, Routers, Alarmas, Cámaras de vigilancia.',
            'Salud y Cuidado Personal' => 'Secadores de pelo, Afeitadoras, Equipos de salud.',
            'Mantenimiento y Bombas' => 'Bombas de agua, riego, filtrado de piscina, herramientas.',
            'Otros' => 'Equipos de uso general no clasificados.',
        ];

        $catModels = [];
        foreach ($categories as $name => $desc) {
            $catModels[$name] = EquipmentCategory::updateOrCreate(
                ['name' => $name],
                ['description' => $desc]
            );
        }

        // 2. Mapeo inteligente de Equipos Existentes
        $mappings = [
            'Refrigeración' => ['heladera', 'freezer', 'cava', 'enfriador'],
            'Lavado y Limpieza' => ['lavarropa', 'secarropa', 'lavavajilla', 'aspiradora'],
            'Agua Caliente (ACS)' => ['termotanque', 'caldera', 'calefon', 'calentador'],
            'Seguridad y Redes' => ['modem', 'router', 'wifi', 'alarma', 'camara', 'portero', 'sensor'],
            'Salud y Cuidado Personal' => ['secador', 'afeitar', 'plancha de pelo', 'depiladora', 'nebulizador', 'tensio'],
            'Mantenimiento y Bombas' => ['bomba', 'piscina', 'riego', 'peltier', 'herramienta', 'taladro'],
            'Cocina y Preparación' => ['tostadora', 'cafetera', 'licuadora', 'batidora', 'microondas', 'horno', 'anafe', 'pava'],
            'Informática y Oficina' => ['pc', 'monitor', 'notebook', 'impresora', 'scanner', 'servidor'],
            'Entretenimiento y Multimedia' => ['tv', 'cine', 'audio', 'consola', 'playstation', 'xbox', 'parlante'],
            'Climatización' => ['aire', 'split', 'estufa', 'caloventor', 'ventilador', 'panel'],
        ];

        $types = EquipmentType::all();
        $count = 0;

        foreach ($types as $type) {
            $name = strtolower($type->name);
            $foundMapping = false;

            foreach ($mappings as $catName => $keywords) {
                foreach ($keywords as $keyword) {
                    if (str_contains($name, $keyword)) {
                        $type->category_id = $catModels[$catName]->id;
                        
                        // Lógica de flags de Tanques
                        if ($catName === 'Climatización') {
                            $type->is_climatization = true;
                        }
                        
                        $type->save();
                        $foundMapping = true;
                        $count++;
                        break 2;
                    }
                }
            }
        }

        // 3. Limpiar categorías viejas que ya no se usan (opcional, mejor dejar por ahora)
        // Pero vamos a unificar 'Oficina' con 'Informática y Oficina' si existe la vieja
        $oldOficina = EquipmentCategory::where('name', 'Oficina')->first();
        if ($oldOficina && $oldOficina->name !== 'Informática y Oficina') {
             EquipmentType::where('category_id', $oldOficina->id)->update(['category_id' => $catModels['Informática y Oficina']->id]);
        }

        $oldElectro = EquipmentCategory::where('name', 'Electrodomésticos')->first();
        if ($oldElectro) {
             // Los que quedaron en Electrodomésticos sin mapeo van a Cocina por defecto
             EquipmentType::where('category_id', $oldElectro->id)->update(['category_id' => $catModels['Cocina y Preparación']->id]);
        }

        echo "\nSe han re-clasificado {$count} tipos de equipos con éxito.\n";
    }
}
