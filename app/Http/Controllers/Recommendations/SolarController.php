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
        $result = $service->analyze($entity);
        return view('solar.water_heater', compact('entity', 'result'));
    }
}
