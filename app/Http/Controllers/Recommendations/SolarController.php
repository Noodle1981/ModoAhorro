<?php
namespace App\Http\Controllers\Recommendations;

use App\Http\Controllers\Controller;
use App\Models\Entity;
use App\Services\SolarWaterHeaterService;

class SolarController extends Controller
{
    public function waterHeater(string $id, SolarWaterHeaterService $service)
    {
        $entity = Entity::findOrFail($id);
        $config = config("entity_types.{$entity->type}", []);
        $result = $service->analyze($entity);
        return view('entities.solar_water_heater', compact('entity', 'result', 'config'));
    }
}
