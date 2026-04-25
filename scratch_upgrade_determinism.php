<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EquipmentType;

$updates = [
    'Router Wifi' => [
        'usage_unit' => 'hours',
        'determinism_score' => 0.95, // Va al Tanque 0
        'consumption_logic' => 'BASE_LOAD',
    ],
    'Heladera' => [
        'determinism_score' => 0.90, // Va al Tanque 0 (es cíclico pero muy predecible)
    ],
    'Lavarropas' => [
        'usage_unit' => 'cycles',
        'determinism_score' => 0.70,
    ],
    'Microondas' => [
        'usage_unit' => 'people_proportional',
        'social_coefficient' => 0.05, // 0.05 kWh por persona al día
        'determinism_score' => 0.60,
    ],
    'Pava Electrica' => [
        'usage_unit' => 'people_proportional',
        'social_coefficient' => 0.15, // 0.15 kWh por persona al día (infusiones)
        'determinism_score' => 0.80,
    ]
];

foreach ($updates as $name => $data) {
    EquipmentType::where('name', $name)->update($data);
    echo "Actualizado determinismo para: $name\n";
}
