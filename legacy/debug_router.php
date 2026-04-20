<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$invoiceId = 3;
$invoice = App\Models\Invoice::find($invoiceId);
$service = app(App\Services\ConsumptionAnalysisService::class);

echo "========== DEBUG ROUTER ==========\n";
$usages = $invoice->equipmentUsages()->with('equipment.type')->get();
foreach ($usages as $usage) {
    if (str_contains(strtolower($usage->equipment->name), 'router')) {
        echo "Equipment: " . $usage->equipment->name . "\n";
        echo "Nominal Power: " . $usage->equipment->nominal_power_w . " W\n";
        echo "Usage Hours: " . $usage->avg_daily_use_hours . "\n";
        echo "Days: " . $usage->use_days_in_period . "\n";
        echo "Load Factor (Type): " . ($usage->equipment->type->load_factor ?? 'N/A') . "\n";
        echo "Is Standby: " . ($usage->equipment->is_standby ? 'Yes' : 'No') . "\n";
        
        // Calculate manually
        $powerKw = $usage->equipment->nominal_power_w / 1000;
        $hours = $usage->avg_daily_use_hours;
        $days = $usage->use_days_in_period;
        $load = $usage->equipment->type->load_factor ?? 1.0;
        
        $calc = $powerKw * $hours * $days * $load;
        echo "Manual Calc: $calc kWh\n";
        
        echo "Service Calc: " . $service->calculateEquipmentConsumption($usage, $invoice) . " kWh\n";
        
        echo "Reconciled (DB): " . $usage->kwh_reconciled . " kWh\n";
        
        if ($usage->equipment->is_standby) {
            $standbyHours = max(0, 24 - $hours);
            $standbyPower = ($usage->equipment->type->default_standby_power_w ?? 0) / 1000;
            $standbyCalc = $standbyPower * $standbyHours * $days;
            echo "Standby Calc: $standbyCalc kWh (Hours: $standbyHours, Power: $standbyPower kW)\n";
        }
    }
}
