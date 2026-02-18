@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-gray-600 to-gray-800 w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-power text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Consumo Fantasma</h1>
                    <p class="text-gray-500">{{ $entity->name }} — Equipos en Stand By</p>
                </div>
            </div>
            <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.show', $entity->id) }}">
                <i class="bi bi-arrow-left mr-2"></i> Volver
            </x-button>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            {{-- Consumo actual --}}
            <div class="bg-gradient-to-br from-gray-700 to-gray-900 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex items-center gap-3 mb-4">
                    <i class="bi bi-lightning-fill text-2xl text-amber-400"></i>
                    <span class="font-medium">Consumo Actual</span>
                </div>
                <p class="text-3xl font-bold mb-2">{{ number_format($totalStandbyKwh, 1) }} kWh</p>
                <p class="text-gray-300 text-sm">Consumo mensual por Stand By</p>
            </div>

            {{-- Costo estimado --}}
            <div class="bg-white rounded-2xl p-6 border-2 border-gray-200 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <i class="bi bi-piggy-bank text-2xl text-gray-600"></i>
                    <span class="font-medium text-gray-600">Costo Estimado</span>
                </div>
                <p class="text-3xl font-bold text-gray-700 mb-2">${{ number_format($totalStandbyCost, 0, ',', '.') }}</p>
                <p class="text-gray-500 text-sm">Costo mensual adicional</p>
            </div>

            {{-- Ahorro --}}
            <div class="bg-white rounded-2xl p-6 border-2 border-emerald-200 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <i class="bi bi-check-circle text-2xl text-emerald-600"></i>
                    <span class="font-medium text-emerald-600">Ahorro</span>
                </div>
                @if($totalRealizedSavings > 0)
                    <p class="text-3xl font-bold text-emerald-600 mb-2">${{ number_format($totalRealizedSavings, 0, ',', '.') }}</p>
                    <p class="text-gray-500 text-sm">Ya estás ahorrando este mes</p>
                @else
                    <p class="text-3xl font-bold text-emerald-600 mb-2">${{ number_format($totalPotentialSavings, 0, ',', '.') }}</p>
                    <p class="text-gray-500 text-sm">Ahorro potencial mensual</p>
                @endif
            </div>
        </div>

        {{-- Equipment Table --}}
        <x-card :padding="false">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="bi bi-plug text-gray-500"></i>
                        Mis Equipos
                    </h3>
                    <p class="text-sm text-gray-400 mt-0.5">Marcá los equipos que dejás enchufados cuando no los usás</p>
                </div>
                <div class="flex items-center gap-4 text-xs text-gray-500">
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span> Enchufado (consume)
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-emerald-400 inline-block"></span> Desenchufado (ahorra)
                    </span>
                </div>
            </div>

            @if($equipmentList->isEmpty())
                <div class="text-center py-16">
                    <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="bi bi-check-circle text-4xl text-emerald-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">¡Sin equipos para analizar!</h3>
                    <p class="text-gray-500">Agregá equipos a tus ambientes para ver el análisis de consumo fantasma.</p>
                </div>
            @else
                {{-- Group by category --}}
                @foreach($equipmentList->groupBy(fn($eq) => $eq->category->name ?? 'Sin categoría') as $categoryName => $items)
                    <div class="px-6 py-2 bg-gray-50 border-b border-gray-100">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ $categoryName }}</span>
                    </div>
                    @foreach($items as $eq)
                        @php
                            $standbyPowerW  = $eq->type->default_standby_power_w ?? 5;
                            $standbyPowerKw = $standbyPowerW / 1000;
                            $activeHours    = $eq->avg_daily_use_hours ?? 2;
                            $standbyHours   = max(0, 24 - $activeHours);
                            $monthlyKwh     = $standbyPowerKw * $standbyHours * 30;
                            $monthlyCost    = $monthlyKwh * 150;
                        @endphp
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50 hover:bg-gray-50 transition-colors {{ $eq->is_standby ? '' : 'opacity-60' }}">
                            {{-- Equipo info --}}
                            <div class="flex items-center gap-3 flex-1">
                                <div class="w-10 h-10 {{ $eq->is_standby ? 'bg-amber-100' : 'bg-emerald-100' }} rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="bi bi-plug {{ $eq->is_standby ? 'text-amber-600' : 'text-emerald-600' }}"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 text-sm">{{ $eq->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $eq->room->name ?? '-' }} · {{ $standbyPowerW }}W standby · {{ number_format($standbyHours, 0) }}h/día en espera</p>
                                </div>
                            </div>

                            {{-- Consumo --}}
                            <div class="text-right mr-8 hidden sm:block">
                                @if($eq->is_standby)
                                    <p class="text-sm font-bold text-gray-800">{{ number_format($monthlyKwh, 1) }} kWh/mes</p>
                                    <p class="text-xs text-red-500">${{ number_format($monthlyCost, 0, ',', '.') }}/mes</p>
                                @else
                                    <p class="text-sm text-emerald-600 font-medium">Ahorrando</p>
                                    <p class="text-xs text-emerald-400">${{ number_format($monthlyCost, 0, ',', '.') }}/mes</p>
                                @endif
                            </div>

                            {{-- Toggle --}}
                            <form action="{{ route($config['route_prefix'] . '.standby.toggle', ['entity' => $entity->id, 'equipment' => $eq->id]) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" class="sr-only peer"
                                        {{ $eq->is_standby ? 'checked' : '' }}
                                        onchange="this.form.submit()">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-amber-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                                </label>
                            </form>
                        </div>
                    @endforeach
                @endforeach
            @endif
        </x-card>

        {{-- Info --}}
        <x-alert type="info" class="mt-6">
            <div class="flex items-start gap-3">
                <i class="bi bi-lightbulb text-xl"></i>
                <div>
                    <strong>¿Cómo funciona?</strong> Activá el toggle en los equipos que dejás enchufados cuando no los usás (TV, microondas, cargadores, etc.). El sistema calcula cuánto consumen en modo espera y cuánto podrías ahorrar desenchufándolos o usando zapatillas con interruptor.
                </div>
            </div>
        </x-alert>
    </div>
</div>
@endsection
