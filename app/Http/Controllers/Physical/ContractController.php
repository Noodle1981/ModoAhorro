<?php
namespace App\Http\Controllers\Physical;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contract;
use App\Models\Entity;
use App\Models\Proveedor;

class ContractController extends Controller
{
    public function showForEntity($entityId)
    {
        $entity = Entity::findOrFail($entityId);
        $config = config("entity_types.{$entity->type}", []);
        $contract = Contract::where('entity_id', $entityId)->first();
        return view('contracts.show', compact('entity', 'contract', 'config'));
    }

    public function index()
    {
        $contracts = Contract::with(['entity', 'proveedor'])->get();
        return view('contracts.index', compact('contracts'));
    }

    public function create()
    {
        $entities = Entity::all();
        $proveedores = Proveedor::all();
        return view('contracts.create', compact('entities', 'proveedores'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        Contract::create($data);
        return redirect()->route('contracts.index')->with('success', 'Contrato creado correctamente.');
    }

    public function edit($id)
    {
        $contract = Contract::findOrFail($id);
        $entities = Entity::all();
        $proveedores = Proveedor::all();
        return view('contracts.edit', compact('contract', 'entities', 'proveedores'));
    }

    public function update(Request $request, $id)
    {
        $contract = Contract::findOrFail($id);
        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $contract->update($data);
        return redirect()->route('contracts.index')->with('success', 'Contrato actualizado correctamente.');
    }

    public function destroy($id)
    {
        $contract = Contract::findOrFail($id);
        $contract->delete();
        return redirect()->route('contracts.index')->with('success', 'Contrato eliminado correctamente.');
    }
}
