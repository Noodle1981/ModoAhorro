@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-plug text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Mis Equipos</h1>
                    <p class="text-gray-500 text-sm">Gestiona todos tus equipos eléctricos</p>
                </div>
            </div>
            <div class="flex gap-3 mt-4 md:mt-0">
                <x-button variant="primary" href="{{ route('equipment.create') }}">
                    <i class="bi bi-plus-circle mr-2"></i> Agregar Equipo
                </x-button>
                <x-button variant="secondary" href="{{ route('equipment.create_portable') }}">
                    <i class="bi bi-laptop mr-2"></i> Equipo Portátil
                </x-button>
            </div>
        </div>

        {{-- Equipment Table --}}
        @if($equipments->isEmpty())
            <x-card class="text-center py-16">
                <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="bi bi-plug text-4xl text-purple-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Sin equipos registrados</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">
                    Comienza agregando tus equipos eléctricos para analizar su consumo.
                </p>
                <x-button variant="primary" href="{{ route('equipment.create') }}">
                    <i class="bi bi-plus-circle mr-2"></i> Agregar primer equipo
                </x-button>
            </x-card>
        @else
            <x-card :padding="false">
                <x-table hover>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4">Equipo</th>
                            <th class="px-6 py-4">Categoría</th>
                            <th class="px-6 py-4">Tipo</th>
                            <th class="px-6 py-4">Potencia</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </x-slot:head>
                    
                    @foreach($equipments as $equipment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 {{ $equipment->is_active ? 'bg-purple-100' : 'bg-gray-100' }} rounded-lg flex items-center justify-center">
                                        <i class="bi bi-plug {{ $equipment->is_active ? 'text-purple-600' : 'text-gray-400' }}"></i>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-900">{{ $equipment->name }}</span>
                                        @if($equipment->room)
                                            <p class="text-xs text-gray-500">{{ $equipment->room->name }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <x-badge variant="info">{{ $equipment->category->name ?? '-' }}</x-badge>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $equipment->type->name ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="font-mono text-gray-900">{{ number_format($equipment->nominal_power_w, 0) }} W</span>
                                @if(isset($equipment->is_validated) && !$equipment->is_validated)
                                    <div class="mt-1">
                                        <x-badge variant="warning" size="xs" title="Usando valores genéricos">
                                            <i class="bi bi-exclamation-triangle mr-1"></i> Genérico
                                        </x-badge>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($equipment->is_active)
                                    <x-badge variant="success" dot>Activo</x-badge>
                                @else
                                    <x-badge variant="danger" dot>De baja</x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <x-button variant="ghost" size="xs" href="{{ route('equipment.edit', $equipment->id) }}" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </x-button>
                                    <x-button variant="ghost" size="xs" href="{{ route('equipment.show', $equipment->id) }}" title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </x-button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            </x-card>
            
            {{-- Summary Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <x-stat-card 
                    title="Total Equipos" 
                    :value="$equipments->count()" 
                    icon="bi-plug" 
                    color="purple"
                />
                <x-stat-card 
                    title="Equipos Activos" 
                    :value="$equipments->where('is_active', true)->count()" 
                    icon="bi-check-circle" 
                    color="emerald"
                />
                <x-stat-card 
                    title="Potencia Total" 
                    :value="number_format($equipments->sum('nominal_power_w'), 0) . ' W'" 
                    icon="bi-lightning-charge" 
                    color="amber"
                />
            </div>
        @endif
    </div>
</div>
@endsection
