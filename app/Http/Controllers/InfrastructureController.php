<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentModel;
use App\Models\EquipmentType;
use App\Models\Room;
use App\Traits\HasActiveEntity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InfrastructureController extends Controller
{
    use HasActiveEntity;

    /**
     * Display a listing of rooms and equipment for the active entity.
     */
    public function index(Request $request)
    {
        $entity = $this->getActiveEntity($request);

        if (! $entity) {
            return redirect()->route('dashboard')->with('warning', 'Debes seleccionar una entidad activa.');
        }

        $rooms = Room::where('entity_id', $entity->id)
            ->with(['equipment.category', 'equipment.type'])
            ->withCount('equipment')
            ->get();

        $categories = EquipmentCategory::orderBy('name')->get();
        $types = EquipmentType::where('is_active', true)->orderBy('name')->get();

        return Inertia::render('Entities/Infrastructure/Index', [
            'entity' => $entity,
            'rooms' => $rooms,
            'categories' => $categories,
            'types' => $types,
        ]);
    }

    /**
     * Store a newly created room.
     */
    public function storeRoom(Request $request)
    {
        $validated = $request->validate([
            'entity_id' => 'required|exists:entities,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $entity = Entity::findOrFail($validated['entity_id']);
        if ($request->user()->cannot('update', $entity)) {
            abort(403);
        }

        Room::create($validated);

        return redirect()->back()->with('success', 'Ambiente creado correctamente.');
    }

    /**
     * Update the specified room.
     */
    public function updateRoom(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($request->user()->cannot('update', $room->entity)) {
            abort(403);
        }

        $room->update($validated);

        return redirect()->back()->with('success', 'Ambiente actualizado correctamente.');
    }

    /**
     * Remove the specified room.
     */
    public function destroyRoom(Request $request, Room $room)
    {
        if ($request->user()->cannot('update', $room->entity)) {
            abort(403);
        }

        $room->delete();

        return redirect()->back()->with('success', 'Ambiente eliminado correctamente.');
    }

    /**
     * Manage Equipment
     */
    public function storeEquipment(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'category_id' => 'required|exists:equipment_categories,id',
            'type_id' => 'required|exists:equipment_types,id',
            'name' => 'required|string|max:255',
            'nominal_power_w' => 'required|numeric|min:0',
            'avg_daily_use_hours' => 'nullable|numeric|min:0|max:24',
            'is_standby' => 'nullable|boolean',
            'is_inverter' => 'nullable|boolean',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'model_id' => 'nullable|exists:equipment_models,id',
            'serial_number' => 'nullable|string|max:255',
            'energy_label' => 'nullable|string|max:10',
            'cantidad' => 'integer|min:1',
        ]);

        $validated['avg_daily_use_hours'] = $validated['avg_daily_use_hours'] ?? 0;
        $validated['is_inverter'] = $validated['is_inverter'] ?? false;

        // Auto vincular a modelo verificado si coincide la marca y modelo
        if (empty($validated['model_id']) && !empty($validated['brand']) && !empty($validated['model'])) {
            $matchingModel = EquipmentModel::where('is_verified', true)
                ->whereRaw('LOWER(TRIM(brand)) = ?', [strtolower(trim($validated['brand']))])
                ->whereRaw('LOWER(TRIM(model)) = ?', [strtolower(trim($validated['model']))])
                ->first();
            if ($matchingModel) {
                $validated['model_id'] = $matchingModel->id;
            }
        }

        $room = Room::findOrFail($validated['room_id']);
        if ($request->user()->cannot('update', $room->entity)) {
            abort(403);
        }

        $cantidad = $request->input('cantidad', 1);

        for ($i = 0; $i < $cantidad; $i++) {
            $name = $validated['name'];
            if ($cantidad > 1) {
                $name .= ' '.($i + 1);
            }

            Equipment::create(array_merge($validated, [
                'name' => $name,
                'is_active' => true,
                'is_validated' => true,
            ]));
        }

        return redirect()->back()->with('success', $cantidad > 1 ? "{$cantidad} equipos creados correctamente." : 'Equipo registrado correctamente.');
    }

    public function updateEquipment(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'category_id' => 'required|exists:equipment_categories,id',
            'type_id' => 'required|exists:equipment_types,id',
            'name' => 'required|string|max:255',
            'nominal_power_w' => 'required|numeric|min:0',
            'avg_daily_use_hours' => 'nullable|numeric|min:0|max:24',
            'is_standby' => 'nullable|boolean',
            'is_inverter' => 'nullable|boolean',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'model_id' => 'nullable|exists:equipment_models,id',
            'serial_number' => 'nullable|string|max:255',
            'energy_label' => 'nullable|string|max:10',
            'is_active' => 'required|boolean',
        ]);

        $validated['avg_daily_use_hours'] = $validated['avg_daily_use_hours'] ?? $equipment->avg_daily_use_hours;
        $validated['is_inverter'] = $validated['is_inverter'] ?? false;

        // Auto vincular a modelo verificado si coincide la marca y modelo
        if (empty($validated['model_id']) && !empty($validated['brand']) && !empty($validated['model'])) {
            $matchingModel = EquipmentModel::where('is_verified', true)
                ->whereRaw('LOWER(TRIM(brand)) = ?', [strtolower(trim($validated['brand']))])
                ->whereRaw('LOWER(TRIM(model)) = ?', [strtolower(trim($validated['model']))])
                ->first();
            if ($matchingModel) {
                $validated['model_id'] = $matchingModel->id;
            }
        }

        if ($request->user()->cannot('update', $equipment->room->entity)) {
            abort(403);
        }

        $equipment->update($validated);

        return redirect()->back()->with('success', 'Especificaciones de equipo actualizadas.');
    }

    public function destroyEquipment(Request $request, Equipment $equipment)
    {
        if ($request->user()->cannot('update', $equipment->room->entity)) {
            abort(403);
        }

        $equipment->delete();

        return redirect()->back()->with('success', 'Equipo eliminado del inventario.');
    }
}
