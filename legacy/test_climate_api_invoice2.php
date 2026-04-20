<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Invoice;
use App\Services\ConsumptionAnalysisService;
use App\Services\Climate\UsageSuggestionService;
use App\Services\Climate\ClimateDataService;

echo "🧪 Test de API Climática - Factura #2 (Otoño)\n\n";

$invoice = Invoice::with('equipmentUsages.equipment.category', 'equipmentUsages.equipment.type', 'contract.entity.locality')->find(2);

echo "Factura #2: {$invoice->start_date} - {$invoice->end_date}\n";
echo "Días totales: " . \Carbon\Carbon::parse($invoice->start_date)->diffInDays(\Carbon\Carbon::parse($invoice->end_date)) . "\n\n";

$climateService = new ClimateDataService();
$usageSuggestionService = new UsageSuggestionService($climateService);
$service = new ConsumptionAnalysisService($usageSuggestionService, $climateService);

echo "Equipos de climatización:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

foreach ($invoice->equipmentUsages as $usage) {
    $category = $usage->equipment->category->name ?? 'Sin categoría';
    
    if ($category === 'Climatización') {
        echo "\n📦 {$usage->equipment->name}\n";
        echo "   Categoría: {$category}\n";
        echo "   Días en período: {$usage->use_days_in_period}\n";
        echo "   Horas/día: {$usage->avg_daily_use_hours}\n";
        
        // Calcular consumo
        $consumption = $service->calculateEquipmentConsumption($usage, $invoice);
        echo "   Consumo calculado: {$consumption} kWh\n";
    }
}

echo "\n✅ Test completado\n";
?>
