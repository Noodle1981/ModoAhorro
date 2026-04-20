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
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('supply_number')->nullable()->change();
            $table->string('rate_name')->nullable()->change();
            $table->boolean('is_three_phase')->default(false)->after('rate_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->string('supply_number')->nullable(false)->change();
            $table->string('rate_name')->nullable(false)->change();
            $table->dropColumn('is_three_phase');
        });
    }
};
