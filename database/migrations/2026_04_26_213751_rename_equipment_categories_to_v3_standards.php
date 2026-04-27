<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $mappings = [
            '0. Conectividad y Seguridad (T0)' => 'Conectividad y Seguridad',
            '1. Refrigeración y Servidores (T1)' => 'Refrigeración y Servidores',
            '2. Climatización y Agua (T2)' => 'Climatización y Agua',
            '3. Línea Blanca y Limpieza (T3)' => 'Línea Blanca y Limpieza',
            '4. Informática y Ocio (T3)' => 'Informática y Ocio',
            '5. Cocina y Pequeños (T3)' => 'Cocina y Pequeños',
            '6. Iluminación y Otros (T3)' => 'Iluminación y Otros',
        ];

        foreach ($mappings as $old => $new) {
            DB::table('equipment_categories')
                ->where('name', $old)
                ->update(['name' => $new]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es estrictamente necesario volver atrás nombres complejos
    }
};
