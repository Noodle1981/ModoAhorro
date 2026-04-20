<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('equipment_types', function (Blueprint $table) {
            $table->string('process_type')->nullable()->after('name')->comment('Tipo de proceso: Motor, Resistencia, Electrónico, etc.');
            $table->decimal('load_factor', 3, 2)->nullable()->default(1.0)->after('process_type')->comment('Factor de carga (duty cycle)');
            $table->decimal('efficiency', 3, 2)->nullable()->default(1.0)->after('load_factor')->comment('Eficiencia del equipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_types', function (Blueprint $table) {
            $table->dropColumn(['process_type', 'load_factor', 'efficiency']);
        });
    }
};
