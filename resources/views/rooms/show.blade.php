@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="{{ $config['rooms_icon'] ?? 'bi-door-open' }} text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $room->name }}</h1>
                    <p class="text-gray-500 text-sm flex items-center gap-2">
                        <i class="{{ $config['icon_secondary'] ?? 'bi-house' }}"></i>
                        {{ $entity->name }}
                    </p>
                </div>
            </div>
            <div class="flex gap-3">
                <x-button variant="warning" href="{{ route($config['route_prefix'] . '.rooms.edit', [$entity->id, $room->id]) }}">
                    <i class="bi bi-pencil mr-2"></i> Editar
                </x-button>
                <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.rooms', $entity->id) }}">
                    <i class="bi bi-arrow-left mr-2"></i> Volver
                </x-button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Room Info --}}
            <x-card>
                <h3 class="font-semibold text-gray-900 flex items-center gap-2 mb-4">
                    <i class="bi bi-info-circle text-blue-500"></i>
                    Información
                </h3>
                
                <dl class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <dt class="text-gray-500">Nombre</dt>
                        <dd class="font-medium text-gray-900">{{ $room->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 mb-1">Descripción</dt>
                        <dd class="text-gray-900">{{ $room->description ?: 'Sin descripción' }}</dd>
                    </div>
                </dl>
            </x-card>

            {{-- Stats --}}
            <x-card>
                <h3 class="font-semibold text-gray-900 flex items-center gap-2 mb-4">
                    <i class="bi bi-bar-chart text-emerald-500"></i>
                    Estadísticas
                </h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-4 bg-purple-50 rounded-xl">
                        <div class="text-3xl font-bold text-purple-600">{{ $room->equipment->count() }}</div>
                        <p class="text-sm text-gray-500 mt-1">Equipos</p>
                    </div>
                    <div class="text-center p-4 bg-amber-50 rounded-xl">
                        <div class="text-3xl font-bold text-amber-600">{{ number_format($room->equipment->sum('nominal_power_w'), 0) }}</div>
                        <p class="text-sm text-gray-500 mt-1">Watts Total</p>
                    </div>
                </div>
                
                <div class="mt-4">
                    <x-button variant="primary" class="w-full" href="{{ route($config['route_prefix'] . '.rooms.equipment', [$entity->id, $room->id]) }}">
                        <i class="bi bi-plug mr-2"></i> Gestionar Equipos
                    </x-button>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection
