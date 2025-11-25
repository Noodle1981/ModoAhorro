@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Detalle del ajuste de uso</h2>
    
    <!-- Resumen General -->
    <div class="card mt-3 mb-4 border-primary">
        <div class="card-body bg-light">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="card-title text-primary mb-1">Factura #{{ $invoice->id }}</h5>
                    <p class="mb-0 text-muted">
                        <strong>Periodo:</strong> {{ \Carbon\Carbon::parse($invoice->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($invoice->end_date)->format('d/m/Y') }}
                        (Days: {{ \Carbon\Carbon::parse($invoice->start_date)->diffInDays(\Carbon\Carbon::parse($invoice->end_date)) }})
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <h6 class="text-uppercase text-muted small">Consumo Total Calculado</h6>
                    <h3 class="text-primary fw-bold">{{ number_format($totalCalculatedConsumption, 2) }} kWh</h3>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <!-- Listado por Habitaciones -->
    @forelse($groupedUsages as $roomName => $usages)
        <h5 class="mt-4 text-secondary border-bottom pb-2">{{ $roomName }}</h5>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Equipo</th>
                        <th>Potencia (W)</th>
                        <th>Frecuencia</th>
                        <th>Detalle de uso</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usages as $usage)
                        <tr>
                            <td class="fw-bold">{{ $usage->equipment->name }}</td>
                            <td>{{ $usage->equipment->nominal_power_w ?? '-' }} W</td>
                            <td>{{ ucfirst($usage->usage_frequency ?? 'diario') }}</td>
                            <td>
                                <small class="text-muted">
                                @if(in_array($usage->usage_frequency, ['diario', 'semanal']) || empty($usage->usage_frequency))
                                    {{ $usage->avg_daily_use_hours }} h/día × {{ $usage->use_days_in_period }} días
                                    <br>({{ $usage->use_days_of_week ?? 'Todos los días' }})
                                @else
                                    {{ $usage->usage_count }} usos × {{ $usage->avg_use_duration }} h
                                @endif
                                </small>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div class="alert alert-warning">No hay ajustes de uso registrados para esta factura.</div>
    @endforelse

    <div class="mt-4">
        <a href="{{ route('usage_adjustments.index') }}" class="btn btn-secondary">Volver al listado</a>
        <a href="{{ route('usage_adjustments.edit', $invoice->id) }}" class="btn btn-primary ms-2">Editar Ajuste</a>
    </div>
</div>
@endsection
