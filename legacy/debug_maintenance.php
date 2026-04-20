<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$type = App\Models\EquipmentType::where('name', 'Router WiFi')->first();
if ($type) {
    echo "Equipment Type: {$type->name}\n";
    $tasks = $type->maintenanceTasks;
    foreach ($tasks as $task) {
        echo "Task: {$task->title}\n";
        echo "  Impact: {$task->efficiency_impact}\n";
        echo "  Frequency: {$task->frequency_days} days\n";
    }
    
    // Check specific equipment instance
    $invoice = App\Models\Invoice::find(3);
    $usage = $invoice->equipmentUsages()->whereHas('equipment', fn($q) => $q->where('name', 'Router WiFi'))->first();
    
    if ($usage) {
        $service = app(App\Services\MaintenanceService::class);
        $status = $service->checkStatus($usage->equipment);
        echo "\nInstance Status:\n";
        echo "  Penalty Factor: {$status['penalty_factor']}\n";
        print_r($status['pending_tasks']);
    }
} else {
    echo "Router WiFi type not found.\n";
}
