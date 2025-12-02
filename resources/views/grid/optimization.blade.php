@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Optimización Horaria (Grid)</h1>
        <a href="{{ route('entities.show', $entity->id) }}" class="btn btn-secondary">Volver</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Tu Plan Actual: {{ $tariffScheme->name }}</h5>
                    <p class="card-text">
                        Proveedor: {{ $tariffScheme->provider }} <br>
                        
                        <!-- Visual Timeline -->
                        <div class="progress mt-2 mb-2" style="height: 25px;">
                            @php
                                // Sort bands by start time for timeline
                                $timelineBands = $tariffScheme->bands->sortBy('start_time');
                                // Simple logic: assume bands cover 24h. 
                                // We need to calculate width % based on duration.
                                // For now, let's hardcode the standard bands colors for demo:
                                // Pico (Red), Valle (Green), Resto (Yellow)
                            @endphp
                            
                            @foreach($timelineBands as $band)
                                @php
                                    $start = \Carbon\Carbon::parse($band->start_time);
                                    $end = \Carbon\Carbon::parse($band->end_time);
                                    if ($end < $start) $end->addDay();
                                    $hours = $start->diffInHours($end);
                                    $width = ($hours / 24) * 100;
                                    
                                    $color = 'bg-secondary';
                                    if (str_contains(strtolower($band->name), 'pico')) $color = 'bg-danger';
                                    elseif (str_contains(strtolower($band->name), 'valle')) $color = 'bg-success';
                                    elseif (str_contains(strtolower($band->name), 'resto')) $color = 'bg-warning text-dark';
                                @endphp
                                <div class="progress-bar {{ $color }}" role="progressbar" style="width: {{ $width }}%" aria-valuenow="{{ $width }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ substr($band->start_time, 0, 5) }}
                                </div>
                            @endforeach
                        </div>

                        @foreach($tariffScheme->bands as $band)
                            <span class="badge bg-light text-dark border">{{ $band->name }}: ${{ $band->price_per_kwh }}/kWh</span>
                        @endforeach
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if(count($opportunities) > 0)
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">📉 Ahorra moviendo tus horarios</h5>
                    </div>
                    <div class="card-body">
                        <p>Tienes {{ count($opportunities) }} equipos que podrías usar en horario nocturno (Valle) para pagar menos.</p>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Equipo</th>
                                        <th>Costo Actual ({{ $opportunities[0]['peak_band_name'] }})</th>
                                        <th>Costo Optimizado ({{ $opportunities[0]['off_peak_band_name'] }})</th>
                                        <th>Ahorro Mensual</th>
                                        <th>Sugerencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($opportunities as $opp)
                                        <tr>
                                            <td><strong>{{ $opp['equipment'] }}</strong></td>
                                            <td class="text-danger">${{ number_format($opp['current_cost'], 0, ',', '.') }}</td>
                                            <td class="text-success">${{ number_format($opp['optimized_cost'], 0, ',', '.') }}</td>
                                            <td><span class="badge bg-success" style="font-size: 1.1em;">${{ number_format($opp['potential_savings'], 0, ',', '.') }} / mes</span></td>
                                            <td>
                                                {{ $opp['suggestion'] }}
                                                @if(isset($opp['suggestion_secondary']))
                                                    <br><small class="text-muted">{{ $opp['suggestion_secondary'] }}</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            No se encontraron oportunidades significativas de ahorro por desplazamiento de horarios. ¡Ya estás optimizado o tus equipos no son desplazables!
        </div>
    @endif

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Simulador de Tarifas</h5>
                    <p class="text-muted">Próximamente: Compara cuánto pagarías con otros planes tarifarios.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
