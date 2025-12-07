@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-bar-chart-line"></i> Panel de Consumo Energético</h2>
        <span class="badge bg-info">{{ $invoices->total() }} Periodos</span>
    </div>

    @if(count($invoices) === 0)
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle fs-3"></i>
            <p class="mt-2">No hay facturas registradas en el sistema.</p>
        </div>
    @else
        <div class="d-flex justify-content-end mb-3">
             <a href="{{ route('consumption.cards') }}" class="btn btn-outline-primary">
                <i class="bi bi-grid"></i> Ver Detalle Visual (Tarjetas)
             </a>
        </div>

        <!-- Tabla Histórica Paginada -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-table"></i> Historial de Periodos</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Periodo</th>
                            <th>Fechas</th>
                            <th class="text-center">Días</th>
                            <th class="text-center">Consumo Fact.</th>
                            <th class="text-center">Desviación</th>
                            <th class="text-end">Monto Total</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                            <tr>
                                <td>
                                    <strong>#{{ $invoice->id }}</strong>
                                    @if($invoice->calculated_metrics->is_adjusted)
                                        <span class="badge bg-success ms-1" style="font-size: 0.65rem;">Ajustado</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($invoice->start_date)->format('d/m/Y') }} - 
                                        {{ \Carbon\Carbon::parse($invoice->end_date)->format('d/m/Y') }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">{{ $invoice->calculated_metrics->days }}</span>
                                </td>
                                <td class="text-center text-muted">
                                    {{ number_format($invoice->calculated_metrics->total_kwh_billed, 0) }} kWh
                                </td>
                                <td class="text-center">
                                    @php
                                        $status = $invoice->calculated_metrics->status;
                                        $badges = [
                                            'critical' => ['bg-danger', 'bi-exclamation-triangle'],
                                            'warning' => ['bg-warning text-dark', 'bi-exclamation-circle'],
                                            'exact' => ['bg-success', 'bi-check-lg']
                                        ];
                                        $badge = $badges[$status] ?? $badges['exact'];
                                        $dev = abs(100 - $invoice->calculated_metrics->deviation_percent);
                                    @endphp
                                    <span class="badge {{ $badge[0] }}">
                                        <i class="bi {{ $badge[1] }}"></i> 
                                        @if($status === 'exact') Exacto @else {{ number_format($dev, 1) }}% @endif
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    ${{ number_format($invoice->total_amount, 0) }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('consumption.panel.show', $invoice->id) }}" class="btn btn-sm btn-outline-primary" title="Ver Detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <div class="card-footer bg-white d-flex justify-content-center">
                {{ $invoices->links() }}
            </div>
        </div>

        <!-- Gráfico de Tendencia (Consumo vs Temperatura) -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-thermometer-sun"></i> Correlación: Consumo vs Temperatura</h5>
            </div>
            <div class="card-body">
                <canvas id="tempChart" height="80"></canvas>
            </div>
        </div>

        <!-- Gráfico de Impacto Climático (Días Extremos) -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-clouds-fill"></i> Impacto Climático: Días Extremos</h5>
            </div>
            <div class="card-body">
                <canvas id="extremeDaysChart" height="80"></canvas>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-2 g-4 mb-4">
             <!-- Costo Diario -->
            <div class="col">
                <div class="card shadow-sm h-100">
                     <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Costo Promedio Diario</h5>
                    </div>
                    <div class="card-body">
                         <canvas id="dailyCostChart" height="150"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Precio Energía -->
             <div class="col">
                <div class="card shadow-sm h-100">
                     <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-graph-up-arrow"></i> Evolución Precio Energía ($/kWh)</h5>
                    </div>
                    <div class="card-body">
                         <canvas id="costPerKwhChart" height="150"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Consumo Total (Original) -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Histórico de Energía (Total kWh)</h5>
            </div>
            <div class="card-body">
                <canvas id="consumptionChart" height="80"></canvas>
            </div>
        </div>

    @endif
</div>

<style>
/* Estilos opcionales para paginación si se usa Bootstrap default */
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartData = @json($chartData);
        
        const labels = chartData.map(d => d.label);
        const consumptions = chartData.map(d => d.consumption); // kWh
        const costs = chartData.map(d => d.cost);             // $ Total
        const avgs = chartData.map(d => d.avg);               // kWh/day
        const temps = chartData.map(d => d.avg_temp);         // °C
        const dailyCosts = chartData.map(d => d.daily_cost);  // $/day
        const costsPerKwh = chartData.map(d => d.cost_per_kwh); // $/kWh

        // --- 1. Consumption vs Temperature (Dual Axis) ---
        const ctxTemp = document.getElementById('tempChart').getContext('2d');
        new Chart(ctxTemp, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Consumo (kWh)',
                        data: consumptions,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Temp. Promedio (°C)',
                        data: temps,
                        type: 'line',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderWidth: 2,
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
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Consumo (kWh)' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Temperatura (°C)' }
                    }
                }
            }
        });

        // --- 1.5 Extreme Days (Stacked Bar) ---
        const ctxExtreme = document.getElementById('extremeDaysChart').getContext('2d');
        const hotDays = chartData.map(d => d.hot_days);
        const coldDays = chartData.map(d => d.cold_days);

        new Chart(ctxExtreme, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Días Calor (>28°C)',
                        data: hotDays,
                        backgroundColor: 'rgba(220, 53, 69, 0.7)', // Red
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Días Frío (<15°C)',
                        data: coldDays,
                        backgroundColor: 'rgba(13, 110, 253, 0.7)', // Blue
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    x: { stacked: true },
                    y: { 
                        stacked: true,
                        beginAtZero: true,
                        title: { display: true, text: 'Cantidad de Días' }
                    }
                }
            }
        });

        // --- 2. Daily Cost ($/day) ---
        const ctxDailyCost = document.getElementById('dailyCostChart').getContext('2d');
        new Chart(ctxDailyCost, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Costo Diario ($)',
                    data: dailyCosts,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: '$/Día' } }
                }
            }
        });

        // --- 3. Cost per kWh ($/kWh) ---
        const ctxCostPerKwh = document.getElementById('costPerKwhChart').getContext('2d');
        new Chart(ctxCostPerKwh, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Precio por kWh ($)',
                    data: costsPerKwh,
                    borderColor: 'rgba(255, 159, 64, 1)',
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, title: { display: true, text: '$/kWh' } }
                }
            }
        });
        
        // --- 4. Original Total Consumption (Simplified) ---
         const ctx = document.getElementById('consumptionChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar', // Changed to bar for clarity
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Consumo Facturado (kWh)',
                        data: consumptions,
                        backgroundColor: 'rgba(13, 110, 253, 0.7)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                 responsive: true,
                 scales: { y: { beginAtZero: true } }
            }
        });
    });
</script>
@endsection
