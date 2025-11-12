@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Contrato</h1>
    <form method="POST" action="{{ route('contracts.update', $contract->id) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="entity_id" class="form-label">Hogar/Entidad</label>
            <select class="form-control" id="entity_id" name="entity_id" required>
                @foreach($entities as $entity)
                    <option value="{{ $entity->id }}" @if($contract->entity_id == $entity->id) selected @endif>{{ $entity->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="supply_number" class="form-label">N° de Suministro</label>
            <input type="text" class="form-control" id="supply_number" name="supply_number" value="{{ $contract->supply_number }}" required>
        </div>
        <div class="mb-3">
            <label for="proveedor_id" class="form-label">Empresa Distribuidora</label>
            <select class="form-control" id="proveedor_id" name="proveedor_id" required>
                @foreach($proveedores as $proveedor)
                    <option value="{{ $proveedor->id }}" @if($contract->proveedor_id == $proveedor->id) selected @endif>{{ $proveedor->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="serial_number" class="form-label">N° de Serie del Medidor</label>
            <input type="text" class="form-control" id="serial_number" name="serial_number" value="{{ $contract->serial_number }}">
        </div>
        <div class="mb-3">
            <label for="contract_identifier" class="form-label">Identificador de Contrato</label>
            <input type="text" class="form-control" id="contract_identifier" name="contract_identifier" value="{{ $contract->contract_identifier }}">
        </div>
        <div class="mb-3">
            <label for="rate_name" class="form-label">Nombre de Tarifa</label>
            <input type="text" class="form-control" id="rate_name" name="rate_name" value="{{ $contract->rate_name }}" required>
        </div>
        <div class="mb-3">
            <label for="contracted_power_kw_p1" class="form-label">Potencia Contratada P1 (kW)</label>
            <input type="number" step="0.001" class="form-control" id="contracted_power_kw_p1" name="contracted_power_kw_p1" value="{{ $contract->contracted_power_kw_p1 }}">
        </div>
        <div class="mb-3">
            <label for="contracted_power_kw_p2" class="form-label">Potencia Contratada P2 (kW)</label>
            <input type="number" step="0.001" class="form-control" id="contracted_power_kw_p2" name="contracted_power_kw_p2" value="{{ $contract->contracted_power_kw_p2 }}">
        </div>
        <div class="mb-3">
            <label for="contracted_power_kw_p3" class="form-label">Potencia Contratada P3 (kW)</label>
            <input type="number" step="0.001" class="form-control" id="contracted_power_kw_p3" name="contracted_power_kw_p3" value="{{ $contract->contracted_power_kw_p3 }}">
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Fecha de Inicio</label>
            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $contract->start_date }}" required>
        </div>
        <div class="mb-3">
            <label for="end_date" class="form-label">Fecha de Fin</label>
            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $contract->end_date }}">
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" @if($contract->is_active) checked @endif>
            <label class="form-check-label" for="is_active">Contrato activo</label>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('contracts.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
