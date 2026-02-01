<?php
namespace App\Http\Controllers\Physical;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Entity;
use App\Models\Invoice;

class InvoiceController extends Controller
{
    public function index($entityId)
    {
        $entity = Entity::findOrFail($entityId);
        $config = config("entity_types.{$entity->type}", []);
        $contract = $entity->contracts()->where('is_active', true)->first();
        $invoices = $contract ? $contract->invoices()->with('usageAdjustment')->orderBy('start_date', 'desc')->get() : collect();
        return view('invoices.index', compact('entity', 'contract', 'invoices', 'config'));
    }

    public function create($entityId)
    {
        $entity = Entity::findOrFail($entityId);
        $config = config("entity_types.{$entity->type}", []);
        $contract = $entity->contracts()->where('is_active', true)->first();
        if (!$contract) {
            return redirect()->route($config['route_prefix'] . '.invoices', $entityId)
                ->with('warning', 'No hay contrato activo registrado para esta entidad.');
        }
        return view('invoices.create', compact('entity', 'contract', 'config'));
    }

    public function store(Request $request, $entityId)
    {
        $entity = Entity::findOrFail($entityId);
        $config = config("entity_types.{$entity->type}", []);
        $contract = $entity->contracts()->where('is_active', true)->first();
        if (!$contract) {
            return redirect()->route($config['route_prefix'] . '.invoices', $entityId)
                ->with('warning', 'No hay contrato activo registrado para esta entidad.');
        }
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'total_energy_consumed_kwh' => 'required|numeric',
            'total_amount' => 'required|numeric',
        ]);

        $data = $request->all();
        // Fix for missing issue_date
        $data['issue_date'] = $data['invoice_date'] ?? now();
        
        // Round monetary values to avoid decimal issues
        $monetaryFields = ['cost_for_energy', 'cost_for_power', 'taxes', 'other_charges', 'total_amount'];
        foreach ($monetaryFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = round($data[$field]);
            }
        }

        $invoice = new Invoice($data);
        $invoice->contract_id = $contract->id;
        $invoice->save();
        return redirect()->route($config['route_prefix'] . '.invoices', $entityId)
            ->with('success', 'Factura cargada correctamente.');
    }

    public function edit($entityId, $invoiceId)
    {
        $entity = Entity::findOrFail($entityId);
        $config = config("entity_types.{$entity->type}", []);
        $contract = $entity->contracts()->where('is_active', true)->first();
        $invoice = Invoice::where('contract_id', optional($contract)->id)->findOrFail($invoiceId);
        return view('invoices.edit', compact('entity', 'invoice', 'contract', 'config'));
    }

    public function update(Request $request, $entityId, $invoiceId)
    {
        $entity = Entity::findOrFail($entityId);
        $config = config("entity_types.{$entity->type}", []);
        $contract = $entity->contracts()->where('is_active', true)->first();
        $invoice = Invoice::where('contract_id', optional($contract)->id)->findOrFail($invoiceId);
        
        $data = $request->all();
        // Fix for missing issue_date
        $data['issue_date'] = $data['invoice_date'] ?? $invoice->issue_date;

        // Round monetary values
        $monetaryFields = ['cost_for_energy', 'cost_for_power', 'taxes', 'other_charges', 'total_amount'];
        foreach ($monetaryFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = round($data[$field]);
            }
        }

        $invoice->update($data);
        return redirect()->route($config['route_prefix'] . '.invoices', $entityId)
            ->with('success', 'Factura actualizada correctamente.');
    }

    public function destroy($entityId, $invoiceId)
    {
        $entity = Entity::findOrFail($entityId);
        $config = config("entity_types.{$entity->type}", []);
        $contract = $entity->contracts()->where('is_active', true)->first();
        $invoice = Invoice::where('contract_id', optional($contract)->id)->findOrFail($invoiceId);
        $invoice->delete();
        
        return redirect()->route($config['route_prefix'] . '.invoices', $entityId)
            ->with('success', 'Factura eliminada correctamente.');
    }
}
