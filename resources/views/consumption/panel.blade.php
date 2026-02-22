@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-bar-chart-line text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Panel de Consumo</h1>
                    <p class="text-gray-500 text-sm">Análisis energético de tu hogar</p>
                </div>
            </div>
            <div class="flex items-center gap-3 mt-4 md:mt-0">
                <x-badge variant="info">{{ $invoices->total() }} Períodos</x-badge>
                <x-button variant="secondary" href="{{ route('consumption.cards') }}">
                    <i class="bi bi-grid mr-2"></i> Vista Tarjetas
                </x-button>
            </div>
        </div>

        @if(count($invoices) === 0)
            {{-- Empty State --}}
            <x-card class="text-center py-16">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="bi bi-bar-chart-line text-4xl text-blue-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Sin datos de consumo</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">
                    Carga facturas para comenzar a analizar tu consumo energético.
                </p>
            </x-card>
        @else
            {{-- Periods Table --}}
            <x-card :padding="false" class="mb-6">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="bi bi-table text-blue-500"></i>
                        Historial de Períodos
                    </h3>
                </div>
                
                <x-table hover>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4">Período</th>
                            <th class="px-6 py-4">Fechas</th>
                            <th class="px-6 py-4 text-center">Días</th>
                            <th class="px-6 py-4 text-center">Días Extremos</th>
                            <th class="px-6 py-4 text-center">Consumo</th>
                            <th class="px-6 py-4 text-right">Monto</th>
                            <th class="px-6 py-4 text-center">Acciones</th>
                        </tr>
                    </x-slot:head>
                    
                    @foreach($invoices as $invoice)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-semibold text-gray-900">#{{ $invoice->id }}</span>
                                    @if($invoice->calculated_metrics->is_adjusted ?? false)
                                        <x-badge variant="success" size="xs">Ajustado</x-badge>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($invoice->start_date)->format('d/m/Y') }} - 
                                {{ \Carbon\Carbon::parse($invoice->end_date)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 bg-gray-100 rounded-lg text-sm font-medium">
                                    {{ $invoice->calculated_metrics->days ?? 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-col gap-1 items-center justify-center">
                                    @if(($invoice->calculated_metrics->hot_days ?? 0) > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-50 text-red-600 rounded text-xs font-medium" title="Días con calor (>24°C)">
                                            <i class="bi bi-fire"></i> {{ $invoice->calculated_metrics->hot_days }} días
                                        </span>
                                    @endif
                                    @if(($invoice->calculated_metrics->cold_days ?? 0) > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-600 rounded text-xs font-medium" title="Días con frío (<18°C)">
                                            <i class="bi bi-snow"></i> {{ $invoice->calculated_metrics->cold_days }} días
                                        </span>
                                    @endif
                                    @if(($invoice->calculated_metrics->hot_days ?? 0) == 0 && ($invoice->calculated_metrics->cold_days ?? 0) == 0)
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="font-mono text-gray-900">
                                    {{ number_format($invoice->calculated_metrics->total_kwh_billed ?? 0, 0) }}
                                </span>
                                <span class="text-gray-500 text-xs">kWh</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-semibold text-emerald-600">
                                    ${{ number_format($invoice->total_amount ?? 0, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <x-button variant="ghost" size="xs" href="{{ route('consumption.panel.show', $invoice->id) }}">
                                    <i class="bi bi-eye"></i>
                                </x-button>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
                
                {{-- Pagination --}}
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $invoices->links() }}
                </div>
            </x-card>

            {{-- Charts Section --}}
            <div class="space-y-6">
                
                {{-- Consumption vs Temperature --}}
                <x-card>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-red-500 rounded-lg flex items-center justify-center">
                            <i class="bi bi-thermometer-sun text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Consumo vs Temperatura</h3>
                            <p class="text-sm text-gray-500">Correlación entre consumo y clima</p>
                        </div>
                    </div>
                    <canvas id="tempChart" height="80"></canvas>
                </x-card>

                {{-- Motor v3 Tendencies --}}
                <x-card>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <i class="bi bi-cpu text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Eficiencia Motor v3</h3>
                            <p class="text-sm text-gray-500">Facturado vs. Calculado (Suma) vs. Cálculo Recomendado</p>
                        </div>
                    </div>
                    <canvas id="motorChart" height="100"></canvas>
                </x-card>

                {{-- Thermodynamic Composition (Tanks) --}}
                <x-card>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-rose-500 to-orange-500 rounded-lg flex items-center justify-center">
                            <i class="bi bi-layers text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Composición Termodinámica</h3>
                            <p class="text-sm text-gray-500">Distribución por Base Crítica, Climatización y Variable</p>
                        </div>
                    </div>
                    <canvas id="tanksChart" height="100"></canvas>
                </x-card>

                {{-- Extreme Days --}}
                <x-card>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-blue-500 rounded-lg flex items-center justify-center">
                            <i class="bi bi-clouds-fill text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Impacto Climático</h3>
                            <p class="text-sm text-gray-500">Días extremos de calor y frío</p>
                        </div>
                    </div>
                    <canvas id="extremeDaysChart" height="80"></canvas>
                </x-card>

                {{-- Cost Charts Row --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Daily Cost --}}
                    <x-card>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg flex items-center justify-center">
                                <i class="bi bi-cash-coin text-white"></i>
                            </div>
                            <h3 class="font-semibold text-gray-900">Costo Diario</h3>
                        </div>
                        <canvas id="dailyCostChart" height="150"></canvas>
                    </x-card>

                    {{-- Price per kWh --}}
                    <x-card>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-amber-600 rounded-lg flex items-center justify-center">
                                <i class="bi bi-graph-up-arrow text-white"></i>
                            </div>
                            <h3 class="font-semibold text-gray-900">Precio Energía ($/kWh)</h3>
                        </div>
                        <canvas id="costPerKwhChart" height="150"></canvas>
                    </x-card>
                </div>

                {{-- Total Consumption --}}
                <x-card>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center">
                            <i class="bi bi-graph-up text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Histórico de Energía</h3>
                            <p class="text-sm text-gray-500">Consumo total facturado (kWh)</p>
                        </div>
                    </div>
                    <canvas id="consumptionChart" height="80"></canvas>
                </x-card>
            </div>
        @endif
    </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = @json($chartData);
    
    const labels = chartData.map(d => d.label);
    const consumptions = chartData.map(d => d.consumption);
    const temps = chartData.map(d => d.avg_temp);
    const dailyCosts = chartData.map(d => d.daily_cost);
    const costsPerKwh = chartData.map(d => d.cost_per_kwh);
    const hotDays = chartData.map(d => d.hot_days);
    const coldDays = chartData.map(d => d.cold_days);
    
    // Motor v3 Variables
    const facturados = chartData.map(d => d.facturado);
    const teoricos = chartData.map(d => d.teorico);
    const recomendados = chartData.map(d => d.recomendado);
    
    // Tanks Variables
    const tanks1 = chartData.map(d => d.tank_1);
    const tanks2 = chartData.map(d => d.tank_2);
    const tanks3 = chartData.map(d => d.tank_3);

    const chartOptions = {
        responsive: true,
        plugins: {
            legend: {
                labels: { usePointStyle: true, padding: 20 }
            }
        }
    };

    // 1. Consumption vs Temperature
    new Chart(document.getElementById('tempChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Consumo (kWh)',
                    data: consumptions,
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Temp. Promedio (°C)',
                    data: temps,
                    type: 'line',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    backgroundColor: 'rgba(239, 68, 68, 0.2)',
                    borderWidth: 3,
                    tension: 0.4,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            ...chartOptions,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: { type: 'linear', display: true, position: 'left', title: { display: true, text: 'Consumo (kWh)' } },
                y1: { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Temperatura (°C)' } }
            }
        }
    });

    // 1A. Eficiencia Motor v3
    new Chart(document.getElementById('motorChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Facturado',
                    data: facturados,
                    borderColor: 'rgba(55, 65, 81, 1)', // Gray 700
                    backgroundColor: 'rgba(55, 65, 81, 0.1)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    tension: 0.3,
                    pointRadius: 4
                },
                {
                    label: 'Calculado (Suma Teórica)',
                    data: teoricos,
                    borderColor: 'rgba(239, 68, 68, 0.7)', // Red 500
                    backgroundColor: 'rgba(239, 68, 68, 0.1)', 
                    borderWidth: 2,
                    tension: 0.3,
                    pointRadius: 4
                },
                {
                    label: 'Cálculo Recomendado',
                    data: recomendados,
                    borderColor: 'rgba(79, 70, 229, 1)', // Indigo 600
                    backgroundColor: 'rgba(79, 70, 229, 0.2)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 5,
                    pointBackgroundColor: 'rgba(79, 70, 229, 1)',
                }
            ]
        },
        options: {
            ...chartOptions,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Consumo (kWh)' } }
            }
        }
    });

    // 1B. Composición Termodinámica (Tanques)
    new Chart(document.getElementById('tanksChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Base Crítica (T1)',
                    data: tanks1,
                    backgroundColor: 'rgba(239, 68, 68, 0.8)', // Red
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Climatización (T2)',
                    data: tanks2,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)', // Blue
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Consumo Variable (T3)',
                    data: tanks3,
                    backgroundColor: 'rgba(168, 85, 247, 0.8)', // Purple
                    borderColor: 'rgba(168, 85, 247, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            ...chartOptions,
            interaction: { mode: 'index', intersect: false },
            scales: {
                x: { stacked: true },
                y: { stacked: true, beginAtZero: true, title: { display: true, text: 'Consumo Autorizado (kWh)' } }
            }
        }
    });

    // 2. Extreme Days
    new Chart(document.getElementById('extremeDaysChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Días Calor (>28°C)', data: hotDays, backgroundColor: 'rgba(239, 68, 68, 0.7)', borderColor: 'rgba(239, 68, 68, 1)', borderWidth: 1 },
                { label: 'Días Frío (<15°C)', data: coldDays, backgroundColor: 'rgba(59, 130, 246, 0.7)', borderColor: 'rgba(59, 130, 246, 1)', borderWidth: 1 }
            ]
        },
        options: {
            ...chartOptions,
            scales: {
                x: { stacked: true },
                y: { stacked: true, beginAtZero: true, title: { display: true, text: 'Cantidad de Días' } }
            }
        }
    });

    // 3. Daily Cost
    new Chart(document.getElementById('dailyCostChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Costo Diario ($)',
                data: dailyCosts,
                borderColor: 'rgba(16, 185, 129, 1)',
                backgroundColor: 'rgba(16, 185, 129, 0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: { ...chartOptions, scales: { y: { beginAtZero: true, title: { display: true, text: '$/Día' } } } }
    });

    // 4. Cost per kWh
    new Chart(document.getElementById('costPerKwhChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Precio por kWh ($)',
                data: costsPerKwh,
                borderColor: 'rgba(245, 158, 11, 1)',
                backgroundColor: 'rgba(245, 158, 11, 0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: { ...chartOptions, scales: { y: { beginAtZero: true, title: { display: true, text: '$/kWh' } } } }
    });

    // 5. Total Consumption
    new Chart(document.getElementById('consumptionChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Consumo Facturado (kWh)',
                data: consumptions,
                backgroundColor: 'rgba(99, 102, 241, 0.7)',
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 1
            }]
        },
        options: { ...chartOptions, scales: { y: { beginAtZero: true } } }
    });
});
</script>
@endsection
