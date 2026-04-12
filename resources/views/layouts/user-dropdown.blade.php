<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="flex items-center gap-3 p-1 rounded-2xl hover:bg-gray-50 transition-all group">
        <div class="w-10 h-10 bg-linear-to-br from-gray-100 to-gray-200 rounded-xl flex items-center justify-center text-gray-700 font-bold border border-gray-100 shadow-xs group-hover:border-indigo-100 transition-colors">
            {{ substr(auth()->user()->name, 0, 1) }}
        </div>
        <div class="hidden md:block text-left">
            <p class="text-[11px] font-black text-gray-900 uppercase tracking-tighter leading-none">{{ auth()->user()->name }}</p>
            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-1">Conectado</p>
        </div>
        <i class="bi bi-chevron-down text-gray-300 text-xs ml-2"></i>
    </button>
    
    <div x-show="open" 
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         class="absolute right-0 mt-3 w-56 bg-white rounded-3xl shadow-2xl border border-gray-100 py-3 z-60"
         style="display: none;">
        
        <div class="px-5 py-3 border-b border-gray-50 mb-2">
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Mi Cuenta</p>
            <p class="text-xs font-black text-gray-800 truncate">{{ auth()->user()->email }}</p>
        </div>

        <a href="#" class="flex items-center gap-3 px-5 py-2.5 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50 transition-colors">
            <i class="bi bi-person-badge text-lg opacity-60"></i>
            <span class="text-xs font-bold">Perfil de Usuario</span>
        </a>
        <a href="#" class="flex items-center gap-3 px-5 py-2.5 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50 transition-colors">
            <i class="bi bi-shield-check text-lg opacity-60"></i>
            <span class="text-xs font-bold">Seguridad y Acceso</span>
        </a>

        <div class="h-px bg-gray-50 my-2"></div>

        <a href="/logout" class="flex items-center gap-3 px-5 py-2.5 text-red-500 hover:bg-red-50 transition-colors">
            <i class="bi bi-box-arrow-right text-lg opacity-60"></i>
            <span class="text-xs font-bold">Cerrar Sesión</span>
        </a>
    </div>
</div>
