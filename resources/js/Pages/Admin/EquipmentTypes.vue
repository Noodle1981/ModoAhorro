<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { 
    Plus, 
    Search, 
    Edit2, 
    Trash2, 
    Zap, 
    ShieldAlert,
    ChevronDown,
    Save
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    equipmentTypes: Array,
    categories: Array
});

const searchQuery = ref('');
const selectedCategory = ref('all');
const selectedTank = ref('all');

const filteredTypes = computed(() => {
    return props.equipmentTypes.filter(t => {
        const matchesSearch = t.name.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesCategory = selectedCategory.value === 'all' || t.category_id === Number(selectedCategory.value);
        const matchesTank = selectedTank.value === 'all' || t.default_tank === selectedTank.value;
        
        return matchesSearch && matchesCategory && matchesTank;
    });
});

const tanks = [
    { value: 'TANK_0', name: 'Tanque 0 (Certeza)', color: 'bg-emerald-500' },
    { value: 'TANK_1', name: 'Tanque 1 (Base)', color: 'bg-sky-500' },
    { value: 'TANK_2', name: 'Tanque 2 (Clima)', color: 'bg-amber-500' },
    { value: 'TANK_3', name: 'Tanque 3 (Resto)', color: 'bg-slate-500' },
];

const editingId = ref(null);
const form = useForm({
    name: '',
    min_watts: 0,
    max_watts: 0,
    default_power_watts: 0,
    thermal_efficiency_penalty: 0,
    determinism_score: 0,
    social_coefficient: 0,
    default_tank: ''
});

const startEdit = (type) => {
    editingId.value = type.id;
    form.name = type.name;
    form.min_watts = type.min_watts;
    form.max_watts = type.max_watts;
    form.default_power_watts = type.default_power_watts;
    form.thermal_efficiency_penalty = type.thermal_efficiency_penalty;
    form.determinism_score = type.determinism_score;
    form.social_coefficient = type.social_coefficient;
};
</script>

<template>
    <Head title="Catálogo Maestro" />

    <MainLayout>
        <div class="max-w-7xl mx-auto">
            <!-- Header Title -->
            <div class="mb-8">
                <h1 class="text-4xl font-black text-slate-900 tracking-tighter mb-2">Catálogo Maestro</h1>
                <p class="text-slate-500 font-medium italic">Edita la física fundamental de los equipos de ModoAhorro.</p>
            </div>

            <!-- Toolbar Ribbon -->
            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm mb-12 flex items-center gap-3">
                <!-- Buscador -->
                <div class="relative flex-1">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="18" />
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Buscar por nombre de equipo..." 
                        class="w-full pl-12 pr-6 py-2.5 bg-slate-50 border-transparent rounded-xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-medium text-sm"
                    />
                </div>

                <!-- Filtro Categoría -->
                <div class="flex-none">
                    <select 
                        v-model="selectedCategory"
                        class="pl-4 pr-10 py-2.5 bg-slate-50 border-transparent rounded-xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-bold text-slate-600 text-sm min-w-[200px]"
                    >
                        <option value="all">Todas las Categorías</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                </div>

                <!-- Filtro Tanque -->
                <div class="flex-none">
                    <select 
                        v-model="selectedTank"
                        class="pl-4 pr-10 py-2.5 bg-slate-50 border-transparent rounded-xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-bold text-slate-600 text-sm min-w-[180px]"
                    >
                        <option value="all">Todos los Tanques</option>
                        <option v-for="tank in tanks" :key="tank.value" :value="tank.value">{{ tank.name }}</option>
                    </select>
                </div>

                <!-- Botón Acción -->
                <button class="bg-slate-900 text-white px-6 py-2.5 rounded-xl font-black uppercase tracking-widest text-[10px] flex items-center gap-2 hover:bg-emerald-600 transition-all shadow-lg shadow-slate-200 flex-none whitespace-nowrap">
                    <Plus :size="16" /> Nuevo Tipo
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100">
                            <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Equipo</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Tanque</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Potencia (Min/Max)</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Default</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Penalty</th>
                            <th class="px-6 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Tanque 0</th>
                            <th class="px-8 py-6 text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="type in filteredTypes" :key="type.id" class="hover:bg-slate-50/30 transition-colors group">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-all">
                                        <Zap :size="20" />
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900 leading-none mb-1">{{ type.name }}</p>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ type.category?.name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex items-center gap-2">
                                    <div :class="['w-2 h-2 rounded-full', tanks.find(tn => tn.value === type.default_tank)?.color || 'bg-slate-300']"></div>
                                    <span class="text-[10px] font-black text-slate-600 uppercase tracking-widest">
                                        {{ (type.default_tank && typeof type.default_tank === 'string') ? type.default_tank.replace('TANK_', 'T') : 'T3' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <span class="text-xs font-mono font-bold text-slate-600 bg-slate-100 px-2 py-1 rounded">
                                    {{ type.min_watts }}W - {{ type.max_watts }}W
                                </span>
                            </td>
                            <td class="px-6 py-6 font-bold text-slate-900">{{ type.default_power_watts }}W</td>
                            <td class="px-6 py-6">
                                <span :class="['text-xs font-black px-3 py-1 rounded-full', type.thermal_efficiency_penalty > 0 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600']">
                                    {{ type.thermal_efficiency_penalty > 0 ? '+' : '' }}{{ type.thermal_efficiency_penalty }}%
                                </span>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex items-center gap-2">
                                    <div class="w-12 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-sky-500" :style="{ width: (type.determinism_score * 100) + '%' }"></div>
                                    </div>
                                    <span class="text-[10px] font-black text-slate-500">{{ type.determinism_score }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <button @click="startEdit(type)" class="p-2 text-slate-400 hover:text-emerald-600 transition-colors">
                                    <Edit2 :size="18" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Info Alert -->
            <div class="mt-12 p-8 bg-sky-50 rounded-[2.5rem] border border-sky-100 flex gap-6 items-start">
                <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-sky-600 shadow-sm shrink-0">
                    <ShieldAlert :size="24" />
                </div>
                <div>
                    <h4 class="text-lg font-black text-sky-900 mb-2">Nota de Ingeniería</h4>
                    <p class="text-sky-800 leading-relaxed max-w-3xl">
                        Los cambios realizados aquí afectan retroactivamente a todas las calibraciones de facturas que no estén cerradas. 
                        Asegúrate de que los valores de <strong>Determinismo</strong> sean superiores a 0.9 solo para equipos cuya firma de consumo sea constante y predecible.
                    </p>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
