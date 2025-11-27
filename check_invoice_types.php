<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Invoice;

echo "🔍 Verificando Tipos de Equipo en Factura #2\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$invoice = Invoice::with('equipmentUsages.equipment.type')->find(2);

if (!$invoice) {
    echo "❌ Factura #2 no encontrada\n";
    exit(1);
}

foreach ($invoice->equipmentUsages as $usage) {
    $equipment = $usage->equipment;
    $type = $equipment->type;
    
    $typeName = $type ? $type->name : '🔴 NULL';
    $loadFactor = $type ? $type->load_factor : 'N/A';
    
    echo "📦 {$equipment->name}\n";
    echo "   Tipo: {$typeName}\n";
    echo "   Load Factor: {$loadFactor}\n";
    echo "   -----------------------------------\n";
}
?>
