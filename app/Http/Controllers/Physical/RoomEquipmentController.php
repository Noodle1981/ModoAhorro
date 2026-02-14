<?php
namespace App\Http\Controllers\Physical;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentType;

class RoomEquipmentController extends Controller
{
    public function dashboard($entityId, $roomId)
    {
        $room = Room::with(['equipment' => function($query) {
            $query->where('is_active', true);
        }, 'equipment.category', 'equipment.type'])->findOrFail($roomId);
        $entity = $room->entity;
        $config = config("entity_types.{$entity->type}", []);
        
        $hasInvoices = $entity->invoices()->exists();
        if (!$hasInvoices) {
            return view('rooms.no_invoices', compact('entity', 'config'));
        }
        
        $categories = EquipmentCategory::all();
        $types = EquipmentType::all();
        return view('rooms.equipment_dashboard', compact('room', 'categories', 'types', 'config'));
    }

    public function store(Request $request, $entityId, $roomId)
    {
        $room = Room::findOrFail($roomId);
        $entity = $room->entity;
        $config = config("entity_types.{$entity->type}", []);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:equipment_categories,id',
            'type_id' => 'required|exists:equipment_types,id',
            'nominal_power_w' => 'required|integer|min:1',
            'is_validated' => 'boolean',
            'cantidad' => 'required|integer|min:1',
        ]);
        
        for ($i = 0; $i < $validated['cantidad']; $i++) {
            $equipment = new Equipment($validated);
            $equipment->room_id = $room->id;
            $equipment->save();
        }
        
        return redirect()->route($config['route_prefix'] . '.rooms.equipment', [$entityId, $roomId])
            ->with('success', 'Equipos agregados correctamente.');
    }

    public function edit($entityId, $roomId, $equipmentId)
    {
        $room = Room::findOrFail($roomId);
        $entity = $room->entity;
        $config = config("entity_types.{$entity->type}", []);
        
        $equipment = Equipment::findOrFail($equipmentId);
        $categories = EquipmentCategory::all();
        $types = EquipmentType::all();
        
        return view('equipment.edit', compact('equipment', 'categories', 'types', 'room', 'config'));
    }

    public function update(Request $request, $entityId, $roomId, $equipmentId)
    {
        $room = Room::findOrFail($roomId);
        $entity = $room->entity;
        $config = config("entity_types.{$entity->type}", []);
        
        $equipment = Equipment::findOrFail($equipmentId);
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:equipment_categories,id',
            'type_id' => 'required|exists:equipment_types,id',
            'nominal_power_w' => 'required|integer|min:1',
            'is_validated' => 'boolean',
        ]);
        
        $equipment->update($request->all());
        
        return redirect()->route($config['route_prefix'] . '.rooms.equipment', [$entityId, $roomId])
            ->with('success', 'Equipo actualizado correctamente.');
    }

    public function destroy($entityId, $roomId, $equipmentId)
    {
        $room = Room::findOrFail($roomId);
        $entity = $room->entity;
        $config = config("entity_types.{$entity->type}", []);
        
        $equipment = Equipment::findOrFail($equipmentId);
        $equipment->is_active = false;
        $equipment->save();

        \App\Models\EquipmentHistory::create([
            'equipment_id' => $equipment->id,
            'action' => 'baja',
            'reason' => request('reason', 'Baja lógica desde el sistema'),
            'action_date' => now(),
            'user_id' => auth()->id(),
        ]);

        return redirect()->route($config['route_prefix'] . '.rooms.equipment', [$entityId, $roomId])
            ->with('success', 'Equipo dado de baja correctamente.');
    }
}
