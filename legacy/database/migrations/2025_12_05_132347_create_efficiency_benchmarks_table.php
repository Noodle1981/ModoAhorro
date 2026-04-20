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
        Schema::create('efficiency_benchmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_type_id')->constrained()->onDelete('cascade');
            $table->decimal('efficiency_gain_factor', 5, 2)->comment('Factor de ganancia (ej: 0.30 = 30% ahorro)');
            $table->decimal('average_market_price', 10, 2)->comment('Precio promedio en mercado');
            $table->string('meli_search_term')->comment('Término de búsqueda en Mercado Libre');
            $table->string('affiliate_link')->nullable()->comment('Link de afiliado generado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('efficiency_benchmarks');
    }
};
