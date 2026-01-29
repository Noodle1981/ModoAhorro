@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-blue-500 to-indigo-600 w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-bar-chart-line text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detalle del Período #{{ $invoice->id }}</h1>
                    <p class="text-gray-500 text-sm">
                        {{ \Carbon\Carbon::parse($invoice->start_date)->format('d/m/Y') }} - 
                        {{ \Carbon\Carbon::parse($invoice->end_date)->format('d/m/Y') }}
                    </p>
                </div>
            </div>
            <x-button variant="secondary" href="{{ route('consumption.panel') }}">
                <i class="bi bi-arrow-left mr-2"></i> Volver al Panel
            </x-button>
        </div>

        {{-- Main Consumption Card --}}
        <x-card class="mb-6 border-l-4 border-l-blue-500">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="bi bi-speedometer2 text-blue-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900">Análisis de Consumo</h3>
            </div>
            
            {{-- Billed Consumption --}}
            <div class="text-center py-6 mb-6 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl">
                <p class="text-sm text-gray-500 uppercase tracking-wide mb-1">Consumo Facturado</p>
                <p class="text-4xl font-bold text-blue-600">{{ number_format($invoice->total_energy_consumed_kwh, 0) }}</p>
                <p class="text-gray-500">kWh</p>
            </div>
            
            {{-- Deviation Alert --}}
            @if(isset($validation) && $validation['alert_level'] === 'danger')
                <x-alert type="danger" class="mb-4">
                    <div class="flex items-start gap-3">
                        <i class="bi bi-exclamation-triangle-fill text-xl"></i>
                        <div>
                            <h4 class="font-semibold">Desviación Alta Detectada</h4>
                            <p class="text-sm mt-1">El consumo calculado difiere en <strong>{{ $validation['deviation_percent'] }}%</strong> del facturado.</p>
                            @if(count($suggestions) > 0)
                                <ul class="mt-2 text-sm list-disc list-inside">
                                    @foreach($suggestions as $suggestion)
                                        <li>{{ $suggestion }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            <x-button variant="warning" size="sm" href="{{ route('usage_adjustments.edit', $invoice->id) }}" class="mt-3">
                                <i class="bi bi-sliders mr-1"></i> Revisar Ajustes
                            </x-button>
                        </div>
                    </div>
                </x-alert>
            @elseif(isset($validation) && $validation['alert_level'] === 'warning')
                <x-alert type="warning" class="mb-4">
                    <strong>Desviación moderada:</strong> {{ $validation['deviation_percent'] }}%
                    <a href="{{ route('usage_adjustments.edit', $invoice->id) }}" class="underline ml-2">Revisar Ajustes</a>
                </x-alert>
            @endif
        </x-card>

        {{-- Two Column Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            
            {{-- Category Breakdown --}}
            <x-card>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="bi bi-pie-chart text-purple-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900">Consumo por Categoría</h3>
                </div>
                
                <div class="space-y-3">
                    @foreach($consumoPorCategoria as $categoria => $consumo)
                        @php $pctCat = $totalEnergia > 0 ? ($consumo / $totalEnergia) * 100 : 0; @endphp
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-medium text-gray-900">{{ $categoria }}</span>
                                    <span class="text-sm text-gray-500">{{ number_format($pctCat, 1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-2 rounded-full" style="width: {{ $pctCat }}%"></div>
                                </div>
                            </div>
                            <div class="ml-4 text-right">
                                <span class="font-bold text-gray-900">{{ number_format($consumo, 0) }}</span>
                                <span class="text-xs text-gray-500">kWh</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>

            {{-- Period Details --}}
            <x-card>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <i class="bi bi-calendar-week text-emerald-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900">Detalles del Período</h3>
                </div>
                
                {{-- Billing Info --}}
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Facturación</h4>
                    @php
                        $startDate = \Carbon\Carbon::parse($invoice->start_date);
                        $endDate = \Carbon\Carbon::parse($invoice->end_date);
                        $days = $startDate->diffInDays($endDate);
                    @endphp
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Nº Factura</span>
                            <span class="font-mono font-medium">#{{ $invoice->id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Período</span>
                            <span>{{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }} <x-badge variant="secondary" size="xs">{{ $days }} días</x-badge></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Monto Total</span>
                            <span class="font-semibold text-emerald-600">${{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Potencia Instalada</span>
                            <span>{{ number_format($totalPotencia, 0) }} W</span>
                        </div>
                    </div>
                </div>

                {{-- Climate Stats --}}
                @if(isset($climateStats))
                    <div class="pt-4 border-t border-gray-200">
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-3">Clima (Histórico)</h4>
                        <div class="grid grid-cols-3 gap-3 mb-3">
                            <div class="p-3 bg-gray-50 rounded-xl text-center">
                                <p class="text-xs text-gray-500">Promedio</p>
                                <p class="font-bold text-gray-900">{{ $climateStats['avg_temp_avg'] ?? '-' }}°C</p>
                            </div>
                            <div class="p-3 bg-red-50 rounded-xl text-center">
                                <p class="text-xs text-gray-500">Máxima</p>
                                <p class="font-bold text-red-600">{{ $climateStats['avg_temp_max'] ?? '-' }}°C</p>
                            </div>
                            <div class="p-3 bg-blue-50 rounded-xl text-center">
                                <p class="text-xs text-gray-500">Mínima</p>
                                <p class="font-bold text-blue-600">{{ $climateStats['avg_temp_min'] ?? '-' }}°C</p>
                            </div>
                        </div>
                        <div class="flex gap-4 text-sm">
                            <span class="text-red-600">
                                <i class="bi bi-thermometer-sun"></i> Días Calor (>28°C): <strong>{{ $climateStats['hot_days_count'] ?? 0 }}</strong>
                            </span>
                            <span class="text-blue-600">
                                <i class="bi bi-thermometer-snow"></i> Días Frío (<15°C): <strong>{{ $climateStats['cold_days_count'] ?? 0 }}</strong>
                            </span>
                        </div>
                    </div>
                @endif
            </x-card>
        </div>

        {{-- Equipment Table --}}
        <x-card :padding="false" class="mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                        <i class="bi bi-lightbulb text-amber-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900">Detalle por Equipo</h3>
                </div>
            </div>
            
            <x-table hover>
                <x-slot:head>
                    <tr>
                        <th class="px-6 py-4">Equipo</th>
                        <th class="px-6 py-4">Categoría</th>
                        <th class="px-6 py-4">Habitación</th>
                        <th class="px-6 py-4 text-right">Potencia</th>
                        <th class="px-6 py-4 text-right">Consumo</th>
                        <th class="px-6 py-4">Equilibrio (Tanque)</th>
                    </tr>
                </x-slot:head>
                
                @foreach($invoice->equipmentUsages as $usage)
                    @php
                        $calibratedUsage = $calibratedUsages->firstWhere('equipment_id', $usage->equipment_id);
                        $status = $calibratedUsage->calibration_status ?? null;
                        $note = $calibratedUsage->calibration_note ?? '';
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $usage->equipment->name }}</td>
                        <td class="px-6 py-4">
                            <x-badge variant="secondary">{{ $usage->equipment->category->name ?? 'General' }}</x-badge>
                        </td>
                        <td class="px-6 py-4 text-gray-600">{{ $usage->equipment->room->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-right font-mono">{{ $usage->equipment->nominal_power_w ?? '-' }} W</td>
                        <td class="px-6 py-4 text-right font-bold text-blue-600">{{ number_format($consumos[$usage->equipment_id] ?? 0, 1) }} kWh</td>
                        <td class="px-6 py-4">
                            @php
                                $tanque = $calibratedUsage->tanque ?? null;
                            @endphp
                            @if($tanque === 1)
                                <x-badge variant="success" title="Inmutable 24/7"><i class="bi bi-shield-lock-fill mr-1"></i> Tanque 1</x-badge>
                            @elseif($tanque === 2)
                                <x-badge variant="info" title="Ajustado por Clima"><i class="bi bi-thermometer-half mr-1"></i> Tanque 2</x-badge>
                            @elseif($tanque === 3)
                                <x-badge variant="warning" title="Ajustado por Elasticidad"><i class="bi bi-sliders mr-1"></i> Tanque 3</x-badge>
                            @else
                                <x-badge variant="secondary">-</x-badge>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>

        {{-- Audit Logs --}}
        @php $auditLogs = $calibratedUsages->first()->audit_logs ?? []; @endphp
        @if(count($auditLogs) > 0)
            <x-card class="mb-6 border-l-4 border-l-amber-500 bg-amber-50/30">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                        <i class="bi bi-journal-text text-amber-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900">Bitácora del Motor de Calibración (v3.0)</h3>
                </div>
                <div class="space-y-2">
                    @foreach($auditLogs as $log)
                        <div class="flex gap-3 text-sm text-gray-700">
                            <span class="text-amber-500">•</span>
                            <p>{{ $log }}</p>
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endif

        {{-- Adjustment Guide --}}
        <x-card>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="bi bi-info-circle text-gray-600"></i>
                </div>
                <h3 class="font-semibold text-gray-900">Jerarquía de Tanques (Metodología v3.0)</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-emerald-50 rounded-xl">
                    <h4 class="font-semibold text-emerald-700 flex items-center gap-2 mb-2">
                        <i class="bi bi-shield-lock-fill"></i> Tanque 1: Base Automática
                    </h4>
                    <p class="text-xs text-gray-600 mb-2">Equipos 24/7 de uso constante. Son inmutables en el tiempo.</p>
                    <ul class="text-xs text-gray-500 space-y-1">
                        <li><i class="bi bi-check-circle-fill text-emerald-500 mr-1"></i> Heladeras, Routers, Alarmas</li>
                    </ul>
                </div>
                <div class="p-4 bg-blue-50 rounded-xl">
                    <h4 class="font-semibold text-blue-700 flex items-center gap-2 mb-2">
                        <i class="bi bi-thermometer-half"></i> Tanque 2: Climatización
                    </h4>
                    <p class="text-xs text-gray-600 mb-2">Sensible al clima exterior (HDD/CDD) y al Perfil Térmico de la vivienda.</p>
                    <ul class="text-xs text-gray-500 space-y-1">
                        <li><i class="bi bi-snow text-blue-500 mr-1"></i> Aires, Estufas, Caloventores</li>
                    </ul>
                </div>
                <div class="p-4 bg-amber-50 rounded-xl">
                    <h4 class="font-semibold text-amber-700 flex items-center gap-2 mb-2">
                        <i class="bi bi-sliders"></i> Tanque 3: Rutina y Ocio
                    </h4>
                    <p class="text-xs text-gray-600 mb-2">Equipos con alta variabilidad. Absorben el ajuste final por Elasticidad.</p>
                    <ul class="text-xs text-gray-500 space-y-1">
                        <li><i class="bi bi-pc-display text-amber-500 mr-1"></i> TV, Consolas, Microondas, Luces</li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-sm text-gray-500">
                    <i class="bi bi-info-circle mr-1"></i>
                    <strong>Nota:</strong> Los termotanques incluyen ajuste climático automático (x1.25 en invierno, x0.85 en verano).
                </p>
            </div>
        </x-card>
    </div>
</div>
@endsection
