<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Entity;

class DashboardController extends Controller
{
    /**
     * Display the entity selector dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $plan = $user->currentPlan();
        
        // Get all entity type configurations
        $entityTypes = config('entity_types', []);
        
        // Get allowed entity types from plan (for business logic)
        $allowedTypesFromPlan = $plan->allowed_entity_types ?? ['hogar'];
        
        // Get user's entities
        $userEntities = $user->entities()->with('locality')->get();
        
        // Prepare data for Inertia
        $entitiesByType = [];
        foreach ($entityTypes as $type => $config) {
            // User request: Only 'hogar' (Casa) is enabled for now. 
            // Others are grayed out regardless of plan.
            $isEnabled = ($type === 'hogar');
            
            $entitiesByType[] = [
                'type' => $type,
                'name' => $config['label'],
                'icon' => $config['icon'],
                'color' => $config['color'],
                'enabled' => $isEnabled,
                'entities' => $userEntities->where('type', $type)->values(),
                'can_add' => $isEnabled && ($userEntities->where('type', $type)->count() < $plan->max_entities),
            ];
        }

        return Inertia::render('Dashboard/Selector', [
            'user' => $user,
            'plan' => $plan,
            'entitiesByType' => $entitiesByType,
        ]);
    }

    /**
     * Display the authenticated home dashboard for the selected entity.
     */
    public function home(Request $request)
    {
        $user = $request->user();
        
        // Use the same logic as HandleInertiaRequests to get the active entity
        $entities = $user->entities()->with('locality')->get();
        $currentEntityId = session('active_entity_id');
        $currentEntity = $entities->where('id', $currentEntityId)->first() ?? $entities->first();

        if ($currentEntity && $request->user()->cannot('view', $currentEntity)) {
            abort(403);
        }

        return Inertia::render('Dashboard/Home', [
            'currentEntity' => $currentEntity,
        ]);
    }
}
