<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentType;

class RoomEquipmentController extends Controller
{
    public function dashboard($entityId, $roomId)
    {
        $room = Room::with(['equipment.category', 'equipment.type'])->findOrFail($roomId);
        $entity = $room->entity;
        $hasInvoices = $entity->invoices()->exists();
        if (!$hasInvoices) {
            return view('rooms.no_invoices', compact('entity'));
        }
        $categories = EquipmentCategory::all();
        $types = EquipmentType::all();
        return view('rooms.equipment_dashboard', compact('room', 'categories', 'types'));
    }

    public function store(Request $request, $entityId, $roomId)
    {
        $room = Room::findOrFail($roomId);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:equipment_categories,id',
            'type_id' => 'required|exists:equipment_types,id',
            'nominal_power_w' => 'required|integer|min:1',
            'cantidad' => 'required|integer|min:1',
        ]);
        for ($i = 0; $i < $validated['cantidad']; $i++) {
            $equipment = new Equipment($validated);
            $equipment->room_id = $room->id;
            $equipment->save();
        }
        return redirect()->route('rooms.equipment.dashboard', [$entityId, $roomId])->with('success', 'Equipos agregados correctamente.');
    }

    public function edit($entityId, $roomId, $equipmentId)
    {
        $room = Room::findOrFail($roomId);
        $equipment = Equipment::findOrFail($equipmentId);
        $categories = EquipmentCategory::all();
        $types = EquipmentType::all();
        return view('equipment.edit', compact('equipment', 'categories', 'types', 'room'));
    }

    public function update(Request $request, $entityId, $roomId, $equipmentId)
    {
        $equipment = Equipment::findOrFail($equipmentId);
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:equipment_categories,id',
            'type_id' => 'required|exists:equipment_types,id',
            'nominal_power_w' => 'required|integer|min:1',
        ]);
        $equipment->update($request->all());
        return redirect()->route('rooms.equipment.dashboard', [$entityId, $roomId])->with('success', 'Equipo actualizado correctamente.');
    }

    public function destroy($entityId, $roomId, $equipmentId)
    {
        $equipment = Equipment::findOrFail($equipmentId);
        $equipment->delete();
        return redirect()->route('rooms.equipment.dashboard', [$entityId, $roomId])->with('success', 'Equipo eliminado correctamente.');
    }
}
