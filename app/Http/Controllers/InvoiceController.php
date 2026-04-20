<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Contract;
use App\Models\Entity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the invoices for the active entity.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $activeEntityId = session('active_entity_id');
        
        // If no active entity in session, get the first one available for the user
        $entity = null;
        if ($activeEntityId) {
            $entity = $user->entities()->where('entities.id', $activeEntityId)->first();
        }
        
        if (!$entity) {
            $entity = $user->entities()->first();
            if ($entity) {
                session(['active_entity_id' => $entity->id]);
            }
        }

        if (!$entity) {
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'invoice_number' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_energy_consumed_kwh' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'cost_for_energy' => 'nullable|numeric|min:0',
            'cost_for_power' => 'nullable|numeric|min:0',
            'taxes' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
        ]);

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
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'invoice_number' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_energy_consumed_kwh' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'cost_for_energy' => 'nullable|numeric|min:0',
            'cost_for_power' => 'nullable|numeric|min:0',
            'taxes' => 'nullable|numeric|min:0',
            'other_charges' => 'nullable|numeric|min:0',
        ]);

        // Security check
        if ($request->user()->cannot('update', $invoice->contract->entity)) {
            abort(403);
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
