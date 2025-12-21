<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Verificación de Límites Máximos en Calibración\n\n";

$service = app(App\Services\ConsumptionAnalysisService::class);

// Factura de verano (624 kWh) - la que distribuye más consumo
$invoice = App\Models\Invoice::where('invoice_number', '138579184')->first();

echo "📄 Factura #{$invoice->invoice_number} ({$invoice->total_energy_consumed_kwh} kWh)\n";
echo "   Período: {$invoice->start_date} → {$invoice->end_date}\n\n";

$calibrated = $service->calibrateInvoiceConsumption($invoice);

echo "🔍 Equipos con SOBREASIGNACIÓN (calibrado > estimado):\n\n";

$violations = [];

foreach ($calibrated as $usage) {
    $estimated = $usage->kwh_estimated;
    $reconciled = $usage->kwh_reconciled;

    if ($reconciled > $estimated * 1.01) { // Margen 1% por redondeo
        $violations[] = $usage;

        $name = $usage->equipment->name;
        $category = $usage->equipment->category->name ?? 'N/A';
        $power = $usage->equipment->nominal_power_w;
        $hours = $usage->avg_daily_use_hours;
        $days = $usage->use_days_in_period;

        $maxTheorical = ($power / 1000) * $hours * $days; // Sin load_factor

        echo "  ⚠️  {$name} ({$category})\n";
        echo "      Potencia: {$power} W\n";
        echo "      Uso declarado: {$hours} h/día × {$days} días\n";
        echo "      Estimado: " . round($estimated, 2) . " kWh\n";
        echo "      Calibrado: " . round($reconciled, 2) . " kWh\n";
        echo "      Máximo teórico: " . round($maxTheorical, 2) . " kWh\n";

        if ($reconciled > $maxTheorical) {
            echo "      ❌ VIOLACIÓN: Calibrado > Máximo teórico\n";
        } else {
            echo "      ✅ OK: Calibrado < Máximo teórico\n";
        }
        echo "\n";
    }
}

if (empty($violations)) {
    echo "  ✅ No se encontraron sobreasignaciones\n";
}

echo "\n🔍 Focos LED específicamente:\n\n";

$leds = $calibrated->filter(function ($u) {
    $name = strtolower($u->equipment->name);
    return stripos($name, 'foco') !== false || stripos($name, 'led') !== false || stripos($name, 'tubo') !== false;
});

foreach ($leds as $usage) {
    $name = $usage->equipment->name;
    $power = $usage->equipment->nominal_power_w;
    $hours = $usage->avg_daily_use_hours;
    $days = $usage->use_days_in_period;
    $estimated = $usage->kwh_estimated;
    $reconciled = $usage->kwh_reconciled;

    $maxTheorical = ($power / 1000) * $hours * $days;

    echo "  • {$name} ({$power}W)\n";
    echo "    {$hours}h/día × {$days} días\n";
    echo "    Estimado: " . round($estimated, 3) . " kWh\n";
    echo "    Calibrado: " . round($reconciled, 3) . " kWh\n";
    echo "    Máximo: " . round($maxTheorical, 3) . " kWh\n";

    if ($reconciled > $maxTheorical) {
        echo "    ❌ PROBLEMA: Excede máximo teórico\n";
    } else {
        echo "    ✅ OK\n";
    }
    echo "\n";
}
