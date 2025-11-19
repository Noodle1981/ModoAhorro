@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Detalle del ajuste de uso</h2>
    <div class="card mt-3">
        <div class="card-body">
            <h5 class="card-title">Factura #{{ $invoice->id }}</h5>
            <p><strong>Periodo:</strong> {{ $invoice->start_date }} - {{ $invoice->end_date }}</p>
            <hr>
            <h6>Equipos ajustados:</h6>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Equipo</th>
                        <th>Habitación</th>
                        <th>Potencia (W)</th>
                        <th>Frecuencia</th>
                        <th>Detalle de uso</th>
                        <th>Fórmula aplicada</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($equipmentUsages as $usage)
                        <tr>
                            <td>{{ $usage->equipment->name }}</td>
                            <td>{{ $usage->equipment->room->name ?? '-' }}</td>
                            <td>{{ $usage->equipment->nominal_power_w ?? '-' }}</td>
                            <td>{{ ucfirst($usage->usage_frequency ?? 'diario') }}</td>
                            <td>
                                @if(in_array($usage->usage_frequency, ['diario', 'semanal']) || empty($usage->usage_frequency))
                                    <strong>Horas/día:</strong> {{ $usage->avg_daily_use_hours }}<br>
                                    <strong>Días de uso:</strong> {{ $usage->use_days_in_period }}<br>
                                    <strong>Días de la semana:</strong> {{ $usage->use_days_of_week ?? '-' }}<br>
                                    <strong>Minutos/día:</strong> {{ isset($usage->avg_daily_use_hours) ? round($usage->avg_daily_use_hours * 60) : 0 }} min
                                @else
                                    <strong>Cantidad de usos:</strong> {{ $usage->usage_count ?? '-' }}<br>
                                    <strong>Duración promedio por uso:</strong> {{ $usage->avg_use_duration ?? '-' }} h
                                @endif
                            </td>
                            <td>
                                @if(in_array($usage->usage_frequency, ['diario', 'semanal']) || empty($usage->usage_frequency))
                                    <span class="badge bg-primary">Consumo = Potencia × Horas/día × Días</span>
                                @else
                                    <span class="badge bg-info">Consumo = Potencia × Duración promedio × Cantidad de usos</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <a href="{{ route('usage_adjustments.index') }}" class="btn btn-secondary mt-3">Volver al listado</a>
</div>
@endsection
