@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-door-open text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $config['rooms_label'] ?? 'Habitaciones' }}</h1>
                    <p class="text-gray-500 text-sm flex items-center gap-2">
                        <i class="{{ $config['icon_secondary'] ?? 'bi-house' }}"></i>
                        {{ $entity->name }}
                    </p>
                </div>
            </div>
            <div class="flex gap-3 mt-4 md:mt-0">
                <x-button variant="primary" href="{{ route($config['route_prefix'] . '.rooms.create', $entity->id) }}">
                    <i class="bi bi-plus-circle mr-2"></i> Nueva {{ str_replace('s', '', $config['rooms_label'] ?? 'Habitación') }}
                </x-button>
                <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.show', $entity->id) }}">
                    <i class="bi bi-arrow-left mr-2"></i> Volver
                </x-button>
            </div>
        </div>

        {{-- Rooms Grid --}}
        @if($rooms->isEmpty())
            <x-card class="text-center py-16">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="{{ $config['rooms_icon'] ?? 'bi-door-open' }} text-4xl text-blue-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Sin {{ strtolower($config['rooms_label'] ?? 'Habitaciones') }}</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">
                    Agrega {{ strtolower($config['rooms_label'] ?? 'habitaciones') }} para organizar tus equipos y calcular el consumo de cada área.
                </p>
                <x-button variant="primary" href="{{ route($config['route_prefix'] . '.rooms.create', $entity->id) }}">
                    <i class="bi bi-plus-circle mr-2"></i> Crear primera {{ str_replace('s', '', strtolower($config['rooms_label'] ?? 'habitación')) }}
                </x-button>
            </x-card>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($rooms as $room)
                    <x-card hover class="group relative">
                        {{-- Room Icon --}}
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="{{ $config['rooms_icon'] ?? 'bi-door-open' }} text-xl text-blue-600"></i>
                            </div>
                            <x-badge variant="{{ $room->equipment->count() > 0 ? 'success' : 'default' }}">
                                {{ $room->equipment->count() }} equipos
                            </x-badge>
                        </div>
                        
                        {{-- Room Info --}}
                        <h3 class="font-semibold text-gray-900 text-lg mb-1">{{ $room->name }}</h3>
                        <p class="text-gray-500 text-sm mb-4 line-clamp-2">
                            {{ $room->description ?: 'Sin descripción' }}
                        </p>
                        
                        {{-- Actions --}}
                        <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                            <x-button variant="ghost" size="xs" href="{{ route($config['route_prefix'] . '.rooms.show', [$entity->id, $room->id]) }}" title="Ver">
                                <i class="bi bi-eye"></i>
                            </x-button>
                            <x-button variant="ghost" size="xs" href="{{ route($config['route_prefix'] . '.rooms.edit', [$entity->id, $room->id]) }}" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </x-button>
                            <x-button variant="primary" size="xs" href="{{ route($config['route_prefix'] . '.rooms.equipment', [$entity->id, $room->id]) }}" class="flex-1">
                                <i class="bi bi-plug mr-1"></i> Equipos
                            </x-button>
                            <form action="{{ route($config['route_prefix'] . '.rooms.destroy', [$entity->id, $room->id]) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <x-button variant="ghost" size="xs" type="submit" 
                                    onclick="return confirm('¿Seguro que deseas eliminar esta habitación?')"
                                    class="text-red-500 hover:text-red-700 hover:bg-red-50">
                                    <i class="bi bi-trash"></i>
                                </x-button>
                            </form>
                        </div>
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
