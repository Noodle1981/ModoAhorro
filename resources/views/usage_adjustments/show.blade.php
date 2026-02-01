@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-blue-500 to-indigo-600 w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-eye text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detalle del Ajuste</h1>
                    <p class="text-gray-500 text-sm">Factura #{{ $invoice->id }} &bull; {{ $entity->name ?? 'Entidad' }}</p>
                </div>
            </div>
            <div class="flex gap-3">
                <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.usage_adjustments', $entity->id) }}">
                    <i class="bi bi-arrow-left mr-2"></i> Volver
                </x-button>
                @if(!$invoice->usage_locked)
                    <x-button variant="primary" href="{{ route($config['route_prefix'] . '.usage_adjustments.edit', ['entity' => $entity->id, 'invoice' => $invoice->id]) }}">
                        <i class="bi bi-pencil mr-2"></i> Editar Ajuste
                    </x-button>
                @else
                    <form action="{{ route($config['route_prefix'] . '.usage_adjustments.unlock', ['entity' => $entity->id, 'invoice' => $invoice->id]) }}" method="POST">
                        @csrf
                        <x-button type="submit" variant="danger">
                            <i class="bi bi-unlock mr-2"></i> Reabrir Periodo
                        </x-button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Status & Summary --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            {{-- Main Info --}}
            <x-card class="col-span-2 border-l-4 border-l-blue-500">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Resumen General</h3>
                        <p class="text-gray-600 mb-1">
                            <strong>Período:</strong> 
                            {{ \Carbon\Carbon::parse($invoice->start_date)->format('d/m/Y') }} - 
                            {{ \Carbon\Carbon::parse($invoice->end_date)->format('d/m/Y') }}
                        </p>
                        <div class="flex gap-2 mt-2">
                             @if($invoice->usage_locked)
                                <x-badge variant="success"><i class="bi bi-lock-fill mr-1"></i> Cerrado / Validado</x-badge>
                             @else
                                <x-badge variant="warning"><i class="bi bi-unlock-fill mr-1"></i> Abierto / En Borrador</x-badge>
                             @endif

                             @if(count($apiSummary) > 0)
                                <x-badge variant="info" title="Calibración Automática Activa">
                                    <i class="bi bi-cpu mr-1"></i> Motor v3 Activo
                                </x-badge>
                             @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="mb-4">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Consumo Facturado</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($invoice->total_energy_consumed_kwh, 0) }} kWh</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Calculado (Suma)</p>
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($totalCalculatedConsumption, 0) }} kWh</p>
                        </div>
                    </div>
                </div>
                
                @if(isset($apiSummary['message']))
                    <div class="mt-4 p-3 bg-blue-50 text-blue-800 rounded-md text-sm border border-blue-100 flex items-start gap-2">
                        <i class="bi bi-info-circle-fill mt-0.5"></i>
                        <div>
                            <strong>Estado Motor:</strong> {{ $apiSummary['message'] }}
                            @if(isset($apiSummary['gap_kwh']))
                                <div class="text-xs mt-1 opacity-80">Brecha: {{ number_format($apiSummary['gap_kwh'], 1) }} kWh ({{ number_format($apiSummary['error_percent'] ?? 0, 1) }}%)</div>
                            @endif
                        </div>
                    </div>
                @endif
            </x-card>

            {{-- Categories --}}
            <div class="space-y-4">
                @foreach($tierStats as $key => $stat)
                <x-card :padding="false" class="relative overflow-hidden">
                    <div class="px-4 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-{{ $stat['color'] }}-100 text-{{ $stat['color'] }}-600 flex items-center justify-center">
                                <i class="bi {{ $stat['icon'] }} text-lg"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $stat['label'] }}</p>
                                <p class="text-xs text-gray-500">{{ $stat['count'] }} Equipos</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="block font-bold text-gray-900">{{ number_format($stat['kwh'], 0) }}</span>
                            <span class="text-xs text-gray-500">kWh</span>
                        </div>
                    </div>
                    {{-- Progress bar --}}
                    @php $percent = $totalCalculatedConsumption > 0 ? ($stat['kwh'] / $totalCalculatedConsumption) * 100 : 0; @endphp
                    <div class="h-1 w-full bg-gray-100">
                        <div class="h-1 bg-{{ $stat['color'] }}-500" style="width: {{ $percent }}%"></div>
                    </div>
                </x-card>
                @endforeach
            </div>
        </div>

        {{-- Rooms & Usage --}}
        <h3 class="text-lg font-bold text-gray-900 mb-4">Detalle por Habitación</h3>
        
        @forelse($groupedUsages as $roomName => $usages)
            <x-card :padding="false" class="mb-6 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="bi bi-door-open text-blue-500"></i>
                        {{ $roomName }}
                    </h3>
                    <span class="text-xs font-medium text-gray-500 bg-white px-2 py-1 rounded border border-gray-200">
                        {{ $usages->count() }} equipos
                    </span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipo / Tipo</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Potencia</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patrón de Uso</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Consumo Calc.</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">API (Motor)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($usages as $usage)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-gray-900">{{ $usage->equipment->name }}</span>
                                            <div class="mt-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $usage->tier_color ?? 'gray' }}-100 text-{{ $usage->tier_color ?? 'gray' }}-800">
                                                    {{ $usage->tier_label ?? 'General' }}
                                                </span>
                                                @if($usage->equipment->is_standby)
                                                    <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800" title="Consumo Vampiro Activo">
                                                        <i class="bi bi-plug-fill mr-1"></i> Standby
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-mono">
                                        {{ $usage->equipment->nominal_power_w ?? '-' }} W
                                    </td>
                                    <td class="px-6 py-4">
                                @if($usage->avg_daily_use_hours > 0 || in_array($usage->usage_frequency, ['diario', 'diariamente', 'semanal']) || empty($usage->usage_frequency))
                                    @php
                                        $totalDays = \Carbon\Carbon::parse($invoice->start_date)->diffInDays(\Carbon\Carbon::parse($invoice->end_date));
                                        $totalDays = max(1, $totalDays);
                                        
                                        $usedDays = $usage->use_days_in_period;
                                        if (empty($usedDays)) {
                                            $factor = match($usage->usage_frequency) {
                                                'casi_frecuentemente' => 0.85,
                                                'frecuentemente' => 0.60,
                                                'ocasionalmente' => 0.30,
                                                'raramente' => 0.10,
                                                'nunca' => 0.0,
                                                default => 0.60
                                            };
                                            $usedDays = floor($totalDays * $factor);
                                        }
                                        
                                        $percent = ($usedDays / $totalDays) * 100;
                                        $freqLabel = ucfirst($usage->usage_frequency ?? 'Diario');

                                        // Variables para lógica climática
                                        $catName = $usage->equipment->category->name ?? '';
                                        $isClimate = ($catName === 'Climatización' || $catName === 'Calefacción');
                                        $climateText = "";
                                        $detectedDays = 0;
                                        
                                        if ($isClimate) {
                                            $isCooling = ($catName === 'Climatización'); // O mejorar detección si es Estufa vs Aire
                                            // Ajuste fino por nombre si la categoría es genérica
                                            if (str_contains(strtolower($usage->equipment->name), 'estufa') || str_contains(strtolower($usage->equipment->name), 'calefac')) {
                                                $isCooling = false;
                                            }

                                            $typeLabel = $isCooling ? 'calor' : 'frío'; // Aire (frío) se usa en días de Calor
                                            
                                            $apiDays = null;
                                            if (isset($climateData) && 
                                               (isset($climateData['cooling_days']) || isset($climateData['heating_days']))) {
                                                $apiDays = $isCooling ? ($climateData['cooling_days'] ?? 0) : ($climateData['heating_days'] ?? 0);
                                            }
                                            $detectedDays = $apiDays ?? 0;
                                            $climateText = " - {$detectedDays} dias de {$typeLabel} detectados";
                                        }
                                    @endphp
                                    <div class="text-sm">
                                        {{-- Header: Frecuencia (Días Uso) [ - Clima detectado] --}}
                                        <div class="font-bold text-gray-700 mb-1">
                                            @if($isClimate)
                                                {{ $freqLabel }} ({{ $usedDays }} días){{ $climateText }}
                                            @else
                                                @if($usedDays == $totalDays)
                                                    {{ $freqLabel }} ({{ $usedDays }}/{{ $totalDays }}) =
                                                @else
                                                    {{ $freqLabel }} ({{ $usedDays }} días) =
                                                @endif
                                            @endif
                                        </div>

                                        {{-- Calculation Line --}}
                                        @if($isClimate)
                                            <span class="font-medium text-blue-600">
                                                {{ $usage->avg_daily_use_hours }} h/día • {{ $detectedDays }} / {{ $totalDays }} total
                                            </span>
                                        @else
                                            <span class="font-medium">
                                                {{ $usage->avg_daily_use_hours }} h/día • {{ $usedDays }} / {{ $totalDays }} días ({{ round($percent) }}%)
                                            </span>
                                        @endif
                                    </div>
                                            <div class="text-xs text-gray-400 mt-0.5">
                                                {{ $usage->use_days_of_week ?? 'Todos los días' }}
                                            </div>
                                        @else
                                            <div>
                                                <span class="font-medium text-gray-900">{{ $usage->usage_count }} veces</span>
                                                <span class="text-gray-400 mx-1">&times;</span>
                                                <span>{{ $usage->avg_use_duration }} h</span>
                                            </div>
                                            <div class="text-xs text-gray-400 mt-0.5">
                                                {{ ucfirst($usage->usage_frequency) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-base font-bold text-gray-900">
                                            {{ number_format($consumptionDetails[$usage->equipment_id] ?? 0, 3) }}
                                        </span>
                                        <span class="text-xs text-gray-500">kWh</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        @if(isset($usage->kwh_reconciled))
                                            @php 
                                                $calc = $consumptionDetails[$usage->equipment_id] ?? 0;
                                                $rec = $usage->kwh_reconciled;
                                                $diff = $rec - $calc;
                                                $diffClass = abs($diff) < 0.001 ? 'text-gray-400' : ($diff > 0 ? 'text-orange-500' : 'text-green-500');
                                                $icon = $diff > 0 ? 'bi-caret-up-fill' : 'bi-caret-down-fill';
                                            @endphp
                                            <div class="flex flex-col items-end">
                                                <span class="font-mono text-sm font-medium text-indigo-700">
                                                    {{ number_format($rec, 3) }}
                                                </span>
                                                @if(abs($diff) >= 0.001)
                                                    <span class="text-xs {{ $diffClass }}">
                                                        {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 3) }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        @empty
            <x-card class="text-center py-12">
                <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-exclamation-triangle text-3xl text-amber-500"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Sin Ajustes</h3>
                <p class="text-gray-500">No hay ajustes de uso registrados para esta factura.</p>
                <div class="mt-4">
                    <x-button variant="primary" href="{{ route($config['route_prefix'] . '.usage_adjustments.edit', ['entity' => $entity->id, 'invoice' => $invoice->id]) }}">
                        Comenzar Ajuste
                    </x-button>
                </div>
            </x-Card>
        @endforelse
    </div>
</div>
@endsection
