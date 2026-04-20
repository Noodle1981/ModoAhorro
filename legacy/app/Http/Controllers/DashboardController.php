<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $plan = $user->currentPlan();
        
        // Get all entity type configurations
        $entityTypes = config('entity_types');
        
        // Get allowed entity types from plan
        $allowedTypes = [];
        if ($plan && $plan->allowed_entity_types) {
            $allowedTypes = is_array($plan->allowed_entity_types) 
                ? $plan->allowed_entity_types 
                : json_decode($plan->allowed_entity_types, true) ?? [];
        }
        
        // If no plan or no restrictions, allow all types
        if (empty($allowedTypes)) {
            $allowedTypes = ['hogar', 'oficina', 'comercio'];
        }
        
        // Get user's entities grouped by type
        $userEntities = $user->entities()->with('locality')->get();
        
        $entitiesByType = [];
        foreach ($entityTypes as $type => $config) {
            $entitiesByType[$type] = [
                'config' => $config,
                'entities' => $userEntities->where('type', $type),
                'allowed' => in_array($type, $allowedTypes),
            ];
        }

        return view('dashboard', [
            'user' => $user,
            'plan' => $plan,
            'entitiesByType' => $entitiesByType,
        ]);
    }
}
