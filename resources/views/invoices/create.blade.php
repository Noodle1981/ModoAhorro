@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-plus-lg text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Nueva Factura</h1>
                    <p class="text-gray-500 text-sm">{{ $entity->name }}</p>
                </div>
            </div>
            <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.invoices', $entity->id) }}">
                <i class="bi bi-arrow-left mr-2"></i> Volver
            </x-button>
        </div>

        {{-- Form --}}
        <x-card>
            <form method="POST" action="{{ route($config['route_prefix'] . '.invoices.store', $entity->id) }}">
                @csrf

                {{-- Basic Info Section --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2 mb-4">
                        <i class="bi bi-file-earmark-text text-emerald-500"></i>
                        Datos de la Factura
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-input 
                            name="invoice_number" 
                            label="N° de Factura" 
                            placeholder="Ej: 0001-00012345"
                            :value="old('invoice_number')"
                        />
                        <x-input 
                            name="invoice_date" 
                            label="Fecha de Emisión" 
                            type="date"
                            :value="old('invoice_date')"
                        />
                    </div>
                </div>

                {{-- Period Section --}}
                <div class="mb-8 pb-8 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2 mb-4">
                        <i class="bi bi-calendar3 text-blue-500"></i>
                        Período de Facturación
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-input 
                            name="start_date" 
                            label="Fecha Inicio" 
                            type="date"
                            :value="old('start_date')"
                            required
                        />
                        <x-input 
                            name="end_date" 
                            label="Fecha Fin" 
                            type="date"
                            :value="old('end_date')"
                            required
                        />
                    </div>
                </div>

                {{-- Consumption Section --}}
                <div class="mb-8 pb-8 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2 mb-4">
                        <i class="bi bi-lightning-charge text-amber-500"></i>
                        Consumo de Energía
                    </h3>
                    
                    @if($contract->is_three_phase)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                        <x-input 
                            name="energy_consumed_p1_kwh" 
                            label="Consumo P1 (kWh)" 
                            type="number"
                            placeholder="0"
                            :value="old('energy_consumed_p1_kwh')"
                            helper="Pico"
                        />
                        <x-input 
                            name="energy_consumed_p2_kwh" 
                            label="Consumo P2 (kWh)" 
                            type="number"
                            placeholder="0"
                            :value="old('energy_consumed_p2_kwh')"
                            helper="Valle"
                        />
                        <x-input 
                            name="energy_consumed_p3_kwh" 
                            label="Consumo P3 (kWh)" 
                            type="number"
                            placeholder="0"
                            :value="old('energy_consumed_p3_kwh')"
                            helper="Resto"
                        />
                    </div>
                    @endif
                    
                    <div class="bg-emerald-50 rounded-xl p-4">
                        <x-input 
                            name="total_energy_consumed_kwh" 
                            label="Consumo Total (kWh)" 
                            type="number"
                            placeholder="0"
                            :value="old('total_energy_consumed_kwh')"
                            required
                            class="bg-white"
                        />
                    </div>
                </div>

                {{-- Costs Section --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2 mb-4">
                        <i class="bi bi-currency-dollar text-green-500"></i>
                        Costos
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <x-input 
                            name="cost_for_energy" 
                            label="Costo Energía" 
                            type="number"
                            placeholder="0.00"
                            :value="old('cost_for_energy')"
                        />
                        <x-input 
                            name="cost_for_power" 
                            label="Costo Potencia" 
                            type="number"
                            placeholder="0.00"
                            :value="old('cost_for_power')"
                        />
                        <x-input 
                            name="taxes" 
                            label="Impuestos" 
                            type="number"
                            placeholder="0.00"
                            :value="old('taxes')"
                        />
                        <x-input 
                            name="other_charges" 
                            label="Otros Cargos" 
                            type="number"
                            placeholder="0.00"
                            :value="old('other_charges')"
                        />
                    </div>
                    
                    <div class="bg-blue-50 rounded-xl p-4">
                        <x-input 
                            name="total_amount" 
                            label="Importe Total" 
                            type="number"
                            placeholder="0.00"
                            :value="old('total_amount')"
                            required
                            class="bg-white"
                        />
                    </div>
                </div>

                <input type="hidden" name="source" value="manual">

                {{-- Actions --}}
                <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                    <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.invoices', $entity->id) }}">
                        Cancelar
                    </x-button>
                    <x-button variant="primary" type="submit">
                        <i class="bi bi-check-lg mr-2"></i> Guardar Factura
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</div>
@endsection
