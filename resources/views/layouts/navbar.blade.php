<nav x-data="{ open: false }" class="border-b border-gray-100 shadow-xs backdrop-blur-md bg-white/90 sticky top-0 z-60">
    <div class="px-6 h-16 flex items-center justify-between">
        {{-- Logo --}}
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <div class="w-10 h-10 bg-linear-to-br from-indigo-500 to-indigo-700 rounded-xl flex items-center justify-center text-white shadow-sm">
                <i class="bi bi-lightning-charge-fill text-lg"></i>
            </div>
            <span class="text-lg font-black text-gray-900 tracking-tighter uppercase">Modo<span class="text-indigo-600">Ahorro</span></span>
        </a>

        {{-- Toggle Button --}}
        <button @click="open = !open" class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-50 border border-gray-100 text-gray-500 hover:text-indigo-600 transition-colors">
            <i class="bi" :class="open ? 'bi-x-lg' : 'bi-list-nested text-2xl'"></i>
        </button>
    </div>

    {{-- Mobile Menu Panel --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="absolute top-16 left-0 w-full bg-white border-b border-gray-100 shadow-2xl py-6 px-6 space-y-6 z-55"
         style="display: none;">
        
        {{-- Profile info --}}
        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl">
            <div class="w-12 h-12 rounded-full bg-white flex items-center justify-center text-indigo-600 font-bold border border-gray-100 shadow-xs">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div>
                <p class="text-sm font-black text-gray-900 uppercase tracking-tighter">{{ auth()->user()->name }}</p>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Panel de Control Móvil</p>
            </div>
        </div>

        {{-- Nav Links --}}
        <div class="space-y-1">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-4 p-4 rounded-2xl {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-lg' : 'text-gray-500 hover:bg-indigo-50 hover:text-indigo-600' }} transition-all">
                <i class="bi bi-grip-vertical text-xl"></i>
                <span class="text-sm font-black uppercase tracking-tight">Dashboard Central</span>
            </a>
            <a href="{{ route('contracts.index') }}" class="flex items-center gap-4 p-4 rounded-2xl {{ request()->routeIs('contracts.*') ? 'bg-indigo-600 text-white shadow-lg' : 'text-gray-500 hover:bg-indigo-50 hover:text-indigo-600' }} transition-all">
                <i class="bi bi-file-earmark-text text-xl"></i>
                <span class="text-sm font-black uppercase tracking-tight">Contratos y Suministros</span>
            </a>
            @php $firstEntity = auth()->user()->entities()->first(); @endphp
            <a href="{{ $firstEntity ? route('rooms.index', ['entity' => $firstEntity->id]) : '#' }}" class="flex items-center gap-4 p-4 rounded-2xl {{ request()->routeIs('rooms.*') ? 'bg-indigo-600 text-white shadow-lg' : 'text-gray-500 hover:bg-indigo-50 hover:text-indigo-600' }} transition-all">
                <i class="bi bi-house-door text-xl"></i>
                <span class="text-sm font-black uppercase tracking-tight">Ambientes y Equipos</span>
            </a>
            <a href="{{ route('consumption.panel') }}" class="flex items-center gap-4 p-4 rounded-2xl {{ request()->routeIs('consumption.*') ? 'bg-indigo-600 text-white shadow-lg' : 'text-gray-500 hover:bg-indigo-50 hover:text-indigo-600' }} transition-all">
                <i class="bi bi-bar-chart-lines text-xl"></i>
                <span class="text-sm font-black uppercase tracking-tight">Paneles de Consumo</span>
            </a>
        </div>

        {{-- Actions --}}
        <div class="pt-4 border-t border-gray-50 flex flex-col gap-3">
            <a href="/logout" class="flex items-center justify-center p-4 rounded-2xl bg-red-50 text-red-600 text-sm font-black uppercase tracking-widest transition-all">
                Cerrar Sesión
            </a>
        </div>
    </div>
</nav>
