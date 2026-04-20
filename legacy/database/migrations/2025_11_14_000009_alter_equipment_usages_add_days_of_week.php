<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('equipment_usages', function (Blueprint $table) {
            $table->string('use_days_of_week')->nullable()->after('use_days_in_period'); // Ejemplo: "L,M,M,J,V,S,D"
        });
    }
    public function down() {
        Schema::table('equipment_usages', function (Blueprint $table) {
            $table->dropColumn('use_days_of_week');
        });
    }
};
