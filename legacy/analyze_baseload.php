<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$invoiceId = 3;
$invoice = App\Models\Invoice::find($invoiceId);

if (!$invoice) {
    die("Invoice $invoiceId not found.");
}

$start = \Carbon\Carbon::parse($invoice->start_date);
$end = \Carbon\Carbon::parse($invoice->end_date);
$days = $start->diffInDays($end);
// Fix: Ensure at least 1 day
$days = max(1, $days);

echo "INVOICE #$invoiceId\n";
echo "Period: " . $start->format('d/m/Y') . " - " . $end->format('d/m/Y') . " ($days days)\n";
echo "------------------------------------------------\n";

$targetNames = ['Heladera con Freezer', 'Router WiFi'];
$usages = $invoice->equipmentUsages()->with('equipment.type')->get();

foreach ($usages as $usage) {
    if (in_array($usage->equipment->name, $targetNames)) {
        $eq = $usage->equipment;
        $type = $eq->type;
        
        $watts = $eq->nominal_power_w;
        $powerKw = $watts / 1000;
        $hours = 24; // Assuming full day for analysis
        
        // Manual Calculation
        // Raw Consumption = kW * h * d
        $rawConsumption = $powerKw * $hours * $days;
        
        // Load Factor logic (from Service)
        $loadFactor = $type->load_factor ?? 1.0;
        
        $finalConsumption = $rawConsumption * $loadFactor;
        
        echo "Equipment: " . $eq->name . "\n";
        echo "  Power: {$watts} W ($powerKw kW)\n";
        echo "  Load Factor: $loadFactor\n";
        echo "  Calculation: $powerKw kW * 24 h * $days days * $loadFactor (Load)\n";
        echo "  = " . number_format($finalConsumption, 2) . " kWh\n";
        echo "\n";
    }
}
