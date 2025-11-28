@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2><i class="bi bi-house-door"></i> Detalles de la entidad hogar</h2>
    <div class="card shadow mt-3">
        <div class="card-body">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Nombre:</strong> {{ $entity->name }}</li>
                <li class="list-group-item"><strong>Calle:</strong> {{ $entity->address_street }}</li>
                <li class="list-group-item"><strong>Código postal:</strong> {{ $entity->address_postal_code }}</li>
                <li class="list-group-item"><strong>Localidad:</strong> {{ $entity->locality->name ?? '-' }}</li>
                <li class="list-group-item"><strong>Metros cuadrados:</strong> {{ $entity->square_meters }}</li>
                <li class="list-group-item"><strong>Cantidad de personas:</strong> {{ $entity->people_count }}</li>
                
                @if(isset($climateProfile) && !empty($climateProfile))
                    <li class="list-group-item bg-light">
                        <strong><i class="bi bi-sun"></i> Perfil Solar (Promedio):</strong>
                        <div class="row mt-2">
                            <div class="col-4 text-center border-end">
                                <h5 class="mb-0 text-warning">{{ $climateProfile['avg_radiation'] }}</h5>
                                <small class="text-muted">MJ/m²</small>
                            </div>
                            <div class="col-4 text-center border-end">
                                <h5 class="mb-0 text-warning">{{ $climateProfile['avg_sunshine_duration'] }}</h5>
                                <small class="text-muted">Horas Sol</small>
                            </div>
                            <div class="col-4 text-center">
                                <h5 class="mb-0 text-secondary">{{ $climateProfile['avg_cloud_cover'] }}%</h5>
                                <small class="text-muted">Nubosidad</small>
                            </div>
                        </div>
                    </li>
                @endif
            </ul>
            <div class="mt-3 d-flex gap-2">
                <a href="{{ route('entities.edit', $entity->id) }}" class="btn btn-warning">
                    <i class="bi bi-pencil-square"></i> Editar
                </a>
                <a href="{{ route('rooms.index', $entity->id) }}" class="btn btn-primary">
                    <i class="bi bi-door-open"></i> Gestionar habitaciones
                </a>
                <a href="{{ route('equipment.index') }}" class="btn btn-secondary">
                    <i class="bi bi-laptop"></i> Todos los equipos
                </a>
                <a href="#" class="btn btn-info" onclick="window.location.href='/entities/{{ $entity->id }}/invoices'; return false;">
                    <i class="bi bi-receipt"></i> Gestionar facturas
                </a>
                <a href="/entities/{{ $entity->id }}/meter" class="btn btn-success">
                    <i class="bi bi-lightning"></i> Gestionar contrato/medidor
                </a>
                <a href="{{ route('entities.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al listado
                </a>
            </div>

            {{-- Mensaje si no hay facturas --}}
            @if(empty($entity->invoices) || $entity->invoices->isEmpty())
                <div class="alert alert-warning mt-4">
                    <i class="bi bi-exclamation-triangle"></i> Para gestionar facturas primero debes ingresar los datos de tu medidor.<br>
                    <a href="/entities/{{ $entity->id }}/meter" class="btn btn-outline-primary btn-sm mt-2">Ingresar datos del medidor</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
