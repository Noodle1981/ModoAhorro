<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('equipment_categories');
            $table->foreignId('type_id')->nullable()->constrained('equipment_types');
            $table->integer('nominal_power_w')->nullable();
            $table->boolean('is_standby')->default(false);
            $table->decimal('avg_daily_use_hours', 4, 2)->nullable();
            $table->integer('use_days_per_week')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('equipment');
    }
};
