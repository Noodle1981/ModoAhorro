<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    Building, 
    Plus, 
    Lock, 
    ArrowRight,
    LogOut,
    Home,
    ShoppingBag,
    Factory,
    Trash2,
    AlertTriangle
} from 'lucide-vue-next';

defineProps({
    user: Object,
    plan: Object,
    entitiesByType: Array,
});

// Mapping for Lucide Icons
const iconMap = {
    'hogar': Home,
    'comercio': ShoppingBag,
    'oficina': Building,
    'industria': Factory
};

// Dynamic Hover classes map for buttons
const hoverBgMap = {
    'hogar': 'hover:bg-emerald-600',
    'comercio': 'hover:bg-purple-600',
    'oficina': 'hover:bg-blue-600'
};

// Creation State
const showCreateModal = ref(false);
const entityTypeToCreate = ref('');
const newEntityName = ref('');

const openCreateModal = (type) => {
    entityTypeToCreate.value = type;
    const defaultName = type === 'comercio' ? 'Nuevo Comercio' : (type === 'oficina' ? 'Nueva Oficina' : 'Nuevo Hogar');
    newEntityName.value = defaultName;
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    entityTypeToCreate.value = '';
    newEntityName.value = '';
};

const confirmCreate = () => {
    const trimmedName = newEntityName.value.trim();
    if (!trimmedName) return;
    
    router.post(route('entities.store'), { 
        type: entityTypeToCreate.value, 
        name: trimmedName 
    }, {
        onSuccess: () => {
            closeCreateModal();
        }
    });
};

// Deletion State
const showDeleteModal = ref(false);
const entityToDelete = ref(null);
const deleteConfirmationText = ref('');

const openDeleteModal = (entity) => {
    entityToDelete.value = entity;
    deleteConfirmationText.value = '';
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    entityToDelete.value = null;
    deleteConfirmationText.value = '';
};

const confirmDelete = () => {
    if (!entityToDelete.value || deleteConfirmationText.value.trim().toUpperCase() !== 'BORRAR') return;
    
    router.delete(route('entities.destroy', entityToDelete.value.id), {
        onSuccess: () => {
            closeDeleteModal();
        }
    });
};
</script>

<template>
    <Head title="Selector de Entidades" />

    <div class="min-h-screen bg-energy-surface flex flex-col">
        <!-- Header / Perfil -->
        <header class="bg-white border-b border-slate-100 py-4 px-6 sm:px-12 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-4">
                <Link href="/" class="transition-transform hover:scale-105">
                    <img src="/images/landing/logo.png" alt="ModoAhorro Logo" class="w-10 h-10 object-contain drop-shadow-sm" />
                </Link>
                <div>
                    <h2 class="text-sm font-black text-slate-900 leading-tight">{{ user.name }}</h2>
                    <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest leading-none mt-1">Plan {{ plan.name }}</p>
                </div>
            </div>
            <Link :href="route('logout')" method="post" as="button" class="text-[10px] font-black text-slate-400 hover:text-rose-600 uppercase tracking-widest transition-colors flex items-center gap-2 cursor-pointer" title="Cerrar sesión completamente y salir a la portada">
                <LogOut :size="14" />
                Cerrar Sesión
            </Link>
        </header>

        <main class="flex-1 p-6 sm:p-12 max-w-6xl mx-auto w-full">
            <div class="mb-12">
                <h1 class="text-4xl font-black text-slate-900 tracking-tight leading-tight">
                    Bienvenido, {{ user.name.split(' ')[0] }}
                </h1>
                <p class="text-slate-500 font-medium mt-2">Gestione la eficiencia de sus <span class="text-emerald-600 font-bold">Entidades</span>.</p>
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
                        <div :class="['w-16 h-16 rounded-2xl flex items-center justify-center mb-6 transition-transform group-hover:scale-110 shadow-sm', type.enabled ? `${type.tailwind_bg} ${type.tailwind_text}` : 'bg-slate-200 text-slate-400']">
                            <component :is="iconMap[type.type] || Building" :size="32" stroke-width="2.5" />
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ type.name }}</h3>
                        <p class="text-sm font-medium text-slate-500 mt-2">{{ type.enabled ? 'Acceda a sus espacios gestionados.' : 'Próximamente disponible.' }}</p>
                    </div>

                    <!-- Listado de Entidades Existentes -->
                    <div class="flex-1 p-8 space-y-3 max-h-[300px] overflow-y-auto">
                        <template v-if="type.entities.length > 0">
                            <div v-for="entity in type.entities" :key="entity.id" class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl hover:bg-blue-50 transition-colors group/item border border-transparent hover:border-blue-100">
                                <div class="overflow-hidden flex-1 mr-2">
                                    <h4 class="text-sm font-bold text-slate-800 truncate">{{ entity.name }}</h4>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest truncate">{{ entity.locality?.name || 'Ubicación' }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button 
                                        @click.stop="openDeleteModal(entity)"
                                        class="p-2 bg-white rounded-xl shadow-sm text-slate-400 hover:text-energy-critical hover:bg-rose-50 transition-all opacity-0 group-hover/item:opacity-100"
                                        title="Eliminar Entidad"
                                    >
                                        <Trash2 :size="16" stroke-width="2.5" />
                                    </button>
                                    <Link :href="route('entities.activate', entity.id)" class="p-2 bg-white rounded-xl shadow-sm text-energy-consumption hover:bg-energy-consumption hover:text-white transition-all opacity-0 group-hover/item:opacity-100">
                                        <ArrowRight :size="16" stroke-width="3" />
                                    </Link>
                                </div>
                            </div>
                        </template>
                        
                        <div v-else-if="type.enabled" class="py-6 text-center border-2 border-dashed border-slate-100 rounded-3xl">
                            <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest italic">Sin entidades creadas</span>
                        </div>
                    </div>

                    <div class="p-8 pt-0 mt-auto">
                        <button 
                            v-if="type.enabled && type.can_add" 
                            @click="openCreateModal(type.type)"
                            :class="[
                                'w-full bg-slate-900 text-white py-4 px-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all flex items-center justify-center gap-2 shadow-lg shadow-slate-200',
                                hoverBgMap[type.type] || 'hover:bg-energy-consumption'
                            ]"
                        >
                             <Plus :size="18" stroke-width="3" />
                             {{ type.type === 'oficina' ? 'Nueva' : 'Nuevo' }} {{ type.name }}
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

        <!-- Create Entity Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xl animate-in fade-in duration-300" @click="closeCreateModal"></div>
            
            <div class="relative bg-white rounded-[40px] shadow-2xl w-full max-w-lg overflow-hidden animate-in zoom-in-95 duration-300 border border-slate-100 border-b-4 border-b-blue-600">
                <div class="p-12 text-center">
                    <div class="mb-8 flex justify-center">
                        <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center relative text-blue-600">
                            <component :is="iconMap[entityTypeToCreate] || Building" :size="48" stroke-width="2.5" />
                        </div>
                    </div>
                    
                    <h2 class="text-2xl font-black text-slate-900 mb-2">Crear {{ entityTypeToCreate === 'comercio' ? 'Nuevo Comercio' : (entityTypeToCreate === 'oficina' ? 'Nueva Oficina' : 'Nuevo Hogar') }}</h2>
                    <p class="text-slate-400 font-medium mb-8">Ingrese el nombre para identificar este espacio.</p>
                    
                    <div class="bg-slate-50 rounded-3xl p-6 mb-8 text-left border border-slate-100">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 text-center">
                            Nombre de la Entidad
                        </label>
                        <input 
                            v-model="newEntityName" 
                            type="text" 
                            class="w-full bg-white border border-slate-200 rounded-2xl p-4 text-center text-sm font-bold text-slate-950 focus:ring-2 focus:ring-blue-600/20 focus:border-blue-600 transition-all outline-none" 
                            placeholder="Ej. Oficina Central"
                            @keyup.enter="confirmCreate"
                        />
                    </div>
                    
                    <div class="flex flex-col gap-4">
                        <button 
                            @click="confirmCreate"
                            :disabled="!newEntityName.trim()"
                            class="w-full bg-slate-900 text-white py-5 rounded-3xl font-black text-xs uppercase tracking-widest transition-all shadow-xl disabled:opacity-35 disabled:cursor-not-allowed enabled:hover:bg-blue-600"
                        >
                            Crear y Continuar
                        </button>
                        <button 
                            @click="closeCreateModal"
                            class="w-full py-5 rounded-3xl font-black text-xs uppercase tracking-widest text-slate-400 hover:bg-slate-50 transition-all"
                        >
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xl animate-in fade-in duration-300" @click="closeDeleteModal"></div>
            
            <div class="relative bg-white rounded-[40px] shadow-2xl w-full max-w-lg overflow-hidden animate-in zoom-in-95 duration-300 border border-slate-100">
                <div class="p-12 text-center">
                    <div class="mb-8 flex justify-center">
                        <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center relative">
                            <AlertTriangle :size="48" class="text-energy-critical relative z-10" />
                            <div class="absolute inset-0 bg-red-200 rounded-full animate-ping opacity-20"></div>
                        </div>
                    </div>
                    
                    <h2 class="text-2xl font-black text-slate-900 mb-4">¿Eliminar esta entidad?</h2>
                    <p class="text-slate-400 font-medium mb-8">
                        Esta acción no se puede deshacer. Se eliminarán permanentemente todas las habitaciones, contratos, facturas y consumos asociados a la entidad 
                        <span class="font-bold text-slate-900">"{{ entityToDelete?.name }}"</span>.
                    </p>
                    
                    <div class="bg-slate-50 rounded-3xl p-6 mb-8 text-left border border-slate-100">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2 text-center">
                            Escriba <span class="text-energy-critical font-extrabold">BORRAR</span> para confirmar
                        </label>
                        <input 
                            v-model="deleteConfirmationText" 
                            type="text" 
                            placeholder="BORRAR" 
                            class="w-full bg-white border border-slate-200 rounded-2xl p-4 text-center text-sm font-black text-slate-950 uppercase focus:ring-2 focus:ring-energy-critical/20 focus:border-energy-critical transition-all" 
                        />
                    </div>
                    
                    <div class="flex flex-col gap-4">
                        <button 
                            @click="confirmDelete"
                            :disabled="deleteConfirmationText.trim().toUpperCase() !== 'BORRAR'"
                            class="w-full bg-slate-900 text-white py-5 rounded-3xl font-black text-xs uppercase tracking-widest transition-all shadow-xl disabled:opacity-35 disabled:cursor-not-allowed enabled:hover:bg-energy-critical"
                        >
                            Confirmar y Eliminar
                        </button>
                        <button 
                            @click="closeDeleteModal"
                            class="w-full py-5 rounded-3xl font-black text-xs uppercase tracking-widest text-slate-400 hover:bg-slate-50 transition-all"
                        >
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

