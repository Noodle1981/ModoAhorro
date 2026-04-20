<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Tablas que queremos CONSERVAR (ModoAhorro y Laravel Core)
$tablesToKeep = [
    'migrations',
    'users',
    'password_reset_tokens',
    'sessions',
    'cache',
    'cache_locks',
    'jobs',
    'job_batches',
    'failed_jobs',
    'personal_access_tokens',
    'entities',
    'entity_user',
    'plans',
    'rooms',
    'localities',
    'provinces',
    'proveedores',
    'utility_companies',
    'contracts',
    'invoices',
    'devices',
    'device_usages',
    'equipment',
    'equipment_categories',
    'equipment_types',
    'equipment_usages',
    'equipment_history',
    'usage_adjustments',
    'climate_data',
    'maintenance_logs',
    'maintenance_schedules',
    'efficiency_benchmarks',
    'sqlite_sequence', // Interna de SQLite
];

// Obtener todas las tablas actuales
$allTables = DB::select("SELECT name FROM sqlite_master WHERE type='table'");

$dropped = [];
$skipped = [];

foreach ($allTables as $table) {
    if (!in_array($table->name, $tablesToKeep)) {
        Schema::dropIfExists($table->name);
        $dropped[] = $table->name;
    } else {
        $skipped[] = $table->name;
    }
}

echo "Limpieza finalizada.\n";
echo "Tablas ELIMINADAS: " . count($dropped) . "\n";
foreach($dropped as $t) echo " - $t\n";
echo "Tablas CONSERVADAS: " . count($skipped) . "\n";

// Ejecutar Vacuum para reclamar espacio
DB::statement('VACUUM');
echo "Base de datos optimizada (VACUUM).\n";
