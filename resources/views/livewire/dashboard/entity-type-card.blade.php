<div class="h-full">
    <x-card :padding="false" class="h-full flex flex-col overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r {{ $config['tailwind_gradient'] ?? 'from-emerald-500 to-emerald-600' }} px-5 py-4 flex justify-between items-center">
            <h3 class="text-white font-semibold flex items-center gap-2">
                <i class="{{ $config['icon'] }}"></i>
                {{ $config['label_plural'] }}
            </h3>
            <x-badge variant="default" class="bg-white/20 text-white">{{ $entities->count() }}</x-badge>
        </div>

        {{-- Body --}}
        <div class="flex-1 p-5">
            @if(!$allowed)
                {{-- Locked State --}}
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-lock text-3xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 mb-3">Disponible en plan superior</p>
                    <x-button variant="outline" size="sm" href="#">
                        <i class="bi bi-arrow-up-circle mr-1"></i> Upgrade
                    </x-button>
                </div>
            @elseif($entities->isEmpty())
                {{-- Empty State --}}
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="{{ $config['icon_secondary'] }} text-3xl text-gray-300"></i>
                    </div>
                    <p class="text-gray-500 mb-3">No tienes {{ strtolower($config['label_plural']) }}</p>
                    <x-button variant="primary" size="sm" href="{{ route($config['route_prefix'] . '.create') }}">
                        <i class="bi bi-plus-circle mr-1"></i> Crear {{ $config['label'] }}
                    </x-button>
                </div>
            @else
                {{-- Entity List --}}
                <div class="space-y-3">
                    @foreach($entities->take(3) as $entity)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg {{ $config['tailwind_bg'] ?? 'bg-emerald-100' }} flex items-center justify-center">
                                    <i class="{{ $config['icon_secondary'] }} {{ $config['tailwind_text'] ?? 'text-emerald-600' }}"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $entity->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $entity->locality->name ?? 'Sin ubicación' }}</p>
                                </div>
                            </div>
                            <x-button variant="ghost" size="xs" href="{{ route($config['route_prefix'] . '.show', $entity->id) }}" class="opacity-0 group-hover:opacity-100">
                                <i class="bi bi-eye"></i>
                            </x-button>
                        </div>
                    @endforeach
                </div>

                @if($entities->count() > 3)
                    <p class="text-center text-xs text-gray-400 mt-3">
                        +{{ $entities->count() - 3 }} más
                    </p>
                @endif
            @endif
        </div>

        {{-- Footer --}}
        @if($allowed)
            <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex gap-2">
                <x-button variant="outline" size="sm" href="{{ route($config['route_prefix'] . '.index') }}" class="flex-1">
                    <i class="bi bi-list mr-1"></i> Ver todos
                </x-button>
                <x-button variant="primary" size="sm" href="{{ route($config['route_prefix'] . '.create') }}">
                    <i class="bi bi-plus"></i>
                </x-button>
            </div>
        @endif
    </x-card>
</div>
