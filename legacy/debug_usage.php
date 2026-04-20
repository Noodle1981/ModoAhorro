<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$invoice = App\Models\Invoice::find(3);
if ($invoice) {
    $usages = $invoice->equipmentUsages()->with('equipment')->get();
    foreach ($usages as $usage) {
        $name = $usage->equipment->name;
        if (in_array($name, ['Heladera con Freezer', 'Router WiFi'])) {
            echo "Equipment: $name\n";
            echo "Avg Daily Use Hours: " . $usage->avg_daily_use_hours . "\n";
            echo "Usage Frequency: " . $usage->usage_frequency . "\n";
            echo "Use Days In Period: " . $usage->use_days_in_period . "\n";
            echo "----------------------------------------\n";
        }
    }
} else {
    echo "Invoice 3 not found.\n";
}
