<nav class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            {{-- Logo --}}
            <div class="flex items-center">
                <a href="/dashboard" class="flex items-center gap-2">
                    <img src="{{ asset('logo.png') }}" alt="Modo Ahorro" class="h-8 w-auto">
                </a>
            </div>

            {{-- Desktop Menu --}}
            <div class="hidden md:flex items-center gap-6">
                @auth
                    @if(auth()->user()->is_super_admin)
                        {{-- Super Admin Menu --}}
                        <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-indigo-600 font-medium transition-colors">
                            <i class="bi bi-shield-check mr-1"></i> Admin Dashboard
                        </a>
                    @else
                        {{-- Regular User Menu --}}
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-emerald-600 font-medium transition-colors">
                            <i class="bi bi-speedometer2 mr-1"></i> Dashboard
                        </a>
                        <a href="{{ route('equipment.index') }}" class="text-gray-600 hover:text-emerald-600 font-medium transition-colors">
                            <i class="bi bi-plug mr-1"></i> Equipos
                        </a>
                        <a href="{{ route('consumption.panel') }}" class="text-gray-600 hover:text-emerald-600 font-medium transition-colors">
                            <i class="bi bi-bar-chart mr-1"></i> Consumo
                        </a>
                    @endif
                    
                    {{-- User Dropdown --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-2 text-gray-700 hover:text-emerald-600 transition-colors">
                            <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center">
                                <i class="bi bi-person text-emerald-600"></i>
                            </div>
                            <span class="font-medium">{{ auth()->user()->name }}</span>
                            <i class="bi bi-chevron-down text-xs"></i>
                        </button>
                        
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition
                             class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-2"
                             style="display: none;">
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">
                                <i class="bi bi-person mr-2"></i> Mi Perfil
                            </a>
                            <a href="#" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">
                                <i class="bi bi-gear mr-2"></i> Configuración
                            </a>
                            <hr class="my-2 border-gray-100">
                            <a href="/logout" class="block px-4 py-2 text-red-600 hover:bg-red-50">
                                <i class="bi bi-box-arrow-right mr-2"></i> Cerrar sesión
                            </a>
                        </div>
                    </div>
                @endauth
                
                @guest
                    <a href="/login" class="text-gray-600 hover:text-emerald-600 font-medium transition-colors">
                        Iniciar sesión
                    </a>
                    <a href="/register" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        Registrarse
                    </a>
                @endguest
            </div>

            {{-- Mobile Menu Button --}}
            <div class="md:hidden flex items-center">
                <button x-data @click="$dispatch('toggle-mobile-menu')" class="text-gray-600 hover:text-emerald-600 p-2">
                    <i class="bi bi-list text-2xl"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-data="{ open: false }" 
         @toggle-mobile-menu.window="open = !open"
         x-show="open" 
         x-transition
         class="md:hidden border-t border-gray-100 bg-white"
         style="display: none;">
        <div class="px-4 py-3 space-y-2">
            @auth
                @if(auth()->user()->is_super_admin)
                    {{-- Super Admin Mobile Menu --}}
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50">
                        <i class="bi bi-shield-check mr-2"></i> Admin Dashboard
                    </a>
                @else
                    {{-- Regular User Mobile Menu --}}
                    <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50">
                        <i class="bi bi-speedometer2 mr-2"></i> Dashboard
                    </a>
                    <a href="{{ route('equipment.index') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50">
                        <i class="bi bi-plug mr-2"></i> Equipos
                    </a>
                    <a href="{{ route('consumption.panel') }}" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50">
                        <i class="bi bi-bar-chart mr-2"></i> Consumo
                    </a>
                @endif
                <hr class="my-2 border-gray-100">
                <div class="px-3 py-2 text-gray-500 text-sm">{{ auth()->user()->name }}</div>
                <a href="/logout" class="block px-3 py-2 rounded-lg text-red-600 hover:bg-red-50">
                    <i class="bi bi-box-arrow-right mr-2"></i> Cerrar sesión
                </a>
            @endauth
            @guest
                <a href="/login" class="block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50">
                    Iniciar sesión
                </a>
                <a href="/register" class="block px-3 py-2 rounded-lg bg-emerald-500 text-white text-center">
                    Registrarse
                </a>
            @endguest
        </div>
    </div>
</nav>
