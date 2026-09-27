<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveInvoiceRequest;
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

        // Get invoices directly for this entity
        $invoices = Invoice::where('entity_id', $entity->id)
            ->orderBy('start_date', 'desc')
            ->get();

        return Inertia::render('Entities/Invoices/Index', [
            'entity' => $entity,
            'contracts' => [],
            'invoices' => $invoices,
        ]);
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(SaveInvoiceRequest $request)
    {
        $entity = $this->getActiveEntity($request);

        if (! $entity || $request->user()->cannot('update', $entity)) {
            abort(403);
        }

        $validated = $request->validated();
        if (empty($validated['issue_date'])) {
            $validated['issue_date'] = $validated['invoice_date'] ?? $validated['start_date'] ?? now();
        }
        $validated['entity_id'] = $entity->id;

        Invoice::create($validated);

        return redirect()->back()->with('success', 'Lectura de consumo registrada correctamente.');
    }

    /**
     * Update the specified invoice in storage.
     */
    public function update(SaveInvoiceRequest $request, Invoice $invoice)
    {
        $entity = $invoice->entity ?? $this->getActiveEntity($request);

        if (! $entity || $request->user()->cannot('update', $entity)) {
            abort(403);
        }

        $validated = $request->validated();
        if (empty($validated['issue_date'])) {
            $validated['issue_date'] = $validated['invoice_date'] ?? $validated['start_date'] ?? now();
        }

        $invoice->update($validated);

        return redirect()->back()->with('success', 'Lectura de consumo actualizada correctamente.');
    }

    /**
     * Remove the specified invoice from storage.
     */
    public function destroy(Request $request, Invoice $invoice)
    {
        $entity = $invoice->entity ?? $this->getActiveEntity($request);

        if (! $entity || $request->user()->cannot('update', $entity)) {
            abort(403);
        }

        $invoice->delete();

        return redirect()->back()->with('success', 'Lectura eliminada correctamente.');
    }
}
