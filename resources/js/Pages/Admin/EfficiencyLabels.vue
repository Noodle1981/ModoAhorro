<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { 
    Sliders, 
    Info, 
    RefreshCw, 
    Plus, 
    Trash2, 
    Check, 
    X, 
    AlertTriangle
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    coefficients: Array,
    categories: Array,
    equipmentTypes: Array,
});

// Categorías agrupadas
const grouped = computed(() => {
    return props.categories.map(cat => ({
        ...cat,
        labels: props.coefficients.filter(c => c.category_id === cat.id)
    })).filter(cat => cat.labels.length > 0);
});

// Estado de Edición Rápida Inline
const inlineEditingId = ref(null);
const inlineCoeffValue = ref(1.0);

const startInlineEdit = (coeff) => {
    inlineEditingId.value = coeff.id;
    inlineCoeffValue.value = coeff.coefficient;
};

const cancelInlineEdit = () => {
    inlineEditingId.value = null;
};

const saveInlineEdit = (coeff) => {
    router.put(route('sistema.efficiency.update', coeff.id), {
        coefficient: inlineCoeffValue.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            inlineEditingId.value = null;
        }
    });
};

// Modal de Creación
const isCreateModalOpen = ref(false);
const availableLabels = ['A+++', 'A++', 'A+', 'A', 'B', 'C', 'D', 'E', 'F', 'G'];

const createForm = useForm({
    category_id: props.categories.length > 0 ? props.categories[0].id : '',
    equipment_type_id: '',
    label: 'A',
    coefficient: 1.0,
});

const openCreateModal = () => {
    createForm.reset();
    createForm.clearErrors();
    createForm.category_id = props.categories.length > 0 ? props.categories[0].id : '';
    createForm.label = 'A';
    createForm.coefficient = 1.0;
    isCreateModalOpen.value = true;
};

const submitCreate = () => {
    createForm.post(route('sistema.efficiency.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isCreateModalOpen.value = false;
        }
    });
};

// Modal de Eliminación
const isDeleteModalOpen = ref(false);
const coeffToDelete = ref(null);

const promptDelete = (coeff) => {
    coeffToDelete.value = coeff;
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    if (!coeffToDelete.value) return;
    router.delete(route('sistema.efficiency.destroy', coeffToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            coeffToDelete.value = null;
        }
    });
};

// Modal de Restablecimiento de Fábrica
const isResetModalOpen = ref(false);
const isResetting = ref(false);

const confirmReset = () => {
    isResetting.value = true;
    router.post(route('sistema.efficiency.reset'), {}, {
        preserveScroll: true,
        onFinish: () => {
            isResetting.value = false;
            isResetModalOpen.value = false;
        }
    });
};
</script>

<template>
    <Head title="Matriz de Eficiencia Energética · Administrador" />

    <MainLayout>
        <div class="max-w-7xl mx-auto space-y-8">
            
            <!-- Header Ribbon -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 sm:p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                <div>
                    <span class="text-xs font-black uppercase tracking-widest text-emerald-600 block mb-1">
                        Normativas & Curvas de Consumo
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Matriz de Eficiencia Energética
                    </h1>
                    <p class="text-slate-500 font-medium text-sm mt-1">
                        Ajustá los multiplicadores de consumo de las etiquetas IRAM (A+++ a G) por categoría o tipo de equipo.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button 
                        @click="isResetModalOpen = true"
                        title="Restablecer estándares IRAM de fábrica"
                        class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-black text-xs px-5 py-3.5 rounded-2xl transition-all cursor-pointer uppercase tracking-wider"
                    >
                        <RefreshCw :size="15" />
                        <span>Restablecer Fábrica</span>
                    </button>

                    <button 
                        @click="openCreateModal"
                        class="inline-flex items-center gap-2 bg-[#009966] hover:bg-[#008055] text-white font-black text-xs px-6 py-3.5 rounded-2xl shadow-lg shadow-emerald-900/10 hover:shadow-emerald-900/20 transition-all hover:-translate-y-0.5 cursor-pointer uppercase tracking-wider shrink-0"
                    >
                        <Plus :size="16" />
                        <span>Nuevo Coeficiente</span>
                    </button>
                </div>
            </div>

            <!-- Grid de Categorías -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                <div 
                    v-for="cat in grouped" 
                    :key="cat.id" 
                    class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xs flex flex-col justify-between"
                >
                    <div>
                        <!-- Category Header -->
                        <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
                            <div class="flex items-center gap-3.5">
                                <div class="w-11 h-11 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center">
                                    <Sliders :size="20" />
                                </div>
                                <div>
                                    <h2 class="text-xl font-black text-slate-900 tracking-tight">{{ cat.name }}</h2>
                                    <p class="text-[11px] font-bold text-slate-400 mt-0.5">{{ cat.description || 'Línea de consumo estandarizada' }}</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-slate-100 rounded-full text-slate-600 font-black text-[10px] uppercase tracking-wider">
                                {{ cat.labels.length }} Clases
                            </span>
                        </div>

                        <!-- Labels List -->
                        <div class="space-y-3">
                            <div 
                                v-for="coeff in cat.labels" 
                                :key="coeff.id" 
                                class="flex items-center justify-between p-3.5 sm:p-4 bg-slate-50/80 hover:bg-white rounded-2xl border border-slate-100/80 hover:border-emerald-500/20 hover:shadow-md transition-all group"
                            >
                                <div class="flex items-center gap-3.5">
                                    <!-- Badge Etiqueta -->
                                    <div 
                                        :class="[
                                            'w-13 h-8 rounded-xl flex items-center justify-center text-xs font-black text-white shadow-xs tracking-wider shrink-0',
                                            coeff.label.startsWith('A') ? 'bg-emerald-500' : 
                                            coeff.label === 'B' ? 'bg-lime-500' :
                                            coeff.label === 'C' ? 'bg-amber-500' :
                                            coeff.label === 'D' ? 'bg-orange-500' : 'bg-rose-500'
                                        ]"
                                    >
                                        {{ coeff.label }}
                                    </div>

                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-black text-slate-900">Clase {{ coeff.label }}</span>
                                            <span v-if="coeff.equipment_type" class="px-2 py-0.5 rounded-md bg-sky-50 text-sky-700 text-[9px] font-black border border-sky-100">
                                                {{ coeff.equipment_type.name }}
                                            </span>
                                        </div>
                                        <!-- Comparación vs Clase A -->
                                        <p class="text-[11px] font-bold mt-0.5" :class="coeff.coefficient < 1 ? 'text-emerald-600' : coeff.coefficient > 1 ? 'text-rose-600' : 'text-slate-400'">
                                            {{ coeff.coefficient < 1 ? `Ahorro de ${((1 - coeff.coefficient) * 100).toFixed(0)}% vs Clase A` : coeff.coefficient > 1 ? `+${((coeff.coefficient - 1) * 100).toFixed(0)}% consumo vs Clase A` : 'Línea de Base (1.00x)' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Multiplicador & Acciones -->
                                <div class="flex items-center gap-3">
                                    
                                    <!-- Modo Edición Inline -->
                                    <div v-if="inlineEditingId === coeff.id" class="flex items-center gap-2">
                                        <input 
                                            v-model.number="inlineCoeffValue" 
                                            type="number" 
                                            step="0.05" 
                                            min="0.1" 
                                            max="5.0" 
                                            class="w-20 px-2.5 py-1.5 bg-white border-2 border-emerald-500 rounded-xl text-sm font-black text-slate-900 focus:outline-none"
                                            @keyup.enter="saveInlineEdit(coeff)"
                                            @keyup.esc="cancelInlineEdit"
                                        />
                                        <button 
                                            @click="saveInlineEdit(coeff)" 
                                            title="Guardar"
                                            class="p-1.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors cursor-pointer"
                                        >
                                            <Check :size="14" />
                                        </button>
                                        <button 
                                            @click="cancelInlineEdit" 
                                            title="Cancelar"
                                            class="p-1.5 bg-slate-200 text-slate-600 rounded-lg hover:bg-slate-300 transition-colors cursor-pointer"
                                        >
                                            <X :size="14" />
                                        </button>
                                    </div>

                                    <!-- Modo Vista Normal -->
                                    <div v-else class="flex items-center gap-3">
                                        <button 
                                            @click="startInlineEdit(coeff)"
                                            title="Click para editar multiplicador"
                                            class="text-right cursor-pointer group/btn py-1 px-2.5 rounded-xl hover:bg-emerald-50 transition-colors"
                                        >
                                            <span class="text-base sm:text-lg font-black text-slate-900 group-hover/btn:text-emerald-600 block">
                                                {{ Number(coeff.coefficient).toFixed(2) }}x
                                            </span>
                                            <span class="text-[9px] font-extrabold uppercase text-slate-400 block group-hover/btn:text-emerald-600">
                                                Editar
                                            </span>
                                        </button>

                                        <button 
                                            @click="promptDelete(coeff)"
                                            title="Eliminar este coeficiente"
                                            class="p-2 text-slate-300 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all opacity-0 group-hover:opacity-100 cursor-pointer"
                                        >
                                            <Trash2 :size="15" />
                                        </button>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ROI & Engineering Note -->
            <div class="p-8 bg-linear-to-r from-emerald-950 to-slate-900 rounded-[2.5rem] text-emerald-100 flex gap-6 items-start overflow-hidden relative shadow-xl">
                <div class="w-14 h-14 bg-emerald-900/60 border border-emerald-700/50 rounded-2xl flex items-center justify-center text-emerald-400 shadow-inner shrink-0 relative z-10">
                    <Info :size="28" />
                </div>
                <div class="relative z-10">
                    <h4 class="text-lg font-black text-white mb-1.5 tracking-tight">Impacto en los Cálculos de ROI y Reemplazos</h4>
                    <p class="text-emerald-200/80 leading-relaxed max-w-4xl text-sm font-medium">
                        Cuando un usuario no declara etiqueta energética en su electrodoméstico, el motor asume automáticamente un multiplicador neutral de <strong>1.00x (Línea de Base)</strong>. 
                        Cuando el usuario tiene un equipo antiguo Clase D (1.60x) o Clase E (1.80x), el módulo de <strong>Reemplazos Eficientes</strong> compara su costo anual contra un modelo Clase A (1.00x) o Clase A+++ (0.65x) para estimar los pesos ahorrados por mes y el tiempo exacto de amortización.
                    </p>
                </div>
            </div>

        </div>

        <!-- MODAL DE CREACIÓN DE COEFICIENTE -->
        <div v-if="isCreateModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="relative w-full max-w-md bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 block">Matriz IRAM</span>
                        <h3 class="text-xl font-black text-slate-900">Nuevo Coeficiente</h3>
                    </div>
                    <button @click="isCreateModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100">
                        <X :size="20" />
                    </button>
                </div>

                <form @submit.prevent="submitCreate" class="p-8 space-y-4">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Categoría *</label>
                        <select 
                            v-model="createForm.category_id" 
                            required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                        >
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Clase / Letra *</label>
                        <select 
                            v-model="createForm.label" 
                            required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                        >
                            <option v-for="lbl in availableLabels" :key="lbl" :value="lbl">Clase {{ lbl }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Multiplicador de Consumo (x) *</label>
                        <input 
                            v-model.number="createForm.coefficient" 
                            type="number" 
                            step="0.05" 
                            min="0.1" 
                            max="10.0" 
                            required 
                            placeholder="Ej: 0.85 o 1.40" 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                        />
                        <span class="text-[11px] font-medium text-slate-400 mt-1 block">1.0 = Estándar. Menor a 1.0 = Ahorro. Mayor a 1.0 = Sobreconsumo.</span>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <button 
                            type="button" 
                            @click="isCreateModalOpen = false" 
                            class="px-5 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 rounded-xl"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            :disabled="createForm.processing"
                            class="bg-[#009966] hover:bg-[#008055] text-white font-black text-xs uppercase tracking-wider px-6 py-3 rounded-2xl shadow-md cursor-pointer disabled:opacity-50"
                        >
                            Guardar Coeficiente
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
                    <h3 class="text-xl font-black text-slate-900">¿Eliminar coeficiente?</h3>
                    <p class="text-sm font-medium text-slate-600 mt-2 leading-relaxed">
                        ¿Estás seguro de que deseas eliminar el coeficiente para <strong>Clase {{ coeffToDelete?.label }}</strong>? 
                        Si un usuario tiene un equipo con esta letra, pasará a usar el valor neutral 1.00x.
                    </p>
                </div>
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button @click="isDeleteModalOpen = false" class="px-5 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 rounded-xl">
                        Cancelar
                    </button>
                    <button @click="confirmDelete" class="px-5 py-2.5 text-xs font-black text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL DE CONFIRMACIÓN DE RESTABLECIMIENTO -->
        <div v-if="isResetModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="relative w-full max-w-md bg-white rounded-[2.5rem] p-8 shadow-2xl border border-slate-100 space-y-6 animate-in fade-in zoom-in-95 duration-200">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <RefreshCw :size="28" />
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-900">¿Restablecer matriz a fábrica?</h3>
                    <p class="text-sm font-medium text-slate-600 mt-2 leading-relaxed">
                        Esta acción reescribirá todos los coeficientes de las categorías a los valores oficiales de las normas IRAM estándar.
                    </p>
                </div>
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button @click="isResetModalOpen = false" class="px-5 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 rounded-xl">
                        Cancelar
                    </button>
                    <button 
                        @click="confirmReset" 
                        :disabled="isResetting"
                        class="px-5 py-2.5 text-xs font-black text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-md disabled:opacity-50"
                    >
                        {{ isResetting ? 'Restableciendo...' : 'Restablecer Valores' }}
                    </button>
                </div>
            </div>
        </div>

    </MainLayout>
</template>

