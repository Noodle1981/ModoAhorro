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
                    <h1 class="text-2xl font-bold text-gray-900">{{ $room->name }}</h1>
                    <p class="text-gray-500 text-sm flex items-center gap-2">
                        <i class="bi bi-house"></i> {{ $room->entity->name ?? 'Entidad' }}
                    </p>
                </div>
            </div>
            <div class="flex gap-3 mt-4 md:mt-0">
                <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.rooms', $room->entity_id) }}">
                    <i class="bi bi-arrow-left mr-2"></i> Volver
                </x-button>
            </div>
        </div>

        {{-- Equipment List --}}
        <x-card :padding="false" class="mb-8">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                    <i class="bi bi-plug text-purple-500"></i>
                    Equipos en esta habitación
                </h3>
                <x-badge variant="purple">{{ $room->equipment->count() }} equipos</x-badge>
            </div>
            
            @if($room->equipment->count())
                <x-table hover>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4">Nombre</th>
                            <th class="px-6 py-4">Categoría</th>
                            <th class="px-6 py-4">Tipo</th>
                            <th class="px-6 py-4">Potencia</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </x-slot:head>
                    
                    @foreach($room->equipment as $equipment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <i class="bi bi-plug text-purple-600"></i>
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $equipment->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <x-badge variant="info">{{ $equipment->category->name ?? '-' }}</x-badge>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $equipment->type->name ?? '-' }}</td>
                            <td class="px-6 py-4">
                                <span class="font-mono text-gray-900">{{ number_format($equipment->nominal_power_w, 0) }} W</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <x-button variant="ghost" size="xs" href="{{ route($config['route_prefix'] . '.rooms.equipment.edit', [$room->entity_id, $room->id, $equipment->id]) }}" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </x-button>
                                    <form action="{{ route($config['route_prefix'] . '.rooms.equipment.destroy', [$room->entity_id, $room->id, $equipment->id]) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <x-button variant="ghost" size="xs" type="submit" 
                                            onclick="return confirm('¿Seguro que deseas eliminar este equipo?')"
                                            class="text-red-500 hover:text-red-700 hover:bg-red-50">
                                            <i class="bi bi-trash"></i>
                                        </x-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @else
                <div class="px-6 py-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-plug text-3xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500">No hay equipos cargados en esta habitación.</p>
                </div>
            @endif
        </x-card>

        {{-- Add Equipment Form --}}
        <x-card>
            <h3 class="font-semibold text-gray-900 flex items-center gap-2 mb-4">
                <i class="bi bi-plus-circle text-emerald-500"></i>
                Agregar nuevo equipo
            </h3>
            
            <x-alert type="info" class="mb-6">
                <strong>Nota importante:</strong> Los valores de potencia sugeridos son estimaciones promedio. 
                Para obtener un cálculo preciso, verifique el consumo real en el manual de su equipo o en la etiqueta del fabricante.
            </x-alert>
            
            @include('equipment.partials.form', ['room' => $room])
        </x-card>
    </div>
</div>
@endsection
