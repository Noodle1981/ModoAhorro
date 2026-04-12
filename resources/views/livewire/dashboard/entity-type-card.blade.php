<div class="h-full" x-data="{ showUpgradeModal: false }">
    <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-xs hover:shadow-md transition-all flex flex-col h-full relative group overflow-hidden">
        
        {{-- Background Decoration --}}
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-linear-to-br {{ $config['tailwind_gradient'] ?? 'from-emerald-500/5 to-emerald-600/5' }} rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700"></div>

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8 relative">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-linear-to-br {{ $config['tailwind_gradient'] ?? 'from-emerald-500 to-emerald-600' }} rounded-2xl flex items-center justify-center text-white shadow-lg shadow-emerald-100">
                    <i class="{{ $config['icon'] }} text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black text-gray-900 uppercase tracking-tight">{{ $config['label_plural'] }}</h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ $entities->count() }} Registrados</p>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="flex-1 relative">
            @if(!$allowed)
                {{-- Locked State --}}
                <div class="text-center py-6 bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mx-auto mb-3 shadow-sm">
                        <i class="bi bi-lock-fill text-xl text-gray-300"></i>
                    </div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Módulo Bloqueado</p>
                    <button @click="showUpgradeModal = true" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-[10px] font-black text-indigo-600 uppercase tracking-widest hover:bg-indigo-50 hover:border-indigo-100 transition-all">
                        Upgrade Plan
                    </button>
                </div>
            @elseif($entities->isEmpty())
                {{-- Empty State --}}
                <div class="text-center py-10">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100">
                        <i class="{{ $config['icon_secondary'] }} text-2xl text-gray-200"></i>
                    </div>
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-6 leading-relaxed">No hay {{ strtolower($config['label_plural']) }}<br>configurados aún.</p>
                    <a href="{{ route($config['route_prefix'] . '.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-indigo-100 transition-all">
                        <i class="bi bi-plus-circle"></i> Nuevo Registro
                    </a>
                </div>
            @else
                {{-- Entity List --}}
                <div class="space-y-3">
                    @foreach($entities->take(3) as $entity)
                        <a href="{{ route($config['route_prefix'] . '.show', $entity->id) }}" class="flex items-center justify-between p-3 rounded-2xl bg-white border border-gray-50 hover:border-indigo-100 hover:bg-indigo-50/30 transition-all group/item">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-xl {{ $config['tailwind_bg'] ?? 'bg-emerald-50' }} flex shrink-0 items-center justify-center border border-transparent group-hover/item:border-white transition-colors">
                                    <i class="{{ $config['icon_secondary'] }} {{ $config['tailwind_text'] ?? 'text-emerald-600' }} text-lg"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-black text-gray-900 uppercase tracking-tighter truncate">{{ $entity->name }}</p>
                                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest truncate">{{ $entity->locality->name ?? 'Ubicación Pendiente' }}</p>
                                </div>
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 group-hover/item:bg-white group-hover/item:text-indigo-600 transition-all">
                                <i class="bi bi-chevron-right text-sm"></i>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if($entities->count() > 3)
                    <div class="mt-4 flex items-center gap-2">
                        <div class="flex -space-x-2">
                            @foreach($entities->slice(3, 3) as $e)
                                <div class="w-6 h-6 rounded-full bg-gray-100 border-2 border-white flex items-center justify-center text-[8px] font-black text-gray-400 uppercase">
                                    {{ substr($e->name, 0, 1) }}
                                </div>
                            @endforeach
                        </div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            +{{ $entities->count() - 3 }} Más
                        </p>
                    </div>
                @endif
            @endif
        </div>

        {{-- Footer --}}
        @if($allowed && $entities->isNotEmpty())
            <div class="mt-8 pt-6 border-t border-gray-50 flex gap-3 relative">
                <a href="{{ route($config['route_prefix'] . '.index') }}" 
                   class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-xl text-[10px] font-black text-gray-500 uppercase tracking-widest transition-all">
                    <span>Ver Listado</span>
                </a>
                <a href="{{ route($config['route_prefix'] . '.create') }}"
                   class="w-12 h-12 inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-lg shadow-indigo-100 transition-all">
                    <i class="bi bi-plus-lg text-lg"></i>
                </a>
            </div>
        @endif
    </div>

    {{-- Upgrade Modal --}}
    <div x-show="showUpgradeModal" 
         x-cloak
         @click.away="showUpgradeModal = false"
         class="fixed inset-0 z-100 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
         style="display: none;">
        <div @click.stop class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all border border-gray-100">
            <div class="bg-linear-to-r from-indigo-600 to-blue-700 px-8 py-6">
                <h3 class="text-white font-black text-lg uppercase tracking-widest flex items-center gap-3">
                    <i class="bi bi-shield-lock"></i>
                    Módulo Restringido
                </h3>
            </div>
            <div class="p-8 text-center">
                <div class="w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="bi bi-lightning-charge text-4xl text-indigo-300"></i>
                </div>
                <h4 class="text-2xl font-black text-gray-900 tracking-tighter uppercase mb-2">Función de Pago</h4>
                <p class="text-sm font-medium text-gray-500 mb-8 leading-relaxed">
                    Esta categoría no está incluida en tu plan actual. Actualiza a **Profesional** para gestionar múltiples tipos de entidades.
                </p>
                <div class="flex flex-col gap-3">
                    <button @click="showUpgradeModal = false" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black uppercase tracking-widest py-4 rounded-2xl shadow-xl shadow-indigo-100 transition-all">
                        Ver Planes y Precios
                    </button>
                    <button @click="showUpgradeModal = false" class="w-full font-black uppercase tracking-widest text-[10px] text-gray-400 py-3">
                        Quizás más tarde
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
