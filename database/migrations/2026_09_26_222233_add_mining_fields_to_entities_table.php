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
            $table->string('camp_shift_type')->nullable()->after('service_turns');
            $table->unsignedInteger('camp_capacity')->nullable()->after('camp_shift_type');
            $table->string('module_type')->nullable()->after('camp_capacity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entities', function (Blueprint $table) {
            $table->dropColumn([
                'camp_shift_type',
                'camp_capacity',
                'module_type',
            ]);
        });
    }
};
