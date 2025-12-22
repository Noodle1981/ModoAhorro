@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-amber-500 to-amber-600 w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-pencil text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Editar Contrato</h1>
                    <p class="text-gray-500 text-sm">{{ $contract->supply_number }}</p>
                </div>
            </div>
            <x-button variant="secondary" href="{{ route('contracts.index') }}">
                <i class="bi bi-arrow-left mr-2"></i> Volver
            </x-button>
        </div>

        {{-- Form --}}
        <x-card>
            <form method="POST" action="{{ route('contracts.update', $contract->id) }}">
                @csrf
                @method('PUT')

                {{-- Entity & Provider Section --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2 mb-4">
                        <i class="bi bi-building text-indigo-500"></i>
                        Entidad y Proveedor
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label for="entity_id" class="block text-sm font-medium text-gray-700">
                                Entidad <span class="text-red-500">*</span>
                            </label>
                            <select name="entity_id" id="entity_id" required
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($entities as $entity)
                                    <option value="{{ $entity->id }}" {{ $contract->entity_id == $entity->id ? 'selected' : '' }}>
                                        {{ $entity->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="space-y-1.5">
                            <label for="proveedor_id" class="block text-sm font-medium text-gray-700">
                                Empresa Distribuidora <span class="text-red-500">*</span>
                            </label>
                            <select name="proveedor_id" id="proveedor_id" required
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->id }}" {{ $contract->proveedor_id == $proveedor->id ? 'selected' : '' }}>
                                        {{ $proveedor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Contract Info Section --}}
                <div class="mb-8 pb-8 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2 mb-4">
                        <i class="bi bi-file-earmark-text text-blue-500"></i>
                        Datos del Contrato
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-input 
                            name="supply_number" 
                            label="N° de Suministro" 
                            placeholder="Ej: 123456789"
                            :value="old('supply_number', $contract->supply_number)"
                            required 
                        />
                        <x-input 
                            name="serial_number" 
                            label="N° Serie del Medidor" 
                            placeholder="Ej: MED-001234"
                            :value="old('serial_number', $contract->serial_number)"
                        />
                        <x-input 
                            name="contract_identifier" 
                            label="Identificador de Contrato" 
                            placeholder="Ej: CONT-2024-001"
                            :value="old('contract_identifier', $contract->contract_identifier)"
                        />
                        <x-input 
                            name="rate_name" 
                            label="Nombre de Tarifa" 
                            placeholder="Ej: T1R, T2, Residencial"
                            :value="old('rate_name', $contract->rate_name)"
                            required 
                        />
                    </div>
                </div>

                {{-- Power Section --}}
                <div class="mb-8 pb-8 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2 mb-4">
                        <i class="bi bi-lightning-charge text-amber-500"></i>
                        Potencia Contratada
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <x-input 
                            name="contracted_power_kw_p1" 
                            label="Potencia P1 (kW)" 
                            type="number"
                            placeholder="0.00"
                            :value="old('contracted_power_kw_p1', $contract->contracted_power_kw_p1)"
                            helper="Pico"
                        />
                        <x-input 
                            name="contracted_power_kw_p2" 
                            label="Potencia P2 (kW)" 
                            type="number"
                            placeholder="0.00"
                            :value="old('contracted_power_kw_p2', $contract->contracted_power_kw_p2)"
                            helper="Valle"
                        />
                        <x-input 
                            name="contracted_power_kw_p3" 
                            label="Potencia P3 (kW)" 
                            type="number"
                            placeholder="0.00"
                            :value="old('contracted_power_kw_p3', $contract->contracted_power_kw_p3)"
                            helper="Resto"
                        />
                    </div>
                </div>

                {{-- Dates Section --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2 mb-4">
                        <i class="bi bi-calendar3 text-emerald-500"></i>
                        Vigencia
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-input 
                            name="start_date" 
                            label="Fecha de Inicio" 
                            type="date"
                            :value="old('start_date', $contract->start_date)"
                            required 
                        />
                        <x-input 
                            name="end_date" 
                            label="Fecha de Fin" 
                            type="date"
                            :value="old('end_date', $contract->end_date)"
                            helper="Dejar vacío si no tiene fecha de fin"
                        />
                    </div>
                    
                    <div class="mt-4 p-4 bg-gray-50 rounded-xl">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ $contract->is_active ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-500 focus:ring-indigo-500 w-5 h-5">
                            <div>
                                <span class="font-medium text-gray-900">Contrato Activo</span>
                                <p class="text-sm text-gray-500">El contrato está vigente y en uso</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                    <x-button variant="secondary" href="{{ route('contracts.index') }}">
                        Cancelar
                    </x-button>
                    <x-button variant="warning" type="submit">
                        <i class="bi bi-check-lg mr-2"></i> Guardar Cambios
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</div>
@endsection
