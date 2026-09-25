<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveEquipmentRequest;
use App\Http\Requests\SaveRoomRequest;
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
    public function storeRoom(SaveRoomRequest $request)
    {
        $validated = $request->validated();

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
    public function updateRoom(SaveRoomRequest $request, Room $room)
    {
        $validated = $request->validated();

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
    public function storeEquipment(SaveEquipmentRequest $request)
    {
        $validated = $request->validated();

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

    public function updateEquipment(SaveEquipmentRequest $request, Equipment $equipment)
    {
        $validated = $request->validated();

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

        $targetRoom = Room::with('entity')->findOrFail($validated['room_id']);
        if ($request->user()->cannot('update', $equipment->room->entity) ||
            $request->user()->cannot('update', $targetRoom->entity)) {
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
