@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2><i class="bi bi-bar-chart-line"></i> Panel de Consumo Energético</h2>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow border-primary">
                <div class="card-body">
                    <h4 class="card-title mb-3"><i class="bi bi-speedometer2"></i> Análisis de Consumo</h4>
                    
                    <div class="row align-items-center mb-4">
                        <div class="col-md-5 text-center">
                            <h6 class="text-muted text-uppercase">Consumo Facturado</h6>
                            <h2 class="display-6 fw-bold text-primary">{{ number_format($invoice->total_energy_consumed_kwh, 2) }} kWh</h2>
                        </div>
                        <div class="col-md-2 text-center">
                            <i class="bi bi-arrow-left-right fs-1 text-muted"></i>
                        </div>
                        <div class="col-md-5 text-center">
                            <h6 class="text-muted text-uppercase">Consumo Calculado (Auditado)</h6>
                            <h2 class="display-6 fw-bold text-success">{{ number_format($totalEnergia, 2) }} kWh</h2>
                        </div>
                    </div>

                    @php
                        $consumoFacturado = $invoice->total_energy_consumed_kwh ?? 0;
                        $porcentaje = $consumoFacturado > 0 ? ($totalEnergia / $consumoFacturado) * 100 : 0;
                        $color = 'secondary';
                        $mensaje = '';
                        if ($porcentaje >= 90 && $porcentaje <= 110) {
                            $color = 'success'; 
                            $mensaje = 'Excelente precisión';
                        } elseif ($porcentaje >= 70 && $porcentaje < 90) {
                            $color = 'warning'; 
                            $mensaje = 'Diferencia aceptable (subestimado)';
                        } elseif ($porcentaje > 110 && $porcentaje <= 130) {
                            $color = 'warning'; 
                            $mensaje = 'Diferencia aceptable (sobreestimado)';
                        } else {
                            $color = 'danger'; 
                            $mensaje = 'Diferencia significativa - Revisar ajustes';
                        }
                    @endphp

                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ min($porcentaje, 100) }}%;" aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100">
                            {{ number_format($porcentaje, 1) }}% del facturado
                        </div>
                    </div>
                    <div class="text-center mt-2">
                        <span class="badge bg-{{ $color }}">{{ $mensaje }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Consumo por Categoría</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @foreach($consumoPorCategoria as $categoria => $consumo)
                            @php 
                                $pctCat = $totalEnergia > 0 ? ($consumo / $totalEnergia) * 100 : 0;
                            @endphp
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $categoria }}</strong>
                                    <div class="progress mt-1" style="height: 5px; width: 100px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ $pctCat }}%"></div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold">{{ number_format($consumo, 2) }} kWh</span>
                                    <br>
                                    <small class="text-muted">{{ number_format($pctCat, 1) }}%</small>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow h-100">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Detalles de la Factura</h5>
                </div>
                <div class="card-body">
                    <p><strong>Nº Factura:</strong> #{{ $invoice->id }}</p>
                    <p><strong>Periodo:</strong> {{ $invoice->start_date }} - {{ $invoice->end_date }}</p>
                    <p><strong>Monto Total:</strong> ${{ number_format($invoice->total_amount, 2) }}</p>
                    <p><strong>Potencia Instalada:</strong> {{ number_format($totalPotencia, 0) }} W</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h5 class="mt-4 mb-3"><i class="bi bi-lightbulb"></i> Detalle por Equipo</h5>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>Equipo</th>
                            <th>Categoría</th>
                            <th>Habitación</th>
                            <th>Potencia (W)</th>
                            <th>Consumo (kWh)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->equipmentUsages as $usage)
                            <tr>
                                <td class="fw-bold">{{ $usage->equipment->name }}</td>
                                <td><span class="badge bg-secondary">{{ $usage->equipment->category->name ?? 'General' }}</span></td>
                                <td>{{ $usage->equipment->room->name ?? '-' }}</td>
                                <td>{{ $usage->equipment->nominal_power_w ?? '-' }}</td>
                                <td class="fw-bold text-primary">{{ number_format($consumos[$usage->equipment_id] ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
