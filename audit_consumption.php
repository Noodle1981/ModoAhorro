<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Invoice;
use App\Models\ClimateData;
use Carbon\Carbon;

// Load first invoice (or the one you want to audit)
$invoice = Invoice::first();
if (!$invoice) {
    echo "No invoice found in database.\n";
    exit(1);
}

// Calculate declared consumption (sum of equipment usage kWh)
$declared = $invoice->equipmentUsages->sum('consumption_kwh');

// Facturado (as stored in the invoice, assuming column `total_kwh` exists)
$facturado = $invoice->total_energy_consumed_kwh ?? null;

// Get locality from invoice (through contract -> entity -> locality)
$locality = $invoice->contract->entity->locality ?? null;
if ($locality && $locality->latitude && $locality->longitude) {
    $startDate = Carbon::parse($invoice->start_date);
    $endDate   = Carbon::parse($invoice->end_date);
    $climate   = ClimateData::where('latitude', $locality->latitude)
        ->where('longitude', $locality->longitude)
        ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
        ->get();
    $cddTotal = $climate->sum('cooling_degree_days');
    $hddTotal = $climate->sum('heating_degree_days');
    $avgTemp  = $climate->avg('temp_avg');
} else {
    $cddTotal = $hddTotal = $avgTemp = null;
}

$output = [
    'invoice_number' => $invoice->invoice_number ?? 'N/A',
    'period' => [
        'start' => $invoice->start_date,
        'end'   => $invoice->end_date,
    ],
    'facturado_kwh' => $facturado,
    'declared_kwh'  => $declared,
    'difference_percent' => $facturado ? round((($declared - $facturado) / $facturado) * 100, 2) : null,
    'climate' => [
        'cdd_total' => $cddTotal,
        'hdd_total' => $hddTotal,
        'avg_temp'  => $avgTemp,
    ],
];

echo json_encode($output, JSON_PRETTY_PRINT);
?>
