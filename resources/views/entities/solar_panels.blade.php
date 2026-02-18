@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-amber-50 via-white to-orange-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-amber-400 to-orange-500 w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-sun text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Paneles Solares</h1>
                    <p class="text-gray-500">{{ $entity->name }}</p>
                </div>
            </div>
            <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.show', $entity->id) }}">
                <i class="bi bi-arrow-left mr-2"></i> Volver
            </x-button>
        </div>

        {{-- Solar Profile --}}
        @if(isset($climateProfile) && !empty($climateProfile))
            <x-card class="mb-6 border-l-4 border-l-amber-400">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                        <i class="bi bi-geo-alt text-amber-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Perfil Solar: {{ $entity->locality->name ?? 'Tu Zona' }}</h3>
                        <p class="text-xs text-gray-500">Zona Climática {{ $climateProfile['climate_zone'] }}</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div class="p-4 bg-amber-50 rounded-xl">
                        <p class="text-2xl font-bold text-amber-600">{{ $climateProfile['avg_sunshine_duration'] ?? '-' }}</p>
                        <p class="text-xs text-gray-500">Horas Sol/Día</p>
                    </div>
                    <div class="p-4 bg-amber-50 rounded-xl">
                        <p class="text-2xl font-bold text-amber-600">{{ $climateProfile['avg_radiation'] ?? '-' }}</p>
                        <p class="text-xs text-gray-500">Radiación (MJ/m²)</p>
                    </div>
                    <div class="p-4 bg-blue-50 rounded-xl">
                        <p class="text-2xl font-bold text-blue-600">{{ $climateProfile['avg_cloud_cover'] ?? '-' }}%</p>
                        <p class="text-xs text-gray-500">Nubosidad</p>
                    </div>
                </div>
            </x-card>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Form Section --}}
            <div class="lg:col-span-4">
                <x-card>
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="bi bi-calculator"></i> Cálculo de Viabilidad
                    </h3>
                    
                    <form action="{{ route($config['route_prefix'] . '.solar_panels', $entity->id) }}" method="POST">
                        @csrf
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Superficie Total (m²)</label>
                                <div class="bg-gray-50 px-3 py-2 rounded-lg border border-gray-200 text-gray-600">
                                    {{ $entity->square_meters }} m²
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Superficie construida registrada</p>
                            </div>

                            <div x-data="{ 
                                totalArea: {{ $entity->square_meters }},
                                availableArea: {{ old('available_area', request('available_area', round($entity->square_meters * 0.4))) }},
                                get percentage() { return Math.round((this.availableArea / this.totalArea) * 100); }
                            }">
                                <label for="available_area" class="block text-sm font-medium text-gray-700 mb-1">
                                    Superficie Disponible (<span x-text="percentage"></span>% del total)
                                </label>
                                
                                <div class="flex items-center gap-4 mb-2">
                                    <input 
                                        type="range" 
                                        x-model="availableArea" 
                                        min="1" 
                                        :max="totalArea" 
                                        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-amber-500"
                                    >
                                    <div class="relative w-24">
                                        <input 
                                            type="number" 
                                            name="available_area" 
                                            id="available_area" 
                                            x-model="availableArea"
                                            min="1" 
                                            :max="totalArea"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm text-right pr-8"
                                            required 
                                        />
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">m²</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <p class="text-xs text-gray-500 mt-1">
                                    Desliza para ajustar el área de techo libre de sombras.
                                </p>
                            </div>

                            <x-button type="submit" variant="primary" class="w-full justify-center">
                                <i class="bi bi-lightning-charge mr-2"></i> Calcular
                            </x-button>
                        </div>
                    </form>
                </x-card>

                @if($avgConsumption > 0)
                    <div class="mt-4 bg-blue-50 p-4 rounded-xl border border-blue-100">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs text-blue-600 font-medium">Consumo Promedio</span>
                            <span class="text-sm font-bold text-blue-700">{{ number_format($avgConsumption, 0) }} kWh/mes</span>
                        </div>
                        <div class="w-full bg-blue-200 rounded-full h-1.5">
                            <div class="bg-blue-500 h-1.5 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Results Section --}}
            <div class="lg:col-span-8">
                @if(isset($result))
                    <div class="space-y-6">
                        {{-- KPI Cards --}}
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm text-center">
                                <p class="text-3xl font-bold text-amber-500">{{ $result['panels_count'] }}</p>
                                <p class="text-xs text-gray-500 uppercase font-medium">Paneles</p>
                            </div>
                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm text-center">
                                <p class="text-3xl font-bold text-gray-800">{{ number_format($result['system_size_kwp'], 1) }}</p>
                                <p class="text-xs text-gray-500 uppercase font-medium">kWp Potencia</p>
                            </div>
                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm text-center">
                                <p class="text-3xl font-bold text-emerald-500">{{ number_format($result['coverage_winter'], 0) }}%</p>
                                <p class="text-xs text-gray-500 uppercase font-medium">Cobertura Invierno</p>
                            </div>
                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm text-center">
                                <p class="text-3xl font-bold text-emerald-600">{{ number_format($result['coverage_summer'], 0) }}%</p>
                                <p class="text-xs text-gray-500 uppercase font-medium">Cobertura Verano</p>
                            </div>
                        </div>

                        {{-- Main Analysis --}}
                        <x-card>
                            <h3 class="font-bold text-lg mb-4 text-gray-800">Análisis Económico Estimado</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-4">
                                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                        <span class="text-gray-600">Generación Mensual Est.</span>
                                        <span class="font-bold">{{ number_format($result['monthly_generation_kwh'], 0) }} kWh</span>
                                    </div>
                                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                        <span class="text-gray-600">Ahorro Anual Proy.</span>
                                        <span class="font-bold text-emerald-600 text-lg">${{ number_format($result['annual_savings'], 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center py-3 border-b border-gray-100">
                                        <span class="text-gray-600">Inversión Estimada</span>
                                        <span class="font-bold text-gray-900">${{ number_format($result['investment'], 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center py-3">
                                        <span class="text-gray-600">Retorno de Inversión (ROI)</span>
                                        <span class="font-bold text-blue-600">{{ number_format($result['roi_years'], 1) }} años</span>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 rounded-xl p-6 flex flex-col justify-center items-center text-center">
                                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-sm mb-4">
                                        <i class="bi bi-check-lg text-3xl text-emerald-500"></i>
                                    </div>
                                    <h4 class="font-bold text-gray-900 mb-2">Proyecto Viable</h4>
                                    <p class="text-sm text-gray-500 mb-6">Basado en tu consumo y superficie disponible, este proyecto generaría un ahorro significativo.</p>
                                    
                                    <div class="w-full">
                                        <x-button variant="primary" class="w-full justify-center" onclick="alert('Funcionalidad de contacto en desarrollo (Comisión por venta)')">
                                            Solicitar Presupuesto
                                            <i class="bi bi-arrow-right ml-2"></i>
                                        </x-button>
                                        <p class="text-[10px] text-gray-400 mt-2">* Presupuesto sin cargo. Valores estimativos.</p>
                                    </div>
                                </div>
                            </div>
                        </x-card>

                        <x-alert type="info">
                            <i class="bi bi-info-circle mr-2"></i>
                            <strong>Nota Importante:</strong> Los valores expresados son estimaciones basadas en promedios de mercado y datos climáticos históricos. Un instalador certificado deberá validar la factibilidad técnica en el sitio.
                        </x-alert>
                    </div>
                @else
                    {{-- Empty State / Intro --}}
                    <div class="h-full flex flex-col items-center justify-center text-center p-8 bg-white rounded-2xl border-2 border-dashed border-gray-200">
                        <div class="w-20 h-20 bg-amber-50 rounded-full flex items-center justify-center mb-4">
                            <i class="bi bi-sun text-4xl text-amber-500"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Descubre tu Potencial Solar</h3>
                        <p class="text-gray-500 max-w-sm mx-auto mb-6">
                            Ingresa la superficie disponible en tu techo para calcular cuántos paneles puedes instalar y cuánto ahorrarías en tu factura eléctrica.
                        </p>
                        <div class="text-sm text-gray-400">
                            <i class="bi bi-arrow-left mr-1"></i> Utiliza el formulario a la izquierda
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
