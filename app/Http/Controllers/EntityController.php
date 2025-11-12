<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EntityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $user = auth()->user();
    $entities = $user->entities()->with('locality')->get();
    return view('entities.index', compact('entities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    $localities = \App\Models\Locality::with('province')->get();
    return view('entities.create', compact('localities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'address_street' => 'required|string|max:255',
            'address_postal_code' => 'required|string|max:20',
            'locality_id' => 'required|exists:localities,id',
            'description' => 'nullable|string',
            'square_meters' => 'required|integer|min:1',
            'people_count' => 'required|integer|min:1',
        ]);

        $entity = \App\Models\Entity::create($request->only([
            'name',
            'address_street',
            'address_postal_code',
            'locality_id',
            'description',
            'square_meters',
            'people_count',
        ]));

        // Asociar entidad al usuario con el plan gratuito
        $user = auth()->user();
        $freePlan = \App\Models\Plan::where('name', 'Gratuito')->first();
        $user->entities()->attach($entity->id, [
            'plan_id' => $freePlan ? $freePlan->id : 1,
            'subscribed_at' => now(),
        ]);

        return redirect()->route('entities.show', $entity->id)
            ->with('success', 'Entidad hogar creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
    $entity = \App\Models\Entity::with('locality')->findOrFail($id);
    return view('entities.show', compact('entity'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
    $entity = \App\Models\Entity::findOrFail($id);
    $localities = \App\Models\Locality::all();
    return view('entities.edit', compact('entity', 'localities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'address_street' => 'required|string|max:255',
            'address_postal_code' => 'required|string|max:20',
            'locality_id' => 'required|exists:localities,id',
            'description' => 'nullable|string',
            'square_meters' => 'required|integer|min:1',
            'people_count' => 'required|integer|min:1',
        ]);

        $entity = \App\Models\Entity::findOrFail($id);
        $entity->update($request->only([
            'name',
            'address_street',
            'address_postal_code',
            'locality_id',
            'description',
            'square_meters',
            'people_count',
        ]));

        return redirect()->route('entities.show', $entity->id)
            ->with('success', 'Entidad actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $entity = \App\Models\Entity::findOrFail($id);
        $entity->delete();
        return redirect()->route('entities.index')
            ->with('success', 'Entidad eliminada correctamente.');
    }
}
