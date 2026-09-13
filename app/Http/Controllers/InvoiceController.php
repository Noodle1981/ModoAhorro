<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveInvoiceRequest;
use App\Models\Contract;
use App\Models\Invoice;
use App\Traits\HasActiveEntity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    use HasActiveEntity;

    /**
     * Display a listing of the invoices for the active entity.
     */
    public function index(Request $request)
    {
        $entity = $this->getActiveEntity($request);

        if (! $entity) {
            return redirect()->route('dashboard')->with('error', 'Debes crear una entidad antes de gestionar facturas.');
        }

        // Get contracts for this entity
        $contracts = Contract::where('entity_id', $entity->id)->with('proveedor')->get();
        $contractIds = $contracts->pluck('id');

        // Get invoices for those contracts
        $invoices = Invoice::whereIn('contract_id', $contractIds)
            ->orderBy('start_date', 'desc')
            ->get();

        return Inertia::render('Entities/Invoices/Index', [
            'entity' => $entity,
            'contracts' => $contracts,
            'invoices' => $invoices,
        ]);
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(SaveInvoiceRequest $request)
    {
        $validated = $request->validated();
        if (empty($validated['issue_date'])) {
            $validated['issue_date'] = $validated['invoice_date'];
        }

        $contract = Contract::findOrFail($validated['contract_id']);

        // Security check: Ensure user owns the entity associated with the contract
        if ($request->user()->cannot('update', $contract->entity)) {
            abort(403);
        }

        Invoice::create($validated);

        return redirect()->back()->with('success', 'Factura cargada correctamente.');
    }

    /**
     * Update the specified invoice in storage.
     */
    public function update(SaveInvoiceRequest $request, Invoice $invoice)
    {
        // Security check
        if ($request->user()->cannot('update', $invoice->contract->entity)) {
            abort(403);
        }

        $validated = $request->validated();
        if (empty($validated['issue_date'])) {
            $validated['issue_date'] = $validated['invoice_date'];
        }

        $invoice->update($validated);

        return redirect()->back()->with('success', 'Factura actualizada correctamente.');
    }

    /**
     * Remove the specified invoice from storage.
     */
    public function destroy(Request $request, Invoice $invoice)
    {
        // Security check
        if ($request->user()->cannot('update', $invoice->contract->entity)) {
            abort(403);
        }

        $invoice->delete();

        return redirect()->back()->with('success', 'Factura eliminada correctamente.');
    }
}
