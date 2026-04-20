<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EquipmentType;

$types = EquipmentType::all();
foreach ($types as $type) {
    echo $type->name . PHP_EOL;
}
