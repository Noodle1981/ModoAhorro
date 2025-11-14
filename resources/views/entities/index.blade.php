@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-house-door"></i> Mis entidades</h2>
        <a href="{{ route('entities.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva entidad
        </a>
    </div>

    <!-- Grupo de tarjetas resumen -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-info shadow-sm">
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
            <div class="card border-success shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-bar-chart-line"></i> Consumo energético</h5>
                    <p class="card-text">Visualiza el consumo estimado y real de tus entidades, compara periodos y optimiza tu gestión.</p>
                    <a href="{{ route('consumption.panel') }}" class="btn btn-success">Panel de consumo</a>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow">
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
</div>
@endsection
