@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('entities.home.index') }}">Mis Hogares</a></li>
                <li class="breadcrumb-item active">{{ $entity->name }}</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="bi bi-house-heart"></i> {{ $entity->name }}</h2>
            <span class="badge bg-primary fs-6">Hogar</span>
        </div>

        <div class="row">
            <!-- Información del Hogar -->
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información del Hogar</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span><strong>Dirección:</strong></span>
                                <span>{{ $entity->address_street }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><strong>Código postal:</strong></span>
                                <span>{{ $entity->address_postal_code }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><strong>Localidad:</strong></span>
                                <span>{{ $entity->locality->name ?? '-' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><strong>Superficie:</strong></span>
                                <span>{{ $entity->square_meters }} m²</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><strong>Personas:</strong></span>
                                <span>{{ $entity->people_count }}</span>
                            </li>
                            @if($entity->description)
                                <li class="list-group-item">
                                    <strong>Descripción:</strong><br>
                                    <span class="text-muted">{{ $entity->description }}</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                @if(isset($climateProfile) && !empty($climateProfile))
                    <div class="card shadow mb-4">
                        <div class="card-header bg-warning bg-opacity-25">
                            <h5 class="mb-0"><i class="bi bi-sun"></i> Perfil Solar</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4 border-end">
                                    <h4 class="mb-0 text-warning">{{ $climateProfile['avg_radiation'] }}</h4>
                                    <small class="text-muted">MJ/m² (Radiación)</small>
                                </div>
                                <div class="col-4 border-end">
                                    <h4 class="mb-0 text-warning">{{ $climateProfile['avg_sunshine_duration'] }}</h4>
                                    <small class="text-muted">Horas de Sol</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="mb-0 text-secondary">{{ $climateProfile['avg_cloud_cover'] }}%</h4>
                                    <small class="text-muted">Nubosidad</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Acciones Rápidas -->
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-grid"></i> Gestión del Hogar</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('entities.home.edit', $entity->id) }}" class="btn btn-warning">
                                <i class="bi bi-pencil-square"></i> Editar información
                            </a>
                            <a href="{{ route('entities.home.rooms', $entity->id) }}" class="btn btn-primary">
                                <i class="bi bi-door-open"></i> Gestionar habitaciones
                            </a>
                            <a href="{{ route('entities.home.invoices', $entity->id) }}" class="btn btn-info">
                                <i class="bi bi-receipt"></i> Gestionar facturas
                            </a>
                            <a href="{{ route('entities.home.meter', $entity->id) }}" class="btn btn-success">
                                <i class="bi bi-lightning"></i> Gestionar contrato/medidor
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-lightbulb"></i> Recomendaciones</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('entities.home.replacements', $entity->id) }}"
                                class="btn btn-outline-success position-relative">
                                <i class="bi bi-arrow-repeat"></i> Reemplazos Eficientes
                                @if(isset($replacementsCount) && $replacementsCount > 0)
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ $replacementsCount }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ route('entities.home.thermal', $entity->id) }}" class="btn btn-outline-primary">
                                <i class="bi bi-thermometer-half"></i> Salud Térmica
                            </a>
                            <a href="{{ route('entities.home.standby_analysis', $entity->id) }}"
                                class="btn btn-outline-secondary">
                                <i class="bi bi-power"></i> Consumo Fantasma
                            </a>
                            <a href="{{ route('entities.home.vacation', $entity->id) }}" class="btn btn-outline-info">
                                <i class="bi bi-airplane"></i> Modo Vacaciones
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerta si no hay facturas -->
        @if($entity->contracts->isEmpty() || $entity->invoices->isEmpty())
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                <strong>Paso recomendado:</strong> Para obtener análisis precisos, primero debes ingresar los datos de tu
                medidor y al menos una factura.
                <br>
                <a href="{{ route('entities.home.meter', $entity->id) }}" class="btn btn-outline-primary btn-sm mt-2">
                    <i class="bi bi-lightning"></i> Ingresar datos del medidor
                </a>
            </div>
        @endif

        <div class="mt-3">
            <a href="{{ route('entities.home.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver al listado de hogares
            </a>
        </div>
    </div>
@endsection