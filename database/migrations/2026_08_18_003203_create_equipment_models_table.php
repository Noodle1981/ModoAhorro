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
        Schema::create('equipment_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('equipment_categories')->nullOnDelete();
            $table->foreignId('type_id')->nullable()->constrained('equipment_types')->nullOnDelete();
            $table->string('brand')->index();
            $table->string('model')->index();
            $table->integer('nominal_power_w')->default(0);
            $table->boolean('is_inverter')->default(false);
            $table->string('energy_label')->nullable();
            $table->decimal('capacity', 8, 2)->nullable();
            $table->string('capacity_unit')->nullable();
            $table->json('extra_attributes')->nullable();
            $table->boolean('is_verified')->default(true)->index();
            $table->integer('occurrences_count')->default(1);
            $table->string('source')->default('ADMIN_MANUAL');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['brand', 'model']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_models');
    }
};
