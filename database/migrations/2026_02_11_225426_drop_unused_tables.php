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
        // Disable foreign key checks for SQLite compatibility
        Schema::disableForeignKeyConstraints();

        try {
            // Drop unused tables
            Schema::dropIfExists('device_usages');
            Schema::dropIfExists('devices');
            Schema::dropIfExists('utility_companies');
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
