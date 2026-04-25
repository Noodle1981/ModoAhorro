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
        Schema::table('equipment_types', function (Blueprint $table) {
            $table->string('usage_unit')->default('hours')->after('consumption_logic');
            $table->decimal('determinism_score', 3, 2)->default(0.5)->after('usage_unit');
            $table->decimal('social_coefficient', 8, 4)->default(0.0)->after('determinism_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_types', function (Blueprint $table) {
            //
        });
    }
};
