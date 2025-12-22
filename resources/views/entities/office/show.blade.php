@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br {{ $config['tailwind_gradient'] ?? 'from-emerald-500 to-emerald-600' }} w-14 h-14 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="{{ $config['icon'] }} text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $entity->name }}</h1>
                    <p class="text-gray-500 flex items-center gap-2">
                        <i class="bi bi-geo-alt"></i>
                        {{ $entity->locality->name ?? 'Sin ubicación' }}
                    </p>
                </div>
            </div>
            <div class="flex gap-3 mt-4 md:mt-0">
                <x-button variant="warning" href="{{ route($config['route_prefix'] . '.edit', $entity->id) }}">
                    <i class="bi bi-pencil mr-2"></i> Editar
                </x-button>
                <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.index') }}">
                    <i class="bi bi-arrow-left mr-2"></i> Volver
                </x-button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            {{-- Info Principal --}}
            <x-card :padding="false">
                <div class="bg-gradient-to-r {{ $config['tailwind_gradient'] ?? 'from-emerald-500 to-emerald-600' }} px-6 py-4 rounded-t-xl">
                    <h3 class="text-white font-semibold flex items-center gap-2">
                        <i class="{{ $config['icon_secondary'] }}"></i>
                        Información General
                    </h3>
                </div>
                <div class="p-6">
                    <dl class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <dt class="text-gray-500">Dirección</dt>
                            <dd class="font-medium text-gray-900">{{ $entity->address_street }}</dd>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <dt class="text-gray-500">Localidad</dt>
                            <dd class="font-medium text-gray-900">{{ $entity->locality->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <dt class="text-gray-500">Código Postal</dt>
                            <dd class="font-medium text-gray-900">{{ $entity->address_postal_code }}</dd>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <dt class="text-gray-500">Metros²</dt>
                            <dd class="font-medium text-gray-900">{{ $entity->square_meters }} m²</dd>
                        </div>
                        <div class="flex justify-between items-center py-2 {{ $config['has_business_hours'] ? 'border-b border-gray-100' : '' }}">
                            <dt class="text-gray-500">{{ $config['people_label'] }}</dt>
                            <dd class="font-medium text-gray-900">{{ $entity->people_count }}</dd>
                        </div>
                        @if($config['has_business_hours'])
                            <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                <dt class="text-gray-500">Horario</dt>
                                <dd class="font-medium text-gray-900">
                                    @if($entity->opens_at && $entity->closes_at)
                                        <x-badge variant="info">
                                            {{ \Carbon\Carbon::parse($entity->opens_at)->format('H:i') }} - 
                                            {{ \Carbon\Carbon::parse($entity->closes_at)->format('H:i') }}
                                        </x-badge>
                                    @else
                                        <span class="text-gray-400">No definido</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <dt class="text-gray-500">Días</dt>
                                <dd class="font-medium text-gray-900">
                                    @if($entity->operating_days)
                                        @php
                                            $days = is_string($entity->operating_days) ? json_decode($entity->operating_days, true) : $entity->operating_days;
                                        @endphp
                                        {{ implode(', ', array_map('ucfirst', $days ?? [])) }}
                                    @else
                                        <span class="text-gray-400">No definido</span>
                                    @endif
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </x-card>

            {{-- Stats --}}
            <x-card>
                <h3 class="font-semibold text-gray-900 flex items-center gap-2 mb-6">
                    <i class="bi bi-bar-chart text-emerald-500"></i>
                    Estadísticas Rápidas
                </h3>
                
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="text-center p-4 bg-blue-50 rounded-xl">
                        <div class="text-3xl font-bold text-blue-600">{{ $entity->rooms->count() }}</div>
                        <p class="text-sm text-gray-500 mt-1">{{ $config['rooms_label'] }}</p>
                    </div>
                    <div class="text-center p-4 bg-emerald-50 rounded-xl">
                        <div class="text-3xl font-bold text-emerald-600">{{ $entity->rooms->flatMap->equipment->count() }}</div>
                        <p class="text-sm text-gray-500 mt-1">Equipos</p>
                    </div>
                    <div class="text-center p-4 bg-amber-50 rounded-xl">
                        <div class="text-3xl font-bold text-amber-600">{{ $replacementsCount }}</div>
                        <p class="text-sm text-gray-500 mt-1">Reemplazos</p>
                    </div>
                </div>

                @if($climateProfile)
                    <div class="border-t border-gray-100 pt-4">
                        <h4 class="text-sm font-medium text-gray-700 flex items-center gap-2 mb-3">
                            <i class="bi bi-thermometer-half text-red-500"></i>
                            Perfil Climático
                        </h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <p class="text-xs text-gray-500">Zona</p>
                                <p class="font-semibold text-gray-900">{{ $climateProfile['climate_zone'] ?? 'N/A' }}</p>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <p class="text-xs text-gray-500">Temp. Media</p>
                                <p class="font-semibold text-gray-900">{{ $climateProfile['avg_temperature'] ?? 'N/A' }}°C</p>
                            </div>
                        </div>
                    </div>
                @endif
            </x-card>
        </div>

        {{-- Quick Actions --}}
        <section class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-4">
                <i class="bi bi-lightning-charge text-amber-500"></i>
                Acciones Rápidas
            </h2>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route($config['route_prefix'] . '.rooms', $entity->id) }}" 
                   class="group bg-white border border-gray-200 rounded-xl p-5 hover:shadow-md hover:border-gray-300 transition-all text-center">
                    <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-gray-200 transition-colors">
                        <i class="{{ $config['rooms_icon'] }} text-xl text-gray-600"></i>
                    </div>
                    <p class="font-medium text-gray-900">{{ $config['rooms_label'] }}</p>
                </a>
                
                <a href="{{ route($config['route_prefix'] . '.invoices', $entity->id) }}" 
                   class="group bg-white border border-gray-200 rounded-xl p-5 hover:shadow-md hover:border-gray-300 transition-all text-center">
                    <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-emerald-200 transition-colors">
                        <i class="bi bi-receipt text-xl text-emerald-600"></i>
                    </div>
                    <p class="font-medium text-gray-900">Facturas</p>
                </a>
                
                <a href="{{ route('consumption.panel') }}" 
                   class="group bg-white border border-gray-200 rounded-xl p-5 hover:shadow-md hover:border-gray-300 transition-all text-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-blue-200 transition-colors">
                        <i class="bi bi-bar-chart-line text-xl text-blue-600"></i>
                    </div>
                    <p class="font-medium text-gray-900">Consumo</p>
                </a>
                
                <a href="{{ route('usage_adjustments.index') }}" 
                   class="group bg-white border border-gray-200 rounded-xl p-5 hover:shadow-md hover:border-gray-300 transition-all text-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-purple-200 transition-colors">
                        <i class="bi bi-sliders text-xl text-purple-600"></i>
                    </div>
                    <p class="font-medium text-gray-900">Ajustes de Uso</p>
                </a>
            </div>
        </section>

        {{-- Recommendations --}}
        @if(count($recommendations) > 0)
            <section>
                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-4">
                    <i class="bi bi-lightbulb text-amber-500"></i>
                    Recomendaciones Disponibles
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($recommendations as $key => $rec)
                        @php
                            $routeName = $config['route_prefix'] . '.' . $key;
                            $colors = [
                                'warning' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-600'],
                                'danger' => ['bg' => 'bg-red-100', 'text' => 'text-red-600'],
                                'primary' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600'],
                                'secondary' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600'],
                                'info' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600'],
                                'success' => ['bg' => 'bg-green-100', 'text' => 'text-green-600'],
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
                                        <x-button variant="outline" size="sm" href="{{ route($routeName, $entity->id) }}">
                                            Ver más <i class="bi bi-arrow-right ml-1"></i>
                                        </x-button>
                                    @else
                                        <x-badge variant="default">Próximamente</x-badge>
                                    @endif
                                </div>
                            </div>
                        </x-card>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
@endsection
