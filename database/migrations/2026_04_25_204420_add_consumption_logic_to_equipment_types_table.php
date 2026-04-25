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
            $table->string('consumption_logic')->default('CONSTANT_ELASTIC')->after('name');
            $table->boolean('is_inverter_capable')->default(false)->after('is_climatization');
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
