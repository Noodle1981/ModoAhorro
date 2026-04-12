<div class="w-72 h-screen sticky top-0 bg-white border-r border-gray-100 flex flex-col shadow-xs overflow-hidden">
    {{-- Sidebar Header --}}
    <div class="px-8 py-8 -mb-4">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
            <div class="w-12 h-12 bg-linear-to-br from-indigo-500 to-indigo-700 rounded-2xl flex items-center justify-center text-white shadow-lg group-hover:scale-105 transition-transform">
                <i class="bi bi-lightning-charge-fill text-xl"></i>
            </div>
            <div>
                <span class="text-xl font-black text-gray-900 tracking-tighter uppercase leading-none block">Modo<span class="text-indigo-600">Ahorro</span></span>
                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-1 block">Energía Inteligente</span>
            </div>
        </a>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-4 py-8 space-y-8 scrollbar-hide">
        
        {{-- Section: Principal --}}
        <div>
            <h3 class="px-4 text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Principal</h3>
            <div class="space-y-1">
                <x-sidebar-link href="{{ route('dashboard') }}" icon="bi-grid-1x2" active="{{ request()->routeIs('dashboard') }}">
                    Dashboard
                </x-sidebar-link>
            </div>
        </div>

        {{-- Section: Gestión Física --}}
        <div>
            <h3 class="px-4 text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Gestión Física</h3>
            <div class="space-y-1">
                <x-sidebar-link href="{{ route('contracts.index') }}" icon="bi-file-earmark-text" active="{{ request()->routeIs('contracts.*') || request()->routeIs('meter') }}">
                    Contratos
                </x-sidebar-link>
                @php $firstEntity = auth()->user()->entities()->first(); @endphp
                <x-sidebar-link href="{{ $firstEntity ? route('rooms.index', ['entity' => $firstEntity->id]) : '#' }}" icon="bi-house-door" active="{{ request()->routeIs('rooms.*') }}">
                    Infraestructura
                </x-sidebar-link>
            </div>
        </div>

        {{-- Section: Recomendaciones --}}
        <div>
            <h3 class="px-4 text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Análisis y Ahorro</h3>
            <div class="space-y-1">
                <x-sidebar-link href="{{ route('consumption.panel') }}" icon="bi-bar-chart-lines" active="{{ request()->routeIs('consumption.*') }}">
                    Consumo Real
                </x-sidebar-link>
                <x-sidebar-link href="{{ $firstEntity ? route('entities.budget', ['entity' => $firstEntity->id]) : '#' }}" icon="bi-wallet2" active="{{ request()->routeIs('entities.budget') }}">
                    Presupuesto
                </x-sidebar-link>
                <x-sidebar-link href="{{ $firstEntity ? route('grid.optimization', ['entity' => $firstEntity->id]) : '#' }}" icon="bi-graph-up-arrow" active="{{ request()->routeIs('grid.optimization') }}">
                    Optimización Red
                </x-sidebar-link>
                <x-sidebar-link href="{{ $firstEntity ? route('maintenance.index', ['entity' => $firstEntity->id]) : '#' }}" icon="bi-wrench-adjustable" active="{{ request()->routeIs('maintenance.*') }}">
                    Mantenimiento
                </x-sidebar-link>
            </div>
        </div>

        @if(auth()->user()->is_super_admin)
            {{-- Section: Admin --}}
            <div class="pt-4 mt-4 border-t border-gray-50">
                <h3 class="px-4 text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Sistema</h3>
                <div class="space-y-1">
                    <x-sidebar-link href="{{ route('admin.dashboard') }}" icon="bi-shield-lock" active="{{ request()->routeIs('admin.*') }}">
                        Administración
                    </x-sidebar-link>
                    <x-sidebar-link href="/efficiency-benchmarks" icon="bi-sliders" active="{{ request()->routeIs('efficiency-benchmarks.*') }}">
                        Benchmarks
                    </x-sidebar-link>
                </div>
            </div>
        @endif
    </nav>

    {{-- Sidebar Footer --}}
    <div class="p-6 border-t border-gray-50">
        <div class="bg-indigo-50 rounded-2xl p-4 flex items-center justify-between group">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-indigo-600 font-bold shadow-sm">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-black text-gray-900 truncate uppercase tracking-tighter">{{ auth()->user()->name }}</p>
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest truncate">Usuario Premium</p>
                </div>
            </div>
            <a href="/logout" class="text-gray-400 hover:text-red-500 transition-colors">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</div>
