<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { 
    Plus, 
    Search, 
    Edit2, 
    Trash2, 
    Sparkles, 
    Users, 
    CheckCircle2, 
    AlertTriangle, 
    X, 
    Tag, 
    ShieldCheck, 
    ArrowRight
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    verifiedModels: Array,
    communitySuggestions: Array,
    categories: Array,
    equipmentTypes: Array,
});

// Pestaña activa principal
const mainTab = ref('community'); // 'community' | 'verified'

// Filtros para Modelos Verificados
const searchQuery = ref('');
const selectedCategory = ref('all');
const selectedType = ref('all');
const selectedInverter = ref('all');

const filteredVerifiedModels = computed(() => {
    return props.verifiedModels.filter(m => {
        const query = searchQuery.value.toLowerCase();
        const matchesSearch = m.brand.toLowerCase().includes(query) || 
                              m.model.toLowerCase().includes(query) ||
                              (m.category?.name && m.category.name.toLowerCase().includes(query)) ||
                              (m.type?.name && m.type.name.toLowerCase().includes(query));
        const matchesCategory = selectedCategory.value === 'all' || m.category_id === Number(selectedCategory.value);
        const matchesType = selectedType.value === 'all' || m.type_id === Number(selectedType.value);
        const matchesInverter = selectedInverter.value === 'all' || 
                                (selectedInverter.value === 'inverter' && m.is_inverter) ||
                                (selectedInverter.value === 'standard' && !m.is_inverter);

        return matchesSearch && matchesCategory && matchesType && matchesInverter;
    });
});

// Modales
const isModalOpen = ref(false);
const isEditing = ref(false);
const currentModelId = ref(null);

const isDeleteModalOpen = ref(false);
const modelToDelete = ref(null);

const form = useForm({
    category_id: '',
    type_id: '',
    brand: '',
    model: '',
    nominal_power_w: 100,
    is_inverter: false,
    energy_label: '',
    capacity: '',
    capacity_unit: '',
    is_verified: true,
    source: 'ADMIN_MANUAL',
    notes: '',
});

// Types filtrados por category en el modal
const modalFilteredTypes = computed(() => {
    if (!form.category_id) return props.equipmentTypes;
    return props.equipmentTypes.filter(t => t.category_id === Number(form.category_id));
});

const openCreateModal = () => {
    isEditing.value = false;
    currentModelId.value = null;
    form.reset();
    form.clearErrors();
    form.category_id = props.categories.length > 0 ? props.categories[0].id : '';
    form.type_id = props.equipmentTypes.length > 0 ? props.equipmentTypes[0].id : '';
    form.is_verified = true;
    form.source = 'ADMIN_MANUAL';
    isModalOpen.value = true;
};

const openApproveModal = (suggestion) => {
    isEditing.value = false;
    currentModelId.value = null;
    form.reset();
    form.clearErrors();
    form.brand = suggestion.brand;
    form.model = suggestion.model;
    form.nominal_power_w = suggestion.avg_watts || 100;
    form.category_id = suggestion.category_id || (props.categories.length > 0 ? props.categories[0].id : '');
    form.type_id = suggestion.type_id || (props.equipmentTypes.length > 0 ? props.equipmentTypes[0].id : '');
    form.is_inverter = Boolean(suggestion.suggested_inverter);
    form.energy_label = suggestion.sample_energy_label || '';
    form.capacity = suggestion.sample_capacity || '';
    form.capacity_unit = suggestion.sample_capacity_unit || '';
    form.is_verified = true;
    form.source = 'COMMUNITY_APPROVED';
    form.notes = `Aprobado a partir de ${suggestion.user_count} aporte(s) de usuarios de la red.`;
    isModalOpen.value = true;
};

const openEditModal = (model) => {
    isEditing.value = true;
    currentModelId.value = model.id;
    form.clearErrors();
    form.category_id = model.category_id;
    form.type_id = model.type_id;
    form.brand = model.brand;
    form.model = model.model;
    form.nominal_power_w = model.nominal_power_w;
    form.is_inverter = model.is_inverter ?? false;
    form.energy_label = model.energy_label || '';
    form.capacity = model.capacity || '';
    form.capacity_unit = model.capacity_unit || '';
    form.is_verified = model.is_verified ?? true;
    form.source = model.source || 'ADMIN_MANUAL';
    form.notes = model.notes || '';
    isModalOpen.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('sistema.models.update', currentModelId.value), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
            }
        });
    } else {
        form.post(route('sistema.models.store'), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
            }
        });
    }
};

const promptDelete = (model) => {
    modelToDelete.value = model;
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    if (!modelToDelete.value) return;
    router.delete(route('sistema.models.destroy', modelToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            modelToDelete.value = null;
        }
    });
};
</script>

<template>
    <Head title="Modelos de Mercado & Inteligencia · Administrador" />

    <MainLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            
            <!-- Page Header Ribbon -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 sm:p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                <div>
                    <span class="text-xs font-black uppercase tracking-widest text-emerald-600 block mb-1">
                        Inteligencia Colectiva de Mercado
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Modelos de Equipos y Sugerencias de Clientes
                    </h1>
                    <p class="text-slate-500 font-medium text-sm mt-1">
                        Validá los electrodomésticos y artefactos que cargan los usuarios para alimentar el catálogo oficial autocompletable.
                    </p>
                </div>
                <button 
                    @click="openCreateModal"
                    class="inline-flex items-center justify-center gap-2 bg-[#009966] hover:bg-[#008055] text-white font-extrabold text-xs px-6 py-4 rounded-2xl shadow-lg shadow-emerald-900/10 hover:shadow-emerald-900/20 transition-all hover:-translate-y-0.5 cursor-pointer uppercase tracking-wider shrink-0"
                >
                    <Plus :size="18" />
                    <span>Nuevo Modelo Manual</span>
                </button>
            </div>

            <!-- Main Tab Selector -->
            <div class="flex items-center gap-3 bg-white p-2 rounded-2xl border border-slate-100 shadow-xs max-w-xl">
                <button 
                    @click="mainTab = 'community'"
                    :class="['flex-1 py-3 px-4 rounded-xl font-black text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-2.5 cursor-pointer', mainTab === 'community' ? 'bg-slate-900 text-white shadow-md' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50']"
                >
                    <Sparkles :size="16" class="text-amber-400" />
                    <span>Bandeja de Clientes</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="mainTab === 'community' ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-700'">
                        {{ communitySuggestions.length }}
                    </span>
                </button>

                <button 
                    @click="mainTab = 'verified'"
                    :class="['flex-1 py-3 px-4 rounded-xl font-black text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-2.5 cursor-pointer', mainTab === 'verified' ? 'bg-slate-900 text-white shadow-md' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50']"
                >
                    <ShieldCheck :size="16" class="text-emerald-400" />
                    <span>Modelos Oficiales</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="mainTab === 'verified' ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-700'">
                        {{ verifiedModels.length }}
                    </span>
                </button>
            </div>

            <!-- PESTAÑA 1: BANDEJA DE INTELIGENCIA DE CLIENTES -->
            <div v-show="mainTab === 'community'" class="space-y-6">
                
                <div class="bg-amber-50/80 border border-amber-200/80 rounded-3xl p-6 flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-700 shrink-0 font-black">
                        💡
                    </div>
                    <div class="text-xs text-amber-950 font-medium leading-relaxed">
                        <strong class="font-bold">¿Cómo funciona esta bandeja?</strong>
                        Aquí se agrupan automáticamente todas las marcas y modelos que los usuarios cargaron libremente en sus hogares o comercios. Podés revisar el promedio de Watts calculados y, con un solo clic en <strong>"Aprobar y Oficializar"</strong>, pasarlo al catálogo de modelos homologados.
                    </div>
                </div>

                <div v-if="communitySuggestions.length === 0" class="bg-white rounded-[2.5rem] p-16 text-center border border-slate-100 shadow-sm">
                    <div class="w-16 h-16 rounded-3xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-4">
                        <CheckCircle2 :size="32" />
                    </div>
                    <h3 class="text-lg font-black text-slate-900">¡Bandeja al día!</h3>
                    <p class="text-slate-500 text-sm font-medium mt-1 max-w-md mx-auto">
                        No hay modelos pendientes de revisión. Todos los equipos cargados por los clientes ya corresponden a modelos homologados o no tienen marca declarada.
                    </p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="(sug, idx) in communitySuggestions" 
                        :key="idx"
                        class="bg-white rounded-[2.5rem] p-6 sm:p-7 border border-slate-100 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between group hover:-translate-y-1 relative overflow-hidden"
                    >
                        <div>
                            <!-- Header Card -->
                            <div class="flex items-center justify-between gap-2 mb-4">
                                <span class="px-3 py-1 rounded-xl bg-slate-100 text-slate-800 text-[10px] font-black uppercase tracking-wider">
                                    {{ sug.brand }}
                                </span>
                                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-black">
                                    <Users :size="12" />
                                    <span>{{ sug.user_count }} en la red</span>
                                </div>
                            </div>

                            <!-- Modelo Title -->
                            <h3 class="text-xl font-black text-slate-900 tracking-tight leading-snug group-hover:text-emerald-600 transition-colors">
                                {{ sug.model }}
                            </h3>
                            <p class="text-xs font-bold text-slate-400 mt-1">
                                {{ sug.type?.name || sug.category?.name || 'Artefacto General' }}
                            </p>

                            <!-- Telemetría Promedio -->
                            <div class="mt-6 p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-slate-500">Potencia Promedio:</span>
                                    <span class="font-black text-slate-900 text-sm">{{ sug.avg_watts }} Watts</span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-slate-400 font-bold">
                                    <span>Rango reportado:</span>
                                    <span>{{ sug.min_watts }}W - {{ sug.max_watts }}W</span>
                                </div>
                                <div v-if="sug.suggested_inverter" class="pt-2 border-t border-slate-200/60 flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider text-indigo-600">
                                    <Sparkles :size="12" />
                                    <span>Mayoría reporta Inverter</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Action CTA -->
                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] font-bold text-slate-400">Sugerencia Comunitaria</span>
                            <button 
                                @click="openApproveModal(sug)"
                                class="inline-flex items-center gap-1.5 bg-slate-900 hover:bg-emerald-600 text-white font-extrabold text-xs px-4 py-2.5 rounded-xl shadow-xs transition-all cursor-pointer"
                            >
                                <span>Aprobar</span>
                                <ArrowRight :size="14" />
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- PESTAÑA 2: CATÁLOGO DE MODELOS OFICIALES VERIFICADOS -->
            <div v-show="mainTab === 'verified'" class="space-y-6">
                
                <!-- Toolbar & Filtros de Modelos -->
                <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm flex flex-col lg:flex-row items-center gap-3">
                    <div class="relative w-full lg:flex-1">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="18" />
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Buscar por marca o modelo oficial..." 
                            class="w-full pl-12 pr-6 py-3 bg-slate-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-medium text-sm text-slate-800"
                        />
                    </div>

                    <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                        <select 
                            v-model="selectedCategory"
                            class="px-4 py-3 bg-slate-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-bold text-slate-700 text-xs min-w-[170px]"
                        >
                            <option value="all">Todas las Categorías</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>

                        <select 
                            v-model="selectedInverter"
                            class="px-4 py-3 bg-slate-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-bold text-slate-700 text-xs min-w-[140px]"
                        >
                            <option value="all">Tecnología: Todas</option>
                            <option value="inverter">Solo Inverter</option>
                            <option value="standard">Estándar</option>
                        </select>
                    </div>
                </div>

                <!-- Tabla de Modelos Oficiales -->
                <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-[900px]">
                            <thead>
                                <tr class="bg-slate-50/70 border-b border-slate-100">
                                    <th class="px-7 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Marca & Modelo</th>
                                    <th class="px-5 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Tipo Asignado</th>
                                    <th class="px-5 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Potencia Oficial</th>
                                    <th class="px-5 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Eficiencia</th>
                                    <th class="px-5 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">En la Red</th>
                                    <th class="px-7 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-if="filteredVerifiedModels.length === 0">
                                    <td colspan="6" class="text-center py-16 text-slate-400 font-medium">
                                        No se encontraron modelos verificados con los filtros seleccionados.
                                    </td>
                                </tr>
                                <tr v-for="model in filteredVerifiedModels" :key="model.id" class="hover:bg-slate-50/50 transition-colors group">
                                    
                                    <!-- Marca & Modelo -->
                                    <td class="px-7 py-5">
                                        <div class="flex items-center gap-3.5">
                                            <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                                <Tag :size="18" />
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-black uppercase text-slate-500 tracking-wider">{{ model.brand }}</span>
                                                    <span v-if="model.is_inverter" class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[9px] font-black uppercase">Inverter</span>
                                                </div>
                                                <p class="font-black text-slate-900 text-base leading-tight mt-0.5">{{ model.model }}</p>
                                                <span v-if="model.capacity" class="text-[11px] font-bold text-slate-400">
                                                    {{ model.capacity }} {{ model.capacity_unit || '' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Tipo Asignado -->
                                    <td class="px-5 py-5">
                                        <div>
                                            <p class="font-bold text-slate-800 text-sm">{{ model.type?.name || 'Sin tipo' }}</p>
                                            <p class="text-xs font-medium text-slate-400">{{ model.category?.name }}</p>
                                        </div>
                                    </td>

                                    <!-- Potencia Oficial -->
                                    <td class="px-5 py-5">
                                        <span class="font-black text-slate-900 text-base">{{ model.nominal_power_w }} W</span>
                                    </td>

                                    <!-- Etiqueta de Eficiencia -->
                                    <td class="px-5 py-5">
                                        <span 
                                            v-if="model.energy_label"
                                            :class="[
                                                'px-2.5 py-1 rounded-lg text-xs font-black text-white shadow-xs',
                                                model.energy_label.startsWith('A') ? 'bg-emerald-500' :
                                                model.energy_label === 'B' ? 'bg-lime-500' :
                                                model.energy_label === 'C' ? 'bg-amber-500' : 'bg-rose-500'
                                            ]"
                                        >
                                            {{ model.energy_label }}
                                        </span>
                                        <span v-else class="text-slate-300 text-xs font-bold">—</span>
                                    </td>

                                    <!-- Cuántos en la Red -->
                                    <td class="px-5 py-5">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-extrabold text-xs">
                                                {{ model.equipment_count || model.occurrences_count || 0 }}
                                            </span>
                                            <span class="text-[11px] font-bold text-slate-400">equipos</span>
                                        </div>
                                    </td>

                                    <!-- Acciones -->
                                    <td class="px-7 py-5 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <button 
                                                @click="openEditModal(model)" 
                                                title="Editar modelo oficial"
                                                class="p-2.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all cursor-pointer"
                                            >
                                                <Edit2 :size="16" />
                                            </button>
                                            <button 
                                                @click="promptDelete(model)" 
                                                title="Eliminar modelo"
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

            </div>

        </div>

        <!-- MODAL DE CREACIÓN / APROBACIÓN / EDICIÓN -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs overflow-y-auto">
            <div class="relative w-full max-w-xl bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 overflow-hidden my-8 animate-in fade-in zoom-in-95 duration-200">
                
                <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 block">
                            {{ isEditing ? 'Editar Modelo Oficial' : (form.source === 'COMMUNITY_APPROVED' ? 'Aprobar Aporte de la Comunidad' : 'Registrar Nuevo Modelo Oficial') }}
                        </span>
                        <h3 class="text-xl font-black text-slate-900">
                            {{ form.brand && form.model ? `${form.brand} ${form.model}` : 'Ficha Técnica Oficial' }}
                        </h3>
                    </div>
                    <button @click="isModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 transition-colors">
                        <X :size="20" />
                    </button>
                </div>

                <form @submit.prevent="submitForm">
                    <div class="p-8 space-y-5 max-h-[65vh] overflow-y-auto">
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Marca *</label>
                                <input 
                                    v-model="form.brand" 
                                    type="text" 
                                    required 
                                    placeholder="Ej: Samsung, Whirlpool, LG" 
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                                />
                                <p v-if="form.errors.brand" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.brand }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Modelo *</label>
                                <input 
                                    v-model="form.model" 
                                    type="text" 
                                    required 
                                    placeholder="Ej: RT38K5930SL" 
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                                />
                                <p v-if="form.errors.model" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.model }}</p>
                            </div>
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
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Tipo de Arquetipo *</label>
                                <select 
                                    v-model="form.type_id" 
                                    required 
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                                >
                                    <option v-for="t in modalFilteredTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Potencia Nominal Oficial (Watts) *</label>
                                <input 
                                    v-model.number="form.nominal_power_w" 
                                    type="number" 
                                    required 
                                    min="0"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                                />
                                <p v-if="form.errors.nominal_power_w" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.nominal_power_w }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Etiqueta Energética</label>
                                <select 
                                    v-model="form.energy_label" 
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                                >
                                    <option value="">Sin etiqueta declarada</option>
                                    <option value="A+++">A+++</option>
                                    <option value="A++">A++</option>
                                    <option value="A+">A+</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                    <option value="E">E</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Capacidad / Dimensión</label>
                                <input 
                                    v-model.number="form.capacity" 
                                    type="number" 
                                    step="0.1" 
                                    placeholder="Ej: 380, 3000, 8" 
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Unidad de Capacidad</label>
                                <input 
                                    v-model="form.capacity_unit" 
                                    type="text" 
                                    placeholder="Ej: litros, frigorías, kg" 
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                                />
                            </div>
                        </div>

                        <!-- Switch Inverter -->
                        <div class="pt-2 border-t border-slate-100">
                            <label class="flex items-center justify-between p-3.5 bg-slate-50 rounded-2xl border border-slate-100 hover:bg-slate-100/70 transition-colors cursor-pointer">
                                <div>
                                    <span class="text-xs font-black text-slate-900 block">¿Es motor / compresor Inverter?</span>
                                    <span class="text-[11px] font-medium text-slate-500">Auto-completará la casilla Inverter cuando el usuario elija este modelo.</span>
                                </div>
                                <input type="checkbox" v-model="form.is_inverter" class="w-5 h-5 rounded-lg text-emerald-600 focus:ring-emerald-500 border-slate-300" />
                            </label>
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Notas internas / Especificaciones</label>
                            <textarea 
                                v-model="form.notes" 
                                rows="2"
                                placeholder="Detalles de la ficha técnica oficial..." 
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-medium text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all"
                            ></textarea>
                        </div>

                    </div>

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
                            <span v-else>{{ isEditing ? 'Guardar Cambios' : 'Oficializar y Publicar' }}</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- MODAL DE CONFIRMACIÓN DE BORRADO -->
        <div v-if="isDeleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="relative w-full max-w-md bg-white rounded-[2.5rem] p-8 shadow-2xl border border-slate-100 space-y-6 animate-in fade-in zoom-in-95 duration-200">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <AlertTriangle :size="28" />
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-900">¿Eliminar modelo oficial?</h3>
                    <p class="text-sm font-medium text-slate-600 mt-2 leading-relaxed">
                        ¿Estás seguro de que deseas eliminar <strong>"{{ modelToDelete?.brand }} {{ modelToDelete?.model }}"</strong>?
                        <br class="mb-2" />
                        Los usuarios que ya tengan este equipo conservarán sus datos intactos, pero dejará de autocompletarse en nuevas cargas.
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
                        @click="confirmDelete" 
                        class="px-5 py-2.5 text-xs font-black text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md transition-all cursor-pointer"
                    >
                        Eliminar Modelo
                    </button>
                </div>
            </div>
        </div>

    </MainLayout>
</template>
