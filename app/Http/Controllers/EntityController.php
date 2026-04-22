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

        $entity->update($validated);

        // Lógica de Uso Mixto (Hogar + Negocio): Estado Deseado
        $identifier = 'Ambiente autogenerado para soporte de actividad económica en el hogar.';
        $businessRoom = $entity->rooms()->where('description', 'like', '%autogenerado%')->first();

        if ($entity->has_business_activity) {
            $intendedName = match($entity->business_type) {
                'almacen' => 'Almacén',
                'taller' => 'Taller',
                'venta' => 'Local / Venta',
                default => 'Espacio de Trabajo / Negocio'
            };

            if ($businessRoom) {
                // Sincronizar nombre si ya existe
                if ($businessRoom->name !== $intendedName) {
                    $businessRoom->update(['name' => $intendedName]);
                }
            } else {
                // Crear si no existe
                $entity->rooms()->create([
                    'name' => $intendedName,
                    'description' => $identifier
                ]);
            }
        } else {
            // Si está inactivo, eliminar cualquier rastro
            if ($businessRoom) {
                $businessRoom->delete();
            }
        }

        return redirect()->route('home')->with('success', 'Perfil de la casa actualizado correctamente.');
    }
}
