<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-linear-to-br from-yellow-400 to-amber-600 w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-lightning-charge-fill text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Optimización de Horarios</h1>
                    <p class="text-gray-500">Analiza y mejora tu consumo según tu tarifa eléctrica</p>
                </div>
            </div>
            <div class="flex gap-3 mt-4 md:mt-0">
                <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.show', $entity->id) }}">
                    <i class="bi bi-arrow-left mr-2"></i> Volver
                </x-button>
            </div>
        </div>

        @php
            $results = $this->results;
            $tariffScheme = $results['tariffScheme'];
            $schedule = $results['schedule'];
            $totalSavings = $results['totalSavings'];
        @endphp

        <div wire:loading.class="opacity-50 transition-opacity" class="transition-opacity">
            
            {{-- Plan Info & Timeline --}}
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="p-8">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8 pb-8 border-b border-gray-100">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center shrink-0">
                                <i class="bi bi-file-text text-2xl text-blue-600"></i>
                            </div>
                            <div>
                                <h5 class="font-bold text-gray-900 text-lg">Plan Actual: {{ $tariffScheme?->name ?? 'Tarifa Plana' }}</h5>
                                <p class="text-sm text-gray-500">Proveedor: {{ $tariffScheme?->provider ?? 'Caucete / Energía San Juan' }}</p>
                            </div>
                        </div>

                        <div class="bg-emerald-50 px-6 py-3 rounded-2xl border border-emerald-100 flex items-center gap-4">
                            <div class="text-right">
                                <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Ahorro Máximo Proyectado</p>
                                <p class="text-2xl font-black text-emerald-700">${{ number_format($totalSavings, 0, ',', '.') }}<span class="text-sm font-normal ml-1">/mes</span></p>
                            </div>
                            <div class="w-10 h-10 bg-emerald-600 rounded-full flex items-center justify-center text-white">
                                <i class="bi bi-piggy-bank text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="flex items-center justify-between mb-4">
                            <h6 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Visualización de Bandas Horarias (24h)</h6>
                            <div class="flex gap-4">
                                <div class="flex items-center gap-1.5 text-[10px] font-bold text-gray-500">
                                    <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Pico
                                </div>
                                <div class="flex items-center gap-1.5 text-[10px] font-bold text-gray-500">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> Resto
                                </div>
                                <div class="flex items-center gap-1.5 text-[10px] font-bold text-gray-500">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Valle
                                </div>
                            </div>
                        </div>
                        
                        {{-- Visual Timeline --}}
                        <div class="relative pt-6">
                            <div class="flex h-12 w-full rounded-2xl overflow-hidden bg-gray-100 shadow-inner border border-gray-200">
                                @if($tariffScheme && $tariffScheme->bands->isNotEmpty())
                                    @php
                                        $timelineBands = $tariffScheme->bands->sortBy('start_time');
                                    @endphp
                                    @foreach($timelineBands as $band)
                                        @php
                                            $start = \Carbon\Carbon::parse($band->start_time);
                                            $end = \Carbon\Carbon::parse($band->end_time);
                                            if ($end < $start) $end->addDay();
                                            $hours = $start->diffInHours($end);
                                            $width = ($hours / 24) * 100;
                                            
                                            $bgClass = 'bg-gray-400';
                                            $textClass = 'text-white';
                                            if (str_contains(strtolower($band->name), 'pico')) {
                                                $bgClass = 'bg-linear-to-b from-red-400 to-red-600';
                                            } elseif (str_contains(strtolower($band->name), 'valle')) {
                                                $bgClass = 'bg-linear-to-b from-emerald-400 to-emerald-600';
                                            } elseif (str_contains(strtolower($band->name), 'resto')) {
                                                $bgClass = 'bg-linear-to-b from-amber-300 to-amber-500';
                                                $textClass = 'text-amber-950';
                                            }
                                        @endphp
                                        <div class="{{ $bgClass }} {{ $textClass }} flex items-center justify-center text-[11px] font-black truncate border-r border-white/20 transition-all hover:scale-[1.02] hover:z-10 cursor-help" 
                                             <?php echo 'style="width: ' . $width . '%"'; ?> 
                                             title="{{ $band->name }}: {{ substr($band->start_time, 0, 5) }} - {{ substr($band->end_time, 0, 5) }} (${{ $band->price_per_kwh }}/kWh)">
                                            {{ substr($band->start_time, 0, 5) }}
                                        </div>
                                    @endforeach
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs font-medium">
                                        No hay información de bandas horarias disponible para este plan.
                                    </div>
                                @endif
                            </div>
                            
                            {{-- Time markers --}}
                            <div class="flex justify-between mt-2 px-1 text-[10px] font-bold text-gray-300">
                                <span>00:00</span>
                                <span>06:00</span>
                                <span>12:00</span>
                                <span>18:00</span>
                                <span>24:00</span>
                            </div>
                        </div>

                        @if($tariffScheme && $tariffScheme->bands->isNotEmpty())
                            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach($tariffScheme->bands as $band)
                                    <div class="bg-gray-50 border border-gray-100 rounded-xl p-3 flex items-center justify-between">
                                        <span class="text-xs font-bold text-gray-600">{{ $band->name }}</span>
                                        <span class="text-sm font-black text-gray-900">${{ number_format($band->price_per_kwh, 2) }}<span class="text-gray-400 text-[10px] font-normal ml-0.5">/kWh</span></span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if(count($schedule) > 0)
                <div class="space-y-6">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2 px-2">
                        <i class="bi bi-stars text-amber-500"></i>
                        Oportunidades de Desplazamiento
                    </h2>
                    
                    <div class="grid grid-cols-1 gap-4">
                        @foreach($schedule as $entry)
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                                <div class="px-6 py-5 flex flex-col md:flex-row md:items-center justify-between gap-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-gray-50 rounded-xl flex items-center justify-center shrink-0 border border-gray-100">
                                            <i class="bi bi-clock-history text-2xl text-gray-400"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-900">{{ $entry['equipment'] }}</h4>
                                            <p class="text-xs text-gray-500">{{ $entry['suggestion'] }}</p>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-6">
                                        <div class="text-center md:text-right">
                                            <p class="text-[10px] font-bold text-red-400 uppercase tracking-widest">Costo Actual</p>
                                            <p class="text-lg font-bold text-red-500">${{ number_format($entry['current_cost'], 0, ',', '.') }}</p>
                                            <p class="text-[9px] text-gray-400">{{ $entry['peak_band_name'] }}</p>
                                        </div>
                                        
                                        <div class="hidden md:block">
                                            <i class="bi bi-arrow-right text-gray-200 text-xl"></i>
                                        </div>

                                        <div class="text-center md:text-right">
                                            <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-widest">Costo Optimizado</p>
                                            <p class="text-lg font-bold text-emerald-600">${{ number_format($entry['optimized_cost'], 0, ',', '.') }}</p>
                                            <p class="text-[9px] text-gray-400">{{ $entry['off_peak_band_name'] }}</p>
                                        </div>

                                        <div class="bg-emerald-50 px-4 py-2 rounded-xl border border-emerald-100 text-center">
                                            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Ahorro</p>
                                            <p class="text-lg font-black text-emerald-700">${{ number_format($entry['potential_savings'], 0, ',', '.') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-amber-50 border-t border-amber-100 px-6 py-2">
                                    <p class="text-[11px] text-amber-800 font-medium">
                                        <i class="bi bi-info-circle-fill mr-1"></i> {{ $entry['suggestion_secondary'] ?? 'Recomendación basada en el perfil de carga típico de este equipo.' }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <x-card class="text-center py-16 border-2 border-dashed border-gray-100">
                    <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-check2-all text-3xl text-blue-500"></i>
                    </div>
                    <h4 class="text-lg font-bold text-gray-900">¡Todo Optimizado!</h4>
                    <p class="text-gray-500 max-w-sm mx-auto text-sm mt-1">
                        No hemos encontrado desplazamientos de carga que generen un ahorro significativo con tu tarifa actual.
                    </p>
                </x-card>
            @endif
        </div>

        {{-- Future Features --}}
        <div class="mt-12 bg-linear-to-br from-gray-800 to-gray-900 rounded-3xl p-8 text-white relative overflow-hidden shadow-xl">
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="max-w-md">
                    <h5 class="text-xl font-black mb-2 flex items-center gap-2">
                        <i class="bi bi-calculator-fill text-yellow-400"></i>
                        Simulador de Tarifas
                    </h5>
                    <p class="text-gray-400 text-sm">
                        Próximamente podrás comparar tu consumo real contra otros planes tarifarios de <strong>Energía San Juan</strong> y <strong>DECSA</strong> para encontrar el que más te conviene.
                    </p>
                </div>
                <div class="shrink-0">
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-xs font-black bg-white/10 text-white border border-white/20 uppercase tracking-widest">
                        En Desarrollo
                    </span>
                </div>
            </div>
            {{-- Decorative circles --}}
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-yellow-500/10 rounded-full blur-3xl"></div>
        </div>
    </div>
</div>
