@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-grid-fill text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detalle de Períodos</h1>
                    <p class="text-gray-500 text-sm">Vista en tarjetas de tu consumo</p>
                </div>
            </div>
            <x-button variant="secondary" href="{{ route('consumption.panel') }}">
                <i class="bi bi-arrow-left mr-2"></i> Volver al Panel
            </x-button>
        </div>

        @if(count($invoicesData) === 0)
            {{-- Empty State --}}
            <x-card class="text-center py-16">
                <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="bi bi-grid-fill text-4xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Sin datos de consumo</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">
                    Carga facturas para visualizar el detalle de tus períodos.
                </p>
            </x-card>
        @else
            {{-- Cards Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($invoicesData as $data)
                    @php
                        $invoice = $data['invoice'];
                        $porcentaje = $data['porcentaje'];
                        $isAdjusted = $data['isAdjusted'];
                        $startDate = \Carbon\Carbon::parse($invoice->start_date);
                        $endDate = \Carbon\Carbon::parse($invoice->end_date);
                        $days = $startDate->diffInDays($endDate);
                    @endphp
                    
                    <div class="bg-white rounded-2xl shadow-sm border {{ $isAdjusted ? 'border-emerald-200' : 'border-gray-200' }} overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-200">
                        {{-- Header --}}
                        <div class="px-5 py-4 {{ $isAdjusted ? 'bg-emerald-50' : 'bg-gray-50' }} flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-calendar-range {{ $isAdjusted ? 'text-emerald-600' : 'text-gray-600' }}"></i>
                                <span class="font-semibold text-gray-900">Período #{{ $invoice->id }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($isAdjusted)
                                    <x-badge variant="success" size="xs">
                                        <i class="bi bi-check-circle mr-1"></i> Ajustado
                                    </x-badge>
                                @else
                                    <x-badge variant="secondary" size="xs">Sin Ajustar</x-badge>
                                @endif
                                
                                {{-- Deviation Badge --}}
                                @if($porcentaje > 130 || $porcentaje < 70)
                                    <x-badge variant="danger" size="xs">{{ number_format(abs(100 - $porcentaje), 1) }}% Desv.</x-badge>
                                @elseif($porcentaje > 110 || $porcentaje < 90)
                                    <x-badge variant="warning" size="xs">{{ number_format(abs(100 - $porcentaje), 1) }}% Desv.</x-badge>
                                @else
                                    <x-badge variant="success" size="xs">Exacto</x-badge>
                                @endif
                            </div>
                        </div>
                        
                        {{-- Body --}}
                        <div class="p-5">
                            {{-- Dates --}}
                            <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
                                <i class="bi bi-calendar3"></i>
                                {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}
                                <span class="px-2 py-0.5 bg-gray-100 rounded text-xs font-medium">{{ $days }} días</span>
                            </div>
                            
                            {{-- Main Consumption --}}
                            <div class="text-center py-4 mb-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl">
                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Consumo Facturado</p>
                                <p class="text-3xl font-bold text-blue-600">
                                    {{ number_format($invoice->total_energy_consumed_kwh ?? 0, 0) }}
                                </p>
                                <p class="text-sm text-gray-500">kWh</p>
                            </div>
                            
                            {{-- Metrics Row --}}
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="p-3 bg-gray-50 rounded-xl text-center">
                                    <p class="text-xs text-gray-500 mb-1">Promedio Diario</p>
                                    <p class="font-bold text-gray-900">
                                        <i class="bi bi-speedometer2 text-blue-500 mr-1"></i>
                                        {{ number_format($data['dailyAvg'], 1) }}
                                    </p>
                                    <p class="text-xs text-gray-500">kWh/día</p>
                                </div>
                                <div class="p-3 bg-gray-50 rounded-xl text-center">
                                    <p class="text-xs text-gray-500 mb-1">Eficiencia</p>
                                    <p class="font-bold text-gray-900">
                                        <i class="bi bi-currency-dollar text-emerald-500 mr-1"></i>
                                        {{ number_format($data['costPerKwh'], 0) }}
                                    </p>
                                    <p class="text-xs text-gray-500">/kWh</p>
                                </div>
                            </div>
                            
                            {{-- Total Amount --}}
                            <div class="flex items-center justify-between p-3 bg-emerald-50 rounded-xl">
                                <span class="text-gray-600">Monto Total</span>
                                <span class="text-xl font-bold text-emerald-600">
                                    ${{ number_format($invoice->total_amount ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        
                        {{-- Actions --}}
                        <div class="px-5 pb-5 space-y-2">
                            <x-button variant="primary" href="{{ route('consumption.panel.show', $invoice->id) }}" class="w-full">
                                <i class="bi bi-eye mr-2"></i> Ver Detalle
                            </x-button>
                            @if(!$isAdjusted)
                                <x-button variant="warning" href="{{ route('usage_adjustments.edit', $invoice->id) }}" class="w-full">
                                    <i class="bi bi-sliders mr-2"></i> Ajustar Uso
                                </x-button>
                            @else
                                <x-button variant="secondary" href="{{ route('usage_adjustments.edit', $invoice->id) }}" class="w-full">
                                    <i class="bi bi-pencil mr-2"></i> Modificar Ajuste
                                </x-button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
