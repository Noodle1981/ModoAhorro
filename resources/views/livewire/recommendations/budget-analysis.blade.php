<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        @php
            $data = $this->data;
            $invoices = $data['invoices'];
            $solarData = $data['solarData'];
        @endphp

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-linear-to-br from-amber-400 to-orange-600 w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-sun-fill text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Análisis de Factibilidad Solar</h1>
                    <p class="text-gray-500">{{ $entity->name }} — Estudio basado en tu historial real</p>
                </div>
            </div>
            <div class="mt-4 md:mt-0">
                <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.show', $entity->id) }}">
                    <i class="bi bi-arrow-left mr-2"></i> Volver
                </x-button>
            </div>
        </div>

        @if(session('success'))
            <x-alert type="success" class="mb-6" wire:transition>{{ session('success') }}</x-alert>
        @endif

        <div wire:loading.class="opacity-50 transition-opacity" class="transition-opacity">
            
            {{-- Property Summary --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                    <h6 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Superficie Total Declarada</h6>
                    <div class="flex items-end gap-2">
                        <h3 class="text-3xl font-black text-gray-900">{{ $entity->square_meters }}</h3>
                        <span class="text-gray-400 font-bold mb-1">m²</span>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                    <h6 class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Consumo Mensual Promedio</h6>
                    @if($data['monthlyConsumption'])
                        <div class="flex items-end gap-2">
                            <h3 class="text-3xl font-black text-gray-900">{{ number_format($data['monthlyConsumption'], 0) }}</h3>
                            <span class="text-gray-400 font-bold mb-1">kWh</span>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">PROMEDIO BASADO EN {{ $data['invoiceCount'] }} FACTURA(S)</p>
                    @else
                        <h3 class="text-3xl font-black text-gray-300">-- kWh</h3>
                        <p class="text-[10px] text-gray-400 mt-1 uppercase">SIN DATOS DE FACTURACIÓN SUFICIENTES</p>
                    @endif
                </div>
            </div>

            @if($invoices->isNotEmpty())
                {{-- Invoice History --}}
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                    <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
                        <h6 class="text-xs font-bold text-gray-500 uppercase tracking-widest flex items-center gap-2">
                            <i class="bi bi-receipt text-blue-500"></i> Historial Analizado
                        </h6>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                <tr>
                                    <th class="px-6 py-3">Factura</th>
                                    <th class="px-6 py-3">Periodo</th>
                                    <th class="px-6 py-3 text-right">Consumo</th>
                                    <th class="px-6 py-3 text-right">Importe</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($invoices->take(5) as $invoice)
                                    @php
                                        $consumption = $invoice->total_energy_consumed_kwh ?? $invoice->equipmentUsages->sum('consumption_kwh');
                                    @endphp
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-3 font-bold text-gray-700">#{{ $invoice->invoice_number }}</td>
                                        <td class="px-6 py-3 text-gray-500 text-xs">
                                            {{ \Carbon\Carbon::parse($invoice->start_date)->format('d/m/y') }} - {{ \Carbon\Carbon::parse($invoice->end_date)->format('d/m/y') }}
                                        </td>
                                        <td class="px-6 py-3 text-right font-bold text-gray-900">{{ number_format($consumption, 0) }} <span class="text-[10px] text-gray-400 ml-0.5">kWh</span></td>
                                        <td class="px-6 py-3 text-right font-black text-emerald-600">${{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($solarData)
                {{-- Solar Coverage --}}
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                    <div class="bg-linear-to-r from-emerald-600 to-teal-700 px-8 py-6 text-white">
                        <h6 class="font-bold flex items-center gap-2 text-lg">
                            <i class="bi bi-battery-charging animate-pulse"></i> Factibilidad de Autogeneración
                        </h6>
                        <p class="text-white/80 text-sm mt-1">Escenario: {{ $solarData['scenario'] === 'FULL_COVERAGE' ? 'Cobertura Total' : 'Cobertura Parcial (Limitada por Espacio)' }}</p>
                    </div>
                    
                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
                            <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 text-center">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Potencia Sugerida</p>
                                <h4 class="text-2xl font-black text-emerald-600">{{ $solarData['system_size_kwp'] }} <span class="text-sm font-normal">kWp</span></h4>
                            </div>
                            <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 text-center">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Cantidad Paneles</p>
                                <h4 class="text-2xl font-black text-blue-600">{{ $solarData['panels_count'] }} <span class="text-sm font-normal">unid.</span></h4>
                            </div>
                            <div class="bg-gray-50 rounded-2xl p-5 border border-gray-100 text-center">
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Área Requerida</p>
                                <h4 class="text-2xl font-black text-cyan-600">{{ number_format($solarData['area_used'], 1) }} <span class="text-sm font-normal">m²</span></h4>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Cobertura en Verano (Max)</span>
                                    <span class="text-sm font-black text-amber-600">{{ $solarData['coverage_summer'] }}%</span>
                                </div>
                                <div class="h-3 w-full bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-linear-to-r from-amber-400 to-orange-500 rounded-full" <?php echo 'style="width: ' . $solarData['coverage_summer'] . '%"'; ?>></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-xs font-bold text-gray-500 uppercase tracking-widest">Cobertura en Invierno (Min)</span>
                                    <span class="text-sm font-black text-emerald-600">{{ $solarData['coverage_winter'] }}%</span>
                                </div>
                                <div class="h-3 w-full bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-linear-to-r from-emerald-400 to-teal-600 rounded-full" <?php echo 'style="width: ' . $solarData['coverage_winter'] . '%"'; ?>></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-linear-to-br from-emerald-600 to-teal-800 rounded-3xl p-8 text-white shadow-xl mb-8 relative overflow-hidden">
                    <div class="relative z-10">
                        <h6 class="font-bold flex items-center gap-2 mb-6">
                            <i class="bi bi-cash-coin text-yellow-400 text-xl"></i> Ahorro Económico Estimado
                        </h6>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20">
                                <p class="text-white/70 text-[10px] font-bold uppercase tracking-widest mb-1">Ahorro Mensual Medio</p>
                                <h3 class="text-3xl font-black text-white">${{ number_format($data['estimatedMonthlySavings'], 0, ',', '.') }}</h3>
                            </div>
                            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20">
                                <p class="text-white/70 text-[10px] font-bold uppercase tracking-widest mb-1">Ahorro Anual Total</p>
                                <h3 class="text-3xl font-black text-yellow-400">${{ number_format($data['estimatedAnnualSavings'], 0, ',', '.') }}</h3>
                            </div>
                        </div>
                        <p class="text-white/50 text-[9px] mt-6 flex items-center gap-1 uppercase tracking-tighter">
                            <i class="bi bi-info-circle"></i> Simulación basada en tarifa media de ${{ number_format($data['averageTariff'], 2) }}/kWh y datos climáticos locales.
                        </p>
                    </div>
                </div>
            @endif

            <div class="mt-8 flex flex-col md:flex-row gap-4">
                <x-button variant="secondary" wire:click="$refresh" class="justify-center">
                    <i class="bi bi-arrow-clockwise mr-2"></i> Recalcular con datos actuales
                </x-button>
                <x-button variant="primary" wire:click="$set('showBudgetModal', true)" class="flex-1 justify-center py-4 text-base shadow-lg bg-orange-500 hover:bg-orange-600">
                    <i class="bi bi-envelope-paper-fill mr-2"></i> Solicitar Presupuesto Técnico
                </x-button>
            </div>
        </div>

        {{-- Budget Modal --}}
        @if($showBudgetModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs" wire:transition>
                <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden">
                    @if(!$budgetRequested)
                        <div class="bg-linear-to-r from-orange-500 to-amber-600 px-8 py-6 text-white">
                            <h3 class="text-xl font-bold flex items-center gap-2">
                                <i class="bi bi-envelope-check"></i> Solicitar Presupuesto
                            </h3>
                            <button wire:click="closeModal" class="absolute top-6 right-8 text-white/80 hover:text-white transition-colors">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        
                        <div class="p-8">
                            <div class="bg-gray-50 rounded-2xl p-6 mb-6 border border-gray-100">
                                <p class="text-sm text-gray-600 mb-4">Enviaremos este estudio a un instalador certificado para que valide la factibilidad técnica en {{ $entity->locality->name ?? 'tu zona' }}.</p>
                                <ul class="space-y-3">
                                    <li class="flex items-center gap-3 text-sm font-bold text-gray-800">
                                        <i class="bi bi-house-door text-orange-500"></i> {{ $entity->name }}
                                    </li>
                                    <li class="flex items-center gap-3 text-sm font-bold text-gray-800">
                                        <i class="bi bi-arrows-fullscreen text-orange-500"></i> {{ $entity->square_meters }} m² totales
                                    </li>
                                    <li class="flex items-center gap-3 text-sm font-bold text-gray-800">
                                        <i class="bi bi-lightning-charge text-orange-500"></i> Capacidad Sugerida: {{ $solarData['system_size_kwp'] ?? '0' }} kWp
                                    </li>
                                </ul>
                            </div>
                            
                            <p class="text-xs text-gray-400 text-center px-4 italic mb-8">
                                Al presionar el botón, un especialista recibirá tus datos de contacto registrados para coordinar una visita de relevamiento.
                            </p>

                            <div class="flex flex-col gap-3">
                                <x-button variant="primary" wire:click="requestBudget" class="w-full justify-center py-4 bg-orange-600 hover:bg-orange-700">
                                    <i class="bi bi-send-fill mr-2"></i> Enviar a Especialista
                                </x-button>
                                <button wire:click="closeModal" class="text-sm font-bold text-gray-400 py-2 hover:text-gray-600 transition-colors">
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="p-10 text-center">
                            <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="bi bi-check-lg text-4xl text-emerald-600"></i>
                            </div>
                            <h3 class="text-2xl font-black text-gray-900 mb-4">¡Solicitud Enviada!</h3>
                            <p class="text-gray-500 mb-8">Hemos registrado tu pedido. Un especialista en energía solar te contactará en las próximas 48hs hábiles para coordinar la visita técnica.</p>
                            <x-button variant="secondary" wire:click="closeModal" class="w-full justify-center">
                                Entendido
                            </x-button>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
