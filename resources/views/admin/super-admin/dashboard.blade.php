@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-gray-900 dark:to-gray-800 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Super Admin Dashboard
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Panel de administración del sistema • Actualizado: {{ now()->format('d/m/Y H:i') }}
            </p>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Users Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border-l-4 border-indigo-500 transform hover:scale-105 transition-transform">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Usuarios Totales</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($analytics['users']['total']) }}</p>
                    </div>
                    <div class="p-3 bg-indigo-100 dark:bg-indigo-900 rounded-full">
                        <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                        {{ $analytics['users']['active'] }} activos (30d)
                    </span>
                </div>
            </div>

            <!-- Entities Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border-l-4 border-emerald-500 transform hover:scale-105 transition-transform">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Entidades Totales</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($analytics['entities']['total']) }}</p>
                    </div>
                    <div class="p-3 bg-emerald-100 dark:bg-emerald-900 rounded-full">
                        <svg class="w-8 h-8 text-emerald-600 dark:text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex gap-2 flex-wrap">
                    @foreach($analytics['entities']['by_type'] as $type => $count)
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                            {{ $type }}: {{ $count }}
                        </span>
                    @endforeach
                </div>
            </div>

            <!-- Rooms Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border-l-4 border-amber-500 transform hover:scale-105 transition-transform">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Habitaciones Totales</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($analytics['rooms']['total']) }}</p>
                    </div>
                    <div class="p-3 bg-amber-100 dark:bg-amber-900 rounded-full">
                        <svg class="w-8 h-8 text-amber-600 dark:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        Promedio: {{ $analytics['rooms']['avg_per_entity'] }} por entidad
                    </span>
                </div>
            </div>

            <!-- Equipment Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border-l-4 border-rose-500 transform hover:scale-105 transition-transform">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Equipos Totales</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($analytics['equipment']['total']) }}</p>
                    </div>
                    <div class="p-3 bg-rose-100 dark:bg-rose-900 rounded-full">
                        <svg class="w-8 h-8 text-rose-600 dark:text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        Promedio: {{ $analytics['equipment']['avg_per_entity'] }} por entidad
                    </span>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Entity Distribution Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Distribución de Entidades</h3>
                <div class="h-64">
                    <canvas id="entityChart"></canvas>
                </div>
            </div>

            <!-- Equipment by Category Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 lg:col-span-2">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Equipos por Categoría</h3>
                <div class="h-64">
                    <canvas id="equipmentChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Growth Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Crecimiento (Últimos 12 Meses)</h3>
            <div class="h-80">
                <canvas id="growthChart"></canvas>
            </div>
        </div>

        <!-- Business Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                <p class="text-sm font-medium opacity-90">Consumo Total</p>
                <p class="text-3xl font-bold mt-2">{{ number_format($analytics['business']['total_consumption_kwh'], 0) }}</p>
                <p class="text-sm opacity-75 mt-1">kWh procesados</p>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                <p class="text-sm font-medium opacity-90">Ahorro Identificado</p>
                <p class="text-3xl font-bold mt-2">{{ number_format($analytics['business']['total_savings_kwh'], 0) }}</p>
                <p class="text-sm opacity-75 mt-1">kWh de ahorro</p>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                <p class="text-sm font-medium opacity-90">Facturas Procesadas</p>
                <p class="text-3xl font-bold mt-2">{{ number_format($analytics['business']['invoices_processed']) }}</p>
                <p class="text-sm opacity-75 mt-1">Total en sistema</p>
            </div>

            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
                <p class="text-sm font-medium opacity-90">Recomendaciones</p>
                <p class="text-3xl font-bold mt-2">{{ number_format($analytics['business']['recommendations_generated']) }}</p>
                <p class="text-sm opacity-75 mt-1">Generadas</p>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Users -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Usuarios Recientes</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Usuario</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Plan</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($analytics['recent_activity']['users'] as $user)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user['name'] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user['email'] }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300">
                                            {{ $user['plan'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $user['created_at'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No hay usuarios recientes
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Entities -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Entidades Recientes</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Entidad</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($analytics['recent_activity']['entities'] as $entity)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $entity['name'] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $entity['owner'] }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            @if($entity['type'] === 'Hogar') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                            @elseif($entity['type'] === 'Oficina') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                            @else bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300
                                            @endif">
                                            {{ $entity['type'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $entity['created_at'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No hay entidades recientes
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    // Entity Distribution Pie Chart
    const entityCtx = document.getElementById('entityChart').getContext('2d');
    new Chart(entityCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($analytics['entities']['by_type'])) !!},
            datasets: [{
                data: {!! json_encode(array_values($analytics['entities']['by_type'])) !!},
                backgroundColor: [
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(168, 85, 247, 0.8)',
                ],
                borderColor: [
                    'rgba(34, 197, 94, 1)',
                    'rgba(59, 130, 246, 1)',
                    'rgba(168, 85, 247, 1)',
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151',
                        padding: 15,
                        font: { size: 12 }
                    }
                }
            }
        }
    });

    // Equipment by Category Bar Chart
    const equipmentCtx = document.getElementById('equipmentChart').getContext('2d');
    new Chart(equipmentCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($analytics['equipment']['by_category'])) !!},
            datasets: [{
                label: 'Cantidad de Equipos',
                data: {!! json_encode(array_values($analytics['equipment']['by_category'])) !!},
                backgroundColor: 'rgba(99, 102, 241, 0.8)',
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 2,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151' },
                    grid: { color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb' }
                },
                x: {
                    ticks: { 
                        color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151',
                        maxRotation: 45,
                        minRotation: 45
                    },
                    grid: { display: false }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151'
                    }
                }
            }
        }
    });

    // Growth Line Chart
    const growthCtx = document.getElementById('growthChart').getContext('2d');
    new Chart(growthCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($analytics['growth']['labels']) !!},
            datasets: [
                {
                    label: 'Usuarios',
                    data: {!! json_encode($analytics['growth']['users']) !!},
                    borderColor: 'rgba(99, 102, 241, 1)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Entidades',
                    data: {!! json_encode($analytics['growth']['entities']) !!},
                    borderColor: 'rgba(34, 197, 94, 1)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151' },
                    grid: { color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb' }
                },
                x: {
                    ticks: { color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151' },
                    grid: { color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb' }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#374151',
                        usePointStyle: true,
                        padding: 20
                    }
                }
            }
        }
    });
</script>
@endsection
