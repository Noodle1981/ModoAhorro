<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentType;

class EquipmentController extends Controller
{
    public function index()
    {
        $equipments = Equipment::with(['category', 'type'])->get();
        return view('equipment.index', compact('equipments'));
    }

    public function create()
    {
        // Obtener entidad activa del usuario (ajustar según tu lógica de sesión)
        $entity = auth()->user()->entities()->first();
        if (!$entity || !$entity->invoices()->exists()) {
            return view('rooms.no_invoices', compact('entity'));
        }
        $categories = EquipmentCategory::all();
        $types = EquipmentType::all();
        return view('equipment.create', compact('categories', 'types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:equipment_categories,id',
            'type_id' => 'required|exists:equipment_types,id',
            'nominal_power_w' => 'required|integer|min:1',
            'is_standby' => 'nullable|boolean',
            'avg_daily_use_hours' => 'nullable|numeric|min:0',
            'use_days_per_week' => 'nullable|integer|min:0|max:7',
            'is_active' => 'nullable|boolean',
            'room_id' => 'nullable|exists:rooms,id',
        ]);
        Equipment::create($validated);
        return redirect()->route('equipment.index')->with('success', 'Equipo agregado correctamente.');
    }

    public function edit($id)
    {
        $equipment = Equipment::findOrFail($id);
        $categories = EquipmentCategory::all();
        $types = EquipmentType::all();
        return view('equipment.edit', compact('equipment', 'categories', 'types'));
    }

    public function update(Request $request, $id)
    {
        $equipment = Equipment::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:equipment_categories,id',
            'type_id' => 'required|exists:equipment_types,id',
            'is_active' => 'required|boolean',
        ]);
        $equipment->update($validated);
        return redirect()->route('equipment.index')->with('success', 'Equipo actualizado correctamente.');
    }
}
