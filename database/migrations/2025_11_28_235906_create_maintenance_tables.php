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
        Schema::create('maintenance_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_type_id')->constrained('equipment_types')->onDelete('cascade');
            $table->string('title');
            $table->integer('frequency_days')->nullable()->comment('Días para vencimiento. Null si es estacional puro');
            $table->integer('season_month')->nullable()->comment('Mes de disparo estacional (1-12)');
            $table->decimal('efficiency_impact', 5, 2)->default(0.10)->comment('Factor de penalización (ej: 0.10 = 10%)');
            $table->timestamps();
        });

        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->foreignId('maintenance_task_id')->constrained('maintenance_tasks')->onDelete('cascade');
            $table->timestamp('completed_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_logs');
        Schema::dropIfExists('maintenance_tasks');
    }
};
