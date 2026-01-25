<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Entity;
use App\Models\User;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Room;
use App\Models\Equipment;
use App\Models\EquipmentUsage;
use App\Models\Locality;
use Carbon\Carbon;

$content = file_get_contents('datoshogar.txt');
$lines = explode("\n", $content);

// 1. Create User if not exists
$user = User::firstOrCreate(['email' => 'test@example.com'], [
    'name' => 'Test User',
    'password' => bcrypt('password')
]);

// 2. Localidad (Capital de San Juan)
$locality = Locality::where('name', 'Capital')->first();

// 3. Entity
$entity = Entity::updateOrCreate(['name' => 'Casa 27'], [
    'type' => 'hogar',
    'address_street' => 'Calle Carlos Gardel Casa 27 B° Enoe Bravo',
    'address_postal_code' => '5300',
    'locality_id' => $locality->id,
    'description' => 'Casa de prueba',
    'square_meters' => 450,
    'people_count' => 4,
]);

if (!$user->entities()->where('entity_id', $entity->id)->exists()) {
    $user->entities()->attach($entity->id, ['plan_id' => 1, 'subscribed_at' => now()]);
}

// 4. Contract
$contract = Contract::updateOrCreate(['contract_number' => '36697'], [
    'entity_id' => $entity->id,
    'proveedor_id' => \App\Models\Proveedor::firstOrCreate(['name' => 'Naturgy'])->id,
    'utility_company_id' => \App\Models\UtilityCompany::firstOrCreate(['name' => 'Naturgy'])->id,
    'client_number' => '07182202700',
    'supply_number' => '07182202700',
    'meter_number' => '9618495',
    'rate_name' => 'T1-R1',
    'tariff_type' => 'T1-R1',
    'start_date' => Carbon::createFromFormat('d/m/Y', '15/01/2025')->subYear(), // Estm
]);

// 5. Invoices (Parsing facts)
$invoices = [];
$invoiceLines = [];
$currentIdx = -1;

foreach ($lines as $line) {
    if (preg_match('/factura (\d+)/i', $line)) {
        $currentIdx++;
        $invoices[$currentIdx] = [];
        continue;
    }
    if ($currentIdx < 0) continue;

    if (preg_match('/Factura\s*(\d+)/i', $line, $m)) $invoices[$currentIdx]['number'] = $m[1];
    if (preg_match('/(\d{2}\/\d{2}\/\d{4})/i', $line, $m) && !isset($invoices[$currentIdx]['issue_date'])) {
        $invoices[$currentIdx]['issue_date'] = Carbon::createFromFormat('d/m/Y', $m[1])->format('Y-m-d');
    }
    if (preg_match('/(\d{2}\/\d{2}\/\d{4})\s*a\s*(\d{2}\/\d{2}\/\d{4})/i', $line, $m)) {
        $invoices[$currentIdx]['start_date'] = Carbon::createFromFormat('d/m/Y', $m[1])->format('Y-m-d');
        $invoices[$currentIdx]['end_date'] = Carbon::createFromFormat('d/m/Y', $m[2])->format('Y-m-d');
    }
    // Handle split lines
    if (preg_match('/([\d\.,]+)\s*kWh/i', $line, $m)) $invoices[$currentIdx]['kwh'] = str_replace(',', '', $m[1]);
    if (preg_match('/Energía\s*([\d\.,]+)/i', $line, $m)) $invoices[$currentIdx]['energy_cost'] = str_replace(',', '', $m[1]);
    if (preg_match('/impuestos\s*([\d\.,]+)/i', $line, $m)) $invoices[$currentIdx]['taxes'] = str_replace(',', '', $m[1]);
    if (preg_match('/Total\s*([\d\.,]+)/i', $line, $m)) $invoices[$currentIdx]['total'] = str_replace(',', '', $m[1]);
    
    // Fallback for some values that might be just on the next line
    if (preg_match('/^([\d\.]+)$/', trim($line), $m)) {
        // This is tricky without state, but let's assume if we see a number after a label...
    }
}

$dbInvoices = [];
foreach ($invoices as $inv) {
    if (!isset($inv['number'])) continue;
    $dbInvoices[$inv['number']] = Invoice::updateOrCreate(['invoice_number' => $inv['number']], [
        'contract_id' => $contract->id,
        'issue_date' => $inv['issue_date'],
        'start_date' => $inv['start_date'],
        'end_date' => $inv['end_date'],
        'total_energy_consumed_kwh' => $inv['kwh'],
        'cost_for_energy' => $inv['energy_cost'],
        'taxes' => $inv['taxes'],
        'total_amount' => $inv['total'],
        'status' => 'paid',
    ]);
}

// 6. Rooms
$roomsSection = false;
$rooms = [];
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === 'rooms') { $roomsSection = true; continue; }
    if ($roomsSection && empty($line)) { $roomsSection = false; continue; }
    if ($roomsSection) {
        $rooms[$line] = Room::firstOrCreate(['name' => $line, 'entity_id' => $entity->id]);
    }
}
$rooms['Portátiles'] = Room::firstOrCreate(['name' => 'Portátiles', 'entity_id' => $entity->id]);

// 7. Equipment and Usages
$eqSection = false;
foreach ($lines as $line) {
    if (preg_match('/Equipo/i', $line)) { $eqSection = true; continue; }
    if ($eqSection && empty(trim($line))) continue;
    if ($eqSection) {
        $parts = explode("\t", trim($line));
        if (count($parts) < 3) continue;
        
        $name = trim($parts[0]);
        $categoryName = trim($parts[1]);
        $roomName = trim($parts[2]);
        $power = isset($parts[3]) ? (int)trim($parts[3]) : 0;
        
        $room = $rooms[$roomName] ?? null;
        if (!$room) continue;

        $category = \App\Models\EquipmentCategory::where('name', $categoryName)->first();
        
        // Find Type
        $typeName = null;
        $typeMap = [
            'Aire Grande' => 'Aire Acondicionado (3500 frigorías)',
            'Aire Portatil' => 'Aire Acondicionado Portátil',
            'Ventilador de Techo' => 'Ventilador de techo',
            'Microondas' => 'Microondas',
            'TV Grande' => 'Televisor LED 50" 4K',
            'Router Wifi' => 'Modem / Router WiFi',
            // ... add more if needed, or use generic
        ];
        $typeName = $typeMap[$name] ?? null;
        $type = $typeName ? \App\Models\EquipmentType::where('name', $typeName)->first() : null;

        $equipment = Equipment::create([
            'name' => $name,
            'room_id' => $room->id,
            'category_id' => $category ? $category->id : 1,
            'type_id' => $type ? $type->id : null,
            'nominal_power_w' => $power ?: ($type ? $type->default_power_watts : 0),
            'is_active' => true,
        ]);

        // Creating generic usages for all invoices to keep data consistent
        foreach ($dbInvoices as $inv) {
            EquipmentUsage::create([
                'equipment_id' => $equipment->id,
                'invoice_id' => $inv->id,
                'avg_daily_use_hours' => ($name == 'Heladera' || $name == 'Router Wifi') ? 24 : 2,
                'use_days_in_period' => 60,
                'usage_frequency' => 'diario',
            ]);
        }
    }
}

echo "Restauración completada.\n";
