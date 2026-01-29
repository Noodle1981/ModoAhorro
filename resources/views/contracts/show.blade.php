@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-file-earmark-text text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Contrato de Suministro</h1>
                    <p class="text-gray-500 text-sm">{{ $entity->name }}</p>
                </div>
            </div>
            <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.invoices', $entity->id) }}">
                <i class="bi bi-arrow-left mr-2"></i> Volver
            </x-button>
        </div>

        @if($contract)
            {{-- Contract Details --}}
            <x-card class="mb-6">
                {{-- Status --}}
                <div class="flex items-center justify-between mb-6 pb-6 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                            <i class="bi bi-building text-indigo-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $contract->proveedor->name ?? 'Sin proveedor' }}</h3>
                            <p class="text-sm text-gray-500">Empresa Distribuidora</p>
                        </div>
                    </div>
                    @if($contract->is_active)
                        <x-badge variant="success" dot>Activo</x-badge>
                    @else
                        <x-badge variant="danger" dot>Inactivo</x-badge>
                    @endif
                </div>

                {{-- Contract Info --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500">N° de Suministro</p>
                            <p class="font-mono text-lg font-semibold text-gray-900">{{ $contract->supply_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">N° Serie del Medidor</p>
                            <p class="font-mono text-gray-900">{{ $contract->serial_number ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Identificador</p>
                            <p class="text-gray-900">{{ $contract->contract_identifier ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-500">Tarifa</p>
                            <p class="font-semibold text-gray-900">{{ $contract->rate_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Inicio</p>
                            <p class="text-gray-900">
                                {{ $contract->start_date ? \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Fin</p>
                            <p class="text-gray-900">
                                {{ $contract->end_date ? \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y') : 'Sin fecha de fin' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Power Stats --}}
                <div class="grid grid-cols-3 gap-4 p-4 bg-gray-50 rounded-xl">
                    <div class="text-center">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Potencia P1</p>
                        <p class="text-xl font-bold text-gray-900">{{ $contract->contracted_power_kw_p1 ?? 0 }}</p>
                        <p class="text-xs text-gray-500">kW (Pico)</p>
                    </div>
                    <div class="text-center border-x border-gray-200">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Potencia P2</p>
                        <p class="text-xl font-bold text-gray-900">{{ $contract->contracted_power_kw_p2 ?? 0 }}</p>
                        <p class="text-xs text-gray-500">kW (Valle)</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Potencia P3</p>
                        <p class="text-xl font-bold text-gray-900">{{ $contract->contracted_power_kw_p3 ?? 0 }}</p>
                        <p class="text-xs text-gray-500">kW (Resto)</p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex gap-3 pt-6 mt-6 border-t border-gray-200">
                    <x-button variant="primary" href="{{ route('contracts.edit', $contract->id) }}" class="flex-1">
                        <i class="bi bi-pencil mr-2"></i> Editar Contrato
                    </x-button>
                </div>
            </x-card>
        @else
            {{-- No Contract --}}
            <x-card class="text-center py-16">
                <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="bi bi-file-earmark-text text-4xl text-indigo-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Sin contrato registrado</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">
                    Registra el contrato de suministro para poder cargar facturas y analizar consumos.
                </p>
                <x-button variant="primary" href="{{ route($config['route_prefix'] . '.contracts.create', $entity->id) }}">
                    <i class="bi bi-plus-circle mr-2"></i> Registrar Contrato
                </x-button>
            </x-card>
        @endif
    </div>
</div>
@endsection
