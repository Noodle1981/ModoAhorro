@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-3">
                <div class="bg-gradient-to-br {{ $config['tailwind_gradient'] ?? 'from-emerald-500 to-emerald-600' }} w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="{{ $config['icon'] }} text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Mis {{ $config['label_plural'] }}</h1>
                    <p class="text-gray-500 text-sm">Gestiona tus {{ strtolower($config['label_plural']) }} y consumo energético</p>
                </div>
            </div>
            <div class="mt-4 md:mt-0">
                <x-button variant="primary" href="{{ route($config['route_prefix'] . '.create') }}">
                    <i class="bi bi-plus-circle mr-2"></i> Nuevo {{ $config['label'] }}
                </x-button>
            </div>
        </div>

        {{-- Entities Table --}}
        <x-card :padding="false" class="mb-8">
            @if($entities->isEmpty())
                {{-- Empty State --}}
                <div class="text-center py-16 px-6">
                    <div class="w-20 h-20 {{ $config['tailwind_bg'] ?? 'bg-emerald-100' }} rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="{{ $config['icon_secondary'] }} text-4xl {{ $config['tailwind_text'] ?? 'text-emerald-600' }}"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Aún no tienes {{ strtolower($config['label_plural']) }}</h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto">
                        Comienza agregando tu primera {{ strtolower($config['label']) }} para gestionar su consumo energético.
                    </p>
                    <x-button variant="primary" size="lg" href="{{ route($config['route_prefix'] . '.create') }}">
                        <i class="bi bi-plus-circle mr-2"></i> Agregar mi primera {{ strtolower($config['label']) }}
                    </x-button>
                </div>
            @else
                <x-table hover>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4">Nombre</th>
                            <th class="px-6 py-4">Localidad</th>
                            <th class="px-6 py-4">Metros²</th>
                            <th class="px-6 py-4">{{ $config['people_label'] }}</th>
                            @if($config['has_business_hours'])
                                <th class="px-6 py-4">Horario</th>
                            @endif
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </x-slot:head>
                    
                    @foreach($entities as $entity)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 {{ $config['tailwind_bg'] ?? 'bg-emerald-100' }} rounded-lg flex items-center justify-center">
                                        <i class="{{ $config['icon_secondary'] }} {{ $config['tailwind_text'] ?? 'text-emerald-600' }}"></i>
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $entity->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $entity->locality->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $entity->square_meters }} m²</td>
                            <td class="px-6 py-4 text-gray-600">{{ $entity->people_count }}</td>
                            @if($config['has_business_hours'])
                                <td class="px-6 py-4 text-gray-600">
                                    @if($entity->opens_at && $entity->closes_at)
                                        <x-badge variant="info">
                                            {{ \Carbon\Carbon::parse($entity->opens_at)->format('H:i') }} - 
                                            {{ \Carbon\Carbon::parse($entity->closes_at)->format('H:i') }}
                                        </x-badge>
                                    @else
                                        <span class="text-gray-400">No definido</span>
                                    @endif
                                </td>
                            @endif
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <x-button variant="secondary" size="sm" href="{{ route($config['route_prefix'] . '.show', $entity->id) }}" title="Ver detalles">
                                        <i class="bi bi-eye text-lg"></i>
                                    </x-button>
                                    <x-button variant="secondary" size="sm" href="{{ route($config['route_prefix'] . '.edit', $entity->id) }}" title="Editar">
                                        <i class="bi bi-pencil text-lg"></i>
                                    </x-button>
                                    <x-button variant="secondary" size="sm" href="{{ route($config['route_prefix'] . '.rooms', $entity->id) }}" title="{{ $config['rooms_label'] }}">
                                        <i class="{{ $config['rooms_icon'] }} text-lg"></i>
                                    </x-button>
                                    <x-button variant="secondary" size="sm" href="{{ route($config['route_prefix'] . '.invoices', $entity->id) }}" title="Facturas">
                                        <i class="bi bi-receipt text-lg"></i>
                                    </x-button>
                                    <form action="{{ route($config['route_prefix'] . '.destroy', $entity->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <x-button variant="ghost" size="sm" type="submit" 
                                            onclick="return confirm('¿Seguro que deseas eliminar esta {{ strtolower($config['label']) }}?')"
                                            class="text-red-500 hover:text-red-700 hover:bg-red-50">
                                            <i class="bi bi-trash text-lg"></i>
                                        </x-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </x-card>



    </div>
</div>
@endsection
