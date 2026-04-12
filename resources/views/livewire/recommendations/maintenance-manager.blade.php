<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-linear-to-br from-orange-500 to-red-600 w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg">
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
            <x-alert type="success" class="mb-6" wire:transition>{{ session('success') }}</x-alert>
        @endif

        <div class="space-y-6" wire:loading.class="opacity-50 transition-opacity">
            @forelse($this->maintenanceData as $data)
                @php
                    $score = $data['status']['health_score'];
                    $scoreColor = $score >= 90 ? '#10b981' : ($score >= 70 ? '#f59e0b' : '#ef4444');
                    $pendingCount = count($data['status']['pending_tasks']);
                    $equipmentId = $data['equipment']->id;
                @endphp

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">

                    {{-- Equipment Header --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/50">
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
                                <span class="text-xs font-medium text-red-600 bg-red-50 px-3 py-1 rounded-full animate-pulse">
                                    {{ $pendingCount }} {{ $pendingCount === 1 ? 'tarea' : 'tareas' }} pendientes
                                </span>
                            @endif
                            <div class="flex flex-col items-center">
                                <div class="w-14 h-14 relative">
                                    <svg class="w-14 h-14 -rotate-90" viewBox="0 0 36 36">
                                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e5e7eb" stroke-width="3"/>
                                        <circle cx="18" cy="18" r="15.9" fill="none"
                                            stroke="{{ $scoreColor }}"
                                            stroke-width="3"
                                            stroke-dasharray="{{ $score }}, 100"
                                            stroke-linecap="round"/>
                                    </svg>
                                    <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-gray-700">{{ $score }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-6 grid grid-cols-1 md:grid-cols-2 gap-8">

                        {{-- Pending Tasks --}}
                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                <i class="bi bi-exclamation-triangle text-amber-500"></i>
                                Estado de Tareas
                            </h4>

                            @if(empty($data['status']['pending_tasks']))
                                <div class="flex items-center gap-2 text-emerald-600 bg-emerald-50 rounded-xl px-4 py-4 border border-emerald-100">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <span class="text-sm font-medium">Todo al día — ¡Máxima eficiencia!</span>
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
                                            <span class="ml-3 text-xs font-semibold text-red-600 bg-red-100 px-2 py-1 rounded-lg">
                                                -{{ $task['impact'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>

                                @if($data['status']['penalty_factor'] > 1.0)
                                    <div class="mt-4 flex items-center gap-2 text-sm text-red-600 bg-red-50 rounded-xl px-4 py-2 border border-red-100">
                                        <i class="bi bi-lightning-fill"></i>
                                        <span>Consumiendo <strong>+{{ round(($data['status']['penalty_factor'] - 1) * 100) }}%</strong> por falta de mantenimiento.</span>
                                    </div>
                                @endif
                            @endif
                        </div>

                        {{-- Register Maintenance --}}
                        <div class="bg-gray-50/50 rounded-2xl p-5 border border-dashed border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                <i class="bi bi-plus-circle text-emerald-500"></i>
                                Registrar Trabajo Realizado
                            </h4>

                            <div class="space-y-4">
                                <div>
                                    <select wire:model="selectedTask.{{ $equipmentId }}" 
                                        class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-orange-300 bg-white shadow-xs">
                                        <option value="">Seleccionar tarea realizada...</option>
                                        @foreach($data['equipment']->type->maintenanceTasks as $task)
                                            <option value="{{ $task->id }}">{{ $task->title }}</option>
                                        @endforeach
                                    </select>
                                    @error("task.$equipmentId") <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                                </div>
                                
                                <input type="text" wire:model="notes.{{ $equipmentId }}" placeholder="Notas opcionales (ej: Cambio de filtro marca X)"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-orange-300 bg-white shadow-xs">
                                
                                <x-button wire:click="registerMaintenance({{ $equipmentId }})" variant="primary" class="w-full justify-center">
                                    <i class="bi bi-check-circle mr-2"></i> Registrar Mantenimiento
                                </x-button>
                            </div>
                        </div>
                    </div>
                </div>

            @empty
                <div class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-gray-200">
                    <div class="w-20 h-20 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="bi bi-tools text-4xl text-orange-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Sin equipos para mantener</h3>
                    <p class="text-gray-500 max-w-md mx-auto">
                        Agregá aires acondicionados, heladeras o lavarropas para ver sus recomendaciones.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Info Footer --}}
        <x-alert type="info" class="mt-8">
            <div class="flex items-start gap-4">
                <div class="bg-blue-100 p-2 rounded-lg">
                    <i class="bi bi-lightbulb text-blue-600"></i>
                </div>
                <div>
                    <strong class="text-blue-900">¿Sabías que?</strong>
                    <p class="text-blue-800 text-sm mt-1">
                        Filtros sucios en un aire pueden aumentar tu consumo un <strong>15%</strong>. 
                        El mantenimiento regular no solo ahorra energía, sino que evita reparaciones costosas.
                    </p>
                </div>
            </div>
        </x-alert>
    </div>
</div>
