<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EquipmentType;

$updates = [
    'Aire Grande' => [
        'consumption_logic' => 'CLIMATE_DEPENDENT',
        'is_inverter_capable' => true,
    ],
    'Aire Portatil' => [
        'consumption_logic' => 'CLIMATE_INEFFICIENT',
        'is_inverter_capable' => false,
    ],
    'Ventilador de Techo' => [
        'consumption_logic' => 'CONSTANT_ELASTIC',
        'is_inverter_capable' => false,
    ],
    'Heladera' => [
        'consumption_logic' => 'BASE_LOAD',
        'is_inverter_capable' => true,
    ],
    'Router Wifi' => [
        'consumption_logic' => 'BASE_LOAD',
        'is_inverter_capable' => false,
    ],
    'Lavarropas' => [
        'consumption_logic' => 'CONSTANT_ELASTIC',
        'is_inverter_capable' => true,
    ]
];

foreach ($updates as $name => $data) {
    EquipmentType::where('name', $name)->update($data);
    echo "Actualizado logic para: $name\n";
}
