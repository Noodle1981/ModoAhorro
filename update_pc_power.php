<?php

use Illuminate\Contracts\Console\Kernel;
use App\Models\Equipment;
use App\Services\ConsumptionAnalysisService;
use App\Models\Invoice;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

// Update PC Gamer (ID 69 based on previous output)
$pc = Equipment::find(69);
if ($pc) {
    echo "Updating PC Gamer (ID 69)...\n";
    echo "Old Power: {$pc->nominal_power_w} W\n";
    
    $pc->nominal_power_w = 250;
    $pc->save();
    
    echo "New Power: {$pc->nominal_power_w} W\n";
    
    // Recalculate Invoice 2 consumption for this equipment
    $invoice = Invoice::find(2);
    $usage = $invoice->equipmentUsages()->where('equipment_id', 69)->first();
    
    if ($usage) {
        $service = $app->make(ConsumptionAnalysisService::class);
        $newKwh = $service->calculateEquipmentConsumption($usage, $invoice);
        echo "New Calculated Consumption (Invoice 2): {$newKwh} kWh\n";
    }
} else {
    echo "PC Gamer ID 69 not found.\n";
}
