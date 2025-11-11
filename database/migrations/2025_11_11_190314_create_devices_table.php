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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('equipment_type_id')->constrained('equipment_types')->onDelete('cascade');
            $table->unsignedInteger('quantity')->default(1);
            $table->string('custom_name')->nullable();
            $table->integer('power_watts_override')->nullable();
            $table->decimal('avg_daily_use_hours_override', 4, 2)->nullable();
            $table->boolean('standby_active')->default(false);
            $table->integer('standby_power_watts')->nullable();
            $table->boolean('is_active')->default(true); // Estado de uso
            $table->date('deactivation_date')->nullable(); // Fecha de baja
            $table->string('deactivation_reason')->nullable(); // Motivo de baja
            $table->foreignId('replaced_by_device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->foreignId('is_backup_for_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
