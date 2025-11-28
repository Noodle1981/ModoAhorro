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
        <!-- Gráfico de Tendencia -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Evolución de Consumo Histórico</h5>
            </div>
            <div class="card-body">
                <canvas id="consumptionChart" height="80"></canvas>
            </div>
        </div>

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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('consumptionChart');
    if (ctx) {
        const chartData = @json($chartData ?? []);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(d => d.label),
                datasets: [
                    {
                        label: 'Consumo (kWh)',
                        data: chartData.map(d => d.consumption),
                        borderColor: '#0d6efd', // Primary Blue
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Costo ($)',
                        data: chartData.map(d => d.cost),
                        borderColor: '#198754', // Success Green
                        backgroundColor: 'rgba(25, 135, 84, 0.0)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    if (context.datasetIndex === 1) {
                                        label += '$' + new Intl.NumberFormat('es-AR').format(context.parsed.y);
                                    } else {
                                        label += context.parsed.y + ' kWh';
                                    }
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Energía (kWh)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                        title: {
                            display: true,
                            text: 'Costo ($)'
                        }
                    },
                }
            }
        });
    }
});
</script>
@endsection
