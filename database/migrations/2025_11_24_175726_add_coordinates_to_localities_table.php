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
        Schema::table('localities', function (Blueprint $table) {
            $table->decimal('latitude', 8, 6)->nullable()->after('postal_code')->comment('Latitud para API de clima');
            $table->decimal('longitude', 9, 6)->nullable()->after('latitude')->comment('Longitud para API de clima');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('localities', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
