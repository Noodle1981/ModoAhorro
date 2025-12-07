@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-grid-fill"></i> Detalle Histórico de Periodos</h2>
        <a href="{{ route('consumption.panel') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al Panel
        </a>
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
                                <span class="badge bg-success me-1">
                                    <i class="bi bi-check-circle"></i> Ajustado
                                </span>
                            @else
                                <span class="badge bg-secondary me-1">
                                    <i class="bi bi-exclamation-circle"></i> Sin Ajustar
                                </span>
                            @endif
                            
                            {{-- Badge de Validación --}}
                            @if($porcentaje > 130 || $porcentaje < 70)
                                <span class="badge bg-danger" title="Desviación crítica">
                                    <i class="bi bi-exclamation-triangle"></i> {{ number_format(abs(100 - $porcentaje), 1) }}% Desv.
                                </span>
                            @elseif($porcentaje > 110 || $porcentaje < 90)
                                <span class="badge bg-warning text-dark" title="Desviación moderada">
                                    <i class="bi bi-exclamation-circle"></i> {{ number_format(abs(100 - $porcentaje), 1) }}% Desv.
                                </span>
                            @else
                                <span class="badge bg-success" title="Precisión excelente">
                                    <i class="bi bi-check-lg"></i> Exacto
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
                                <div class="col-12 text-center">
                                    <small class="text-muted d-block">Consumo Facturado</small>
                                    <h3 class="text-primary mb-0 fw-bold">{{ number_format($invoice->total_energy_consumed_kwh ?? 0, 0) }}</h3>
                                    <small class="text-muted">kWh</small>
                                </div>
                            </div>

                            <!-- Métricas Adicionales -->
                            <div class="row mb-3 g-2 text-center">
                                <div class="col-6">
                                    <div class="p-2 border rounded bg-light">
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Promedio Diario</small>
                                        <span class="fw-bold text-dark">
                                            <i class="bi bi-speedometer2"></i> {{ number_format($data['dailyAvg'], 1) }}
                                        </span>
                                        <small class="text-muted" style="font-size: 0.7rem;">kWh/día</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 border rounded bg-light">
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">Eficiencia</small>
                                        <span class="fw-bold text-dark">
                                            <i class="bi bi-currency-dollar"></i> {{ number_format($data['costPerKwh'], 0) }}
                                        </span>
                                        <small class="text-muted" style="font-size: 0.7rem;">/kWh</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Monto total -->
                            <div class="mb-3 p-2 bg-light rounded border">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Monto Total:</span>
                                    <strong class="fs-5 text-success">${{ number_format($invoice->total_amount ?? 0, 0) }}</strong>
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
