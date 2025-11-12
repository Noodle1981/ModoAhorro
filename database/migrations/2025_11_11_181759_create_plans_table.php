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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Gratuito, Premium, etc.
            $table->text('features')->nullable(); // JSON o texto para escalabilidad
            $table->decimal('price', 8, 2)->default(0); // Precio del plan
            $table->unsignedInteger('max_entities')->default(1); // Límite de entidades por plan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
