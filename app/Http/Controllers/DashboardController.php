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
        
        if ($user->is_super_admin) {
            return redirect()->route('sistema.admin');
        }

        $plan = $user->currentPlan();
        
        // Get all entity type configurations
        $entityTypes = config('entity_types', []);
        
        // Get user's entities
        $userEntities = $user->entities()->with('locality')->get();
        
        // Prepare data for Inertia
        $entitiesByType = [];
        foreach ($entityTypes as $type => $config) {
            // User request: 'hogar', 'comercio' and 'oficina' are enabled. 
            // Others are grayed out regardless of plan.
            $isEnabled = in_array($type, ['hogar', 'comercio', 'oficina']);
            
            $entitiesByType[] = [
                'type' => $type,
                'name' => $config['label'],
                'enabled' => $isEnabled,
                'entities' => $userEntities->where('type', $type)->values(),
                'can_add' => $isEnabled && ($userEntities->where('type', $type)->count() < $plan->max_entities),
                // Dynamic styling classes from central config
                'tailwind_bg' => $config['tailwind_bg'] ?? 'bg-slate-100',
                'tailwind_text' => $config['tailwind_text'] ?? 'text-slate-600',
                'tailwind_gradient' => $config['tailwind_gradient'] ?? 'from-slate-500 to-slate-600',
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
        
        $entities = $user->entities()->with('locality.province')->get();
        $currentEntityId = session('active_entity_id');
        $currentEntity = $entities->where('id', $currentEntityId)->first() ?? $entities->first();

        if ($currentEntity && $request->user()->cannot('view', $currentEntity)) {
            abort(403);
        }

        // Obtener clima actual e histórico para indicadores del Home
        $weatherService = app(\App\Services\ClimateService::class);
        $weather = null;
        $climateProfile = null;
        
        if ($currentEntity && $currentEntity->locality) {
            $weather = $weatherService->getCurrentWeather($currentEntity->locality);
            $climateProfile = $weatherService->getLocalityClimateProfile($currentEntity->locality);
        }

        // Cargar estadísticas reales para los KPIs del dashboard
        $stats = [
            'co2_reduced' => 0.0,
            'daily_cost' => 0.0,
            'monthly_consumption' => 0.0,
            'tanks' => [1 => 0, 2 => 0, 3 => 0, 4 => 0],
            'has_data' => false,
        ];

        if ($currentEntity) {
            $invoices = \App\Models\Invoice::whereHas('contract', fn($q) => $q->where('entity_id', $currentEntity->id))->get();
            if ($invoices->isNotEmpty()) {
                $stats['has_data'] = true;
                
                $latestInvoice = $invoices->sortByDesc('end_date')->first();
                $totalDays = \Carbon\Carbon::parse($latestInvoice->start_date)->diffInDays(\Carbon\Carbon::parse($latestInvoice->end_date)) ?: 30;
                
                $stats['monthly_consumption'] = round(($latestInvoice->total_energy_consumed_kwh / $totalDays) * 30, 0);
                $stats['daily_cost'] = round($latestInvoice->total_amount / $totalDays, 2);
                
                // Estimación de CO2 reducido
                $solarSaving = $currentEntity->has_solar ? 150 : 0;
                $thermalProfile = $currentEntity->thermal_profile ?? [];
                $thermalSaving = isset($thermalProfile['thermal_score']) && $thermalProfile['thermal_score'] > 60
                    ? ($thermalProfile['thermal_score'] - 60) * 2
                    : 0;
                $stats['co2_reduced'] = round(($solarSaving + $thermalSaving) * 0.4, 2);
                
                // Desglose de tanques del último periodo calibrado
                $lastCalibrated = $invoices->whereNotNull('calibrated_at')->sortByDesc('end_date')->first();
                if ($lastCalibrated) {
                    $usages = $lastCalibrated->equipmentUsages()->get();
                    foreach ($usages as $usage) {
                        $kwh = $usage->kwh_reconciled ?? $usage->consumption_kwh ?? 0;
                        $tank = (int) $usage->tank_assignment;
                        if (isset($stats['tanks'][$tank])) {
                            $stats['tanks'][$tank] += $kwh;
                        }
                    }
                }
            }
        }

        return Inertia::render('Dashboard/Home', [
            'currentEntity' => $currentEntity,
            'currentWeather' => $weather,
            'climateProfile' => $climateProfile,
            'stats' => $stats,
        ]);
    }
}
