<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    DollarSign, 
    ArrowLeft, 
    ChevronRight, 
    Zap, 
    Building, 
    Search,
    TrendingUp,
    Clock,
    Filter,
    Activity,
    History
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    periods: Array,
    selectedPeriodId: [String, Number],
    equipmentData: Array,
    pricePerKwh: Number
});

const searchQuery = ref('');

const filteredData = computed(() => {
    if (!searchQuery.value) return props.equipmentData;
    const q = searchQuery.value.toLowerCase();
    return props.equipmentData.filter(d => 
        d.name.toLowerCase().includes(q) || 
        d.room.toLowerCase().includes(q) || 
        d.category.toLowerCase().includes(q)
    );
});

const changePeriod = (id) => {
    router.get(route('analisis.equipment-cost'), { period_id: id }, { preserveState: true });
};

const formatDate = (start, end) => {
    return new Date(start).toLocaleDateString('es-ES', { month: 'short' }) + ' - ' + 
           new Date(end).toLocaleDateString('es-ES', { month: 'short', year: '2-digit' });
};
</script>

<template>
    <MainLayout>
        <Head title="Coste por Equipo" />

        <div class="max-w-7xl mx-auto space-y-10 pb-20">
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-4 text-slate-400">
                <Link :href="route('analisis.consumption')" class="hover:text-emerald-500 transition-colors flex items-center gap-2 text-xs font-bold uppercase tracking-widest">
                    <ArrowLeft :size="14" />
                    Consumo Real
                </Link>
                <span class="text-slate-200">/</span>
                <Link :href="route('analisis.time')" class="hover:text-indigo-500 transition-colors flex items-center gap-2 text-xs font-bold uppercase tracking-widest">
                    <History :size="14" />
                    Evolución
                </Link>
                <span class="text-slate-200">/</span>
                <span class="text-xs font-bold uppercase tracking-widest text-slate-300 italic">Impacto por Equipo</span>
            </div>

            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-500/10 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-500/20">
                        <DollarSign :size="14" />
                        Auditoría de Gastos
                    </div>
                    <h1 class="text-5xl font-black text-slate-900 tracking-tighter leading-none">
                        Coste por <span class="text-emerald-500">Equipo</span>
                    </h1>
                    <p class="text-lg text-slate-500 font-medium">Análisis monetario de cada artefacto en base a la tarifa del periodo.</p>
                </div>

                <div class="flex flex-col gap-2">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest text-right px-2">Seleccionar Periodo</p>
                    <div class="flex items-center gap-2">
                        <select 
                            :value="selectedPeriodId" 
                            @change="changePeriod($event.target.value)"
                            class="bg-white border border-slate-100 rounded-3xl py-4 px-8 text-sm font-black text-slate-900 shadow-2xl shadow-slate-200/40 outline-none focus:ring-4 focus:ring-emerald-500/10 transition-all appearance-none pr-12 relative"
                        >
                            <option v-for="p in periods" :key="p.id" :value="p.id">
                                {{ formatDate(p.start_date, p.end_date) }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-slate-900 rounded-[40px] p-8 text-white relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 opacity-10">
                        <DollarSign :size="120" />
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 text-white/50">Precio Energía Tarifa</p>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-black">${{ pricePerKwh.toFixed(2) }}</span>
                        <span class="text-xs font-bold text-white/40">/ kWh</span>
                    </div>
                </div>
                <div class="bg-white border border-slate-100 rounded-[40px] p-8 shadow-2xl shadow-slate-200/20">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4 text-slate-300">Total en Pesos</p>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-black text-slate-900">${{ filteredData.reduce((acc, d) => acc + d.cost, 0).toLocaleString('es-ES', { minimumFractionDigits: 2 }) }}</span>
                    </div>
                </div>
                <div class="bg-emerald-50 border border-emerald-100 rounded-[40px] p-8 relative group cursor-pointer overflow-hidden">
                    <div class="absolute top-0 right-0 p-6 text-emerald-200 group-hover:scale-110 transition-transform">
                        <TrendingUp :size="40" />
                    </div>
                    <p class="text-[10px] font-black text-emerald-600 uppercase tracking-[0.2em] mb-4">Gasto Mayor</p>
                    <div class="space-y-1">
                        <div class="text-2xl font-black text-emerald-900 truncate pr-10">{{ filteredData[0]?.name || '-' }}</div>
                        <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">${{ (filteredData[0]?.cost || 0).toLocaleString('es-ES', { minimumFractionDigits: 2 }) }}</p>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-white rounded-[56px] border border-slate-100 shadow-2xl shadow-slate-200/40 overflow-hidden">
                <div class="p-10 border-b border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-8">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tighter">Impacto por Artefacto</h3>
                        <p class="text-xs text-slate-400 font-medium mt-1">Lista ordenada por impacto económico descendente</p>
                    </div>
                    <div class="relative max-w-sm w-full">
                        <Search :size="18" class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300" />
                        <input 
                            v-model="searchQuery" 
                            type="text" 
                            placeholder="Filtrar por nombre, área o categoría..." 
                            class="w-full bg-slate-50 border-none rounded-[24px] py-4 pl-14 pr-8 text-sm font-bold text-slate-900 focus:ring-4 focus:ring-emerald-500/10 transition-all outline-none placeholder:text-slate-300"
                        />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-10 py-5 text-[10px] font-black text-slate-300 uppercase tracking-[0.15em]">Equipo</th>
                                <th class="px-10 py-5 text-[10px] font-black text-slate-300 uppercase tracking-[0.15em]">Área / Categoría</th>
                                <th class="px-10 py-5 text-[10px] font-black text-slate-300 uppercase tracking-[0.15em] text-center">Uso/Día</th>
                                <th class="px-10 py-5 text-[10px] font-black text-slate-300 uppercase tracking-[0.15em] text-right">Consumo</th>
                                <th class="px-10 py-5 text-[10px] font-black text-emerald-400 uppercase tracking-[0.15em] text-right">Impacto en Pesos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="item in filteredData" :key="item.id" class="group hover:bg-emerald-50/40 transition-all duration-300">
                                <td class="px-10 py-8">
                                    <div class="flex items-center gap-5">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 group-hover:bg-white group-hover:text-emerald-500 shadow-sm transition-all duration-500 group-hover:scale-110">
                                            <Zap :size="20" />
                                        </div>
                                        <div class="space-y-1">
                                            <p class="text-base font-black text-slate-900 tracking-tight group-hover:text-emerald-600 transition-colors">{{ item.name }}</p>
                                            <div class="flex items-center gap-2">
                                                <div class="w-2 h-2 rounded-full bg-emerald-500/30"></div>
                                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">ID: {{ item.id }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-10 py-8">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 text-slate-900 font-bold text-xs uppercase tracking-tight">
                                            <Building :size="14" class="text-slate-400" />
                                            {{ item.room }}
                                        </div>
                                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.1em]">{{ item.category }}</p>
                                    </div>
                                </td>
                                <td class="px-10 py-8 text-center">
                                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 rounded-xl text-slate-600 group-hover:bg-white transition-colors">
                                        <Clock :size="14" />
                                        <span class="text-sm font-black">{{ item.hours }}h</span>
                                    </div>
                                </td>
                                <td class="px-10 py-8 text-right">
                                    <div class="text-sm font-black text-slate-500 group-hover:text-slate-900 transition-colors">
                                        {{ Math.round(item.kwh) }} <span class="text-[10px] text-slate-300">kWh</span>
                                    </div>
                                </td>
                                <td class="px-10 py-8 text-right">
                                    <div class="flex flex-col items-end">
                                        <span class="text-xl font-black text-slate-900 group-hover:text-emerald-600 transition-colors">${{ item.cost.toLocaleString('es-ES', { minimumFractionDigits: 2 }) }}</span>
                                        <div class="h-1 w-20 bg-emerald-100 rounded-full mt-2 overflow-hidden">
                                            <div class="h-full bg-emerald-500" :style="{ width: (item.cost / filteredData[0].cost * 100) + '%' }"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Audit Note -->
            <div class="bg-emerald-900 rounded-[48px] p-12 text-white relative overflow-hidden shadow-2xl shadow-emerald-900/40">
                <div class="absolute -right-20 -bottom-20 opacity-10">
                    <DollarSign :size="300" />
                </div>
                <div class="relative z-10 flex flex-col md:flex-row items-center gap-12">
                    <div class="w-20 h-20 bg-emerald-500 rounded-3xl flex items-center justify-center text-white shadow-2xl shadow-emerald-500/50 rotate-3 shrink-0">
                        <Activity :size="40" />
                    </div>
                    <div class="space-y-4">
                        <h4 class="text-3xl font-black tracking-tighter">Auditoría Cuántica de Gastos</h4>
                        <p class="text-emerald-100 font-medium leading-relaxed max-w-4xl opacity-80">
                            Esta vista calcula el coste real de cada equipo multiplicando su consumo reconciliado por el precio por kWh promedio de este periodo. 
                            Es la herramienta definitiva para decidir **qué equipo reemplazar** o **dónde reducir horas de uso** para ver un impacto directo en tu próxima factura.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
