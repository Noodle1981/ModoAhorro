<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPlanEntities
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        // Obtener el plan del primer registro de la relación pivote
        $pivot = $user->entities()->first()?->pivot;
        $planId = $pivot?->plan_id;
        $plan = $planId ? \App\Models\Plan::find($planId) : null;
        $maxEntities = $plan?->max_entities ?? 1;
        $entitiesCount = $user->entities()->count();
        // Si el usuario supera el límite de entidades de su plan, lo bloqueamos
        if ($entitiesCount > $maxEntities) {
            return redirect()->route('dashboard')->withErrors(['plan' => 'Tu plan solo permite acceso a ' . $maxEntities . ' entidad(es).']);
        }
        return $next($request);
    }
}
