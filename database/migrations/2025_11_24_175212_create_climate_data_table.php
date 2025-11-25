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
        Schema::create('climate_data', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->decimal('latitude', 8, 6);
            $table->decimal('longitude', 9, 6);
            $table->decimal('temp_max', 4, 1)->comment('Temperatura máxima del día (°C)');
            $table->decimal('temp_min', 4, 1)->comment('Temperatura mínima del día (°C)');
            $table->decimal('temp_avg', 4, 1)->nullable()->comment('Temperatura promedio (°C)');
            $table->timestamps();
            
            // Evitar duplicados por fecha y ubicación
            $table->unique(['date', 'latitude', 'longitude']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('climate_data');
    }
};
