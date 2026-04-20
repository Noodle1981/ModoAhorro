<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para acceder.');
        }

        // Check if user is super admin
        if (!auth()->user()->is_super_admin) {
            abort(403, 'No tienes autorización para acceder a esta sección.');
        }

        return $next($request);
    }
}
