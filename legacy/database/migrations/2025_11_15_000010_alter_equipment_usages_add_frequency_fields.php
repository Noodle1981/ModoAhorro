<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('equipment_usages', function (Blueprint $table) {
            $table->string('usage_frequency')->nullable()->after('use_days_in_period'); // diario, semanal, quincenal, mensual, puntual
            $table->integer('usage_count')->nullable()->after('usage_frequency'); // cantidad de usos en el periodo
            $table->float('avg_use_duration')->nullable()->after('usage_count'); // duración promedio por uso (horas)
        });
    }
    public function down() {
        Schema::table('equipment_usages', function (Blueprint $table) {
            $table->dropColumn(['usage_frequency', 'usage_count', 'avg_use_duration']);
        });
    }
};
