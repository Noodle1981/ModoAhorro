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
        Schema::create('device_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('devices')->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->decimal('consumption_kwh', 10, 3)->nullable(); // Consumo estimado en el periodo
            $table->unsignedInteger('usage_days')->nullable(); // Días de uso en el periodo
            $table->unsignedInteger('usage_hours_per_day')->nullable(); // Horas de uso por día
            $table->string('periodicity')->nullable(); // Ejemplo: diario, semanal
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_usages');
    }
};
