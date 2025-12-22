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
                    <p class="text-gray-500">{{ $entity->name }} - Equipos en Stand By</p>
                </div>
            </div>
            <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.show', $entity->id) }}">
                <i class="bi bi-arrow-left mr-2"></i> Volver
            </x-button>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            {{-- Current Consumption --}}
            <div class="bg-gradient-to-br from-gray-700 to-gray-900 rounded-2xl p-6 text-white shadow-lg">
                <div class="flex items-center gap-3 mb-4">
                    <i class="bi bi-lightning-fill text-2xl text-amber-400"></i>
                    <span class="font-medium">Consumo Actual</span>
                </div>
                <p class="text-3xl font-bold mb-2">{{ number_format($totalStandbyKwh, 1) }} kWh</p>
                <p class="text-gray-300 text-sm">Consumo mensual por Stand By</p>
            </div>

            {{-- Estimated Cost --}}
            <div class="bg-white rounded-2xl p-6 border-2 border-gray-200 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <i class="bi bi-piggy-bank text-2xl text-gray-600"></i>
                    <span class="font-medium text-gray-600">Costo Estimado</span>
                </div>
                <p class="text-3xl font-bold text-gray-700 mb-2">${{ number_format($totalStandbyCost, 0, ',', '.') }}</p>
                <p class="text-gray-500 text-sm">Costo mensual adicional</p>
            </div>

            {{-- Savings --}}
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
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                    <i class="bi bi-plug text-gray-500"></i>
                    Equipos con Consumo Fantasma
                </h3>
            </div>

            @if($equipmentList->isEmpty())
                <div class="text-center py-16">
                    <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="bi bi-check-circle text-4xl text-emerald-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">¡Excelente!</h3>
                    <p class="text-gray-500">No se detectaron equipos con consumo fantasma significativo.</p>
                </div>
            @else
                <x-table hover>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4">Equipo</th>
                            <th class="px-6 py-4">Ubicación</th>
                            <th class="px-6 py-4">Potencia Stand By</th>
                            <th class="px-6 py-4">Horas en Espera</th>
                            <th class="px-6 py-4">Consumo/Mes</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4">Acción</th>
                        </tr>
                    </x-slot:head>
                    
                    @foreach($equipmentList as $eq)
                        @php
                            $standbyPowerKw = ($eq->type->default_standby_power_w ?? 0) / 1000;
                            $potentialStandbyHours = max(0, 24 - ($eq->avg_daily_use_hours ?? 0));
                            $monthlyKwh = $standbyPowerKw * $potentialStandbyHours * 30;
                            $monthlyCost = $monthlyKwh * 150;
                        @endphp
                        <tr class="{{ $eq->is_standby ? '' : 'bg-gray-50 opacity-60' }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 {{ $eq->is_standby ? 'bg-amber-100' : 'bg-emerald-100' }} rounded-lg flex items-center justify-center">
                                        <i class="bi bi-plug {{ $eq->is_standby ? 'text-amber-600' : 'text-emerald-600' }}"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $eq->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $eq->type->name ?? 'Desconocido' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $eq->room->name ?? '-' }}</td>
                            <td class="px-6 py-4 font-mono">{{ $eq->type->default_standby_power_w ?? 0 }} W</td>
                            <td class="px-6 py-4">{{ number_format($potentialStandbyHours, 1) }} hs/día</td>
                            <td class="px-6 py-4">
                                @if($eq->is_standby)
                                    <span class="font-bold text-gray-900">{{ number_format($monthlyKwh, 1) }} kWh</span>
                                    <p class="text-xs text-red-500">${{ number_format($monthlyCost, 0, ',', '.') }}</p>
                                @else
                                    <span class="line-through text-gray-400">{{ number_format($monthlyKwh, 1) }} kWh</span>
                                    <p class="text-xs text-emerald-500">Ahorrado</p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($eq->is_standby)
                                    <x-badge variant="warning"><i class="bi bi-plug-fill mr-1"></i> Enchufado</x-badge>
                                @else
                                    <x-badge variant="success"><i class="bi bi-plug mr-1"></i> Desenchufado</x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <form action="{{ route($config['route_prefix'] . '.standby.toggle', ['entity' => $entity->id, 'equipment' => $eq->id]) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" 
                                            {{ $eq->is_standby ? 'checked' : '' }} 
                                            onchange="this.form.submit()">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                                    </label>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </x-card>

        {{-- Info --}}
        <x-alert type="info" class="mt-6">
            <div class="flex items-start gap-3">
                <i class="bi bi-lightbulb text-xl"></i>
                <div>
                    <strong>Tip:</strong> Usa zapatillas con interruptor para desconectar fácilmente varios equipos a la vez cuando no los uses.
                </div>
            </div>
        </x-alert>
    </div>
</div>
@endsection
