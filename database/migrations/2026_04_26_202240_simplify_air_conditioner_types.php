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
        $typeInverter = DB::table('equipment_types')->where('name', 'Aire Split Inverter')->first();
        $typeOnOff = DB::table('equipment_types')->where('name', 'Aire Split (On/Off)')->first();

        if ($typeInverter && $typeOnOff) {
            // 1. Mover equipos del tipo Inverter al tipo On/Off y marcar el flag is_inverter
            DB::table('equipment')
                ->where('type_id', $typeInverter->id)
                ->update([
                    'type_id' => $typeOnOff->id,
                    'is_inverter' => true
                ]);

            // 2. Eliminar el tipo Inverter
            DB::table('equipment_types')->where('id', $typeInverter->id)->delete();

            // 3. Renombrar el tipo On/Off a un nombre genérico
            DB::table('equipment_types')->where('id', $typeOnOff->id)->update(['name' => 'Aire Split']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversión manual si fuera necesaria, pero en desarrollo solemos avanzar.
    }
};
