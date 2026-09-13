<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { 
    Plus, 
    Star, 
    Edit2, 
    Trash2, 
    Search, 
    Award, 
    ExternalLink, 
    X, 
    AlertTriangle,
    ShoppingBag
} from 'lucide-vue-next';
import { shallowRef, computed } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    benchmarks: Array,
    categories: Array,
    equipmentTypes: Array,
});

const searchQuery = shallowRef('');
const selectedCategory = shallowRef('all');

const filteredBenchmarks = computed(() => {
    return props.benchmarks.filter(b => {
        const query = searchQuery.value.toLowerCase();
        const matchesSearch = b.name.toLowerCase().includes(query) ||
                              (b.meli_search_term && b.meli_search_term.toLowerCase().includes(query)) ||
                              (b.category?.name && b.category.name.toLowerCase().includes(query)) ||
                              (b.equipment_type?.name && b.equipment_type.name.toLowerCase().includes(query));
        const matchesCategory = selectedCategory.value === 'all' || b.category_id === Number(selectedCategory.value);

        return matchesSearch && matchesCategory;
    });
});

// Modales
const isModalOpen = shallowRef(false);
const isEditing = shallowRef(false);
const currentBenchmarkId = shallowRef(null);

const isDeleteModalOpen = shallowRef(false);
const benchmarkToDelete = shallowRef(null);

const form = useForm({
    category_id: '',
    equipment_type_id: '',
    name: '',
    energy_label: 'A+++',
    watts: 100,
    efficiency_gain_factor: 0.35,
    average_market_price: 0,
    meli_search_term: '',
    affiliate_link: '',
    recommendation_text: '',
});

const modalFilteredTypes = computed(() => {
    if (!form.category_id) return props.equipmentTypes;
    return props.equipmentTypes.filter(t => t.category_id === Number(form.category_id));
});

const openCreateModal = () => {
    isEditing.value = false;
    currentBenchmarkId.value = null;
    form.reset();
    form.clearErrors();
    form.category_id = props.categories.length > 0 ? props.categories[0].id : '';
    form.equipment_type_id = '';
    form.energy_label = 'A+++';
    form.watts = 100;
    form.efficiency_gain_factor = 0.35;
    form.average_market_price = 450000;
    isModalOpen.value = true;
};

const openEditModal = (benchmark) => {
    isEditing.value = true;
    currentBenchmarkId.value = benchmark.id;
    form.clearErrors();
    form.category_id = benchmark.category_id;
    form.equipment_type_id = benchmark.equipment_type_id || '';
    form.name = benchmark.name;
    form.energy_label = benchmark.energy_label || 'A+++';
    form.watts = benchmark.watts;
    form.efficiency_gain_factor = benchmark.efficiency_gain_factor ?? 0.35;
    form.average_market_price = benchmark.average_market_price ?? 0;
    form.meli_search_term = benchmark.meli_search_term || '';
    form.affiliate_link = benchmark.affiliate_link || '';
    form.recommendation_text = benchmark.recommendation_text || '';
    isModalOpen.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('sistema.benchmarks.update', currentBenchmarkId.value), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
            }
        });
    } else {
        form.post(route('sistema.benchmarks.store'), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
            }
        });
    }
};

const promptDelete = (benchmark) => {
    benchmarkToDelete.value = benchmark;
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    if (!benchmarkToDelete.value) return;
    router.delete(route('sistema.benchmarks.destroy', benchmarkToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            benchmarkToDelete.value = null;
        }
    });
};
</script>

<template>
    <Head title="Benchmarks de Eficiencia · Administrador" />

    <MainLayout>
        <div class="max-w-7xl mx-auto space-y-8">
            
            <!-- Page Header Ribbon -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 sm:p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                <div>
                    <span class="text-xs font-black uppercase tracking-widest text-emerald-600 block mb-1">
                        Estándares de Reemplazo & ROI
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Benchmarks de Referencia del Mercado
                    </h1>
                    <p class="text-slate-500 font-medium text-sm mt-1">
                        Configurá los modelos estándar de alta eficiencia con los que se comparan los equipos antiguos para calcular el retorno de inversión.
                    </p>
                </div>

                <button 
                    @click="openCreateModal"
                    class="inline-flex items-center justify-center gap-2 bg-[#009966] hover:bg-[#008055] text-white font-extrabold text-xs px-6 py-4 rounded-2xl shadow-lg shadow-emerald-900/10 hover:shadow-emerald-900/20 transition-all hover:-translate-y-0.5 cursor-pointer uppercase tracking-wider shrink-0"
                >
                    <Plus :size="18" />
                    <span>Nuevo Benchmark</span>
                </button>
            </div>

            <!-- Toolbar & Filtros -->
            <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm flex flex-col lg:flex-row items-center gap-3">
                <div class="relative w-full lg:flex-1">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="18" />
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Buscar por nombre, producto recomendado o categoría..." 
                        class="w-full pl-12 pr-6 py-3 bg-slate-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-medium text-sm text-slate-800"
                    />
                </div>

                <select 
                    v-model="selectedCategory"
                    class="px-4 py-3 bg-slate-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-bold text-slate-700 text-xs min-w-[200px]"
                >
                    <option value="all">Todas las Categorías</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                </select>
            </div>

            <!-- Grid de Benchmarks -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <div 
                    v-for="benchmark in filteredBenchmarks" 
                    :key="benchmark.id" 
                    class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xs hover:shadow-xl transition-all duration-300 overflow-hidden group flex flex-col justify-between"
                >
                    <div>
                        <!-- Card Header -->
                        <div class="p-7 pb-4">
                            <div class="flex justify-between items-start mb-4">
                                <div class="w-12 h-12 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-500 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-xs">
                                    <Star :size="22" />
                                </div>
                                <span 
                                    :class="[
                                        'px-3.5 py-1 text-[10px] font-black uppercase tracking-widest rounded-full shadow-xs text-white',
                                        benchmark.energy_label?.startsWith('A') ? 'bg-emerald-500' : 'bg-lime-500'
                                    ]"
                                >
                                    Clase {{ benchmark.energy_label }}
                                </span>
                            </div>

                            <h3 class="text-xl font-black text-slate-900 tracking-tight leading-snug group-hover:text-emerald-600 transition-colors">
                                {{ benchmark.name }}
                            </h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                                    {{ benchmark.category?.name }}
                                </span>
                                <span v-if="benchmark.equipment_type" class="text-[11px] font-bold text-emerald-600">
                                    · {{ benchmark.equipment_type.name }}
                                </span>
                            </div>
                        </div>

                        <!-- Metrics Ribbon -->
                        <div class="px-7 py-4 bg-slate-50 border-y border-slate-100 grid grid-cols-3 gap-2 text-center">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Potencia</p>
                                <p class="text-sm font-black text-slate-900">{{ benchmark.watts }} W</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Ahorro Base</p>
                                <p class="text-sm font-black text-emerald-600">{{ (benchmark.efficiency_gain_factor * 100).toFixed(0) }}%</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Inversión Ref.</p>
                                <p class="text-sm font-black text-slate-900">${{ (benchmark.average_market_price || 0).toLocaleString('es-AR') }}</p>
                            </div>
                        </div>

                        <!-- Recommendation Pitch -->
                        <div class="p-7 space-y-4">
                            <p class="text-slate-600 text-xs font-medium leading-relaxed italic bg-slate-50/60 p-3.5 rounded-2xl border border-slate-100">
                                "{{ benchmark.recommendation_text || 'Modelo recomendado por alta eficiencia y rápida amortización.' }}"
                            </p>

                            <div v-if="benchmark.meli_search_term" class="flex items-center gap-2 text-xs font-bold text-slate-500">
                                <ShoppingBag :size="14" class="text-amber-500 shrink-0" />
                                <span class="truncate">Búsqueda: <strong class="text-slate-800">{{ benchmark.meli_search_term }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="px-7 pb-6 pt-2 flex items-center justify-between border-t border-slate-50">
                        <div class="flex items-center gap-1">
                            <button 
                                @click="openEditModal(benchmark)"
                                title="Editar Benchmark"
                                class="p-2.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all cursor-pointer"
                            >
                                <Edit2 :size="16" />
                            </button>
                            <button 
                                @click="promptDelete(benchmark)"
                                title="Eliminar Benchmark"
                                class="p-2.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all cursor-pointer"
                            >
                                <Trash2 :size="16" />
                            </button>
                        </div>

                        <a 
                            v-if="benchmark.affiliate_link" 
                            :href="benchmark.affiliate_link" 
                            target="_blank" 
                            class="inline-flex items-center gap-1.5 text-xs font-black text-emerald-600 hover:underline uppercase tracking-wider"
                        >
                            <span>Ver Tienda</span>
                            <ExternalLink :size="13" />
                        </a>
                    </div>

                </div>

                <!-- Empty Card CTA -->
                <button 
                    @click="openCreateModal"
                    class="bg-slate-50/70 border-2 border-dashed border-slate-200 hover:border-emerald-500/50 hover:bg-emerald-50/40 rounded-[2.5rem] p-8 flex flex-col items-center justify-center gap-4 group transition-all cursor-pointer min-h-[350px]"
                >
                    <div class="w-16 h-16 bg-white rounded-3xl flex items-center justify-center text-slate-300 group-hover:text-emerald-600 shadow-sm transition-all">
                        <Plus :size="32" />
                    </div>
                    <span class="font-black text-slate-400 group-hover:text-emerald-700 uppercase tracking-widest text-xs">
                        Agregar Nuevo Benchmark
                    </span>
                </button>

            </div>
            
            <!-- ROI Education Banner -->
            <div class="bg-linear-to-r from-emerald-950 to-slate-900 p-8 sm:p-10 rounded-[3rem] text-emerald-100 flex flex-col md:flex-row items-center gap-8 shadow-xl">
                <div class="w-20 h-20 bg-emerald-800/80 border border-emerald-600/40 rounded-3xl flex items-center justify-center text-emerald-300 shadow-inner shrink-0">
                    <Award :size="40" />
                </div>
                <div>
                    <h3 class="text-2xl font-black text-white mb-2 tracking-tight">Motor de Amortización y Reemplazos Eficientes</h3>
                    <p class="text-emerald-200/80 text-sm leading-relaxed max-w-4xl font-medium">
                        Cuando un usuario tiene un equipo antiguo o ineficiente, el sistema calcula el consumo actual ($kWh$) y simula el reemplazo con estos <strong>Benchmarks de Referencia</strong>. 
                        Multiplicando los $kWh$ ahorrados por la tarifa real de la factura, el sistema calcula exactamente el <strong>Ahorro Mensual en Pesos ($)</strong> y el <strong>Plazo de Recupero de Inversión (Payback)</strong>.
                    </p>
                </div>
            </div>

        </div>

        <!-- MODAL DE CREACIÓN / EDICIÓN -->
        <Modal :show="isModalOpen" max-width="2xl" @close="isModalOpen = false">
            <div class="relative w-full overflow-hidden">
                
                <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 block">
                            {{ isEditing ? 'Modificar Benchmark de Referencia' : 'Nuevo Benchmark de Referencia' }}
                        </span>
                        <h3 class="text-xl font-black text-slate-900">
                            {{ isEditing ? form.name : 'Configurar Modelo Benchmark' }}
                        </h3>
                    </div>
                    <button @click="isModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100">
                        <X :size="20" />
                    </button>
                </div>

                <form @submit.prevent="submitForm">
                    <div class="p-8 space-y-5 max-h-[65vh] overflow-y-auto">
                        
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Nombre del Benchmark / Modelo Recomendado *</label>
                            <input 
                                v-model="form.name" 
                                type="text" 
                                required 
                                placeholder="Ej: Heladera No Frost Inverter Clase A+++ 380L" 
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Categoría *</label>
                                <select 
                                    v-model="form.category_id" 
                                    required 
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                                >
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Tipo de Arquetipo Asociado</label>
                                <select 
                                    v-model="form.equipment_type_id" 
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                                >
                                    <option value="">Aplica a toda la categoría</option>
                                    <option v-for="t in modalFilteredTypes" :key="t.id" :value="t.id">{{ t.name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-slate-100">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Clase Energética *</label>
                                <select 
                                    v-model="form.energy_label" 
                                    required 
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                                >
                                    <option value="A+++">A+++</option>
                                    <option value="A++">A++</option>
                                    <option value="A+">A+</option>
                                    <option value="A">A</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Potencia Ideal (W) *</label>
                                <input 
                                    v-model.number="form.watts" 
                                    type="number" 
                                    required 
                                    min="0"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Ahorro Base (0.01 - 0.99) *</label>
                                <input 
                                    v-model.number="form.efficiency_gain_factor" 
                                    type="number" 
                                    step="0.05"
                                    min="0.01" 
                                    max="0.99"
                                    required 
                                    placeholder="Ej: 0.35 para 35%" 
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Precio de Mercado Promedio ($)</label>
                                <input 
                                    v-model.number="form.average_market_price" 
                                    type="number" 
                                    min="0" 
                                    placeholder="Ej: 650000" 
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                                />
                                <span class="text-[11px] font-medium text-slate-400 mt-1 block">Inversión estimada para calcular el plazo de amortización (ROI).</span>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Término de Búsqueda (Mercado Libre)</label>
                                <input 
                                    v-model="form.meli_search_term" 
                                    type="text" 
                                    placeholder="Ej: Heladera No Frost Inverter A+++" 
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Link Afiliado / Tienda</label>
                            <input 
                                v-model="form.affiliate_link" 
                                type="url" 
                                placeholder="https://mercadolibre.com.ar/..." 
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Texto de Recomendación (Pitch al Usuario)</label>
                            <textarea 
                                v-model="form.recommendation_text" 
                                rows="3"
                                placeholder="Ej: Reemplazar tu equipo por un modelo Inverter Clase A+++ te permitirá reducir drásticamente el consumo continuo, amortizando la inversión en pocos meses." 
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-medium text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                            ></textarea>
                        </div>

                    </div>

                    <div class="px-8 py-5 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <button 
                            type="button" 
                            @click="isModalOpen = false" 
                            class="px-5 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 rounded-xl cursor-pointer"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="bg-[#009966] hover:bg-[#008055] text-white font-black text-xs uppercase tracking-wider px-6 py-3 rounded-2xl shadow-md cursor-pointer disabled:opacity-50"
                        >
                            {{ isEditing ? 'Guardar Cambios' : 'Registrar Benchmark' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- MODAL DE CONFIRMACIÓN DE BORRADO -->
        <Modal :show="isDeleteModalOpen" max-width="md" @close="isDeleteModalOpen = false">
            <div class="p-8 space-y-6">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <AlertTriangle :size="28" />
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-900">¿Eliminar benchmark?</h3>
                    <p class="text-sm font-medium text-slate-600 mt-2 leading-relaxed">
                        ¿Estás seguro de que deseas eliminar el benchmark <strong>"{{ benchmarkToDelete?.name }}"</strong>?
                    </p>
                </div>
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button @click="isDeleteModalOpen = false" class="px-5 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 rounded-xl cursor-pointer">
                        Cancelar
                    </button>
                    <button @click="confirmDelete" class="px-5 py-2.5 text-xs font-black text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md cursor-pointer">
                        Eliminar
                    </button>
                </div>
            </div>
        </Modal>

    </MainLayout>
</template>

