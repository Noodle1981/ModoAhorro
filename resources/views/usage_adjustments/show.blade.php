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
                        <th>Horas/día</th>
                        <th>Días de uso</th>
                        <th>Días de la semana</th>
                        <th>Minutos/día</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($equipmentUsages as $usage)
                        <tr>
                            <td>{{ $usage->equipment->name }}</td>
                            <td>{{ $usage->equipment->room->name ?? '-' }}</td>
                            <td>{{ $usage->equipment->nominal_power_w ?? '-' }}</td>
                            <td>{{ $usage->avg_daily_use_hours }}</td>
                            <td>{{ $usage->use_days_in_period }}</td>
                            <td>{{ $usage->use_days_of_week ?? '-' }}</td>
                            <td>
                                @php
                                    $minutos = round($usage->avg_daily_use_hours * 60);
                                @endphp
                                @if($minutos >= 60)
                                    {{ number_format($minutos / 60, 2) }} h
                                @else
                                    {{ $minutos }} min
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
