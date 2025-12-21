<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Verificación de Consumo Calculado vs Facturado\n\n";

$invoices = App\Models\Invoice::with([
    'equipmentUsages.equipment.type',
    'equipmentUsages.equipment.category'
])->get();

$service = app(App\Services\ConsumptionAnalysisService::class);

foreach ($invoices as $invoice) {
    $calculated = $service->calculateInvoiceConsumption($invoice);
    $totalCalculated = array_sum($calculated);
    $facturado = $invoice->total_energy_consumed_kwh;

    if ($facturado > 0) {
        $diff = round((($totalCalculated / $facturado) - 1) * 100, 2);
        $status = ($totalCalculated >= $facturado * 0.85 && $totalCalculated <= $facturado * 1.15) ? '✅' : '❌';

        echo "Factura #{$invoice->invoice_number}\n";
        echo "  Facturado: {$facturado} kWh\n";
        echo "  Calculado: " . round($totalCalculated, 2) . " kWh\n";
        echo "  Diferencia: {$diff}% {$status}\n\n";
    }
}
