<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Prueba de ConsumptionCalibrator con Facturas Reales\n\n";

$service = app(App\Services\ConsumptionAnalysisService::class);

$invoices = App\Models\Invoice::with([
    'equipmentUsages.equipment.type',
    'equipmentUsages.equipment.category'
])->orderBy('start_date')->get();

foreach ($invoices as $invoice) {
    echo "=" . str_repeat("=", 70) . "\n";
    echo "📄 Factura #{$invoice->invoice_number}\n";
    echo "   Período: {$invoice->start_date} → {$invoice->end_date}\n";
    echo "   Facturado: {$invoice->total_energy_consumed_kwh} kWh\n\n";

    // Obtener consumos calibrados
    $calibrated = $service->calibrateInvoiceConsumption($invoice);

    $totalCalibrated = $calibrated->sum('kwh_reconciled');
    $totalEstimated = $calibrated->sum('kwh_estimated');

    echo "   Estimado (sin calibrar): " . round($totalEstimated, 2) . " kWh\n";
    echo "   Calibrado: " . round($totalCalibrated, 2) . " kWh\n";
    echo "   Precisión: " . round(($totalCalibrated / $invoice->total_energy_consumed_kwh) * 100, 2) . "%\n\n";

    // Mostrar top 5 consumidores calibrados
    echo "   Top 5 consumidores (calibrados):\n";
    $top5 = $calibrated->sortByDesc('kwh_reconciled')->take(5);
    foreach ($top5 as $usage) {
        $name = $usage->equipment->name;
        $category = $usage->equipment->category->name ?? 'N/A';
        $estimated = round($usage->kwh_estimated, 2);
        $reconciled = round($usage->kwh_reconciled, 2);
        $status = $usage->calibration_status ?? 'N/A';

        echo "     • {$name} ({$category})\n";
        echo "       Estimado: {$estimated} kWh → Calibrado: {$reconciled} kWh [{$status}]\n";
    }
    echo "\n";
}
