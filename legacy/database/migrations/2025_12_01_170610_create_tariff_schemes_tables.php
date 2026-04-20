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
        Schema::create('tariff_schemes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider');
            $table->timestamps();
        });

        Schema::create('tariff_bands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tariff_scheme_id')->constrained('tariff_schemes')->onDelete('cascade');
            $table->string('name'); // Pico, Valle, Resto
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('price_per_kwh', 10, 2);
            $table->boolean('is_weekend_applicable')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tariff_bands');
        Schema::dropIfExists('tariff_schemes');
    }
};
