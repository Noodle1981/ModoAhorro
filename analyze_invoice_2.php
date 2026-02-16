<?php

use Illuminate\Contracts\Console\Kernel;
use App\Models\Invoice;
use App\Models\EquipmentUsage;
use App\Models\Equipment;
use App\Services\ConsumptionAnalysisService; // To double check calculation logic if needed

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

// Get the service instance properly resolved
$consumptionService = $app->make(ConsumptionAnalysisService::class);

$invoiceId = 2; // Target Invoice
$invoice = Invoice::with('contract.entity')->find($invoiceId);

if (!$invoice) {
    echo "Invoice $invoiceId not found.\n";
    exit;
}

echo "Analyzing Invoice ID: $invoiceId\n";
echo "Period: {$invoice->start_date} to {$invoice->end_date}\n";
echo "Billed Consumption: {$invoice->total_energy_consumed_kwh} kWh\n";

// Get usages
$usages = EquipmentUsage::where('invoice_id', $invoiceId)
    ->with(['equipment.category', 'equipment.type', 'equipment.room'])
    ->get();

$totalCalculated = 0;
$breakdown = [];

foreach ($usages as $usage) {
    // We'll use the service to get the exact value being calculated
    $kwh = $consumptionService->calculateEquipmentConsumption($usage, $invoice);
    $totalCalculated += $kwh;
    
    $breakdown[] = [
        'id' => $usage->id,
        'eq_id' => $usage->equipment_id,
        'name' => $usage->equipment->name ?? 'Unknown',
        'room' => $usage->equipment->room->name ?? 'N/A',
        'category' => $usage->equipment->category->name ?? 'N/A',
        'kwh' => $kwh,
        'hours' => $usage->avg_daily_use_hours,
        'freq' => $usage->usage_frequency,
        'effective_days' => $usage->use_days_in_period, // raw days stored
    ];
}

echo "Total Calculated: " . number_format($totalCalculated, 2) . " kWh\n\n";
echo "Top Consumers:\n";

// Sort by kWh desc
usort($breakdown, function($a, $b) {
    return $b['kwh'] <=> $a['kwh'];
});

foreach (array_slice($breakdown, 0, 10) as $item) {
    echo sprintf(
        "%-30s | %-15s | %-10s | %6.2f kWh | %s h/day | Freq: %s\n",
        substr($item['name'], 0, 30),
        substr($item['room'], 0, 15),
        substr($item['category'], 0, 10),
        $item['kwh'],
        $item['hours'],
        $item['freq']
    );
}

echo "\nNote: Showing top 10 consumers only.\n";
