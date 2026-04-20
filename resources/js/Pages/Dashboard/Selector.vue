<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { 
    Building, 
    Plus, 
    Lock, 
    ArrowRight,
    LogOut,
    Home,
    ShoppingBag,
    Factory
} from 'lucide-vue-next';

const props = defineProps({
    user: Object,
    plan: Object,
    entitiesByType: Array,
});

// Mapping for Lucide Icons
const iconMap = {
    'hogar': Home,
    'comercio': ShoppingBag,
    'industria': Factory
};
</script>

<template>
    <Head title="Selector de Entidades" />

    <div class="min-h-screen bg-energy-surface flex flex-col">
        <!-- Header / Perfil -->
        <header class="bg-white border-b border-slate-100 py-4 px-6 sm:px-12 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-energy-consumption rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-100 font-black">
                    MA
                </div>
                <div>
                    <h2 class="text-sm font-black text-slate-900 leading-tight">{{ user.name }}</h2>
                    <p class="text-[10px] font-black text-energy-consumption uppercase tracking-widest leading-none mt-1">Plan {{ plan.name }}</p>
                </div>
            </div>
            <Link :href="route('logout')" method="post" as="button" class="text-[10px] font-black text-slate-400 hover:text-energy-critical uppercase tracking-widest transition-colors flex items-center gap-2">
                <LogOut :size="14" />
                Cerrar Sesión
            </Link>
        </header>

        <main class="flex-1 p-6 sm:p-12 max-w-6xl mx-auto w-full">
            <div class="mb-12">
                <h1 class="text-4xl font-black text-slate-900 tracking-tight leading-tight">
                    Bienvenido, {{ user.name.split(' ')[0] }}
                </h1>
                <p class="text-slate-500 font-medium mt-2">Gestione la eficiencia de sus <span class="text-energy-consumption font-bold">Entidades</span>.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div v-for="type in entitiesByType" :key="type.type" 
                    :class="['group relative flex flex-col h-full rounded-3xl border transition-all duration-500', 
                        type.enabled 
                        ? 'bg-white border-slate-100 shadow-xl shadow-slate-200/40 hover:shadow-2xl hover:shadow-blue-900/5 hover:-translate-y-2' 
                        : 'bg-slate-100 border-slate-200 grayscale opacity-70 cursor-not-allowed'
                    ]"
                >
                    <!-- Header Tarjeta -->
                    <div class="p-8 pb-0">
                        <div :class="['w-16 h-16 rounded-2xl flex items-center justify-center mb-6 transition-transform group-hover:scale-110 shadow-sm', type.enabled ? 'bg-energy-consumption text-white' : 'bg-slate-200 text-slate-400']">
                            <component :is="iconMap[type.type] || Building" :size="32" stroke-width="2.5" />
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ type.name }}</h3>
                        <p class="text-sm font-medium text-slate-500 mt-2">{{ type.enabled ? 'Acceda a sus espacios gestionados.' : 'Próximamente disponible.' }}</p>
                    </div>

                    <!-- Listado de Entidades Existentes -->
                    <div class="flex-1 p-8 space-y-3">
                        <template v-if="type.entities.length > 0">
                            <div v-for="entity in type.entities" :key="entity.id" class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl hover:bg-blue-50 transition-colors group/item border border-transparent hover:border-blue-100">
                                <div class="overflow-hidden">
                                    <h4 class="text-sm font-bold text-slate-800 truncate">{{ entity.name }}</h4>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest truncate">{{ entity.locality?.name || 'Ubicación' }}</p>
                                </div>
                                <Link :href="route('entities.activate', entity.id)" class="p-2 bg-white rounded-xl shadow-sm text-energy-consumption hover:bg-energy-consumption hover:text-white transition-all opacity-0 group-hover/item:opacity-100">
                                    <ArrowRight :size="16" stroke-width="3" />
                                </Link>
                            </div>
                        </template>
                        
                        <div v-else-if="type.enabled" class="py-6 text-center border-2 border-dashed border-slate-100 rounded-3xl">
                            <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest italic">Sin entidades creadas</span>
                        </div>
                    </div>

                    <!-- Botón Añadir -->
                    <div class="p-8 pt-0 mt-auto">
                        <button v-if="type.enabled && type.can_add" class="w-full bg-slate-900 text-white py-4 px-4 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-energy-consumption transition-all flex items-center justify-center gap-2 shadow-lg shadow-slate-200">
                             <Plus :size="18" stroke-width="3" />
                             Nueva {{ type.name }}
                        </button>
                        <div v-else-if="!type.enabled" class="flex items-center justify-center gap-2 py-4 text-slate-400 font-black text-[10px] uppercase tracking-widest">
                            <Lock :size="14" />
                            Bloqueado
                        </div>
                        <div v-else-if="!type.can_add" class="text-center p-4 bg-amber-50 rounded-2xl border border-amber-100">
                            <span class="text-[10px] font-black text-amber-700 uppercase tracking-widest leading-tight block">Límite alcanzado</span>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</template>

