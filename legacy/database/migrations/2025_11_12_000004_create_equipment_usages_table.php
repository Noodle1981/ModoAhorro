<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('equipment_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->boolean('is_standby')->default(false);
            $table->decimal('avg_daily_use_hours', 4, 2)->nullable();
            $table->integer('use_days_in_period')->nullable();
            $table->decimal('consumption_kwh', 10, 2)->nullable();
            $table->decimal('climate_adjustment_percent', 5, 2)->nullable();
            $table->timestamps();
        });
    }
    public function down() {
        Schema::dropIfExists('equipment_usages');
    }
};
