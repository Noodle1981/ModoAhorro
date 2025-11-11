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
        Schema::create('entities', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del hogar
            $table->string('type')->default('hogar'); // Tipo de entidad
            $table->string('address_street')->nullable();
            $table->string('address_postal_code')->nullable();
            $table->unsignedInteger('locality_id')->nullable();
            $table->text('description')->nullable();
            $table->float('square_meters')->nullable(); // Metros cuadrados
            $table->unsignedInteger('people_count')->nullable(); // Cantidad de personas
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entities');
    }
};
