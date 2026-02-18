<div class="h-full" x-data="{ showUpgradeModal: false }">
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
                    <button @click="showUpgradeModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                        <i class="bi bi-arrow-up-circle"></i> Upgrade
                    </button>
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
                <a href="{{ route($config['route_prefix'] . '.index') }}" 
                   class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 hover:border-gray-300 transition-all group no-underline">
                    <i class="bi bi-grid-3x3-gap text-gray-500 group-hover:text-gray-700"></i>
                    <span>Ver todos</span>
                </a>
                <a href="{{ route($config['route_prefix'] . '.create') }}"
                   class="inline-flex items-center justify-center w-10 h-10 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white rounded-lg shadow-sm hover:shadow-md transition-all no-underline">
                    <i class="bi bi-plus text-lg"></i>
                </a>
            </div>
        @endif
    </x-card>

    {{-- Upgrade Modal --}}
    <div x-show="showUpgradeModal" 
         x-cloak
         @click.away="showUpgradeModal = false"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         style="display: none;">
        <div @click.stop class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all">
            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4 flex items-center justify-between">
                <h3 class="text-white font-semibold text-lg flex items-center gap-2">
                    <i class="bi bi-info-circle"></i>
                    Funcionalidad No Disponible
                </h3>
                <button @click="showUpgradeModal = false" class="text-white/80 hover:text-white transition-colors">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>
            
            {{-- Modal Body --}}
            <div class="p-6 text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-lock text-3xl text-blue-600"></i>
                </div>
                <h4 class="text-xl font-bold text-gray-900 mb-2">Esta opción no está disponible</h4>
                <p class="text-gray-600 mb-6">
                    Esta funcionalidad no está habilitada en la versión demo. Para acceder a {{ strtolower($config['label_plural']) }}, contactá con el administrador.
                </p>
                
                {{-- Modal Actions --}}
                <button @click="showUpgradeModal = false" 
                        class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-3 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                    Entendido
                </button>
            </div>
        </div>
    </div>
</div>
