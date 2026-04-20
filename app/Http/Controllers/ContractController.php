<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Entity;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ContractController extends Controller
{
    /**
     * Display a listing of the contracts for the user's entities.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $entities = $user->entities()->with('locality')->get();
        $currentEntityId = session('active_entity_id');
        $currentEntity = $entities->where('id', $currentEntityId)->first() ?? $entities->first();

        // Si no hay entidades, enviamos vacío
        if (!$currentEntity) {
            return Inertia::render('Entities/Contracts/Index', [
                'contracts' => [],
                'entities' => [],
                'proveedores' => [],
                'active_entity_id' => null
            ]);
        }

        $contracts = Contract::with(['entity', 'proveedor'])
            ->whereIn('entity_id', $entities->pluck('id'))
            ->orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Filtrar proveedores por la provincia de la entidad activa
        $proveedores = Proveedor::where('province_id', $currentEntity->locality->province_id ?? 0)
            ->orderBy('name')
            ->get();

        return Inertia::render('Entities/Contracts/Index', [
            'contracts' => $contracts,
            'entities' => $entities,
            'proveedores' => $proveedores,
            'active_entity_id' => $currentEntity->id
        ]);
    }

    /**
     * Store a newly created contract in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'entity_id' => 'required|exists:entities,id',
            'proveedor_id' => 'required|exists:proveedores,id',
            'supply_number' => 'required|string|max:255',
            'meter_number' => 'nullable|string|max:255',
            'contract_number' => 'nullable|string|max:255|unique:contracts,contract_number',
            'rate_name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'is_three_phase' => 'required|boolean',
            'contracted_power_kw_p1' => 'required|numeric|min:0',
            'contracted_power_kw_p2' => 'nullable|numeric|min:0',
            'contracted_power_kw_p3' => 'nullable|numeric|min:0',
            'is_active' => 'required|boolean',
        ], [
            'contract_number.unique' => 'Este número de contrato ya se encuentra registrado en el sistema.'
        ]);

        $entity = Entity::findOrFail($validated['entity_id']);
        
        // Security check: Ensure user owns the entity
        if ($request->user()->cannot('update', $entity)) {
            abort(403);
        }

        // If this contract is active, deactivate others for the same entity
        if ($validated['is_active']) {
            Contract::where('entity_id', $entity->id)->update(['is_active' => false]);
        }

        Contract::create($validated);

        return redirect()->back()->with('success', 'Contrato registrado correctamente.');
    }

    /**
     * Update the specified contract in storage.
     */
    public function update(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'entity_id' => 'required|exists:entities,id',
            'proveedor_id' => 'required|exists:proveedores,id',
            'supply_number' => 'required|string|max:255',
            'meter_number' => 'nullable|string|max:255',
            'contract_number' => [
                'nullable', 
                'string', 
                'max:255', 
                Rule::unique('contracts', 'contract_number')->ignore($contract->id)
            ],
            'rate_name' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'is_three_phase' => 'required|boolean',
            'contracted_power_kw_p1' => 'required|numeric|min:0',
            'contracted_power_kw_p2' => 'nullable|numeric|min:0',
            'contracted_power_kw_p3' => 'nullable|numeric|min:0',
            'is_active' => 'required|boolean',
        ], [
            'contract_number.unique' => 'Este número de contrato ya pertenece a otro registro.'
        ]);

        // Security check: Ensure user owns the current and target entity
        if ($request->user()->cannot('update', $contract->entity) || 
            $request->user()->cannot('update', Entity::find($validated['entity_id']))) {
            abort(403);
        }

        if ($validated['is_active']) {
            Contract::where('entity_id', $validated['entity_id'])
                ->where('id', '!=', $contract->id)
                ->update(['is_active' => false]);
        }

        $contract->update($validated);

        return redirect()->back()->with('success', 'Contrato actualizado correctamente.');
    }

    /**
     * Remove the specified contract from storage.
     */
    public function destroy(Request $request, Contract $contract)
    {
        // Security check
        if ($request->user()->cannot('update', $contract->entity)) {
            abort(403);
        }

        $contract->delete();

        return redirect()->back()->with('success', 'Contrato eliminado correctamente.');
    }

    /**
     * Toggle the active status of a contract.
     */
    public function toggleActive(Request $request, Contract $contract)
    {
        // Security check
        if ($request->user()->cannot('update', $contract->entity)) {
            abort(403);
        }

        if (!$contract->is_active) {
            // Deactivate others for the same entity
            Contract::where('entity_id', $contract->entity_id)
                ->where('id', '!=', $contract->id)
                ->update(['is_active' => false]);
            
            $contract->update(['is_active' => true]);
        } else {
            $contract->update(['is_active' => false]);
        }

        return redirect()->back()->with('success', 'Estado del contrato actualizado.');
    }
}
