<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entity;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    public function index($entityId)
    {
        $entity = Entity::findOrFail($entityId);
        $contract = $entity->contracts()->where('is_active', true)->first();
        $invoices = $contract ? $contract->invoices : collect();
        return view('invoices.index', compact('entity', 'contract', 'invoices'));
    }

    public function create($entityId)
    {
        $entity = Entity::findOrFail($entityId);
        $contract = $entity->contracts()->where('is_active', true)->first();
        if (!$contract) {
            return redirect()->route('entities.invoices.index', $entityId)
                ->with('warning', 'No hay contrato activo registrado para este hogar.');
        }
        return view('invoices.create', compact('entity', 'contract'));
    }

    public function store(Request $request, $entityId)
    {
        $entity = Entity::findOrFail($entityId);
        $contract = $entity->contracts()->where('is_active', true)->first();
        if (!$contract) {
            return redirect()->route('entities.invoices.index', $entityId)
                ->with('warning', 'No hay contrato activo registrado para este hogar.');
        }
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'total_energy_consumed_kwh' => 'required|numeric',
            'total_amount' => 'required|numeric',
        ]);
        $invoice = new Invoice($request->all());
        $invoice->contract_id = $contract->id;
        $invoice->save();
        return redirect()->route('entities.invoices.index', $entityId)
            ->with('success', 'Factura cargada correctamente.');
    }

    public function edit($entityId, $invoiceId)
    {
    $entity = Entity::findOrFail($entityId);
    $contract = $entity->contracts()->where('is_active', true)->first();
    $invoice = Invoice::where('contract_id', optional($contract)->id)->findOrFail($invoiceId);
    return view('invoices.edit', compact('entity', 'invoice', 'contract'));
    }

    public function update(Request $request, $entityId, $invoiceId)
    {
        $entity = Entity::findOrFail($entityId);
        $contract = $entity->contracts()->where('is_active', true)->first();
        $invoice = Invoice::where('contract_id', optional($contract)->id)->findOrFail($invoiceId);
        $invoice->update($request->all());
        return redirect()->route('entities.invoices.index', $entityId)
            ->with('success', 'Factura actualizada correctamente.');
    }
}
