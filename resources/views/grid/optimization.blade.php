@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="bi bi-lightning-charge-fill text-yellow-500"></i> Optimización de Horarios
                </h1>
                <p class="text-sm text-gray-500">Analiza y mejora tu consumo según tu tarifa eléctrica</p>
            </div>
            <div class="mt-4 md:mt-0">
                <x-button variant="secondary" href="{{ route('entities.show', $entity->id) }}">
                    <i class="bi bi-arrow-left mr-2"></i> Volver
                </x-button>
            </div>
        </div>

        {{-- Plan Info & Timeline --}}
        <x-card class="mb-8">
            <div class="flex items-center gap-3 mb-4 border-b border-gray-100 pb-4">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="bi bi-file-text text-blue-600"></i>
                </div>
                <div>
                    <h5 class="font-semibold text-gray-900">Tu Plan Actual: {{ $tariffScheme->name }}</h5>
                    <p class="text-sm text-gray-500">Proveedor: {{ $tariffScheme->provider }}</p>
                </div>
            </div>

            <div class="mb-6">
                <h6 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Esquema Horario (24h)</h6>
                
                {{-- Visual Timeline --}}
                <div class="flex h-8 w-full rounded-xl overflow-hidden bg-gray-200 shadow-inner">
                    @php
                        // Sort bands by start time for timeline
                        $timelineBands = $tariffScheme->bands->sortBy('start_time');
                    @endphp
                    
                    @foreach($timelineBands as $band)
                        @php
                            $start = \Carbon\Carbon::parse($band->start_time);
                            $end = \Carbon\Carbon::parse($band->end_time);
                            if ($end < $start) $end->addDay();
                            $hours = $start->diffInHours($end);
                            $width = ($hours / 24) * 100;
                            
                            // Tailwind colors
                            $bgClass = 'bg-gray-400';
                            $textClass = 'text-white';
                            if (str_contains(strtolower($band->name), 'pico')) {
                                $bgClass = 'bg-red-500';
                            } elseif (str_contains(strtolower($band->name), 'valle')) {
                                $bgClass = 'bg-emerald-500';
                            } elseif (str_contains(strtolower($band->name), 'resto')) {
                                $bgClass = 'bg-amber-400';
                                $textClass = 'text-gray-900';
                            }
                        @endphp
                        <div class="{{ $bgClass }} {{ $textClass }} flex items-center justify-center text-[10px] font-bold truncate transition-all hover:opacity-90" 
                             style="width: {{ $width }}%" 
                             title="{{ $band->name }}: {{ substr($band->start_time, 0, 5) }} - {{ substr($band->end_time, 0, 5) }}">
                            {{ substr($band->start_time, 0, 5) }}
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach($tariffScheme->bands as $band)
                        <x-badge variant="secondary" class="border border-gray-200">
                            {{ $band->name }}: <strong>${{ $band->price_per_kwh }}/kWh</strong>
                        </x-badge>
                    @endforeach
                </div>
            </div>
        </x-card>

        @if(count($opportunities) > 0)
            <x-card class="mb-8 border-l-4 border-l-emerald-500">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <i class="bi bi-graph-down-arrow text-emerald-600"></i>
                    </div>
                    <div>
                        <h5 class="font-semibold text-gray-900">Ahorra moviendo tus horarios</h5>
                        <p class="text-sm text-gray-500">Tienes {{ count($opportunities) }} equipos optimizables para horario Valle.</p>
                    </div>
                </div>

                <x-table>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-3">Equipo</th>
                            <th class="px-6 py-3 text-right">Costo Actual ({{ $opportunities[0]['peak_band_name'] }})</th>
                            <th class="px-6 py-3 text-right">Costo Optimizado ({{ $opportunities[0]['off_peak_band_name'] }})</th>
                            <th class="px-6 py-3 text-center">Ahorro Mensual</th>
                            <th class="px-6 py-3">Sugerencia</th>
                        </tr>
                    </x-slot:head>
                    @foreach($opportunities as $opp)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $opp['equipment'] }}</td>
                            <td class="px-6 py-4 text-right text-red-600 font-medium">${{ number_format($opp['current_cost'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right text-emerald-600 font-medium">${{ number_format($opp['optimized_cost'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">
                                <x-badge variant="success" size="lg">
                                    ${{ number_format($opp['potential_savings'], 0, ',', '.') }} / mes
                                </x-badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <div class="flex flex-col">
                                    <span>{{ $opp['suggestion'] }}</span>
                                    @if(isset($opp['suggestion_secondary']))
                                        <span class="text-xs text-gray-400 mt-1">{{ $opp['suggestion_secondary'] }}</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            </x-card>
        @else
            <x-alert type="info" class="mb-8">
                <div class="flex items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>No se encontraron oportunidades significativas de ahorro por desplazamiento de horarios. ¡Ya estás optimizado o tus equipos no son desplazables!</span>
                </div>
            </x-alert>
        @endif

        {{-- Future Features --}}
        <x-card class="bg-gray-50 border-dashed">
            <div class="text-center py-6">
                <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="bi bi-calculator text-gray-500 text-xl"></i>
                </div>
                <h5 class="font-semibold text-gray-900">Simulador de Tarifas</h5>
                <p class="text-sm text-gray-500">Próximamente: Compara cuánto pagarías con otros planes tarifarios disponibles en tu zona.</p>
            </div>
        </x-card>
    </div>
</div>
@endsection
