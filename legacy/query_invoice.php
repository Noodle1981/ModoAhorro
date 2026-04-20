<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Invoice;
use App\Services\ConsumptionAnalysisService;

$service = $app->make(ConsumptionAnalysisService::class);
$invoice = Invoice::find(1);

if ($invoice) {
    echo "--- CALIBRATED DATA FOR INVOICE 1 (#{$invoice->invoice_number}) ---" . PHP_EOL;
    echo "Period: " . $invoice->start_date . " to " . $invoice->end_date . PHP_EOL;
    echo "Total Factura (kWh): " . $invoice->total_energy_consumed_kwh . PHP_EOL;
    echo PHP_EOL;

    $calibratedUsages = $service->calibrateInvoiceConsumption($invoice);

    echo sprintf("| %-25s | %-10s | %-10s | %-10s | %-20s |" . PHP_EOL, "Equipo", "Potencia", "Hrs Declara", "kWh Calib", "Nota Ajuste");
    echo str_repeat("-", 85) . PHP_EOL;

    foreach ($calibratedUsages as $usage) {
        $name = $usage->equipment->name;
        $power = ($usage->equipment->nominal_power_w ?? 0) . "W";
        $hours = $usage->avg_daily_use_hours;
        $kwh = round($usage->kwh_reconciled, 2);
        $note = $usage->adjustment_note;

        echo sprintf("| %-25s | %-10s | %-10s | %-10s | %-20s |" . PHP_EOL, $name, $power, $hours, $kwh, $note);
    }
} else {
    echo "Invoice 1 not found." . PHP_EOL;
}
