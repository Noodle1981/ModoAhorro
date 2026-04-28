<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_types', function (Blueprint $table) {
            $table->decimal('energy_per_cycle', 8, 3)->nullable()->after('default_power_watts')
                  ->comment('Consumo en kWh por cada ciclo de uso (ej: 1.5 kWh por lavado)');
        });

        Schema::table('equipment_usages', function (Blueprint $table) {
            $table->decimal('cycles_per_period', 8, 2)->nullable()->after('usage_count')
                  ->comment('Cantidad de ciclos realizados en el periodo de la factura');
        });
    }

    public function down(): void
    {
        Schema::table('equipment_types', function (Blueprint $table) {
            $table->dropColumn('energy_per_cycle');
        });

        Schema::table('equipment_usages', function (Blueprint $table) {
            $table->dropColumn('cycles_per_period');
        });
    }
};
