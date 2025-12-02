<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('equipment_usages', function (Blueprint $table) {
            if (!Schema::hasColumn('equipment_usages', 'consumption_kwh')) {
                $table->float('consumption_kwh')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('equipment_usages', function (Blueprint $table) {
            $table->dropColumn('consumption_kwh');
        });
    }
};
