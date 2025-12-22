@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-purple-500 to-indigo-600 w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-sliders text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Ajuste de Uso</h1>
                    <p class="text-gray-500 text-sm">Factura #{{ $invoice->id }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3 mt-4 md:mt-0">
                @php
                    $startDate = \Carbon\Carbon::parse($invoice->start_date);
                    $endDate = \Carbon\Carbon::parse($invoice->end_date);
                    $days = $startDate->diffInDays($endDate);
                @endphp
                <div class="px-4 py-2 bg-white rounded-lg border border-gray-200 shadow-sm">
                    <span class="text-sm text-gray-500">Período:</span>
                    <span class="font-semibold text-gray-900">{{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</span>
                    <x-badge variant="secondary" size="xs" class="ml-2">{{ $days }} días</x-badge>
                </div>
            </div>
        </div>

        {{-- Locked Warning --}}
        @if($invoice->usage_locked)
            <x-alert type="warning" class="mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="bi bi-lock-fill text-xl"></i>
                        <div>
                            <strong>Período Cerrado</strong>
                            <p class="text-sm mt-1">Este período está bloqueado. Para modificar, debes reabrirlo.</p>
                        </div>
                    </div>
                    <form action="{{ route('usage_adjustments.unlock', $invoice->id) }}" method="POST">
                        @csrf
                        <x-button variant="warning" type="submit">
                            <i class="bi bi-unlock-fill mr-1"></i> Reabrir
                        </x-button>
                    </form>
                </div>
            </x-alert>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('usage_adjustments.update', $invoice->id) }}">
            @csrf

            @forelse($rooms as $room)
                @if($room->equipment->isNotEmpty())
                    <x-card :padding="false" class="mb-6">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                            <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                                <i class="bi bi-door-open text-blue-500"></i>
                                {{ $room->name }}
                            </h3>
                        </div>
                        
                        <div class="divide-y divide-gray-100">
                            @foreach($room->equipment as $equipment)
                                @php $usage = $usages[$equipment->id] ?? null; @endphp
                                <div class="p-4 hover:bg-gray-50" x-data="{ 
                                    frequency: '{{ $usage->usage_frequency ?? 'diario' }}',
                                    hours: {{ $usage->avg_daily_use_hours ?? 0 }},
                                    allDays: false
                                }" x-init="
                                    allDays = document.querySelectorAll('[data-day-{{ $equipment->id }}]:checked').length === 7;
                                ">
                                    <div class="grid grid-cols-1 lg:grid-cols-6 gap-4 items-start">
                                        {{-- Equipment Info --}}
                                        <div class="lg:col-span-2">
                                            <p class="font-medium text-gray-900">{{ $equipment->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $equipment->room->name ?? '-' }}</p>
                                            <p class="text-xs text-gray-400 mt-1">
                                                <i class="bi bi-lightning"></i> {{ $equipment->nominal_power_w ?? '-' }} W
                                            </p>
                                        </div>

                                        {{-- Frequency --}}
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Frecuencia</label>
                                            <select name="usages[{{ $equipment->id }}][usage_frequency]" 
                                                x-model="frequency"
                                                class="block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                                @php
                                                    $freqs = ['diario' => 'Diario', 'semanal' => 'Semanal', 'quincenal' => 'Quincenal', 'mensual' => 'Mensual', 'puntual' => 'Puntual'];
                                                @endphp
                                                @foreach($freqs as $key => $label)
                                                    <option value="{{ $key }}" {{ ($usage->usage_frequency ?? 'diario') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Hours/Usage --}}
                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Uso</label>
                                            
                                            {{-- Daily/Weekly --}}
                                            <div x-show="frequency === 'diario' || frequency === 'semanal'">
                                                <div class="flex items-center gap-2">
                                                    <input type="range" min="0" max="24" step="0.5" 
                                                        x-model="hours"
                                                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-500">
                                                    <input type="number" min="0" max="24" step="0.1"
                                                        name="usages[{{ $equipment->id }}][avg_daily_use_hours]" 
                                                        x-model="hours"
                                                        class="w-16 rounded-lg border-gray-300 text-sm text-center">
                                                </div>
                                                <p class="text-xs text-gray-500 mt-1" x-text="Math.round(hours * 60) + ' min/día'"></p>
                                            </div>

                                            {{-- Occasional --}}
                                            <div x-show="frequency !== 'diario' && frequency !== 'semanal'" class="flex gap-2">
                                                <div>
                                                    <input type="number" min="0" step="1" 
                                                        name="usages[{{ $equipment->id }}][usage_count]" 
                                                        value="{{ $usage->usage_count ?? '' }}"
                                                        placeholder="Usos"
                                                        class="w-16 rounded-lg border-gray-300 text-sm">
                                                </div>
                                                <div>
                                                    <input type="number" min="0" step="0.1" 
                                                        name="usages[{{ $equipment->id }}][avg_use_duration]" 
                                                        value="{{ $usage->avg_use_duration ?? '' }}"
                                                        placeholder="Horas"
                                                        class="w-16 rounded-lg border-gray-300 text-sm">
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Days --}}
                                        <div class="lg:col-span-2">
                                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Días de uso</label>
                                            <div class="flex flex-wrap gap-1">
                                                <label class="flex items-center gap-1 cursor-pointer mr-2">
                                                    <input type="checkbox" x-model="allDays"
                                                        @change="document.querySelectorAll('[data-day-{{ $equipment->id }}]').forEach(cb => cb.checked = allDays)"
                                                        class="rounded border-gray-300 text-blue-500 focus:ring-blue-500">
                                                    <span class="text-xs font-medium">Todos</span>
                                                </label>
                                                @php
                                                    $days = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];
                                                    $selected = isset($usage->use_days_of_week) ? explode(',', $usage->use_days_of_week) : [];
                                                @endphp
                                                @foreach($days as $day)
                                                    <label class="inline-flex items-center justify-center w-8 h-8 cursor-pointer">
                                                        <input type="checkbox" 
                                                            name="usages[{{ $equipment->id }}][use_days_of_week][]" 
                                                            value="{{ $day }}"
                                                            data-day-{{ $equipment->id }}
                                                            {{ in_array($day, $selected) ? 'checked' : '' }}
                                                            class="sr-only peer">
                                                        <span class="flex items-center justify-center w-full h-full text-xs font-medium rounded-lg border-2 border-gray-200 peer-checked:border-blue-500 peer-checked:bg-blue-500 peer-checked:text-white transition-colors">
                                                            {{ $day }}
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-card>
                @endif
            @empty
                <x-card class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-inbox text-3xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500">No hay equipos activos para ajustar en este período.</p>
                </x-card>
            @endforelse

            {{-- Notes & Actions --}}
            <x-card class="mt-6">
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notas del ajuste</label>
                    <textarea name="notes" id="notes" rows="3" 
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Observaciones opcionales sobre este período...">{{ $usageAdjustment->notes ?? '' }}</textarea>
                </div>

                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pt-6 border-t border-gray-200">
                    <label class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl cursor-pointer {{ $invoice->usage_locked ? 'opacity-50' : '' }}">
                        <input type="checkbox" name="lock_invoice" value="1" 
                            {{ $invoice->usage_locked ? 'checked disabled' : '' }}
                            class="rounded border-gray-300 text-purple-500 focus:ring-purple-500 w-5 h-5">
                        <div>
                            <span class="font-medium text-gray-900">Cerrar Período</span>
                            <p class="text-sm text-gray-500">Bloquear edición futura</p>
                        </div>
                    </label>

                    <div class="flex gap-3">
                        <x-button variant="secondary" href="{{ route('usage_adjustments.index') }}">
                            Cancelar
                        </x-button>
                        <x-button variant="primary" type="submit" {{ $invoice->usage_locked ? 'disabled' : '' }}>
                            <i class="bi bi-check-lg mr-2"></i> Guardar Ajustes
                        </x-button>
                    </div>
                </div>
            </x-card>
        </form>
    </div>
</div>
@endsection
