<?php
namespace App\Http\Controllers\Physical;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $entityId = request()->route('entity');
        $entity = \App\Models\Entity::findOrFail($entityId);
        $rooms = $entity->rooms()->get();
        return view('rooms.index', compact('entity', 'rooms'));
    }

    public function create()
    {
        $entityId = request()->route('entity');
        $entity = \App\Models\Entity::findOrFail($entityId);
        return view('rooms.create', compact('entity'));
    }

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

    public function show($entityId, $roomId)
    {
        $entity = \App\Models\Entity::findOrFail($entityId);
        $room = \App\Models\Room::findOrFail($roomId);
        return view('rooms.show', compact('entity', 'room'));
    }

    public function edit($entityId, $roomId)
    {
        $entity = \App\Models\Entity::findOrFail($entityId);
        $room = \App\Models\Room::findOrFail($roomId);
        return view('rooms.edit', compact('entity', 'room'));
    }

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

    public function destroy($entityId, $roomId)
    {
        $entity = \App\Models\Entity::findOrFail($entityId);
        $room = \App\Models\Room::findOrFail($roomId);
        $room->delete();
        return redirect()->route('rooms.index', $entity->id)
            ->with('success', 'Habitación eliminada correctamente.');
    }
}
