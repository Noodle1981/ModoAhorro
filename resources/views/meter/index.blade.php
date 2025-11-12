@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Medidor del Hogar</h1>
    @if($meter)
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Datos del Medidor</h5>
                <p><strong>Número de Serie:</strong> {{ $meter->serial_number }}</p>
                <p><strong>Empresa proveedora:</strong> {{ $meter->company->name }}</p>
                <p><strong>Instalado el:</strong> {{ $meter->installed_at }}</p>
                <a href="{{ route('entities.meter.edit', [$entity->id, $meter->id]) }}" class="btn btn-primary">Editar Medidor</a>
                    <a href="{{ route('entities.show', $entity->id) }}" class="btn btn-secondary mt-2">Volver al hogar</a>
            </div>
        </div>
    @else
        <div class="alert alert-info">No hay medidor registrado para este hogar.</div>
        <a href="{{ route('entities.meter.create', $entity->id) }}" class="btn btn-success">Registrar Medidor</a>
        <a href="{{ route('entities.show', $entity->id) }}" class="btn btn-secondary mt-2">Volver al hogar</a>
    @endif
</div>
@endsection
