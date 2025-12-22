@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2><i class="bi bi-house-heart"></i> Mis Hogares</h2>
            <a href="{{ route('entities.home.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nuevo hogar
            </a>
        </div>

        <!-- Tabla de Hogares -->
        <div class="card shadow mb-4">
            <div class="card-body">
                @if($entities->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-house-door display-1 text-muted"></i>
                        <h4 class="mt-3 text-muted">Aún no tienes hogares registrados</h4>
                        <p class="text-muted">Comienza agregando tu primer hogar para gestionar su consumo energético.</p>
                        <a href="{{ route('entities.home.create') }}" class="btn btn-primary btn-lg mt-2">
                            <i class="bi bi-plus-circle"></i> Agregar mi primer hogar
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Localidad</th>
                                    <th>Metros²</th>
                                    <th>Personas</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($entities as $entity)
                                    <tr>
                                        <td>
                                            <i class="bi bi-house-door text-primary"></i>
                                            {{ $entity->name }}
                                        </td>
                                        <td>{{ $entity->locality->name ?? '-' }}</td>
                                        <td>{{ $entity->square_meters }} m²</td>
                                        <td>{{ $entity->people_count }}</td>
                                        <td>
                                            <a href="{{ route('entities.home.show', $entity->id) }}" class="btn btn-info btn-sm"
                                                title="Ver detalles">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('entities.home.edit', $entity->id) }}" class="btn btn-warning btn-sm"
                                                title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="{{ route('entities.home.rooms', $entity->id) }}"
                                                class="btn btn-secondary btn-sm" title="Habitaciones">
                                                <i class="bi bi-door-open"></i>
                                            </a>
                                            <a href="{{ route('entities.home.invoices', $entity->id) }}"
                                                class="btn btn-success btn-sm" title="Facturas">
                                                <i class="bi bi-receipt"></i>
                                            </a>
                                            <form action="{{ route('entities.home.destroy', $entity->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('¿Seguro que deseas eliminar este hogar?')"
                                                    title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
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

        @if($entities->isNotEmpty())
            @php $firstEntity = $entities->first(); @endphp

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
                                    <p class="card-text">Revisa el estado de tus facturas y realiza el <b>ajuste de uso</b> para
                                        obtener cálculos precisos.</p>
                                    <a href="{{ route('usage_adjustments.index') }}" class="btn btn-info">Ir a ajustes de
                                        uso</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-bar-chart-line"></i> Consumo energético</h5>
                                    <p class="card-text">Visualiza el consumo estimado y real de tus hogares, compara periodos y
                                        optimiza tu gestión.</p>
                                    <a href="{{ route('consumption.panel') }}" class="btn btn-success">Panel de consumo</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Centro de Recomendaciones para Hogares -->
            <div class="card mb-4 shadow">
                <div class="card-header bg-white">
                    <h4 class="mb-0"><i class="bi bi-lightbulb"></i> Centro de Recomendaciones</h4>
                    <small class="text-muted">Recomendaciones optimizadas para hogares</small>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Paneles Solares -->
                        <div class="col-md-4">
                            <div class="card border-warning shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-sun"></i> Paneles Solares</h5>
                                    <p class="card-text">Calcula el potencial de energía solar para tu hogar y solicita un
                                        presupuesto.</p>
                                    <a href="{{ route('entities.home.budget', $firstEntity->id) }}" class="btn btn-warning">
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
                                    <a href="{{ route('entities.home.solar_water_heater', $firstEntity->id) }}"
                                        class="btn btn-danger">Pedir presupuesto</a>
                                </div>
                            </div>
                        </div>

                        <!-- Reemplazos -->
                        <div class="col-md-4">
                            <div class="card border-primary shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-arrow-repeat"></i> Reemplazos</h5>
                                    <p class="card-text">Descubre qué equipos conviene renovar por eficiencia energética.</p>
                                    <a href="{{ route('entities.home.replacements', $firstEntity->id) }}"
                                        class="btn btn-primary">Ver
                                        recomendaciones</a>
                                </div>
                            </div>
                        </div>

                        <!-- Consumo Fantasma -->
                        <div class="col-md-4">
                            <div class="card border-secondary shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-power"></i> Consumo Fantasma</h5>
                                    <p class="card-text">Detecta y reduce el consumo de equipos en modo espera (Stand By).</p>
                                    <a href="{{ route('entities.home.standby_analysis', $firstEntity->id) }}"
                                        class="btn btn-secondary">Analizar Stand By</a>
                                </div>
                            </div>
                        </div>

                        <!-- Mantenimiento -->
                        <div class="col-md-4">
                            <div class="card border-info shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-tools"></i> Mantenimiento</h5>
                                    <p class="card-text">Gestiona el mantenimiento de tus aires, lavarropas y heladeras.</p>
                                    <a href="{{ route('entities.home.maintenance', $firstEntity->id) }}"
                                        class="btn btn-info">Ver
                                        mantenimientos</a>
                                </div>
                            </div>
                        </div>

                        <!-- Vacaciones -->
                        <div class="col-md-4">
                            <div class="card border-success shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-airplane"></i> Vacaciones</h5>
                                    <p class="card-text">Recomendaciones para ahorrar energía cuando no estás en casa.</p>
                                    <a href="{{ route('entities.home.vacation', $firstEntity->id) }}"
                                        class="btn btn-success">Modo
                                        Vacaciones</a>
                                </div>
                            </div>
                        </div>

                        <!-- Salud Térmica -->
                        <div class="col-md-4">
                            <div class="card border-danger shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-thermometer-half"></i> Salud Térmica</h5>
                                    <p class="card-text">Diagnostica la aislación de tu hogar y recibe recomendaciones.</p>
                                    <a href="{{ route('entities.home.thermal', $firstEntity->id) }}"
                                        class="btn btn-danger">Diagnóstico
                                        Térmico</a>
                                </div>
                            </div>
                        </div>

                        <!-- Medidor Inteligente -->
                        <div class="col-md-4">
                            <div class="card border-primary shadow-sm h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-speedometer2"></i> Medidor Inteligente</h5>
                                    <p class="card-text">Conoce los beneficios de la medición inteligente y solicítalo.</p>
                                    <a href="{{ route('entities.home.smart_meter_demo', $firstEntity->id) }}"
                                        class="btn btn-primary">Ver Demo
                                        en Vivo</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection