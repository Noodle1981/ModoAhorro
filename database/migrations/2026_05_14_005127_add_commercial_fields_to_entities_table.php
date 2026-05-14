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
            $table->string('comercio_type')->nullable()->after('type');
            $table->integer('staff_count')->nullable()->after('people_count');
            $table->integer('visitors_count')->nullable()->after('staff_count');
            $table->integer('service_turns')->nullable()->after('visitors_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entities', function (Blueprint $table) {
            $table->dropColumn(['comercio_type', 'staff_count', 'visitors_count', 'service_turns']);
        });
    }
};
