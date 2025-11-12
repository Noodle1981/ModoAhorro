<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entity;
use App\Models\Meter;

class MeterController extends Controller
{
    public function index($entityId)
    {
        $entity = Entity::findOrFail($entityId);
        $meter = $entity->meter; // Relación uno a uno
        return view('meter.index', compact('entity', 'meter'));
    }

    public function create($entityId)
    {
    $entity = Entity::findOrFail($entityId);
    $province = $entity->locality ? $entity->locality->province : null;
    $companies = $province ? $province->companies : collect();
    return view('meter.create', compact('entity', 'companies'));
    }

    public function store(Request $request, $entityId)
    {
        $entity = Entity::findOrFail($entityId);
        $meter = new Meter($request->all());
        $meter->entity_id = $entity->id;
        $meter->save();
        return redirect()->route('entities.meter.index', $entity->id)->with('success', 'Medidor registrado correctamente.');
    }

    public function edit($entityId, $meterId)
    {
    $entity = Entity::findOrFail($entityId);
    $meter = Meter::where('entity_id', $entityId)->findOrFail($meterId);
    $province = $entity->locality ? $entity->locality->province : null;
    $companies = $province ? $province->companies : collect();
    return view('meter.edit', compact('entity', 'meter', 'companies'));
    }

    public function update(Request $request, $entityId, $meterId)
    {
        $meter = Meter::where('entity_id', $entityId)->findOrFail($meterId);
        $meter->update($request->all());
        return redirect()->route('entities.meter.index', $entityId)->with('success', 'Medidor actualizado correctamente.');
    }
}
