@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ showModal: false }">
    <div class="bg-white shadow-xl sm:rounded-lg overflow-hidden">
        <div class="bg-amber-100 border-b border-amber-200 px-6 py-4">
            <h4 class="text-xl font-bold text-amber-900 flex items-center gap-2">
                <i class="bi bi-sun-fill text-amber-600"></i> Calculadora de Paneles Solares
            </h4>
            <p class="text-sm text-amber-800">Estima el potencial de energía solar para tu propiedad</p>
        </div>
        
        <div class="p-6">
            <h5 class="text-lg font-semibold text-gray-800">Propiedad: {{ $entity->name }}</h5>
            <p class="text-gray-500 mb-6 flex items-center gap-1">
                <i class="bi bi-geo-alt"></i> {{ $entity->address_street }}, {{ $entity->locality->name ?? 'N/A' }}
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <h6 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Superficie Total</h6>
                    <h3 class="text-2xl font-bold text-gray-900">{{ $entity->square_meters }} m²</h3>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <h6 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Consumo Mensual Promedio</h6>
                    @if($monthlyConsumption)
                        <h3 class="text-2xl font-bold text-gray-900">{{ number_format($monthlyConsumption, 0) }} kWh</h3>
                        <small class="text-gray-500">Promedio basado en {{ $invoiceCount }} factura(s)</small>
                    @else
                        <h3 class="text-2xl font-bold text-gray-400">-- kWh</h3>
                        <small class="text-gray-500">Sin datos de facturación</small>
                    @endif
                </div>
            </div>

            @if($invoices->isNotEmpty())
            <div class="bg-white border rounded-lg shadow-sm mb-8 overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h6 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                        <i class="bi bi-receipt"></i> Historial de Facturas
                    </h6>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-gray-500">Nº Factura</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-500">Periodo</th>
                                <th class="px-4 py-2 text-center font-medium text-gray-500">Días</th>
                                <th class="px-4 py-2 text-right font-medium text-gray-500">Consumo</th>
                                <th class="px-4 py-2 text-right font-medium text-gray-500">Importe</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($invoices as $invoice)
                                @php
                                    $consumption = $invoice->total_energy_consumed_kwh ?? $invoice->equipmentUsages->sum('consumption_kwh');
                                    $startDate = \Carbon\Carbon::parse($invoice->start_date);
                                    $endDate = \Carbon\Carbon::parse($invoice->end_date);
                                    $days = $startDate->diffInDays($endDate);
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 font-medium text-gray-900">#{{ $invoice->invoice_number }}</td>
                                    <td class="px-4 py-2 text-gray-500">{{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</td>
                                    <td class="px-4 py-2 text-center text-gray-500">{{ $days }}</td>
                                    <td class="px-4 py-2 text-right text-gray-900">{{ number_format($consumption, 0) }} kWh</td>
                                    <td class="px-4 py-2 text-right text-gray-900">${{ number_format($invoice->total_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <hr class="my-6 border-gray-200">

            @if(isset($climateProfile) && !empty($climateProfile))
            <div class="border border-amber-200 rounded-lg bg-amber-50 mb-8 overflow-hidden">
                <div class="bg-amber-100/50 px-4 py-3 border-b border-amber-200">
                    <h6 class="text-sm font-semibold text-amber-900 flex items-center gap-2">
                        <i class="bi bi-sun"></i> Perfil Solar de tu Zona
                    </h6>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div class="border-r border-amber-200">
                            <h4 class="text-xl font-bold text-amber-600">{{ $climateProfile['avg_radiation'] }}</h4>
                            <small class="text-amber-800">Radiación (MJ/m²)</small>
                        </div>
                        <div class="border-r border-amber-200">
                            <h4 class="text-xl font-bold text-amber-600">{{ $climateProfile['avg_sunshine_duration'] }}</h4>
                            <small class="text-amber-800">Horas de Sol</small>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-gray-600">{{ $climateProfile['avg_cloud_cover'] }}%</h4>
                            <small class="text-gray-600">Nubosidad</small>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-center text-gray-500">
                        <i class="bi bi-info-circle"></i> Datos históricos reales de {{ $entity->locality->name ?? 'tu zona' }}
                    </div>
                </div>
            </div>
            @endif

            @if(isset($solarData))
            <div class="border border-emerald-200 rounded-lg shadow-sm mb-8 overflow-hidden">
                <div class="bg-emerald-600 px-4 py-3">
                    <h6 class="text-white font-semibold flex items-center gap-2">
                        <i class="bi bi-battery-charging"></i> Cobertura Solar Estimada
                    </h6>
                </div>
                <div class="p-6">
                    <div class="rounded-md p-4 mb-6 {{ $solarData['scenario'] === 'FULL_COVERAGE' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-yellow-50 border border-yellow-200 text-yellow-800' }}">
                        <div class="flex items-center gap-3">
                            <i class="bi {{ $solarData['scenario'] === 'FULL_COVERAGE' ? 'bi-check-circle-fill text-green-500' : 'bi-exclamation-triangle-fill text-yellow-500' }} text-xl"></i>
                            <strong>{{ $solarData['scenario'] === 'FULL_COVERAGE' ? 'Cobertura Total Posible' : 'Cobertura Parcial (Limitada por Espacio)' }}</strong>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center mb-6">
                        <div class="p-3 bg-gray-50 rounded">
                            <h3 class="text-2xl font-bold text-emerald-600">{{ $solarData['system_size_kwp'] }} kWp</h3>
                            <small class="text-gray-500">Potencia a Instalar</small>
                        </div>
                        <div class="p-3 bg-gray-50 rounded">
                            <h3 class="text-2xl font-bold text-blue-600">{{ $solarData['panels_count'] }}</h3>
                            <small class="text-gray-500">Paneles (550W)</small>
                        </div>
                        <div class="p-3 bg-gray-50 rounded">
                            <h3 class="text-2xl font-bold text-cyan-600">{{ number_format($solarData['area_used'], 1) }} m²</h3>
                            <small class="text-gray-500">Espacio Requerido</small>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-center text-sm text-gray-500 mb-6">
                        <div class="border-r border-gray-100">
                            <span>Área Declarada: <strong>{{ $entity->square_meters }} m²</strong></span>
                        </div>
                        <div>
                            <span>Área Necesaria (100%): <strong>{{ number_format($solarData['target_area'], 1) }} m²</strong></span>
                        </div>
                    </div>

                    <h6 class="text-gray-700 font-semibold border-b border-gray-100 pb-2 mb-4">Impacto en tu Consumo</h6>
                    
                    <div class="mb-4">
                        <div class="flex justify-between mb-1 text-sm">
                            <span class="text-gray-600">Cobertura Verano (Pico)</span>
                            <span class="font-bold text-gray-800">{{ $solarData['coverage_summer'] }}% {{ $solarData['coverage_summer'] < 100 ? '(Reduces tu factura)' : '(Cubres el pico)' }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-amber-400 h-2.5 rounded-full" style="width: {{ $solarData['coverage_summer'] }}%"></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="flex justify-between mb-1 text-sm">
                            <span class="text-gray-600">Cobertura Invierno (Promedio)</span>
                            <span class="font-bold text-gray-800">{{ $solarData['coverage_winter'] }}% {{ $solarData['coverage_winter'] >= 100 ? '(Te sobra energía)' : '' }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-emerald-500 h-2.5 rounded-full" style="width: {{ $solarData['coverage_winter'] }}%"></div>
                        </div>
                    </div>

                    <div class="text-center mt-6">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-50 text-yellow-800 border border-yellow-200">
                            <i class="bi bi-lightning-fill text-yellow-500 mr-1"></i> Generación Mensual Est.: {{ $solarData['monthly_generation_kwh'] }} kWh
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-8">
                <h6 class="text-green-800 font-bold flex items-center gap-2 mb-4">
                    <i class="bi bi-cash-coin"></i> Ahorro Estimado
                </h6>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex justify-between items-center bg-white p-3 rounded shadow-sm">
                        <span class="text-gray-600">Ahorro mensual (Promedio):</span>
                        <strong class="text-green-700 text-lg">${{ number_format($estimatedMonthlySavings, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex justify-between items-center bg-white p-3 rounded shadow-sm">
                        <span class="text-gray-600">Ahorro anual:</span>
                        <strong class="text-green-700 text-lg">${{ number_format($estimatedAnnualSavings, 0, ',', '.') }}</strong>
                    </div>
                </div>
                <small class="text-green-600 block mt-3 text-xs">
                    * Cálculo basado en simulación histórica con tarifa de ${{ number_format($averageTariff, 2) }}/kWh
                </small>
            </div>
            @endif

            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <h6 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                    <i class="bi bi-lightbulb text-yellow-500"></i> Sobre esta estimación
                </h6>
                <ul class="list-disc list-inside text-xs text-gray-500 space-y-1">
                    <li>Eficiencia de paneles: ~170W por m²</li>
                    <li>Horas pico de sol en San Juan: ~4.5 horas/día</li>
                    <li>Los valores son aproximados y pueden variar según orientación del techo, sombras, y otros factores</li>
                </ul>
            </div>

            <div class="mt-8 flex gap-3">
                <a href="{{ route('entities.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    <i class="bi bi-arrow-left mr-2"></i> Volver
                </a>
                
                <button type="button" @click="showModal = true" class="flex-grow inline-flex items-center justify-center px-4 py-2 bg-amber-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-600 focus:bg-amber-700 active:bg-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="bi bi-envelope mr-2"></i> Solicitar Presupuesto Personalizado
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Alpine.js -->
    <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <!-- Modal panel -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" @click.away="showModal = false" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <div class="bg-amber-500 px-4 py-3 sm:px-6 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-white flex items-center gap-2">
                        <i class="bi bi-envelope"></i> Solicitar Presupuesto
                    </h3>
                    <button type="button" @click="showModal = false" class="text-white hover:text-amber-100 focus:outline-none">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 mb-4">¡Gracias por tu interés en energía solar!</p>
                        <p class="text-sm text-gray-500 mb-2">Hemos registrado tu consulta con los siguientes datos:</p>
                        <ul class="list-disc list-inside text-sm text-gray-600 mb-4 bg-gray-50 p-3 rounded">
                            <li><strong>Propiedad:</strong> {{ $entity->name }}</li>
                            <li><strong>Superficie:</strong> {{ $entity->square_meters }} m²</li>
                            <li><strong>Área disponible:</strong> <span id="modalArea">--</span> m²</li>
                            <li><strong>Capacidad estimada:</strong> <span id="modalCapacity">--</span> kWp</li>
                        </ul>
                        <p class="text-sm text-green-600 font-medium flex items-center gap-2">
                            <i class="bi bi-check-circle"></i> Un especialista se pondrá en contacto contigo pronto.
                        </p>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="showModal = false" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
