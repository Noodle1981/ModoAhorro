@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-bar-chart-line"></i> Panel de Consumo Energético</h2>
        <span class="badge bg-info">{{ count($invoicesData) }} Periodos</span>
    </div>

    @if(count($invoicesData) === 0)
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle fs-3"></i>
            <p class="mt-2">No hay facturas registradas en el sistema.</p>
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @foreach($invoicesData as $data)
                @php
                    $invoice = $data['invoice'];
                    $totalEnergia = $data['totalEnergia'];
                    $porcentaje = $data['porcentaje'];
                    $color = $data['color'];
                    $mensaje = $data['mensaje'];
                    $isAdjusted = $data['isAdjusted'];
                    
                    // Determinar el color del borde según estado
                    $borderColor = $isAdjusted ? 'success' : 'secondary';
                @endphp
                
                <div class="col">
                    <div class="card h-100 shadow-sm border-{{ $borderColor }} hover-card" style="transition: transform 0.2s, box-shadow 0.2s;">
                        <div class="card-header bg-{{ $borderColor }} bg-opacity-10 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-calendar-range"></i> Periodo #{{ $invoice->id }}
                            </h5>
                            @if($isAdjusted)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Ajustado
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-exclamation-circle"></i> Sin Ajustar
                                </span>
                            @endif
                        </div>
                        
                        <div class="card-body">
                            <!-- Fechas del periodo -->
                            <div class="mb-3">
                                @php
                                    $startDate = \Carbon\Carbon::parse($invoice->start_date);
                                    $endDate = \Carbon\Carbon::parse($invoice->end_date);
                                    $days = $startDate->diffInDays($endDate);
                                @endphp
                                <small class="text-muted">
                                    <i class="bi bi-calendar3"></i> 
                                    {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}
                                    <span class="badge bg-light text-dark ms-1">{{ $days }} días</span>
                                </small>
                            </div>

                            <!-- Consumos -->
                            <div class="row mb-3">
                                <div class="col-6 text-center">
                                    <small class="text-muted d-block">Facturado</small>
                                    <h4 class="text-primary mb-0">{{ number_format($invoice->total_energy_consumed_kwh ?? 0, 0) }}</h4>
                                    <small class="text-muted">kWh</small>
                                </div>
                                <div class="col-6 text-center">
                                    <small class="text-muted d-block">Calculado</small>
                                    <h4 class="text-success mb-0">{{ number_format($totalEnergia, 0) }}</h4>
                                    <small class="text-muted">kWh</small>
                                </div>
                            </div>

                            <!-- Indicador de precisión -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted">Precisión</small>
                                    <small class="fw-bold text-{{ $color }}">{{ number_format($porcentaje, 1) }}%</small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $color }}" role="progressbar" 
                                         style="width: {{ min($porcentaje, 100) }}%;" 
                                         aria-valuenow="{{ $porcentaje }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                <small class="text-{{ $color }} d-block mt-1">
                                    <i class="bi bi-info-circle"></i> {{ $mensaje }}
                                </small>
                            </div>

                            <!-- Monto total -->
                            <div class="mb-3 p-2 bg-light rounded">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Monto Total:</span>
                                    <strong>${{ number_format($invoice->total_amount ?? 0, 2) }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-transparent">
                            <div class="d-grid gap-2">
                                <a href="{{ route('consumption.panel.show', $invoice->id) }}" 
                                   class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Ver Detalle
                                </a>
                                @if(!$isAdjusted)
                                    <a href="{{ route('usage_adjustments.edit', $invoice->id) }}" 
                                       class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-sliders"></i> Ajustar Uso
                                    </a>
                                @else
                                    <a href="{{ route('usage_adjustments.edit', $invoice->id) }}" 
                                       class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-pencil"></i> Modificar Ajuste
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<style>
.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>
@endsection
