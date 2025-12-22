<?php
namespace App\Http\Controllers\Consumption;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SmartMeterController extends Controller
{
    public function demo(\App\Models\Entity $entity)
    {
        $baseLoad = 150;
        $whaleCapacity = 3500;

        $equipment = $entity->rooms->flatMap(fn($r) => $r->equipment);
        
        if ($equipment->isNotEmpty()) {
            $baseLoad = $equipment->filter(function($e) {
                $name = strtolower($e->name);
                return str_contains($name, 'heladera') || str_contains($name, 'router') || str_contains($name, 'modem');
            })->sum(fn($e) => $e->type->default_power_watts ?? 0);
            
            $baseLoad += 50;

            $whaleCapacity = $equipment->filter(function($e) {
                $watts = $e->type->default_power_watts ?? 0;
                return $watts > 500;
            })->sum(fn($e) => $e->type->default_power_watts ?? 0);
        }

        if ($baseLoad < 50) $baseLoad = 150;
        if ($whaleCapacity < 1000) $whaleCapacity = 3500;

        return view('smart_meter.demo', compact('entity', 'baseLoad', 'whaleCapacity'));
    }
}
