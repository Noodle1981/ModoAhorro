<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entity;
use App\Models\Locality;
use Inertia\Inertia;

class EntityController extends Controller
{
    public function edit(Request $request)
    {
        $currentEntityId = session('active_entity_id');
        $entity = Entity::with('locality.province')->find($currentEntityId);

        if (!$entity || $request->user()->cannot('update', $entity)) {
            abort(403);
        }

        $provinces = \App\Models\Province::whereIn('name', ['San Juan', 'Mendoza', 'San Luis'])->get();
        $localityQuery = Locality::whereIn('province_id', $provinces->pluck('id'));

        $localitiesData = $localityQuery->get()->map(function ($l) {
            return [
                'id' => $l->id,
                'name' => $l->name,
                'province_id' => $l->province_id,
            ];
        });

        // Obtener clima actual e histórico para el perfil de salud
        $weather = null;
        $climateProfile = null;
        if ($entity->locality) {
            $weatherService = app(\App\Services\ClimateService::class);
            $weather = $weatherService->getCurrentWeather($entity->locality);
            $climateProfile = $weatherService->getLocalityClimateProfile($entity->locality);
        }

        return Inertia::render('Entity/Edit', [
            'entity' => $entity,
            'provinces' => $provinces,
            'localities' => $localitiesData,
            'currentWeather' => $weather,
            'climateProfile' => $climateProfile,
        ]);
    }

    /**
     * Update the entity details.
     */
    public function update(Request $request)
    {
        $currentEntityId = session('active_entity_id');
        $entity = Entity::findOrFail($currentEntityId);

        if ($request->user()->cannot('update', $entity)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'usage_type' => 'required|string|in:residencial,comercial,oficina',
            'address_street' => 'nullable|string|max:255',
            'address_postal_code' => 'nullable|string|max:20',
            'locality_id' => 'nullable|exists:localities,id',
            'square_meters' => 'nullable|numeric|min:1',
            'people_count' => 'nullable|integer|min:0',
            'construction_year' => 'nullable|integer|min:1900|max:'.date('Y'),
            'has_gas' => 'boolean',
            'has_solar' => 'boolean',
            'has_business_activity' => 'boolean',
            'business_type' => 'nullable|string|in:almacen,taller,venta',
            'description' => 'nullable|string',
        ]);

        $oldHasBusiness = $entity->has_business_activity;
        $entity->update($validated);

        // Lógica de Uso Mixto: Inyectar ambiente de negocio específico
        if ($entity->has_business_activity && !$oldHasBusiness) {
            $name = match($entity->business_type) {
                'almacen' => 'Almacén / Depósito',
                'taller' => 'Taller / Producción',
                'venta' => 'Local de Venta / Atención',
                default => 'Espacio de Trabajo / Negocio'
            };

            $exists = $entity->rooms()->where('name', 'like', "%{$name}%")->exists();
            
            if (!$exists) {
                $entity->rooms()->create([
                    'name' => $name,
                    'description' => 'Ambiente autogenerado para soporte de actividad económica en el hogar.'
                ]);
            }
        }

        return redirect()->route('home')->with('success', 'Perfil de la casa actualizado correctamente.');
    }
}
