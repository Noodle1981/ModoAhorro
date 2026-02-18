@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-orange-500 to-red-600 w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-tools text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Mantenimiento Preventivo</h1>
                    <p class="text-gray-500">{{ $entity->name }} — Estado de tus equipos</p>
                </div>
            </div>
            <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.show', $entity->id) }}">
                <i class="bi bi-arrow-left mr-2"></i> Volver
            </x-button>
        </div>

        @if(session('success'))
            <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
        @endif

        @forelse($maintenanceData as $data)
            @php
                $score = $data['status']['health_score'];
                $scoreColor = $score >= 90 ? 'emerald' : ($score >= 70 ? 'amber' : 'red');
                $pendingCount = count($data['status']['pending_tasks']);
            @endphp

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-6 overflow-hidden">

                {{-- Equipment Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center">
                            <i class="bi bi-plug text-orange-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $data['equipment']->name }}</h3>
                            <p class="text-xs text-gray-400">{{ $data['equipment']->room->name ?? '-' }} · {{ $data['equipment']->type->name ?? '' }}</p>
                        </div>
                    </div>

                    {{-- Health Score Badge --}}
                    <div class="flex items-center gap-3">
                        @if($pendingCount > 0)
                            <span class="text-xs font-medium text-red-600 bg-red-50 px-3 py-1 rounded-full">
                                {{ $pendingCount }} {{ $pendingCount === 1 ? 'tarea pendiente' : 'tareas pendientes' }}
                            </span>
                        @endif
                        <div class="flex flex-col items-center">
                            <div class="w-14 h-14 relative">
                                <svg class="w-14 h-14 -rotate-90" viewBox="0 0 36 36">
                                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e5e7eb" stroke-width="3"/>
                                    <circle cx="18" cy="18" r="15.9" fill="none"
                                        stroke="{{ $score >= 90 ? '#10b981' : ($score >= 70 ? '#f59e0b' : '#ef4444') }}"
                                        stroke-width="3"
                                        stroke-dasharray="{{ $score }}, 100"
                                        stroke-linecap="round"/>
                                </svg>
                                <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-700">{{ $score }}%</span>
                            </div>
                            <span class="text-xs text-gray-400 mt-1">Salud</span>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Pending Tasks --}}
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <i class="bi bi-exclamation-triangle text-amber-500"></i>
                            Tareas Pendientes
                        </h4>

                        @if(empty($data['status']['pending_tasks']))
                            <div class="flex items-center gap-2 text-emerald-600 bg-emerald-50 rounded-xl px-4 py-3">
                                <i class="bi bi-check-circle-fill"></i>
                                <span class="text-sm font-medium">Todo al día — ¡Excelente!</span>
                            </div>
                        @else
                            <div class="space-y-2">
                                @foreach($data['status']['pending_tasks'] as $task)
                                    <div class="flex items-start justify-between bg-red-50 border border-red-100 rounded-xl px-4 py-3">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-800">{{ $task['task'] }}</p>
                                            <p class="text-xs text-red-500 mt-0.5">
                                                <i class="bi bi-clock mr-1"></i>Vence: {{ $task['due_date'] }}
                                            </p>
                                        </div>
                                        <span class="ml-3 text-xs font-semibold text-red-600 bg-red-100 px-2 py-1 rounded-lg whitespace-nowrap">
                                            -{{ $task['impact'] }} eficiencia
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                            @if($data['status']['penalty_factor'] > 1.0)
                                <div class="mt-3 flex items-center gap-2 text-sm text-red-600 bg-red-50 rounded-xl px-4 py-2">
                                    <i class="bi bi-lightning-fill"></i>
                                    <span>Penalización de consumo actual: <strong>+{{ round(($data['status']['penalty_factor'] - 1) * 100) }}%</strong></span>
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- Register Maintenance --}}
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            <i class="bi bi-check2-square text-emerald-500"></i>
                            Registrar Mantenimiento Realizado
                        </h4>

                        <form action="{{ route('maintenance.log.store', $data['equipment']->id) }}" method="POST" class="space-y-3">
                            @csrf
                            <select name="maintenance_task_id" required
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-orange-300 bg-white">
                                <option value="">Seleccionar tarea realizada...</option>
                                @foreach($data['equipment']->type->maintenanceTasks as $task)
                                    <option value="{{ $task->id }}">{{ $task->title }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="notes" placeholder="Notas opcionales (ej: Cambio de filtro marca X)"
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-orange-300">
                            <x-button type="submit" variant="primary" class="w-full">
                                <i class="bi bi-check-circle mr-2"></i> Registrar
                            </x-button>
                        </form>
                    </div>
                </div>
            </div>

        @empty
            <div class="text-center py-20">
                <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="bi bi-tools text-4xl text-orange-500"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Sin equipos con mantenimiento configurado</h3>
                <p class="text-gray-500 max-w-md mx-auto">
                    Agregá equipos como aires acondicionados, heladeras o lavarropas para ver sus recomendaciones de mantenimiento preventivo.
                </p>
            </div>
        @endforelse

        {{-- Info Footer --}}
        @if(count($maintenanceData) > 0)
            <x-alert type="info" class="mt-4">
                <div class="flex items-start gap-3">
                    <i class="bi bi-lightbulb text-xl"></i>
                    <div>
                        <strong>¿Por qué importa el mantenimiento?</strong>
                        Un aire acondicionado con filtros sucios puede consumir hasta un <strong>15% más</strong>.
                        Una heladera con condensador sucio, hasta un <strong>15% más</strong>.
                        El mantenimiento preventivo no solo alarga la vida útil — reduce tu factura.
                    </div>
                </div>
            </x-alert>
        @endif

    </div>
</div>
@endsection
