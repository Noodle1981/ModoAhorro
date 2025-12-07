@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Header Resultado -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-10">
            <div class="card shadow border-left-{{ $scoreResult['color'] }}">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center border-right">
                            <h5 class="text-uppercase text-muted mb-2">Calificación Térmica</h5>
                            <div class="display-3 font-weight-bold text-{{ $scoreResult['color'] }}">
                                {{ $scoreResult['label'] }}
                            </div>
                            <div class="text-xs text-muted font-weight-bold">SCORE: {{ $scoreResult['score'] }}/100</div>
                        </div>
                        <div class="col-md-8 pl-md-5">
                            <h3 class="font-weight-bold text-gray-800 mb-2">
                                @if($scoreResult['score'] >= 75)
                                    ¡Excelente Aislación! 🏠✨
                                @elseif($scoreResult['score'] >= 50)
                                    Aislación Aceptable 🏠⚠️
                                @else
                                    Tu casa pierde energía 🏠💨
                                @endif
                            </h3>
                            <p class="mb-0">
                                @if($scoreResult['score'] >= 75)
                                    Tu vivienda retiene bien la temperatura. Tus equipos de climatización trabajan eficientemente.
                                @elseif($scoreResult['score'] >= 50)
                                    Hay margen de mejora. Podrías reducir tu consumo atacando puntos específicos.
                                @else
                                    Detectamos fugas térmicas importantes. Tu aire acondicionado trabaja el doble para compensar la falta de aislación.
                                @endif
                            </p>
                            <div class="mt-3">
                                <a href="{{ route('thermal.wizard', $entity) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-redo"></i> Recalcular
                                </a>
                                <a href="{{ route('entities.show', $entity) }}" class="btn btn-sm btn-primary">
                                    Volver al Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recomendaciones -->
    @if(count($recommendations) > 0)
        <h4 class="mb-3 text-gray-800 pl-3 border-left-primary">Top 3 Mejoras Recomendadas</h4>
        <div class="row">
            @foreach($recommendations as $rec)
                <div class="col-md-4 mb-4">
                    <div class="card shadow h-100 border-bottom-{{ $rec['color'] }}">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-circle bg-{{ $rec['color'] }} text-white mr-3">
                                    <i class="{{ $rec['icon'] }}"></i>
                                </div>
                                <h5 class="font-weight-bold text-gray-800 mb-0">{{ $rec['title'] }}</h5>
                            </div>
                            <div class="mb-3">
                                <span class="badge badge-danger">Problema</span>
                                <small class="d-block mt-1">{{ $rec['problem'] }}</small>
                            </div>
                            <div class="mb-3">
                                <span class="badge badge-success">Solución</span>
                                <p class="font-weight-bold mt-1 text-{{ $rec['color'] }}">{{ $rec['solution'] }}</p>
                            </div>
                            <div class="row text-center mt-4">
                                <div class="col-6 border-right">
                                    <small class="text-uppercase text-muted font-weight-bold">Costo</small>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $rec['cost_level'] }}</div>
                                </div>
                                <div class="col-6">
                                    <small class="text-uppercase text-muted font-weight-bold">Impacto</small>
                                    <div class="h5 mb-0 font-weight-bold text-{{ $rec['color'] }}">{{ $rec['impact'] }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-success mt-4">
            <i class="fas fa-check-circle mr-2"></i> ¡No tenemos recomendaciones urgentes! Tu casa está muy bien aislada.
        </div>
    @endif
</div>
@endsection
