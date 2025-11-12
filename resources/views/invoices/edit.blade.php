@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Factura</h1>
    <form method="POST" action="{{ route('entities.invoices.update', [$entity->id, $invoice->id]) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="invoice_number" class="form-label">N° de Factura</label>
            <input type="text" class="form-control" id="invoice_number" name="invoice_number" value="{{ $invoice->invoice_number }}">
        </div>
        <div class="mb-3">
            <label for="invoice_date" class="form-label">Fecha de Factura</label>
            <input type="date" class="form-control" id="invoice_date" name="invoice_date" value="{{ $invoice->invoice_date }}">
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Fecha Inicio</label>
            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $invoice->start_date }}" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">Fecha Fin</label>
            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $invoice->end_date }}" required>
        </div>
        <div class="mb-3">
            <label for="energy_consumed_p1_kwh" class="form-label">Consumo P1 (kWh)</label>
            <input type="number" step="0.001" class="form-control" id="energy_consumed_p1_kwh" name="energy_consumed_p1_kwh" value="{{ $invoice->energy_consumed_p1_kwh }}">
        </div>
        <div class="mb-3">
            <label for="energy_consumed_p2_kwh" class="form-label">Consumo P2 (kWh)</label>
            <input type="number" step="0.001" class="form-control" id="energy_consumed_p2_kwh" name="energy_consumed_p2_kwh" value="{{ $invoice->energy_consumed_p2_kwh }}">
        </div>
        <div class="mb-3">
            <label for="energy_consumed_p3_kwh" class="form-label">Consumo P3 (kWh)</label>
            <input type="number" step="0.001" class="form-control" id="energy_consumed_p3_kwh" name="energy_consumed_p3_kwh" value="{{ $invoice->energy_consumed_p3_kwh }}">
        </div>
        <div class="mb-3">
            <label for="total_energy_consumed_kwh" class="form-label">Consumo Total (kWh)</label>
            <input type="number" step="0.001" class="form-control" id="total_energy_consumed_kwh" name="total_energy_consumed_kwh" value="{{ $invoice->total_energy_consumed_kwh }}" required>
        </div>
        <div class="mb-3">
            <label for="cost_for_energy" class="form-label">Costo Energía</label>
            <input type="number" step="0.01" class="form-control" id="cost_for_energy" name="cost_for_energy" value="{{ $invoice->cost_for_energy }}">
        </div>
        <div class="mb-3">
            <label for="cost_for_power" class="form-label">Costo Potencia</label>
            <input type="number" step="0.01" class="form-control" id="cost_for_power" name="cost_for_power" value="{{ $invoice->cost_for_power }}">
        </div>
        <div class="mb-3">
            <label for="taxes" class="form-label">Impuestos</label>
            <input type="number" step="0.01" class="form-control" id="taxes" name="taxes" value="{{ $invoice->taxes }}">
        </div>
        <div class="mb-3">
            <label for="other_charges" class="form-label">Otros Cargos</label>
            <input type="number" step="0.01" class="form-control" id="other_charges" name="other_charges" value="{{ $invoice->other_charges }}">
        </div>
        <div class="mb-3">
            <label for="total_amount" class="form-label">Importe Total</label>
            <input type="number" step="0.01" class="form-control" id="total_amount" name="total_amount" value="{{ $invoice->total_amount }}" required>
        </div>
        {{-- Campo oculto para Energía Inyectada (kWh), solo se usará en otro contexto --}}
        {{-- Campo oculto para Compensación Excedente, solo se analizará en otro contexto --}}
        {{-- Campo oculto para Archivo, no se mostrará en el formulario --}}
        <div class="mb-3">
            <label for="source" class="form-label">Fuente</label>
            <input type="text" class="form-control" id="source" name="source" value="{{ $invoice->source }}">
        </div>
        {{-- Campo oculto para Huella CO2 (kg), solo se analizará en otro contexto --}}
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('entities.invoices.index', $entity->id) }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
