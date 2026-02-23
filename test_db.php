<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$invoice = \App\Models\Invoice::with('contract')->find(2);
$service = app(\App\Services\ConsumptionAnalysisService::class);
$result = $service->calibrateInvoiceConsumption($invoice);
echo json_encode($result['summary'], JSON_PRETTY_PRINT);
