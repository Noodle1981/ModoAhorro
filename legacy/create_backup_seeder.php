<?php
// Script temporal para volcar Casa 27 a un seeder
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Entity;
use App\Models\Room;
use App\Models\Equipment;
use App\Models\EquipmentUsage;
use App\Models\Invoice;
use App\Models\Contract;

$entityName = 'Casa 27';
$entity = Entity::where('name', $entityName)->first();

if (!$entity) {
    die("No se encontró la entidad $entityName\n");
}

$seederContent = "<?php\n\nnamespace Database\Seeders;\n\nuse Illuminate\Database\Seeder;\nuse App\Models\Entity;\nuse App\Models\User;\nuse App\Models\Contract;\nuse App\Models\Invoice;\nuse App\Models\Room;\nuse App\Models\Equipment;\nuse App\Models\EquipmentUsage;\nuse App\Models\Locality;\nuse App\Models\UtilityCompany;\nuse App\Models\Proveedor;\n\nclass BackupCasa27Seeder extends Seeder\n{\n    public function run(): void\n    {\n";

// 1. Entity
$eArr = $entity->toArray();
unset($eArr['id'], $eArr['created_at'], $eArr['updated_at']);
$seederContent .= "        \$entity = Entity::updateOrCreate(['name' => '{$entityName}'], " . var_export($eArr, true) . ");\n\n";

// 2. User association
$seederContent .= "        \$user = User::first();\n";
$seederContent .= "        if (!\$user->entities()->where('entity_id', \$entity->id)->exists()) {\n";
$seederContent .= "            \$user->entities()->attach(\$entity->id, ['plan_id' => 1, 'subscribed_at' => now()]);\n";
$seederContent .= "        }\n\n";

// 3. Contracts and Invoices
foreach ($entity->contracts as $contract) {
    $cArr = $contract->toArray();
    unset($cArr['id'], $cArr['entity_id'], $cArr['created_at'], $cArr['updated_at']);
    $seederContent .= "        \$contract = Contract::updateOrCreate(['contract_number' => '{$cArr['contract_number']}', 'entity_id' => \$entity->id], " . var_export($cArr, true) . ");\n\n";
    
    foreach ($contract->invoices as $invoice) {
        $iArr = $invoice->toArray();
        unset($iArr['id'], $iArr['contract_id'], $iArr['created_at'], $iArr['updated_at']);
        $seederContent .= "        \$invoice = Invoice::updateOrCreate(['invoice_number' => '{$iArr['invoice_number']}'], " . var_export($iArr, true) . " + ['contract_id' => \$contract->id]);\n\n";
    }
}

// 4. Rooms and Equipment
foreach ($entity->rooms as $room) {
    $rArr = $room->toArray();
    unset($rArr['id'], $rArr['entity_id'], $rArr['created_at'], $rArr['updated_at']);
    $seederContent .= "        \$room = Room::updateOrCreate(['name' => '{$rArr['name']}', 'entity_id' => \$entity->id], " . var_export($rArr, true) . ");\n\n";
    
    foreach ($room->equipment as $eq) {
        $eqArr = $eq->toArray();
        unset($eqArr['id'], $eqArr['room_id'], $eqArr['category_id'], $eqArr['type_id'], $eqArr['created_at'], $eqArr['updated_at']);
        
        $categoryName = $eq->category->name;
        $typeName = $eq->type->name ?? null;

        $seederContent .= "        \$category = \App\Models\EquipmentCategory::where('name', '{$categoryName}')->first();\n";
        if ($typeName) {
            $seederContent .= "        \$type = \App\Models\EquipmentType::where('name', '{$typeName}')->first();\n";
        } else {
            $seederContent .= "        \$type = null;\n";
        }
        
        $seederContent .= "        \$equipment = Equipment::create(" . var_export($eqArr, true) . " + [\n";
        $seederContent .= "            'room_id' => \$room->id, \n";
        $seederContent .= "            'category_id' => \$category->id, \n";
        $seederContent .= "            'type_id' => \$type ? \$type->id : null\n";
        $seederContent .= "        ]);\n\n";
        
        // Find usages for this equipment in invoices of this entity
        foreach ($entity->invoices as $inv) {
            $usage = EquipmentUsage::where('equipment_id', $eq->id)->where('invoice_id', $inv->id)->first();
            if ($usage) {
                $uArr = $usage->toArray();
                unset($uArr['id'], $uArr['equipment_id'], $uArr['invoice_id'], $uArr['created_at'], $uArr['updated_at']);
                $seederContent .= "        EquipmentUsage::create(" . var_export($uArr, true) . " + ['equipment_id' => \$equipment->id, 'invoice_id' => Invoice::where('invoice_number', '{$inv->invoice_number}')->first()->id]);\n\n";
            }
        }
    }
}

$seederContent .= "    }\n}\n";

file_put_contents('database/seeders/BackupCasa27Seeder.php', $seederContent);
echo "BackupCasa27Seeder generado exitosamente.\n";
