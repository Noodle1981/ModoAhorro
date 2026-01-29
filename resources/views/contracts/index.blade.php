@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-file-earmark-text text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Contratos</h1>
                    <p class="text-gray-500 text-sm">Gestiona tus contratos de suministro eléctrico</p>
                </div>
            </div>
            <div class="flex gap-3 mt-4 md:mt-0">
                <x-button variant="primary" href="{{ route('contracts.create') }}">
                    <i class="bi bi-plus-circle mr-2"></i> Nuevo Contrato
                </x-button>
                <x-button variant="secondary" href="{{ route('dashboard') }}">
                    <i class="bi bi-arrow-left mr-2"></i> Volver
                </x-button>
            </div>
        </div>

        {{-- Contracts Grid --}}
        @if($contracts->isEmpty())
            <x-card class="text-center py-16">
                <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="bi bi-file-earmark-text text-4xl text-indigo-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Sin contratos registrados</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">
                    Registra tu contrato de suministro eléctrico para cargar facturas y analizar consumos.
                </p>
                <x-button variant="primary" href="{{ route('contracts.create') }}">
                    <i class="bi bi-plus-circle mr-2"></i> Crear primer contrato
                </x-button>
            </x-card>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($contracts as $contract)
                    <x-card hover class="relative">
                        {{-- Status Badge --}}
                        <div class="absolute top-4 right-4">
                            @if($contract->is_active)
                                <x-badge variant="success" dot>Activo</x-badge>
                            @else
                                <x-badge variant="danger" dot>Inactivo</x-badge>
                            @endif
                        </div>

                        {{-- Entity --}}
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                                <i class="bi bi-building text-indigo-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $contract->entity->name ?? 'Sin entidad' }}</h3>
                                <p class="text-sm text-gray-500">{{ $contract->proveedor->name ?? 'Sin proveedor' }}</p>
                            </div>
                        </div>

                        {{-- Details --}}
                        <div class="space-y-3 mb-4 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">N° Suministro</span>
                                <span class="font-mono text-gray-900">{{ $contract->supply_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tarifa</span>
                                <span class="text-gray-900">{{ $contract->rate_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Potencia</span>
                                <span class="text-gray-900">
                                    {{ $contract->contracted_power_kw_p1 ?? '-' }} kW
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Inicio</span>
                                <span class="text-gray-900">
                                    {{ $contract->start_date ? \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') : '-' }}
                                </span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex gap-2 pt-4 border-t border-gray-100">
                            <x-button variant="ghost" size="sm" href="{{ route(config('entity_types.' . $contract->entity->type . '.route_prefix') . '.meter', $contract->entity->id) }}" class="flex-1">
                                <i class="bi bi-eye mr-1"></i> Ver
                            </x-button>
                            <x-button variant="ghost" size="sm" href="{{ route('contracts.edit', $contract->id) }}" class="flex-1">
                                <i class="bi bi-pencil mr-1"></i> Editar
                            </x-button>
                            <form action="{{ route('contracts.destroy', $contract->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <x-button variant="ghost" size="sm" type="submit" 
                                    onclick="return confirm('¿Eliminar este contrato?')"
                                    class="text-red-500 hover:text-red-700 hover:bg-red-50">
                                    <i class="bi bi-trash"></i>
                                </x-button>
                            </form>
                        </div>
                    </x-card>
                @endforeach
            </div>

            {{-- Summary --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                <x-stat-card 
                    title="Total Contratos" 
                    :value="$contracts->count()" 
                    icon="bi-file-earmark-text" 
                    color="indigo"
                />
                <x-stat-card 
                    title="Contratos Activos" 
                    :value="$contracts->where('is_active', true)->count()" 
                    icon="bi-check-circle" 
                    color="emerald"
                />
                <x-stat-card 
                    title="Potencia Total" 
                    :value="number_format($contracts->sum('contracted_power_kw_p1'), 1) . ' kW'" 
                    icon="bi-lightning-charge" 
                    color="amber"
                />
            </div>
        @endif
    </div>
</div>
@endsection
