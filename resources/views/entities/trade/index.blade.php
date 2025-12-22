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
                    <i class="bi bi-plus-circle mr-2"></i> Nueva {{ $config['label'] }}
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
                                    <x-button variant="ghost" size="xs" href="{{ route($config['route_prefix'] . '.show', $entity->id) }}" title="Ver detalles">
                                        <i class="bi bi-eye"></i>
                                    </x-button>
                                    <x-button variant="ghost" size="xs" href="{{ route($config['route_prefix'] . '.edit', $entity->id) }}" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </x-button>
                                    <x-button variant="ghost" size="xs" href="{{ route($config['route_prefix'] . '.rooms', $entity->id) }}" title="{{ $config['rooms_label'] }}">
                                        <i class="{{ $config['rooms_icon'] }}"></i>
                                    </x-button>
                                    <x-button variant="ghost" size="xs" href="{{ route($config['route_prefix'] . '.invoices', $entity->id) }}" title="Facturas">
                                        <i class="bi bi-receipt"></i>
                                    </x-button>
                                    <form action="{{ route($config['route_prefix'] . '.destroy', $entity->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <x-button variant="ghost" size="xs" type="submit" 
                                            onclick="return confirm('¿Seguro que deseas eliminar esta {{ strtolower($config['label']) }}?')"
                                            class="text-red-500 hover:text-red-700 hover:bg-red-50">
                                            <i class="bi bi-trash"></i>
                                        </x-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </x-card>

        @if($entities->isNotEmpty())
            @php $firstEntity = $entities->first(); @endphp

            {{-- Consumption Center --}}
            <section class="mb-8">
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-4">
                    <i class="bi bi-lightning-charge text-amber-500"></i>
                    Centro de Consumo
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-card hover class="group">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="bi bi-receipt text-2xl text-blue-600"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 mb-1">Facturas y Ajustes</h3>
                                <p class="text-gray-500 text-sm mb-3">
                                    Revisa el estado de tus facturas y realiza el <strong>ajuste de uso</strong> para obtener cálculos precisos.
                                </p>
                                <x-button variant="primary" size="sm" href="{{ route('usage_adjustments.index') }}">
                                    Ir a ajustes de uso
                                </x-button>
                            </div>
                        </div>
                    </x-card>

                    <x-card hover class="group">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="bi bi-bar-chart-line text-2xl text-emerald-600"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 mb-1">Consumo Energético</h3>
                                <p class="text-gray-500 text-sm mb-3">
                                    Visualiza el consumo estimado y real, compara periodos y optimiza tu gestión.
                                </p>
                                <x-button variant="success" size="sm" href="{{ route('consumption.panel') }}">
                                    Panel de consumo
                                </x-button>
                            </div>
                        </div>
                    </x-card>
                </div>
            </section>

            {{-- Recommendations Center --}}
            <section>
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <i class="bi bi-lightbulb text-amber-500"></i>
                        Centro de Recomendaciones
                    </h2>
                    <p class="text-sm text-gray-500">Recomendaciones optimizadas para {{ strtolower($config['label_plural']) }}</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($config['recommendations'] as $key => $rec)
                        @if($rec['enabled'] ?? false)
                            @php
                                $routeName = $config['route_prefix'] . '.' . $key;
                                $colors = [
                                    'warning' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-600', 'gradient' => 'from-amber-500 to-amber-600'],
                                    'danger' => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'gradient' => 'from-red-500 to-red-600'],
                                    'primary' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'gradient' => 'from-emerald-500 to-emerald-600'],
                                    'secondary' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'gradient' => 'from-gray-500 to-gray-600'],
                                    'info' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'gradient' => 'from-blue-500 to-blue-600'],
                                    'success' => ['bg' => 'bg-green-100', 'text' => 'text-green-600', 'gradient' => 'from-green-500 to-green-600'],
                                ];
                                $color = $colors[$rec['color']] ?? $colors['primary'];
                            @endphp
                            
                            <x-card hover class="group">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 {{ $color['bg'] }} rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="{{ $rec['icon'] }} text-2xl {{ $color['text'] }}"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900 mb-1">{{ $rec['label'] }}</h3>
                                        <p class="text-gray-500 text-sm mb-3">{{ $rec['description'] }}</p>
                                        
                                        @if(Route::has($routeName))
                                            <x-button variant="outline" size="sm" href="{{ route($routeName, $firstEntity->id) }}">
                                                Ver más <i class="bi bi-arrow-right ml-1"></i>
                                            </x-button>
                                        @else
                                            <x-badge variant="default">Próximamente</x-badge>
                                        @endif
                                    </div>
                                </div>
                            </x-card>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

    </div>
</div>
@endsection
