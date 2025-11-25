<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Entity;

$entity = Entity::find(1);
if (!$entity) {
    echo "Entity with ID 1 not found.\n";
    exit(1);
}
$entity->square_meters = 450;
$entity->save();

echo "Entity ID 1 square_meters set to {$entity->square_meters}.\n";
?>
