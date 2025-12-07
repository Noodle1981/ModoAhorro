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
        Schema::table('equipment', function (Blueprint $table) {
            $table->integer('acquisition_year')->nullable()->comment('Año de compra');
            $table->string('energy_label')->nullable()->comment('Etiqueta Energética: A+++, A, B, C, etc.');
            $table->boolean('is_inverter')->default(false)->comment('Tecnología Inverter');
            $table->decimal('capacity', 8, 2)->nullable()->comment('Capacidad: 3500, 8, 200');
            $table->string('capacity_unit')->nullable()->comment('Unidad: frigorias, kg, litros');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            //
        });
    }
};
