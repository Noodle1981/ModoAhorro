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
        $config = config("entity_types.{$entity->type}", []);
        return view('rooms.index', compact('entity', 'rooms', 'config'));
    }

    public function create()
    {
        $entityId = request()->route('entity');
        $entity = \App\Models\Entity::findOrFail($entityId);
        $config = config("entity_types.{$entity->type}", []);
        return view('rooms.create', compact('entity', 'config'));
    }

    public function store(Request $request)
    {
        $entityId = request()->route('entity');
        $entity = \App\Models\Entity::findOrFail($entityId);
        $config = config("entity_types.{$entity->type}", []);
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $room = $entity->rooms()->create($request->only(['name', 'description']));
        return redirect()->route($config['route_prefix'] . '.rooms', $entity->id)
            ->with('success', 'Habitación creada correctamente.');
    }

    public function show($entityId, $roomId)
    {
        $entity = \App\Models\Entity::findOrFail($entityId);
        $room = \App\Models\Room::findOrFail($roomId);
        $config = config("entity_types.{$entity->type}", []);
        return view('rooms.show', compact('entity', 'room', 'config'));
    }

    public function edit($entityId, $roomId)
    {
        $entity = \App\Models\Entity::findOrFail($entityId);
        $room = \App\Models\Room::findOrFail($roomId);
        $config = config("entity_types.{$entity->type}", []);
        return view('rooms.edit', compact('entity', 'room', 'config'));
    }

    public function update(Request $request, $entityId, $roomId)
    {
        $entity = \App\Models\Entity::findOrFail($entityId);
        $room = \App\Models\Room::findOrFail($roomId);
        $config = config("entity_types.{$entity->type}", []);
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $room->update($request->only(['name', 'description']));
        return redirect()->route($config['route_prefix'] . '.rooms', $entity->id)
            ->with('success', 'Habitación actualizada correctamente.');
    }

    public function destroy($entityId, $roomId)
    {
        $entity = \App\Models\Entity::findOrFail($entityId);
        $room = \App\Models\Room::findOrFail($roomId);
        $config = config("entity_types.{$entity->type}", []);
        $room->delete();
        return redirect()->route($config['route_prefix'] . '.rooms', $entity->id)
            ->with('success', 'Habitación eliminada correctamente.');
    }
}
