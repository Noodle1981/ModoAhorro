<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { 
    Plus, 
    Search, 
    Edit2, 
    Trash2, 
    Zap, 
    ShieldAlert, 
    AlertTriangle, 
    X, 
    Power, 
    Cpu, 
    Layers
} from 'lucide-vue-next';
import { shallowRef, computed } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    equipmentTypes: Array,
    categories: Array
});

// Filtros y Búsqueda
const searchQuery = shallowRef('');
const selectedCategory = shallowRef('all');
const selectedTank = shallowRef('all');
const selectedStatus = shallowRef('all');

const tanks = [
    { value: 0, name: 'Tanque 0 (Certeza / Standby)', badge: 'T0 Certeza', color: 'bg-emerald-500', text: 'text-emerald-700 bg-emerald-50 border-emerald-200' },
    { value: 1, name: 'Tanque 1 (Base Inmutable)', badge: 'T1 Base', color: 'bg-sky-500', text: 'text-sky-700 bg-sky-50 border-sky-200' },
    { value: 2, name: 'Tanque 2 (Climatización)', badge: 'T2 Clima', color: 'bg-amber-500', text: 'text-amber-700 bg-amber-50 border-amber-200' },
    { value: 3, name: 'Tanque 3 (Elasticidad / Uso)', badge: 'T3 Elasticidad', color: 'bg-indigo-500', text: 'text-indigo-700 bg-indigo-50 border-indigo-200' },
];

const consumptionLogics = [
    { value: 'CONSTANT_ELASTIC', label: 'Elasticidad de Uso Estándar (Horas)' },
    { value: 'BASE_LOAD', label: 'Carga Base Permanente 24/7' },
    { value: 'CLIMATE_CORRELATED', label: 'Correlación Climática (Grados Día)' },
    { value: 'SEASONAL_HABIT', label: 'Hábito Estacional' },
    { value: 'TURNS_BASED', label: 'Basado en Turnos Comerciales' },
    { value: 'SERVICE_HOURS', label: 'Horas de Servicio Registradas' },
];

const usageUnits = [
    { value: 'hours', label: 'Horas diarias / Horas de servicio' },
    { value: 'cycles', label: 'Ciclos de uso (ej. Lavados)' },
    { value: 'people_proportional', label: 'Proporcional a personas' },
];

const filteredTypes = computed(() => {
    return props.equipmentTypes.filter(t => {
        const matchesSearch = t.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                              (t.category?.name && t.category.name.toLowerCase().includes(searchQuery.value.toLowerCase()));
        const matchesCategory = selectedCategory.value === 'all' || t.category_id === Number(selectedCategory.value);
        const matchesTank = selectedTank.value === 'all' || t.default_tank === Number(selectedTank.value);
        const matchesStatus = selectedStatus.value === 'all' || 
                              (selectedStatus.value === 'active' && t.is_active) || 
                              (selectedStatus.value === 'inactive' && !t.is_active);
        
        return matchesSearch && matchesCategory && matchesTank && matchesStatus;
    });
});

// Modales
const isModalOpen = shallowRef(false);
const isEditing = shallowRef(false);
const activeTab = shallowRef('general'); // 'general' | 'power' | 'engine'
const currentTypeId = shallowRef(null);

const isDeleteModalOpen = shallowRef(false);
const typeToDelete = shallowRef(null);

// Formulario reactivo
const form = useForm({
    category_id: '',
    name: '',
    is_active: true,
    default_power_watts: 100,
    min_watts: 10,
    max_watts: 5000,
    default_avg_daily_use_hours: 4,
    default_standby_power_w: 0,
    default_tank: 3,
    load_factor: 1.0,
    thermal_efficiency_penalty: 0,
    determinism_score: 0.5,
    social_coefficient: 0.0,
    consumption_logic: 'CONSTANT_ELASTIC',
    usage_unit: 'hours',
    is_climatization: false,
    is_inverter_capable: false,
    is_shiftable: false,
});

const openCreateModal = () => {
    isEditing.value = false;
    currentTypeId.value = null;
    activeTab.value = 'general';
    form.reset();
    form.clearErrors();
    form.category_id = props.categories.length > 0 ? props.categories[0].id : '';
    form.default_tank = 3;
    form.is_active = true;
    form.load_factor = 1.0;
    form.determinism_score = 0.5;
    isModalOpen.value = true;
};

const openEditModal = (type) => {
    isEditing.value = true;
    currentTypeId.value = type.id;
    activeTab.value = 'general';
    form.clearErrors();
    form.category_id = type.category_id;
    form.name = type.name;
    form.is_active = type.is_active ?? true;
    form.default_power_watts = type.default_power_watts ?? 0;
    form.min_watts = type.min_watts ?? 0;
    form.max_watts = type.max_watts ?? 0;
    form.default_avg_daily_use_hours = type.default_avg_daily_use_hours ?? 0;
    form.default_standby_power_w = type.default_standby_power_w ?? 0;
    form.default_tank = Number(type.default_tank ?? 3);
    form.load_factor = type.load_factor ?? 1.0;
    form.thermal_efficiency_penalty = type.thermal_efficiency_penalty ?? 0;
    form.determinism_score = type.determinism_score ?? 0.5;
    form.social_coefficient = type.social_coefficient ?? 0.0;
    form.consumption_logic = type.consumption_logic ?? 'CONSTANT_ELASTIC';
    form.usage_unit = type.usage_unit ?? 'hours';
    form.is_climatization = type.is_climatization ?? false;
    form.is_inverter_capable = type.is_inverter_capable ?? false;
    form.is_shiftable = type.is_shiftable ?? false;
    isModalOpen.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('sistema.catalogue.update', currentTypeId.value), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
            },
        });
    } else {
        form.post(route('sistema.catalogue.store'), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
            },
        });
    }
};

const toggleActive = (type) => {
    router.patch(route('sistema.catalogue.toggle', type.id), {}, {
        preserveScroll: true,
    });
};

const promptDelete = (type) => {
    typeToDelete.value = type;
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    if (!typeToDelete.value) return;
    router.delete(route('sistema.catalogue.destroy', typeToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            typeToDelete.value = null;
        },
        onError: () => {
            isDeleteModalOpen.value = false;
        }
    });
};
</script>

<template>
    <Head title="Catálogo Maestro · Administrador" />

    <MainLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            
            <!-- Page Header Ribbon -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 sm:p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                <div>
                    <span class="text-xs font-black uppercase tracking-widest text-emerald-600 block mb-1">
                        Control Global del Sistema
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Catálogo Maestro de Equipos
                    </h1>
                    <p class="text-slate-500 font-medium text-sm mt-1">
                        Gestioná los arquetipos base, potencias de referencia y reglas físicas del motor de cálculo.
                    </p>
                </div>
                <button 
                    @click="openCreateModal"
                    class="inline-flex items-center justify-center gap-2 bg-[#009966] hover:bg-[#008055] text-white font-extrabold text-xs px-6 py-4 rounded-2xl shadow-lg shadow-emerald-900/10 hover:shadow-emerald-900/20 transition-all hover:-translate-y-0.5 cursor-pointer uppercase tracking-wider shrink-0"
                >
                    <Plus :size="18" />
                    <span>Nuevo Tipo de Equipo</span>
                </button>
            </div>

            <!-- Toolbar Ribbon & Filtros -->
            <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm flex flex-col lg:flex-row items-center gap-3">
                <!-- Buscador -->
                <div class="relative w-full lg:flex-1">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="18" />
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Buscar por nombre o categoría..." 
                        class="w-full pl-12 pr-6 py-3 bg-slate-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-medium text-sm text-slate-800"
                    />
                </div>

                <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                    <!-- Filtro Categoría -->
                    <select 
                        v-model="selectedCategory"
                        class="px-4 py-3 bg-slate-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-bold text-slate-700 text-xs min-w-[170px]"
                    >
                        <option value="all">Todas las Categorías</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>

                    <!-- Filtro Tanque -->
                    <select 
                        v-model="selectedTank"
                        class="px-4 py-3 bg-slate-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-bold text-slate-700 text-xs min-w-[150px]"
                    >
                        <option value="all">Todos los Tanques</option>
                        <option v-for="tank in tanks" :key="tank.value" :value="tank.value">{{ tank.name }}</option>
                    </select>

                    <!-- Filtro Estado -->
                    <select 
                        v-model="selectedStatus"
                        class="px-4 py-3 bg-slate-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-bold text-slate-700 text-xs min-w-[140px]"
                    >
                        <option value="all">Todos los Estados</option>
                        <option value="active">Solo Activos</option>
                        <option value="inactive">Solo Archivados</option>
                    </select>
                </div>
            </div>

            <!-- Table Container -->
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100">
                                <th class="px-7 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Equipo & Categoría</th>
                                <th class="px-5 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Estado</th>
                                <th class="px-5 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Tanque</th>
                                <th class="px-5 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Potencia Def. (Rango)</th>
                                <th class="px-5 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Física Motor</th>
                                <th class="px-5 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Uso Red</th>
                                <th class="px-7 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-if="filteredTypes.length === 0">
                                <td colspan="7" class="text-center py-16 text-slate-400 font-medium">
                                    No se encontraron tipos de equipo con los filtros seleccionados.
                                </td>
                            </tr>
                            <tr v-for="type in filteredTypes" :key="type.id" :class="['hover:bg-slate-50/50 transition-colors group', !type.is_active ? 'opacity-60 bg-slate-50/30' : '']">
                                
                                <!-- Equipo y Badges -->
                                <td class="px-7 py-5">
                                    <div class="flex items-center gap-3.5">
                                        <div :class="['w-11 h-11 rounded-2xl flex items-center justify-center transition-all', type.is_active ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400']">
                                            <Zap :size="20" />
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <p class="font-black text-slate-900 text-sm leading-tight">{{ type.name }}</p>
                                                <span v-if="type.is_inverter_capable" class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 text-[9px] font-black uppercase tracking-wider border border-indigo-100">Inverter</span>
                                                <span v-if="type.is_climatization" class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 text-[9px] font-black uppercase tracking-wider border border-amber-100">Clima</span>
                                            </div>
                                            <p class="text-xs font-bold text-slate-400 mt-0.5">{{ type.category?.name || 'Sin Categoría' }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Estado Toggle -->
                                <td class="px-5 py-5">
                                    <button 
                                        @click="toggleActive(type)"
                                        :title="type.is_active ? 'Click para archivar/desactivar' : 'Click para activar'"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider transition-all cursor-pointer border"
                                        :class="type.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-600 border-slate-200 hover:bg-slate-200'"
                                    >
                                        <span :class="['w-1.5 h-1.5 rounded-full', type.is_active ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400']"></span>
                                        <span>{{ type.is_active ? 'Activo' : 'Archivado' }}</span>
                                    </button>
                                </td>

                                <!-- Tanque Asignado -->
                                <td class="px-5 py-5">
                                    <span 
                                        class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-black tracking-wider uppercase border"
                                        :class="tanks.find(tn => tn.value === Number(type.default_tank))?.text || 'bg-slate-100 text-slate-600 border-slate-200'"
                                    >
                                        {{ tanks.find(tn => tn.value === Number(type.default_tank))?.badge || 'T3 Elasticidad' }}
                                    </span>
                                </td>

                                <!-- Potencias -->
                                <td class="px-5 py-5">
                                    <div>
                                        <span class="font-black text-slate-900 text-sm">{{ type.default_power_watts }} W</span>
                                        <div class="text-[11px] font-bold text-slate-400 mt-0.5">
                                            {{ type.min_watts }}W - {{ type.max_watts }}W
                                        </div>
                                    </div>
                                </td>

                                <!-- Parámetros Motor -->
                                <td class="px-5 py-5">
                                    <div class="space-y-1 text-xs">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-slate-400 text-[11px] font-bold">Carga:</span>
                                            <span class="font-extrabold text-slate-700">{{ (type.load_factor * 100).toFixed(0) }}%</span>
                                        </div>
                                        <div v-if="type.thermal_efficiency_penalty > 0" class="flex items-center gap-1.5">
                                            <span class="text-rose-400 text-[11px] font-bold">Penalización:</span>
                                            <span class="font-extrabold text-rose-600">+{{ type.thermal_efficiency_penalty }}%</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-slate-400 text-[11px] font-bold">Determ:</span>
                                            <span class="font-extrabold text-sky-600">{{ type.determinism_score }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Cantidad de Equipos en Usuarios -->
                                <td class="px-5 py-5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-extrabold text-xs">
                                            {{ type.equipment_count || 0 }}
                                        </span>
                                        <span class="text-[11px] font-bold text-slate-400">equipos</span>
                                    </div>
                                </td>

                                <!-- Botones Acciones -->
                                <td class="px-7 py-5 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button 
                                            @click="openEditModal(type)" 
                                            title="Editar parámetros"
                                            class="p-2.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all cursor-pointer"
                                        >
                                            <Edit2 :size="16" />
                                        </button>
                                        <button 
                                            @click="promptDelete(type)" 
                                            title="Eliminar o Archivar"
                                            class="p-2.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all cursor-pointer"
                                        >
                                            <Trash2 :size="16" />
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Info Alert de Ingeniería -->
            <div class="p-8 bg-linear-to-r from-sky-50 to-indigo-50/50 rounded-[2.5rem] border border-sky-100 flex gap-6 items-start">
                <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-sky-600 shadow-xs shrink-0 border border-sky-100">
                    <ShieldAlert :size="24" />
                </div>
                <div>
                    <h4 class="text-base font-black text-sky-950 mb-1">Criterio de Preservación Histórica</h4>
                    <p class="text-sky-900/80 text-sm leading-relaxed max-w-4xl font-medium">
                        Los tipos de equipos que ya están siendo utilizados por usuarios en sus hogares o comercios están protegidos contra el borrado físico para evitar romper análisis y facturas históricas. Si un modelo queda obsoleto, simplemente podés <strong>Archivarlo / Desactivarlo</strong> para que no aparezca en nuevas selecciones.
                    </p>
                </div>
            </div>

        </div>

        <!-- MODAL DE CREACIÓN / EDICIÓN -->
        <Modal :show="isModalOpen" max-width="2xl" @close="isModalOpen = false">
            <div class="relative w-full overflow-hidden">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 block">
                            {{ isEditing ? 'Modificar Arquetipo' : 'Registrar Nuevo Arquetipo' }}
                        </span>
                        <h3 class="text-xl font-black text-slate-900">
                            {{ isEditing ? form.name : 'Nuevo Tipo de Equipo' }}
                        </h3>
                    </div>
                    <button @click="isModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition-colors">
                        <X :size="20" />
                    </button>
                </div>

                <!-- Tab Navigation -->
                <div class="flex border-b border-slate-100 px-8 bg-white">
                    <button 
                        type="button"
                        @click="activeTab = 'general'"
                        :class="['py-3.5 px-4 font-black text-xs uppercase tracking-wider border-b-2 transition-all cursor-pointer flex items-center gap-2', activeTab === 'general' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-400 hover:text-slate-600']"
                    >
                        <Layers :size="14" />
                        <span>1. Datos Generales</span>
                    </button>
                    <button 
                        type="button"
                        @click="activeTab = 'power'"
                        :class="['py-3.5 px-4 font-black text-xs uppercase tracking-wider border-b-2 transition-all cursor-pointer flex items-center gap-2', activeTab === 'power' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-400 hover:text-slate-600']"
                    >
                        <Power :size="14" />
                        <span>2. Potencias & Rangos</span>
                    </button>
                    <button 
                        type="button"
                        @click="activeTab = 'engine'"
                        :class="['py-3.5 px-4 font-black text-xs uppercase tracking-wider border-b-2 transition-all cursor-pointer flex items-center gap-2', activeTab === 'engine' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-slate-400 hover:text-slate-600']"
                    >
                        <Cpu :size="14" />
                        <span>3. Motor & Fórmulas</span>
                    </button>
                </div>

                <!-- Modal Body Form -->
                <form @submit.prevent="submitForm">
                    <div class="p-8 space-y-6 max-h-[60vh] overflow-y-auto">
                        
                        <!-- TAB 1: DATOS GENERALES -->
                        <div v-show="activeTab === 'general'" class="space-y-5">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Nombre del Tipo de Equipo *</label>
                                <input 
                                    v-model="form.name" 
                                    type="text" 
                                    required 
                                    placeholder="Ej: Heladera con freezer No-Frost" 
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.name }}</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Categoría *</label>
                                    <select 
                                        v-model="form.category_id" 
                                        required 
                                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                                    >
                                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                    </select>
                                    <p v-if="form.errors.category_id" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.category_id }}</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Tanque por Defecto *</label>
                                    <select 
                                        v-model="form.default_tank" 
                                        required 
                                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                                    >
                                        <option v-for="tank in tanks" :key="tank.value" :value="tank.value">{{ tank.name }}</option>
                                    </select>
                                    <p v-if="form.errors.default_tank" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.default_tank }}</p>
                                </div>
                            </div>

                            <!-- Switches Booleans -->
                            <div class="pt-2 border-t border-slate-100 space-y-3">
                                <label class="flex items-center justify-between p-3.5 bg-slate-50 rounded-2xl border border-slate-100 hover:bg-slate-100/70 transition-colors cursor-pointer">
                                    <div>
                                        <span class="text-xs font-black text-slate-900 block">¿Es equipo de climatización?</span>
                                        <span class="text-[11px] font-medium text-slate-500">Se correlaciona automáticamente con la temperatura zonal y los Grados Día.</span>
                                    </div>
                                    <input type="checkbox" v-model="form.is_climatization" class="w-5 h-5 rounded-lg text-emerald-600 focus:ring-emerald-500 border-slate-300" />
                                </label>

                                <label class="flex items-center justify-between p-3.5 bg-slate-50 rounded-2xl border border-slate-100 hover:bg-slate-100/70 transition-colors cursor-pointer">
                                    <div>
                                        <span class="text-xs font-black text-slate-900 block">¿Admite tecnología Inverter?</span>
                                        <span class="text-[11px] font-medium text-slate-500">Permite al usuario marcar si su equipo es Inverter para aplicar curvas de modulación.</span>
                                    </div>
                                    <input type="checkbox" v-model="form.is_inverter_capable" class="w-5 h-5 rounded-lg text-emerald-600 focus:ring-emerald-500 border-slate-300" />
                                </label>

                                <label class="flex items-center justify-between p-3.5 bg-slate-50 rounded-2xl border border-slate-100 hover:bg-slate-100/70 transition-colors cursor-pointer">
                                    <div>
                                        <span class="text-xs font-black text-slate-900 block">¿Es desplazable a horario nocturno?</span>
                                        <span class="text-[11px] font-medium text-slate-500">Utilizado en las recomendaciones de optimización horaria y tarifas de red.</span>
                                    </div>
                                    <input type="checkbox" v-model="form.is_shiftable" class="w-5 h-5 rounded-lg text-emerald-600 focus:ring-emerald-500 border-slate-300" />
                                </label>

                                <label class="flex items-center justify-between p-3.5 bg-slate-50 rounded-2xl border border-slate-100 hover:bg-slate-100/70 transition-colors cursor-pointer">
                                    <div>
                                        <span class="text-xs font-black text-slate-900 block">Estado Activo</span>
                                        <span class="text-[11px] font-medium text-slate-500">Si está inactivo/archivado, no se ofrecerá a nuevos usuarios.</span>
                                    </div>
                                    <input type="checkbox" v-model="form.is_active" class="w-5 h-5 rounded-lg text-emerald-600 focus:ring-emerald-500 border-slate-300" />
                                </label>
                            </div>
                        </div>

                        <!-- TAB 2: POTENCIAS Y RANGOS -->
                        <div v-show="activeTab === 'power'" class="space-y-5">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Potencia por Defecto (Watts) *</label>
                                <input 
                                    v-model.number="form.default_power_watts" 
                                    type="number" 
                                    required 
                                    min="0"
                                    max="100000"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                                />
                                <span class="text-[11px] font-medium text-slate-400 mt-1 block">Valor de referencia que se auto-completará en el formulario del usuario.</span>
                                <p v-if="form.errors.default_power_watts" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.default_power_watts }}</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Potencia Mínima (Watts)</label>
                                    <input 
                                        v-model.number="form.min_watts" 
                                        type="number" 
                                        min="0"
                                        max="100000"
                                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                                    />
                                    <p v-if="form.errors.min_watts" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.min_watts }}</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Potencia Máxima (Watts)</label>
                                    <input 
                                        v-model.number="form.max_watts" 
                                        type="number" 
                                        min="0"
                                        max="100000"
                                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                                    />
                                    <p v-if="form.errors.max_watts" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.max_watts }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Standby por Defecto (Watts)</label>
                                    <input 
                                        v-model.number="form.default_standby_power_w" 
                                        type="number" 
                                        min="0"
                                        max="1000"
                                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                                    />
                                    <span class="text-[11px] font-medium text-slate-400 mt-1 block">Consumo pasivo cuando está enchufado y apagado.</span>
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Horas de Uso Promedio (h/día)</label>
                                    <input 
                                        v-model.number="form.default_avg_daily_use_hours" 
                                        type="number" 
                                        step="0.5"
                                        min="0"
                                        max="24"
                                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- TAB 3: MOTOR Y FÓRMULAS -->
                        <div v-show="activeTab === 'engine'" class="space-y-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Factor de Carga (0.0 - 1.0)</label>
                                    <input 
                                        v-model.number="form.load_factor" 
                                        type="number" 
                                        step="0.05"
                                        min="0"
                                        max="1"
                                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                                    />
                                    <span class="text-[11px] font-medium text-slate-400 mt-1 block">Ej: 0.70 si el compresor corta por termostato el 30% del tiempo.</span>
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Penalización Térmica (%)</label>
                                    <input 
                                        v-model.number="form.thermal_efficiency_penalty" 
                                        type="number" 
                                        step="1"
                                        min="0"
                                        max="100"
                                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                                    />
                                    <span class="text-[11px] font-medium text-slate-400 mt-1 block">Porcentaje de sobreconsumo en picos extremos de calor.</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Puntuación Determinismo (0.0 - 1.0)</label>
                                    <input 
                                        v-model.number="form.determinism_score" 
                                        type="number" 
                                        step="0.05"
                                        min="0"
                                        max="1"
                                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                                    />
                                    <span class="text-[11px] font-medium text-slate-400 mt-1 block">1.0 = Certeza absoluta (Tanque 0). 0.5 = Comportamiento elástico.</span>
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Coeficiente Social</label>
                                    <input 
                                        v-model.number="form.social_coefficient" 
                                        type="number" 
                                        step="0.1"
                                        min="0"
                                        max="10"
                                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                                    />
                                    <span class="text-[11px] font-medium text-slate-400 mt-1 block">Multiplicador por cantidad de personas/habitantes.</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Lógica de Consumo</label>
                                    <select 
                                        v-model="form.consumption_logic" 
                                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                                    >
                                        <option v-for="cl in consumptionLogics" :key="cl.value" :value="cl.value">{{ cl.label }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Unidad de Medida de Uso</label>
                                    <select 
                                        v-model="form.usage_unit" 
                                        class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                                    >
                                        <option v-for="unit in usageUnits" :key="unit.value" :value="unit.value">{{ unit.label }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Modal Actions Footer -->
                    <div class="px-8 py-5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <button 
                            type="button" 
                            @click="isModalOpen = false" 
                            class="px-5 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 rounded-xl hover:bg-slate-100 transition-colors cursor-pointer"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 bg-[#009966] hover:bg-[#008055] text-white font-black text-xs uppercase tracking-wider px-6 py-3 rounded-2xl shadow-md transition-all hover:-translate-y-0.5 cursor-pointer disabled:opacity-50"
                        >
                            <span v-if="form.processing">Guardando...</span>
                            <span v-else>{{ isEditing ? 'Actualizar Tipo' : 'Crear Tipo de Equipo' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- MODAL DE CONFIRMACIÓN DE BORRADO -->
        <Modal :show="isDeleteModalOpen" max-width="md" @close="isDeleteModalOpen = false">
            <div class="p-8 space-y-6">
                
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center" :class="(typeToDelete?.equipment_count || 0) > 0 ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600'">
                    <AlertTriangle :size="28" />
                </div>

                <div>
                    <h3 class="text-xl font-black text-slate-900">
                        {{ (typeToDelete?.equipment_count || 0) > 0 ? 'Equipo en uso en la red' : '¿Eliminar del catálogo?' }}
                    </h3>
                    
                    <p v-if="(typeToDelete?.equipment_count || 0) > 0" class="text-sm font-medium text-slate-600 mt-2 leading-relaxed">
                        El tipo <strong>"{{ typeToDelete?.name }}"</strong> está siendo utilizado por <strong class="text-amber-600">{{ typeToDelete?.equipment_count }} equipo(s)</strong> en entidades de usuarios.
                        <br class="mb-2" />
                        Para no romper análisis ni facturas históricas, el borrado físico está bloqueado. Podés <strong>archivarlo/desactivarlo</strong> para que no aparezca en nuevas selecciones.
                    </p>

                    <p v-else class="text-sm font-medium text-slate-600 mt-2 leading-relaxed">
                        ¿Estás seguro de que deseas eliminar permanentemente <strong>"{{ typeToDelete?.name }}"</strong>? Ningún usuario tiene este equipo vinculado actualmente.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button 
                        @click="isDeleteModalOpen = false" 
                        class="px-5 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 rounded-xl hover:bg-slate-100 transition-colors cursor-pointer"
                    >
                        Cancelar
                    </button>

                    <button 
                        v-if="(typeToDelete?.equipment_count || 0) > 0"
                        @click="toggleActive(typeToDelete); isDeleteModalOpen = false" 
                        class="px-5 py-2.5 text-xs font-black text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-md transition-all cursor-pointer"
                    >
                        {{ typeToDelete?.is_active ? 'Archivar / Desactivar' : 'Activar' }}
                    </button>

                    <button 
                        v-else
                        @click="confirmDelete" 
                        class="px-5 py-2.5 text-xs font-black text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md transition-all cursor-pointer"
                    >
                        Eliminar Definitivamente
                    </button>
                </div>

            </div>
        </Modal>

    </MainLayout>
</template>

