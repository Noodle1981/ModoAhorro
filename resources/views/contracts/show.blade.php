@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Contrato de Suministro</h1>
    @if($contract)
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Datos del Contrato</h5>
                <p><strong>N° de Suministro:</strong> {{ $contract->supply_number }}</p>
                <p><strong>N° de Serie del Medidor:</strong> {{ $contract->serial_number }}</p>
                <p><strong>Empresa Distribuidora:</strong> {{ $contract->utilityCompany->name ?? '-' }}</p>
                <p><strong>Empresa Distribuidora:</strong> {{ $contract->proveedor->name ?? '-' }}</p>
                <p><strong>Empresa Distribuidora:</strong> {{ $contract->proveedor->name ?? '-' }}</p>
                <p><strong>Identificador de Contrato:</strong> {{ $contract->contract_identifier }}</p>
                <p><strong>Tarifa:</strong> {{ $contract->rate_name }}</p>
                <p><strong>Potencia Contratada P1:</strong> {{ $contract->contracted_power_kw_p1 }} kW</p>
                <p><strong>Potencia Contratada P2:</strong> {{ $contract->contracted_power_kw_p2 }} kW</p>
                <p><strong>Potencia Contratada P3:</strong> {{ $contract->contracted_power_kw_p3 }} kW</p>
                <p><strong>Inicio:</strong> {{ $contract->start_date }}</p>
                <p><strong>Fin:</strong> {{ $contract->end_date ?? '-' }}</p>
                <p><strong>Activo:</strong> {{ $contract->is_active ? 'Sí' : 'No' }}</p>
                <a href="{{ route('contracts.edit', $contract->id) }}" class="btn btn-primary">Editar Contrato</a>
            </div>
        </div>
    @else
        <div class="alert alert-info">No hay contrato registrado para este hogar.</div>
        <a href="{{ route('contracts.create') }}?entity_id={{ $entity->id }}" class="btn btn-success">Registrar Contrato</a>
    @endif
    <a href="{{ route('entities.show', $entity->id) }}" class="btn btn-secondary mt-2">Volver al hogar</a>
</div>
@endsection
