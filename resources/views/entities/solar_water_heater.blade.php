@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-amber-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-orange-500 to-red-500 w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-sun text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Calefón Solar</h1>
                    <p class="text-gray-500">{{ $entity->name }}</p>
                </div>
            </div>
            <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.show', $entity->id) }}">
                <i class="bi bi-arrow-left mr-2"></i> Volver
            </x-button>
        </div>

        {{-- Location Info --}}
        <div class="flex items-center gap-2 text-gray-600 mb-6">
            <i class="bi bi-geo-alt"></i>
            <span>{{ $entity->address_street }}, {{ $entity->locality->name ?? 'N/A' }}</span>
        </div>

        {{-- Solar Profile --}}
        @if(isset($climateProfile) && !empty($climateProfile))
            <x-card class="mb-6 border-l-4 border-l-amber-400">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                        <i class="bi bi-sun text-amber-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900">Perfil Solar de tu Zona</h3>
                </div>
                
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div class="p-4 bg-amber-50 rounded-xl">
                        <p class="text-2xl font-bold text-amber-600">{{ $climateProfile['avg_radiation'] }}</p>
                        <p class="text-xs text-gray-500">Radiación (MJ/m²)</p>
                    </div>
                    <div class="p-4 bg-amber-50 rounded-xl">
                        <p class="text-2xl font-bold text-amber-600">{{ $climateProfile['avg_sunshine_duration'] }}</p>
                        <p class="text-xs text-gray-500">Horas de Sol</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-xl">
                        <p class="text-2xl font-bold text-gray-600">{{ $climateProfile['avg_cloud_cover'] }}%</p>
                        <p class="text-xs text-gray-500">Nubosidad</p>
                    </div>
                </div>
                
                <p class="text-xs text-gray-500 text-center mt-4">
                    <i class="bi bi-info-circle mr-1"></i>
                    Datos históricos reales de {{ $entity->locality->name ?? 'tu zona' }}
                </p>
            </x-card>
        @endif

        {{-- Main Content --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            
            {{-- Input Form (if needed) --}}
            @if(!isset($result))
                <div class="lg:col-span-2">
                    <x-card>
                        <div class="text-center mb-6">
                            <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="bi bi-fire text-3xl text-orange-600"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Analicemos tu consumo de agua caliente</h3>
                            <p class="text-gray-500 max-w-lg mx-auto">
                                No detectamos un termotanque eléctrico en tus equipos. Para calcular tu ahorro solar, necesitamos saber tu consumo actual de gas.
                            </p>
                        </div>

                        <form action="{{ route($config['route_prefix'] . '.solar_water_heater', $entity->id) }}" method="POST" class="max-w-md mx-auto">
                            @csrf
                            <div class="space-y-4">
                                {{-- Hidden field to force calculation in service --}}
                                <input type="hidden" name="calculate" value="1">
                                
                                <div class="bg-blue-50 p-4 rounded-lg flex items-start gap-3 text-sm text-blue-800">
                                    <i class="bi bi-info-circle mt-1"></i>
                                    <p>El sistema calculará el ahorro basándose en la cantidad de personas ({{ $entity->people_count }}) y la zona climática, comparando contra el uso de gas.</p>
                                </div>

                                <x-button type="submit" variant="primary" class="w-full justify-center">
                                    <i class="bi bi-calculator mr-2"></i> Calcular Ahorro
                                </x-button>
                            </div>
                        </form>
                    </x-card>
                </div>
            @else
                    {{-- Recommendation --}}
                    <x-card class="border-2 border-emerald-200">
                        <div class="text-center py-6">
                            <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="bi bi-check-circle text-3xl text-emerald-600"></i>
                            </div>
                            <p class="text-sm text-gray-500 uppercase tracking-wide mb-2">Recomendación</p>
                            <p class="text-5xl font-bold text-emerald-600 mb-2">{{ $result['waterHeaterData']['recommended_equipment_liters'] }}</p>
                            <p class="text-xl text-gray-700">Litros</p>
                            <p class="text-gray-500">Termotanque Solar</p>
                        </div>
                        
                        {{-- People Count Adjustment Form --}}
                        <div class="px-4 py-4 bg-gray-50 rounded-xl mb-4">
                             <form action="{{ route($config['route_prefix'] . '.solar_water_heater', $entity->id) }}" method="POST" id="update-people-form">
                                @csrf
                                <div x-data="{ 
                                    peopleCount: {{ request('people_count', $result['waterHeaterData']['people_count']) }}
                                }">
                                    <label class="block text-sm font-medium text-gray-700 mb-1 text-center">
                                        Calculado para <span x-text="peopleCount" class="font-bold text-gray-900"></span> personas
                                    </label>
                                    
                                    <div class="flex items-center gap-2">
                                        <i class="bi bi-person text-gray-400"></i>
                                        <input 
                                            type="range" 
                                            name="people_count" 
                                            x-model="peopleCount" 
                                            min="1" 
                                            max="15" 
                                            step="1"
                                            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-emerald-500"
                                            @change="document.getElementById('update-people-form').submit()"
                                        >
                                        <i class="bi bi-people text-gray-400"></i>
                                    </div>
                                    <p class="text-[10px] text-gray-400 text-center mt-1">Deslizá para ajustar</p>
                                </div>
                            </form>
                        </div>

                        <div class="flex justify-around py-4 border-t border-gray-100">
                             <div class="text-center">
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($result['waterHeaterData']['daily_liters'], 0) }} L</p>
                                <p class="text-xs text-gray-500">Demanda Diaria</p>
                            </div>
                        </div>
                    </x-card>
    
                {{-- Savings --}}
                <x-card>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-piggy-bank text-blue-600"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900">Ahorro Estimado</h3>
                    </div>
                    
                    {{-- Fuel Tabs --}}
                    <div x-data="{ activeTab: 'natural' }" class="space-y-4">
                        <div class="flex gap-2 border-b border-gray-200">
                            <button @click="activeTab = 'natural'" 
                                :class="activeTab === 'natural' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500'"
                                class="px-4 py-2 text-sm font-medium transition-colors">
                                Gas Natural
                            </button>
                            <button @click="activeTab = 'garrafa'" 
                                :class="activeTab === 'garrafa' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500'"
                                class="px-4 py-2 text-sm font-medium transition-colors">
                                Garrafa
                            </button>
                            <button @click="activeTab = 'electric'" 
                                :class="activeTab === 'electric' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500'"
                                class="px-4 py-2 text-sm font-medium transition-colors">
                                Electricidad
                            </button>
                        </div>
                        
                        {{-- Gas Natural --}}
                        <div x-show="activeTab === 'natural'" class="space-y-4">
                            <div class="text-center py-4 bg-blue-50 rounded-xl">
                                <p class="text-3xl font-bold text-blue-600">${{ number_format($result['waterHeaterData']['savings']['gas_natural']['monthly_savings'], 0, ',', '.') }}</p>
                                <p class="text-sm text-gray-500">Ahorro Mensual</p>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-600">Consumo evitado</span>
                                    <span class="font-semibold">{{ $result['waterHeaterData']['savings']['gas_natural']['m3_per_month'] }} m³/mes</span>
                                </div>
                                <div class="flex justify-between py-2">
                                    <span class="text-gray-600">Ahorro Anual</span>
                                    <span class="font-bold text-emerald-600">${{ number_format($result['waterHeaterData']['savings']['gas_natural']['annual_savings'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Garrafa --}}
                        <div x-show="activeTab === 'garrafa'" x-cloak class="space-y-4">
                            <div class="text-center py-4 bg-blue-50 rounded-xl">
                                <p class="text-3xl font-bold text-blue-600">${{ number_format($result['waterHeaterData']['savings']['gas']['monthly_savings'], 0, ',', '.') }}</p>
                                <p class="text-sm text-gray-500">Ahorro Mensual</p>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-600">Garrafas evitadas</span>
                                    <span class="font-semibold">{{ $result['waterHeaterData']['savings']['gas']['garrafas_per_month'] }}/mes</span>
                                </div>
                                <div class="flex justify-between py-2">
                                    <span class="text-gray-600">Ahorro Anual</span>
                                    <span class="font-bold text-emerald-600">${{ number_format($result['waterHeaterData']['savings']['gas']['annual_savings'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Electric --}}
                        <div x-show="activeTab === 'electric'" x-cloak class="space-y-4">
                            <div class="text-center py-4 bg-blue-50 rounded-xl">
                                <p class="text-3xl font-bold text-blue-600">${{ number_format($result['waterHeaterData']['savings']['electric']['monthly_savings'], 0, ',', '.') }}</p>
                                <p class="text-sm text-gray-500">Ahorro Mensual</p>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between py-2 border-b border-gray-100">
                                    <span class="text-gray-600">Energía ahorrada</span>
                                    <span class="font-semibold">{{ number_format($result['waterHeaterData']['monthly_energy_kwh'] * 0.75, 0) }} kWh/mes</span>
                                </div>
                                <div class="flex justify-between py-2">
                                    <span class="text-gray-600">Ahorro Anual</span>
                                    <span class="font-bold text-emerald-600">${{ number_format($result['waterHeaterData']['savings']['electric']['annual_savings'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-gray-100">
                             <x-button variant="primary" class="w-full justify-center" onclick="alert('Funcionalidad de contacto en desarrollo (Comisión por venta)')">
                                Solicitar Presupuesto
                                <i class="bi bi-arrow-right ml-2"></i>
                            </x-button>
                            <p class="text-[10px] text-center text-gray-400 mt-2">* Presupuesto sin cargo. Valores estimativos.</p>
                        </div>
                    </div>
                </x-card>
            @endif
        </div>

        {{-- Info Alert --}}
        <x-alert type="warning" class="mb-6">
            <div class="flex items-start gap-3">
                <i class="bi bi-lightbulb text-xl"></i>
                <div>
                    <strong>¿Sabías que?</strong>
                    <p class="text-sm mt-1">El sol puede cubrir el 100% de tu necesidad de agua caliente en verano y hasta el 60% en invierno.</p>
                </div>
            </div>
        </x-alert>
    </div>
</div>
@endsection
