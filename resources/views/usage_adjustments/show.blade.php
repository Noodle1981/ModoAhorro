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
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-sm">
                                    <i class="bi bi-lock-fill mr-1.5 opacity-80"></i> Cerrado / Validado
                                </span>
                             @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200 shadow-sm">
                                    <i class="bi bi-unlock-fill mr-1.5 opacity-80"></i> Abierto / En Borrador
                                </span>
                             @endif

                             @if(count($apiSummary) > 0)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-medium bg-indigo-50 text-indigo-700 border border-indigo-200 shadow-sm" title="Calibración Automática Activa">
                                    <i class="bi bi-cpu mr-1.5 opacity-80"></i> Motor v3 Activo
                                </span>
                             @endif
                        </div>
                    </div>
                    @php
                        $facturadoKwh = $invoice->total_energy_consumed_kwh ?? $invoice->consumption_kwh ?? 0;
                        $teoricoKwh = $apiSummary['theoretical_total'] ?? $totalCalculatedConsumption;
                        $recomendadoKwh = $apiSummary['calibrated_total'] ?? 0;
                        
                        $pctDiff = $facturadoKwh > 0 ? (($recomendadoKwh - $facturadoKwh) / $facturadoKwh) * 100 : 0;
                        $pctSign = $pctDiff > 0 ? '+' : '';
                    @endphp

                    <div class="flex flex-wrap items-end justify-end gap-4 mt-4 md:mt-0">
                        <!-- Facturado -->
                        <div class="flex flex-col items-center min-w-[120px]">
                            <span class="text-[10px] text-gray-500 uppercase tracking-widest font-semibold mb-1.5">Facturado</span>
                            <div class="bg-gray-800 text-white px-4 py-2.5 rounded-xl shadow-sm w-full text-center">
                                <span class="text-2xl font-bold tracking-tight">{{ number_format($facturadoKwh, 0) }}</span>
                                <span class="text-xs font-medium opacity-80 ml-0.5">kWh</span>
                            </div>
                        </div>
                        
                        <!-- Separator -->
                        <div class="text-gray-300 pb-3.5 px-1 hidden sm:block">
                            <i class="bi bi-chevron-right text-lg"></i>
                        </div>

                        <!-- Calculado -->
                        <div class="flex flex-col items-center min-w-[120px]">
                            <span class="text-[10px] text-gray-500 uppercase tracking-widest font-semibold mb-1.5">Calculado (Suma)</span>
                            <div class="bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl shadow-sm w-full text-center">
                                <span class="text-2xl font-bold tracking-tight">{{ number_format($teoricoKwh, 0) }}</span>
                                <span class="text-xs font-medium opacity-80 ml-0.5">kWh</span>
                            </div>
                        </div>

                        <!-- Separator -->
                        <div class="text-indigo-300 pb-3.5 px-1 hidden sm:block">
                            <i class="bi bi-arrow-right-short text-2xl"></i>
                        </div>

                        <!-- Recomendado -->
                        <div class="flex flex-col items-center min-w-[150px]">
                            <span class="text-[10px] text-indigo-600 uppercase tracking-widest font-bold mb-1.5">Cálculo Recomendado</span>
                            <div class="bg-indigo-50 border border-indigo-200 text-indigo-700 px-4 py-2.5 rounded-xl shadow-sm ring-2 ring-indigo-500/20 w-full text-center flex items-center justify-center gap-2">
                                <div class="flex items-baseline">
                                    <span class="text-2xl font-bold tracking-tight">{{ number_format($recomendadoKwh, 0) }}</span>
                                    <span class="text-xs font-bold opacity-90 ml-0.5">kWh</span>
                                </div>
                                
                                @if($facturadoKwh > 0 && $recomendadoKwh != $facturadoKwh)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-bold bg-indigo-200 text-indigo-800 shadow-sm border border-indigo-300" title="Respecto al Facturado">
                                        {{ $pctSign }}{{ number_format($pctDiff, 1) }}%
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
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
                            @php
                                $isTank3 = isset($stat['key']) && $stat['key'] === 'ballenas';
                                $reconciledVal = $stat['reconciled_kwh'] ?? $stat['kwh'];
                                $globalExcess = $facturadoKwh > 0 ? max(0, $recomendadoKwh - $facturadoKwh) : 0;
                                $t3Excess = $isTank3 ? min($reconciledVal, $globalExcess) : 0;
                            @endphp

                            @if($isTank3 && ($stat['kwh'] > $reconciledVal || $t3Excess > 0))
                                <div class="flex items-center justify-end gap-1.5 mb-0.5 flex-wrap">
                                    @if($stat['kwh'] > $reconciledVal)
                                        <span class="text-[11px] text-gray-400 line-through" title="Teórico Original">{{ number_format($stat['kwh'], 1) }}</span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 tracking-tight" title="Recorte del Motor">
                                            {{ number_format((($reconciledVal - $stat['kwh']) / $stat['kwh']) * 100, 1) }}%
                                        </span>
                                    @endif
                                    @if($t3Excess > 0)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700 tracking-tight border border-gray-200" title="Exceso tolerado sobre factura real">
                                            +{{ number_format($t3Excess, 1) }} kWh Tolerado
                                        </span>
                                    @endif
                                </div>
                                <div class="font-bold text-gray-900 text-lg leading-none">{{ number_format($reconciledVal, 1) }} <span class="text-[11px] text-gray-500 font-normal">kWh</span></div>
                            @else
                                <span class="block font-bold text-gray-900 text-lg leading-none">{{ number_format($reconciledVal, 1) }}</span>
                                <span class="text-[11px] text-gray-500">kWh</span>
                            @endif
                        </div>
                    </div>
                    {{-- Progress bar --}}
                    @php 
                        $totalBasis = $totalCalculatedConsumption > 0 ? $totalCalculatedConsumption : 1;
                        $t3Good = $reconciledVal - $t3Excess;

                        $percentGood = ($t3Good / $totalBasis) * 100;
                        $percentExcess = ($t3Excess / $totalBasis) * 100;
                        
                        $percentTotal = ($stat['kwh'] / $totalBasis) * 100;
                        $percentCut = max(0, $percentTotal - ($percentGood + $percentExcess));
                    @endphp
                    <div class="h-2 w-full bg-gray-100 flex rounded-b-lg overflow-hidden">
                        <div class="h-full bg-{{ $stat['color'] }}-500 transition-all duration-500" style="width: {{ $percentGood }}%" title="Consumo Meta"></div>
                        
                        @if($percentExcess > 0)
                            <div class="h-full bg-gray-400 relative transition-all duration-500" style="width: {{ $percentExcess }}%" title="Exceso Tolerado ({{ number_format($t3Excess, 1) }} kWh sobre factura)"></div>
                        @endif

                        @if(isset($stat['key']) && $stat['key'] === 'ballenas' && $percentCut > 0)
                            <div class="h-full bg-rose-400 relative transition-all duration-500" style="width: {{ $percentCut }}%" title="Ahorro por Límite de Motor">
                                <div class="absolute inset-0 opacity-40 mix-blend-overlay" style="background-image: repeating-linear-gradient(-45deg, transparent, transparent 4px, currentColor 4px, currentColor 8px); color: white;"></div>
                            </div>
                        @elseif($percentCut > 0)
                            <div class="h-full bg-{{ $stat['color'] }}-300 opacity-50 transition-all duration-500" style="width: {{ $percentCut }}%"></div>
                        @endif
                    </div>
                </x-card>
                @endforeach
            </div>
        </div>

        {{-- Room Summary Cards --}}
        <h3 class="text-lg font-bold text-gray-900 mb-4">Resumen por Ambientes</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
            @foreach($groupedUsages as $roomName => $usages)
                @php
                    $roomKwh = 0;
                    $roomCount = $usages->count();
                    foreach($usages as $u) {
                        $roomKwh += $consumptionDetails[$u->equipment_id] ?? 0;
                    }
                    $percent = $totalCalculatedConsumption > 0 ? ($roomKwh / $totalCalculatedConsumption) * 100 : 0;
                @endphp
                <x-card :padding="false" class="relative overflow-hidden">
                    <div class="px-4 py-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                    <i class="bi bi-door-open text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 truncate" title="{{ $roomName }}">{{ $roomName }}</p>
                                    <p class="text-xs text-gray-500">{{ $roomCount }} equipos</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-baseline justify-between">
                            <span class="text-lg font-bold text-gray-900">{{ number_format($roomKwh, 1) }} <span class="text-xs font-normal text-gray-500">kWh</span></span>
                            <span class="text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">{{ round($percent) }}%</span>
                        </div>
                    </div>
                    <div class="h-1 w-full bg-gray-100">
                        <div class="h-1 bg-indigo-500" style="width: {{ $percent }}%"></div>
                    </div>
                </x-card>
            @endforeach
        </div>

        {{-- Rooms & Usage Tables --}}
        <h3 class="text-lg font-bold text-gray-900 mb-4">Detalle de Equipos</h3>
        
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
