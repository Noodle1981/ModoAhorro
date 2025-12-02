<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SmartMeterController extends Controller
{
    public function demo(\App\Models\Entity $entity)
    {
        // 1. Calculate Base Load (Hormigas)
        // Fridge, Router, Standby
        $baseLoad = 150; // Default fallback
        
        // 2. Calculate Whales Capacity (Ballenas)
        // AC, Oven, Washing Machine
        $whaleCapacity = 3500; // Default fallback

        // Try to calculate from real equipment if available
        $equipment = $entity->rooms->flatMap(fn($r) => $r->equipment);
        
        if ($equipment->isNotEmpty()) {
            $baseLoad = $equipment->filter(function($e) {
                $name = strtolower($e->name);
                return str_contains($name, 'heladera') || str_contains($name, 'router') || str_contains($name, 'modem');
            })->sum(fn($e) => $e->type->default_power_watts ?? 0);
            
            // Add some standby baseline
            $baseLoad += 50; 

            $whaleCapacity = $equipment->filter(function($e) {
                $watts = $e->type->default_power_watts ?? 0;
                return $watts > 500; // Arbitrary threshold for "Whale"
            })->sum(fn($e) => $e->type->default_power_watts ?? 0);
        }

        // Ensure reasonable defaults if calculation yields 0
        if ($baseLoad < 50) $baseLoad = 150;
        if ($whaleCapacity < 1000) $whaleCapacity = 3500;

        return view('smart_meter.demo', compact('entity', 'baseLoad', 'whaleCapacity'));
    }
}
