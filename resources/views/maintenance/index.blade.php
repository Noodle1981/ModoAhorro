@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-tools"></i> Mantenimiento - {{ $entity->name }}</h2>
        <a href="{{ route('entities.index') }}" class="btn btn-secondary">Volver</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        @forelse($maintenanceData as $data)
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100 {{ $data['status']['health_score'] < 70 ? 'border-danger' : ($data['status']['health_score'] < 90 ? 'border-warning' : 'border-success') }}">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $data['equipment']->name }}</h5>
                        <span class="badge {{ $data['status']['health_score'] < 70 ? 'bg-danger' : ($data['status']['health_score'] < 90 ? 'bg-warning text-dark' : 'bg-success') }}">
                            Salud: {{ $data['status']['health_score'] }}%
                        </span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-2">
                            <small>Ubicación: {{ $data['equipment']->room->name ?? '-' }}</small>
                        </p>
                        
                        @if($data['status']['penalty_factor'] > 1.0)
                            <div class="alert alert-danger py-2 mb-3">
                                <i class="bi bi-exclamation-triangle"></i> 
                                Penalización de consumo: <strong>+{{ round(($data['status']['penalty_factor'] - 1) * 100) }}%</strong>
                            </div>
                        @else
                            <div class="alert alert-success py-2 mb-3">
                                <i class="bi bi-check-circle"></i> Equipo optimizado
                            </div>
                        @endif

                        <h6 class="mt-3">Tareas Pendientes:</h6>
                        @if(empty($data['status']['pending_tasks']))
                            <p class="text-success"><i class="bi bi-check-all"></i> Todo al día</p>
                        @else
                            <ul class="list-group list-group-flush mb-3">
                                @foreach($data['status']['pending_tasks'] as $task)
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                        <div>
                                            <strong>{{ $task['task'] }}</strong>
                                            @if(str_contains($task['task'], 'Filtros'))
                                                <a href="#" class="text-decoration-none ms-1" data-bs-toggle="modal" data-bs-target="#filterModal">
                                                    <small>❓ ¿Cómo lo hago?</small>
                                                </a>
                                            @endif
                                            <br>
                                            <small class="text-danger">Vence: {{ $task['due_date'] }}</small>
                                        </div>
                                        <span class="badge bg-secondary">Impacto: {{ $task['impact'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <hr>
                        <h6>Registrar Mantenimiento:</h6>
                        <form action="{{ route('maintenance.log.store', $data['equipment']->id) }}" method="POST">
                            @csrf
                            <div class="input-group mb-2">
                                <select name="maintenance_task_id" class="form-select" required>
                                    <option value="">Seleccionar tarea realizada...</option>
                                    @foreach($data['equipment']->type->maintenanceTasks as $task)
                                        <option value="{{ $task->id }}">{{ $task->title }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary">Registrar</button>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="alert('Funcionalidad de solicitud de técnico próximamente.')">
                                    👷 Solicitar Técnico
                                </button>
                            </div>
                            <input type="text" name="notes" class="form-control form-control-sm mt-2" placeholder="Notas opcionales (ej: Cambio de filtro marca X)">
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    No hay equipos que requieran mantenimiento en esta entidad, o no se han configurado las tareas para los tipos de equipos existentes.
                </div>
            </div>
        @endforelse
    </div>
    </div>
</div>

<!-- Modal Limpieza Filtros -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cómo limpiar los filtros del Aire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ol>
                    <li class="mb-2"><strong>Levanta la tapa frontal:</strong> Con el equipo apagado, busca las muescas laterales y levanta suavemente el panel frontal.</li>
                    <li class="mb-2"><strong>Saca la malla plástica:</strong> Verás unos filtros de red. Desengánchalos y retíralos con cuidado.</li>
                    <li class="mb-2"><strong>Lávalos:</strong> Pásalos bajo el grifo con agua tibia. Si están muy sucios, usa un poco de detergente neutro.</li>
                    <li class="mb-2"><strong>Seca y coloca:</strong> Déjalos secar a la sombra (no al sol directo) y vuelve a colocarlos en su lugar.</li>
                </ol>
                <div class="alert alert-info">
                    <small>💡 Un filtro limpio mejora el rendimiento y reduce el consumo hasta un 15%.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>
@endsection
