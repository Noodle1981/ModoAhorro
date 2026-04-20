<?php
// ------------------------------------------------------------
// check_entity_square.php
// ------------------------------------------------------------
// Uso: php check_entity_square.php
// ------------------------------------------------------------

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Entity;

// ------------------------------------------------------------
// 1️⃣  Buscar la entidad
// ------------------------------------------------------------
$entity = Entity::find(1);

if (!$entity) {
    // No existe la entidad con ID 1
    fwrite(STDERR, "❌  Entity with ID 1 not found.\n");
    exit(1);
}

// ------------------------------------------------------------
// 2️⃣  Obtener el valor de square_meters
// ------------------------------------------------------------
$squareMeters = $entity->square_meters;

// ------------------------------------------------------------
// 3️⃣  Mostrar resultado (con manejo de null)
// ------------------------------------------------------------
if (is_null($squareMeters)) {
    echo "⚠️  square_meters is NULL for Entity ID 1.\n";
} else {
    echo "✅  Entity ID 1 – square_meters = {$squareMeters}\n";
}

// ------------------------------------------------------------
// 4️⃣  Capturar cualquier excepción inesperada
// ------------------------------------------------------------
?>