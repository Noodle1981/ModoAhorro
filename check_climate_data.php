<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\ClimateData;
$records = ClimateData::orderBy('date','desc')->take(5)->get();
echo $records->toJson(JSON_PRETTY_PRINT);
?>
