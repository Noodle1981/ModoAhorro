@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-house-door"></i> Mis entidades</h2>
        <a href="{{ route('entities.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva entidad
        </a>
    </div>

    <!-- Tabla de Entidades -->
    <div class="card shadow mb-4">
        <div class="card-body">
            @if($entities->isEmpty())
                <p class="text-muted">No hay entidades registradas aún.</p>
            @else
                <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Localidad</th>
                                <th>Metros²</th>
                                <th>Personas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entities as $entity)
                                <tr>
                                    <td>{{ $entity->name }}</td>
                                    <td>{{ $entity->type }}</td>
                                    <td>{{ $entity->locality->name ?? '-' }}</td>
                                    <td>{{ $entity->square_meters }}</td>
                                    <td>{{ $entity->people_count }}</td>
                                    <td>
                                        <a href="{{ route('entities.show', $entity->id) }}" class="btn btn-info btn-sm"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('entities.edit', $entity->id) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i></a>
                                        <a href="{{ route('rooms.index', $entity->id) }}" class="btn btn-secondary btn-sm"><i class="bi bi-door-open"></i> Gestionar habitaciones</a>
                                        <a href="{{ route('entities.invoices.index', $entity->id) }}" class="btn btn-success btn-sm"><i class="bi bi-receipt"></i> Gestionar facturas</a>
                                        <form action="{{ route('entities.destroy', $entity->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar esta entidad?')"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Centro de Consumo -->
    <div class="card mb-4 shadow">
        <div class="card-header bg-white">
            <h4 class="mb-0"><i class="bi bi-lightning-charge"></i> Centro de Consumo</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card border-info shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-receipt"></i> Facturas y Ajustes</h5>
                            <p class="card-text">Revisa el estado de tus facturas y realiza el <b>ajuste de uso</b> para obtener cálculos precisos.</p>
                            @if(Route::has('usage_adjustments.index') && Auth::check())
                                <a href="{{ route('usage_adjustments.index') }}" class="btn btn-info">Ir a ajustes de uso</a>
                            @else
                                <a href="#" class="btn btn-info disabled">Ajustes no disponibles</a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-success shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-bar-chart-line"></i> Consumo energético</h5>
                            <p class="card-text">Visualiza el consumo estimado y real de tus entidades, compara periodos y optimiza tu gestión.</p>
                            <a href="{{ route('consumption.panel') }}" class="btn btn-success">Panel de consumo</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Centro de Recomendaciones -->
    <div class="card mb-4 shadow">
        <div class="card-header bg-white">
            <h4 class="mb-0"><i class="bi bi-lightbulb"></i> Centro de Recomendaciones</h4>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-warning shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-sun"></i> Paneles Solares</h5>
                            <p class="card-text">Calcula el potencial de energía solar para tu propiedad y solicita un presupuesto personalizado.</p>
                            <a href="{{ route('entities.budget', $entity->id) }}" class="btn btn-warning">
                                <i class="bi bi-calculator"></i> Pedir presupuesto
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Calefones Solares -->
                <div class="col-md-4">
                    <div class="card border-danger shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-droplet-half"></i> Calefones Solares</h5>
                            <p class="card-text">Ahorra gas o electricidad calentando agua con energía solar.</p>
                            @if($entities->isNotEmpty())
                                <a href="{{ route('entities.solar_water_heater', $entities->first()->id) }}" class="btn btn-danger">Pedir presupuesto</a>
                            @else
                                <button class="btn btn-danger disabled">Crear entidad primero</button>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- Recomendaciones de Reemplazos -->
                <div class="col-md-4">
                    <div class="card border-primary shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-arrow-repeat"></i> Reemplazos</h5>
                            <p class="card-text">Descubre qué equipos conviene renovar por eficiencia energética.</p>
                            <a href="#" class="btn btn-primary">Ver recomendaciones</a>
                        </div>
                    </div>
                </div>
                <!-- Consumo Fantasma -->
                <div class="col-md-4">
                    <div class="card border-secondary shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-power"></i> Consumo Fantasma</h5>
                            <p class="card-text">Detecta y reduce el consumo de equipos en modo espera (Stand By).</p>
                            <a href="#" class="btn btn-secondary">Analizar Stand By</a>
                        </div>
                    </div>
                </div>
                <!-- Mantenimiento -->
                <div class="col-md-4">
                    <div class="card border-info shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-tools"></i> Mantenimiento</h5>
                            <p class="card-text">Gestiona el mantenimiento de tus aires, lavarropas y heladeras.</p>
                            @if($entities->isNotEmpty())
                                <a href="{{ route('maintenance.index', $entities->first()->id) }}" class="btn btn-info">Ver mantenimientos</a>
                            @else
                                <button class="btn btn-info disabled">Crear entidad primero</button>
                            @endif
                        </div>
                    </div>
                </div>
                <!-- Vacaciones -->
                <div class="col-md-4">
                    <div class="card border-success shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-airplane"></i> Vacaciones</h5>
                            <p class="card-text">Recomendaciones para ahorrar energía cuando no estás en casa.</p>
                            <a href="#" class="btn btn-success">Modo Vacaciones</a>
                        </div>
                    </div>
                </div>
                <!-- Usos Horarios -->
                <div class="col-md-4">
                    <div class="card border-dark shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-clock"></i> Usos Horarios</h5>
                            <p class="card-text">Aprovecha las tarifas reducidas usando tus equipos en horarios óptimos.</p>
                            <a href="#" class="btn btn-dark">Ver horarios</a>
                        </div>
                    </div>
                </div>
                <!-- Medidor Inteligente -->
                <div class="col-md-4">
                    <div class="card border-primary shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-speedometer2"></i> Medidor Inteligente</h5>
                            <p class="card-text">Conoce los beneficios de la medición inteligente y solicítalo.</p>
                            <a href="#" class="btn btn-primary">Solicitar presupuesto</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
