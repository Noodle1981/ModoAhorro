<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Equipment;
use App\Models\Invoice;
use App\Services\ConsumptionAnalysisService;
use App\Services\Climate\UsageSuggestionService;
use App\Services\Climate\ClimateDataService;

echo "🔍 Verificación Lavarropa (ID: 33) - Factura #2\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$equipment = Equipment::with('type')->find(33);
$invoice = Invoice::find(2);
$usage = $invoice->equipmentUsages()->where('equipment_id', 33)->first();

if (!$equipment || !$usage) {
    echo "❌ No se encontró el equipo o el uso.\n";
    exit;
}

echo "📦 Equipo: {$equipment->name}\n";
echo "⚡ Potencia Nominal: {$equipment->nominal_power_w} W\n";
echo "🏷️  Tipo: {$equipment->type->name}\n";
echo "⚙️  Load Factor: {$equipment->type->load_factor}\n";
echo "-----------------------------------\n";
echo "📅 Uso Registrado:\n";
echo "   • Horas/día: {$usage->avg_daily_use_hours}\n";
echo "   • Días/semana: {$usage->use_days_per_week}\n";
echo "   • Días en periodo: {$usage->use_days_in_period}\n";

// Calcular consumo
$climateService = new ClimateDataService();
$usageSuggestionService = new UsageSuggestionService($climateService);
$service = new ConsumptionAnalysisService($usageSuggestionService, $climateService);

$consumption = $service->calculateEquipmentConsumption($usage, $invoice);

echo "-----------------------------------\n";
echo "🧮 Consumo Calculado: {$consumption} kWh\n";
echo "   (Fórmula: " . ($equipment->nominal_power_w/1000) . " kW * {$usage->avg_daily_use_hours}h * {$usage->use_days_in_period}d * {$equipment->type->load_factor})\n";
?>
