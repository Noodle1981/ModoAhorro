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
        Schema::table('entities', function (Blueprint $table) {
            $table->integer('construction_year')->nullable()->after('people_count');
            $table->string('usage_type')->default('residencial')->after('type');
            $table->boolean('has_gas')->default(false)->after('construction_year');
            $table->boolean('has_solar')->default(false)->after('has_gas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entities', function (Blueprint $table) {
            $table->dropColumn(['construction_year', 'usage_type', 'has_gas', 'has_solar']);
        });
    }
};
