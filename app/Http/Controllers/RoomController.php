<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $entityId = request()->route('entity');
    $entity = \App\Models\Entity::findOrFail($entityId);
    $rooms = $entity->rooms()->get();
    return view('rooms.index', compact('entity', 'rooms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    $entityId = request()->route('entity');
    $entity = \App\Models\Entity::findOrFail($entityId);
    return view('rooms.create', compact('entity'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $entityId = request()->route('entity');
        $entity = \App\Models\Entity::findOrFail($entityId);
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $room = $entity->rooms()->create($request->only(['name', 'description']));
        return redirect()->route('rooms.index', $entity->id)
            ->with('success', 'Habitación creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show($entityId, $roomId)
    {
        $entity = \App\Models\Entity::findOrFail($entityId);
        $room = \App\Models\Room::findOrFail($roomId);
        return view('rooms.show', compact('entity', 'room'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($entityId, $roomId)
    {
        $entity = \App\Models\Entity::findOrFail($entityId);
        $room = \App\Models\Room::findOrFail($roomId);
        return view('rooms.edit', compact('entity', 'room'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $entityId, $roomId)
    {
        $entity = \App\Models\Entity::findOrFail($entityId);
        $room = \App\Models\Room::findOrFail($roomId);
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $room->update($request->only(['name', 'description']));
        return redirect()->route('rooms.index', $entity->id)
            ->with('success', 'Habitación actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($entityId, $roomId)
    {
        $entity = \App\Models\Entity::findOrFail($entityId);
        $room = \App\Models\Room::findOrFail($roomId);
        $room->delete();
        return redirect()->route('rooms.index', $entity->id)
            ->with('success', 'Habitación eliminada correctamente.');
    }
}
