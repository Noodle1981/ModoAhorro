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

        <div class="mb-8">
            <x-card :padding="false">
                <div class="bg-gradient-to-r {{ $config['tailwind_gradient'] ?? 'from-emerald-500 to-emerald-600' }} px-6 py-4 rounded-t-xl flex justify-between items-center">
                    <h3 class="text-white font-semibold flex items-center gap-2">
                        <i class="bi bi-info-circle"></i>
                        Vista General
                    </h3>
                    <x-badge variant="white" class="bg-white/20 text-white border-none">
                        {{ ucfirst($entity->type) }}
                    </x-badge>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-0">
                    {{-- General Info Column --}}
                    <div class="lg:col-span-7 p-6 border-b lg:border-b-0 lg:border-r border-gray-100">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Detalles de Ubicación</h4>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                            <div class="flex justify-between items-center py-2 border-b border-gray-50 md:border-none">
                                <dt class="text-gray-500 text-sm">Dirección</dt>
                                <dd class="font-medium text-gray-900 text-sm text-right">{{ $entity->address_street }}</dd>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-50 md:border-none">
                                <dt class="text-gray-500 text-sm">Localidad</dt>
                                <dd class="font-medium text-gray-900 text-sm text-right">{{ $entity->locality->name ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-50 md:border-none">
                                <dt class="text-gray-500 text-sm">Código Postal</dt>
                                <dd class="font-medium text-gray-900 text-sm text-right">{{ $entity->address_postal_code }}</dd>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-50 md:border-none">
                                <dt class="text-gray-500 text-sm">Metros²</dt>
                                <dd class="font-medium text-gray-900 text-sm text-right">{{ $entity->square_meters }} m²</dd>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-gray-50 md:border-none">
                                <dt class="text-gray-500 text-sm">{{ $config['people_label'] }}</dt>
                                <dd class="font-medium text-gray-900 text-sm text-right">{{ $entity->people_count }}</dd>
                            </div>
                            @if($config['has_business_hours'])
                                <div class="flex justify-between items-center py-2 border-b border-gray-50 md:border-none">
                                    <dt class="text-gray-500 text-sm">Horario</dt>
                                    <dd class="font-medium text-gray-900 text-sm text-right">
                                        @if($entity->opens_at && $entity->closes_at)
                                            {{ \Carbon\Carbon::parse($entity->opens_at)->format('H:i') }} - {{ \Carbon\Carbon::parse($entity->closes_at)->format('H:i') }}
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    {{-- Stats Column --}}
                    <div class="lg:col-span-5 p-6 bg-gray-50/50">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Estadísticas y Clima</h4>
                        
                        <div class="grid grid-cols-3 gap-3 mb-6">
                            <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm text-center">
                                <div class="text-xl font-bold text-blue-600">{{ $entity->rooms->count() }}</div>
                                <p class="text-[10px] text-gray-500 uppercase">{{ $config['rooms_label'] }}</p>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm text-center">
                                <div class="text-xl font-bold text-emerald-600">{{ $entity->rooms->flatMap->equipment->count() }}</div>
                                <p class="text-[10px] text-gray-500 uppercase">Equipos</p>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-gray-100 shadow-sm text-center">
                                <div class="text-xl font-bold text-amber-600">{{ $replacementsCount }}</div>
                                <p class="text-[10px] text-gray-500 uppercase">Ahorros</p>
                            </div>
                        </div>

                        @if($climateProfile)
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center text-sm">
                                        <i class="bi bi-thermometer-half"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex justify-between">
                                            <span class="text-xs text-gray-500">Zona Climática</span>
                                            <span class="text-xs font-semibold text-gray-900">{{ $climateProfile['climate_zone'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm">
                                        <i class="bi bi-sun"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex justify-between">
                                            <span class="text-xs text-gray-500">Temp. Media Anual</span>
                                            <span class="text-xs font-semibold text-gray-900">{{ $climateProfile['avg_temperature'] ?? 'N/A' }}°C</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Energy Performance (New V3 Feature) --}}
        <section class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2 mb-4">
                <i class="bi bi-shield-check text-emerald-500"></i>
                Desempeño Energético
            </h2>
            
            <x-card>
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                    {{-- Energy Label --}}
                    <div class="md:col-span-4 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r border-gray-100 pb-6 md:pb-0">
                        <p class="text-sm font-medium text-gray-500 mb-4 text-center">Clasificación de la Entidad</p>
                        @php
                            $hasProfile = !empty($entity->thermal_profile['energy_label']);
                            $label = $entity->thermal_profile['energy_label'] ?? '?';
                            $score = $entity->thermal_profile['thermal_score'] ?? 0;
                            $colors = [
                                'A' => 'bg-green-600',
                                'B' => 'bg-lime-500',
                                'C' => 'bg-yellow-400',
                                'D' => 'bg-orange-500',
                                'E' => 'bg-red-600',
                                '?' => 'bg-gray-400',
                            ];
                            $bgColor = $colors[$label] ?? 'bg-gray-400';
                        @endphp
                        
                        <div class="relative w-32 h-32 flex items-center justify-center">
                            @if($hasProfile)
                                <div class="absolute inset-0 {{ $bgColor }} opacity-10 rounded-full animate-pulse"></div>
                            @endif
                            <div class="w-24 h-24 {{ $bgColor }} rounded-2xl flex items-center justify-center shadow-lg transform rotate-45 transition-transform duration-500">
                                <span class="text-4xl font-extrabold text-white transform -rotate-45 transition-transform duration-500">{{ $label }}</span>
                            </div>
                        </div>
                        <p class="mt-6 text-2xl font-bold text-gray-900">{{ $hasProfile ? $score . '/100' : 'N/A' }}</p>
                        <p class="text-sm text-gray-500">Score de Salud Térmica</p>
                    </div>

                    {{-- Profile Details --}}
                    <div class="md:col-span-8">
                        @if($hasProfile)
                            <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-6">Características Destacadas</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                @php
                                    $features = [
                                        ['icon' => 'bi bi-layers', 'label' => 'Techo', 'val' => $entity->thermal_profile['roof_type'] ?? 'No especificado'],
                                        ['icon' => 'bi bi-window', 'label' => 'Ventanas', 'val' => $entity->thermal_profile['window_type'] ?? 'No especificado'],
                                        ['icon' => 'bi bi-wind', 'label' => 'Aislación', 'val' => ($entity->thermal_profile['roof_insulation'] ?? false) ? 'Reforzada' : 'Estándar'],
                                        ['icon' => 'bi bi-brightness-high', 'label' => 'Exposición Solar', 'val' => $entity->thermal_profile['sun_exposure'] ?? 'Normal'],
                                    ];
                                @endphp
                                
                                @foreach($features as $f)
                                    <div class="flex items-start gap-3">
                                        <div class="mt-1 w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-emerald-600">
                                            <i class="{{ $f['icon'] }}"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">{{ $f['label'] }}</p>
                                            <p class="font-medium text-gray-900 text-sm italic">
                                                {{ str_replace('_', ' ', ucfirst($f['val'])) }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="bg-amber-50 rounded-xl p-6 border border-amber-100 mb-4 inline-block mx-auto">
                                    <i class="bi bi-exclamation-triangle text-amber-500 text-3xl"></i>
                                </div>
                                <h4 class="text-gray-900 font-bold">Diagnóstico Pendiente</h4>
                                <p class="text-gray-500 text-sm max-w-md mx-auto mt-2">
                                    Aún no has clasificado el desempeño térmico de esta entidad. Realiza el diagnóstico para obtener recomendaciones personalizadas.
                                </p>
                            </div>
                        @endif

                        <div class="mt-8 pt-6 border-t border-gray-100 flex justify-between items-center">
                            <p class="text-sm text-gray-500 italic">
                                * Esta clasificación se basa en la envolvente térmica del hogar.
                            </p>
                            <x-button variant="{{ $hasProfile ? 'outline' : 'warning' }}" size="sm" href="{{ route('thermal.wizard', $entity->id) }}">
                                <i class="bi {{ $hasProfile ? 'bi-arrow-repeat' : 'bi-play-fill' }} mr-2"></i> 
                                {{ $hasProfile ? 'Rehacer Diagnóstico' : 'Comenzar Diagnóstico' }}
                            </x-button>
                        </div>
                    </div>
                </div>
            </x-card>
        </section>

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
