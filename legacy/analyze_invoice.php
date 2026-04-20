<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $invoice_id = $argv[1] ?? 3;
    $invoice = \App\Models\Invoice::with(['contract.entity.locality', 'equipmentUsages.equipment.type'])->find($invoice_id);
    if (!$invoice) {
        echo json_encode(['error' => "Invoice $invoice_id not found"]);
        exit;
    }
    
    // Si la factura tiene un monto real, el motor lo usará. Si no, usará el teórico.

    $service = app(\App\Services\ConsumptionAnalysisService::class);
    $calibration = $service->calibrateInvoiceConsumption($invoice);
    
    $results = [];
    foreach($invoice->equipmentUsages as $usage) {
        $kwh = $service->calculateEquipmentConsumption($usage, $invoice);
        $results[$usage->equipment->name] = $kwh;
    }
    
    echo json_encode([
        'invoice_total_kwh' => $invoice->consumption_kwh,
        'calculated_total' => array_sum($results),
        'equipment_breakdown' => $results,
        'summary' => $calibration['summary'] ?? []
    ], JSON_PRETTY_PRINT);

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
