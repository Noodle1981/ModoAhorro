@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2><i class="bi bi-bar-chart-line"></i> Panel de Consumo Energético</h2>
    <div class="row mb-4">
        <div class="col-md-8 mx-auto">
            <div class="card shadow border-primary">
                <div class="card-body">
                    <h4 class="card-title mb-3"><i class="bi bi-receipt"></i> Resumen de Factura</h4>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Factura:</strong> #{{ $invoice->id }}</div>
                        <div class="col-6"><strong>Periodo:</strong> {{ $invoice->start_date }} - {{ $invoice->end_date }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Total facturado:</strong> ${{ number_format($invoice->total_amount, 2) }}</div>
                        <div class="col-6"><strong>Consumo facturado:</strong> {{ number_format($invoice->total_energy_consumed_kwh, 2) }} kWh</div>
                    </div>
                    @php
                        $consumoFacturado = $invoice->total_energy_consumed_kwh ?? 0;
                        $porcentaje = $consumoFacturado > 0 ? ($totalEnergia / $consumoFacturado) * 100 : 0;
                        $color = 'secondary';
                        if ($porcentaje >= 90 && $porcentaje <= 110) {
                            $color = 'success'; // Verde: acierto
                        } elseif ($porcentaje >= 70 && $porcentaje < 90) {
                            $color = 'warning'; // Amarillo: aceptable
                        } elseif ($porcentaje > 110 && $porcentaje <= 130) {
                            $color = 'warning'; // Amarillo: aceptable
                        } else {
                            $color = 'danger'; // Rojo: fuera de rango
                        }
                    @endphp
                    <div class="row mb-2">
                        <div class="col-12">
                            <strong>Proximidad del ajuste:</strong>
                            <span class="badge bg-{{ $color }}">
                                {{ number_format($porcentaje, 1) }}%
                                @if($color == 'success')
                                    (Ajuste óptimo)
                                @elseif($color == 'warning')
                                    (Ajuste aceptable)
                                @else
                                    (Ajuste fuera de rango)
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6"><strong>Potencia nominal instalada:</strong> {{ number_format($totalPotencia, 0) }} W</div>
                        <div class="col-6"><strong>Total energía calculada:</strong> {{ number_format($totalEnergia, 2) }} kWh</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <h5 class="mt-4 mb-3"><i class="bi bi-lightbulb"></i> Consumo por equipo en el periodo</h5>
            <table class="table table-striped table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>Equipo</th>
                        <th>Habitación</th>
                        <th>Potencia (W)</th>
                        <th>Consumo estimado (kWh)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->equipmentUsages as $usage)
                        <tr>
                            <td>{{ $usage->equipment->name }}</td>
                            <td>{{ $usage->equipment->room->name ?? '-' }}</td>
                            <td>{{ $usage->equipment->nominal_power_w ?? '-' }}</td>
                            <td>{{ number_format($consumos[$usage->equipment_id] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
