@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-cyan-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Result Card --}}
        @php
            $colorMap = [
                'success' => 'emerald',
                'warning' => 'amber',
                'danger' => 'red',
                'info' => 'blue',
            ];
            $resultColor = $colorMap[$scoreResult['color']] ?? 'gray';
        @endphp
        
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="h-2 bg-{{ $resultColor }}-500"></div>
            
            <div class="p-8">
                <div class="flex flex-col md:flex-row md:items-center gap-8">
                    {{-- Score --}}
                    <div class="text-center md:text-left md:border-r md:border-gray-200 md:pr-8">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Calificación Térmica</p>
                        <div class="inline-flex items-center justify-center w-24 h-24 bg-{{ $resultColor }}-100 rounded-2xl mb-2">
                            <span class="text-5xl font-bold text-{{ $resultColor }}-600">{{ $scoreResult['label'] }}</span>
                        </div>
                        <p class="text-sm text-gray-500">Score: <strong>{{ $scoreResult['score'] }}/100</strong></p>
                    </div>
                    
                    {{-- Message --}}
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">
                            @if($scoreResult['score'] >= 75)
                                ¡Excelente Aislación! 🏠✨
                            @elseif($scoreResult['score'] >= 50)
                                Aislación Aceptable 🏠⚠️
                            @else
                                Tu casa pierde energía 🏠💨
                            @endif
                        </h2>
                        <p class="text-gray-600 mb-4">
                            @if($scoreResult['score'] >= 75)
                                Tu vivienda retiene bien la temperatura. Tus equipos de climatización trabajan eficientemente.
                            @elseif($scoreResult['score'] >= 50)
                                Hay margen de mejora. Podrías reducir tu consumo atacando puntos específicos.
                            @else
                                Detectamos fugas térmicas importantes. Tu aire acondicionado trabaja el doble para compensar la falta de aislación.
                            @endif
                        </p>
                        <div class="flex gap-3">
                            <x-button variant="secondary" href="{{ route('thermal.wizard', $entity) }}">
                                <i class="bi bi-arrow-clockwise mr-2"></i> Recalcular
                            </x-button>
                            <x-button variant="primary" href="{{ route($config['route_prefix'] . '.show', $entity->id) }}">
                                Volver al Dashboard
                            </x-button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recommendations --}}
        @if(count($recommendations) > 0)
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-1 h-6 bg-blue-500 rounded"></span>
                    Top 3 Mejoras Recomendadas
                </h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($recommendations as $rec)
                    @php $recColor = $colorMap[$rec['color']] ?? 'gray'; @endphp
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border-b-4 border-b-{{ $recColor }}-500 hover:shadow-xl transition-shadow">
                        <div class="p-6">
                            {{-- Header --}}
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 bg-{{ $recColor }}-100 rounded-xl flex items-center justify-center">
                                    <i class="{{ $rec['icon'] }} text-{{ $recColor }}-600 text-xl"></i>
                                </div>
                                <h4 class="font-bold text-gray-900">{{ $rec['title'] }}</h4>
                            </div>
                            
                            {{-- Problem --}}
                            <div class="mb-4">
                                <x-badge variant="danger" size="xs">Problema</x-badge>
                                <p class="text-sm text-gray-600 mt-2">{{ $rec['problem'] }}</p>
                            </div>
                            
                            {{-- Solution --}}
                            <div class="mb-4">
                                <x-badge variant="success" size="xs">Solución</x-badge>
                                <p class="font-semibold text-{{ $recColor }}-600 mt-2">{{ $rec['solution'] }}</p>
                            </div>
                            
                            {{-- Stats --}}
                            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100 text-center">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Costo</p>
                                    <p class="font-bold text-gray-900">{{ $rec['cost_level'] }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Impacto</p>
                                    <p class="font-bold text-{{ $recColor }}-600">{{ $rec['impact'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- No Recommendations --}}
            <x-card class="text-center py-12">
                <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="bi bi-check-circle text-4xl text-emerald-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">¡Excelente!</h3>
                <p class="text-gray-500">No tenemos recomendaciones urgentes. Tu casa está muy bien aislada.</p>
            </x-Card>
        @endif
    </div>
</div>
@endsection
