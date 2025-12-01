@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="bi bi-airplane"></i> Plan de Ahorro: Vacaciones ({{ $days }} días)</h2>
            <p class="text-muted mb-0">Sigue esta lista para ahorrar y proteger tu hogar.</p>
        </div>
        <a href="{{ route('vacation.index', $entity->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <!-- Resumen de Ahorro -->
    <div class="card bg-success text-white shadow mb-4">
        <div class="card-body text-center p-4">
            <h5 class="card-title text-uppercase opacity-75">Ahorro Potencial Total</h5>
            <h1 class="display-3 fw-bold">${{ number_format($result['total_savings'], 0, ',', '.') }}</h1>
            <p class="card-text fs-5">Si completas esta lista antes de salir.</p>
        </div>
    </div>

    <div class="row">
        <!-- Columna Izquierda: Checklist -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Tu Checklist de Salida</h5>
                </div>
                <div class="list-group list-group-flush">
                    @foreach($result['checklist'] as $item)
                        <label class="list-group-item d-flex gap-3 align-items-center p-3">
                            <input class="form-check-input flex-shrink-0" type="checkbox" style="font-size: 1.5em;">
                            <div class="d-flex gap-3 w-100 justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0 d-flex align-items-center gap-2">
                                        <i class="bi {{ $item['icon'] }} text-{{ $item['color'] }}"></i>
                                        {{ $item['title'] }}
                                        @if($item['category'] == 'critical')
                                            <span class="badge bg-danger">Crítico</span>
                                        @elseif($item['category'] == 'security')
                                            <span class="badge bg-dark">Seguridad</span>
                                        @endif
                                    </h6>
                                    <p class="mb-0 opacity-75">{{ $item['description'] }}</p>
                                    <div class="text-primary fw-bold mt-1">{{ $item['action'] }}</div>
                                </div>
                                @if(isset($item['savings']) && $item['savings'] > 0)
                                    <div class="text-end">
                                        <small class="text-muted d-block">Ahorro</small>
                                        <span class="text-success fw-bold">+${{ number_format($item['savings'], 0, ',', '.') }}</span>
                                    </div>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Consejos -->
        <div class="col-md-4">
            <div class="card border-info shadow-sm mb-3">
                <div class="card-body">
                    <h5 class="card-title text-info"><i class="bi bi-lightbulb"></i> Tip Pro</h5>
                    <p class="card-text">
                        ¿Vas a dejar luces prendidas por seguridad? Una luz fija las 24hs gasta y delata que no estás.
                    </p>
                    <hr>
                    <p class="mb-0">
                        <strong>Recomendamos:</strong> Foco Inteligente que se programa desde el celular para prenderse solo de 20hs a 23hs.
                    </p>
                </div>
            </div>
            
            <div class="card border-warning shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-warning"><i class="bi bi-shield-check"></i> Antes de cerrar</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="bi bi-check2"></i> Cerrar llave de paso de agua.</li>
                        <li class="mb-2"><i class="bi bi-check2"></i> Cerrar llave de gas (si aplica).</li>
                        <li><i class="bi bi-check2"></i> Tirar la basura.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
