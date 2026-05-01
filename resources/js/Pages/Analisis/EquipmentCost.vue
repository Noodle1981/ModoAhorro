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
    History,
    ChevronDown,
    ChevronUp
} from 'lucide-vue-next';
import { Line, Bar } from 'vue-chartjs';
import { 
    Chart as ChartJS, 
    Title, Tooltip, Legend, 
    LineElement, PointElement, 
    LinearScale, CategoryScale, 
    BarElement, Filler 
} from 'chart.js';

ChartJS.register(
    Title, Tooltip, Legend, 
    LineElement, PointElement, 
    LinearScale, CategoryScale, 
    BarElement, Filler
);

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

// Lógica de Historial (Sparklines & Expanded)
const expandedRow = ref(null);

const toggleRow = (id) => {
    expandedRow.value = expandedRow.value === id ? null : id;
};

const getSparklineData = (history) => {
    return {
        labels: history.map(h => h.label),
        datasets: [{
            data: history.map(h => h.cost),
            borderColor: '#10b981', // emerald-500
            borderWidth: 2,
            tension: 0.4,
            pointRadius: 0
        }]
    };
};

const sparklineOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { enabled: false } },
    scales: { x: { display: false }, y: { display: false, min: 0 } },
    layout: { padding: 0 },
    animation: false
};

const getDetailedChartData = (history) => {
    return {
        labels: history.map(h => h.label),
        datasets: [
            {
                label: 'Coste ($)',
                data: history.map(h => h.cost),
                backgroundColor: '#10b981',
                borderRadius: 4,
                yAxisID: 'y'
            },
            {
                label: 'Consumo (kWh)',
                data: history.map(h => h.kwh),
                borderColor: '#94a3b8',
                backgroundColor: 'transparent',
                type: 'line',
                borderWidth: 2,
                borderDash: [5, 5],
                tension: 0.4,
                pointRadius: 4,
                yAxisID: 'y1'
            }
        ]
    };
};

const detailedChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 6, font: { size: 10, weight: '700' } } },
        tooltip: { backgroundColor: '#0f172a', padding: 12, cornerRadius: 12 }
    },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 10, weight: '600' } } },
        y: { position: 'left', grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 } }, beginAtZero: true },
        y1: { position: 'right', grid: { display: false }, ticks: { font: { size: 10 } }, beginAtZero: true }
    }
};
</script>

<template>
    <MainLayout>
        <Head title="Coste por Equipo" />

        <div class="max-w-7xl mx-auto space-y-10 pb-20">
            <!-- Minimalist Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <div class="flex items-center gap-2 text-slate-400 mb-1">
                        <Link :href="route('analisis.consumption')" class="hover:text-emerald-500 transition-colors text-[10px] font-black uppercase tracking-widest">Auditoría</Link>
                        <span class="text-slate-200">/</span>
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-300">Coste por Equipo</span>
                    </div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tighter leading-none flex items-center gap-3">
                        Impacto <span class="text-emerald-500">Económico</span>
                        <span v-if="selectedPeriodId === 'all'" class="text-[10px] px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-md uppercase font-black tracking-widest mt-1">Promedio Histórico</span>
                    </h1>
                </div>

                <div class="relative min-w-[240px]">
                    <select 
                        :value="selectedPeriodId" 
                        @change="changePeriod($event.target.value)"
                        class="w-full bg-white border border-slate-200 rounded-2xl py-3 px-5 text-sm font-bold text-slate-900 shadow-sm outline-none focus:ring-4 focus:ring-emerald-500/10 transition-all appearance-none pr-10"
                    >
                        <option value="all">Promedio Histórico (Global)</option>
                        <option v-for="p in periods" :key="p.id" :value="p.id">
                            Periodo: {{ formatDate(p.start_date, p.end_date) }}
                        </option>
                    </select>
                    <ChevronDown :size="16" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                </div>
            </div>

            <!-- Compact Stats Bar -->
            <div class="flex flex-wrap items-center gap-4">
                <div class="bg-slate-900 rounded-3xl p-5 text-white flex-1 min-w-[200px] relative overflow-hidden flex items-center justify-between shadow-xl shadow-slate-900/10">
                    <div>
                        <p class="text-[9px] font-black text-white/40 uppercase tracking-[0.2em] mb-1">Tarifa Promedio</p>
                        <p class="text-xl font-black">${{ pricePerKwh.toFixed(2) }}<span class="text-[10px] font-medium text-white/30 ml-1">/kWh</span></p>
                    </div>
                    <DollarSign :size="32" class="text-white/10" />
                </div>
                
                <div class="bg-white border border-slate-100 rounded-3xl p-5 flex-1 min-w-[200px] flex items-center justify-between shadow-lg shadow-slate-200/20">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">{{ selectedPeriodId === 'all' ? 'Gasto Prom. Periodo' : 'Total en Pesos' }}</p>
                        <p class="text-xl font-black text-slate-900">${{ filteredData.reduce((acc, d) => acc + d.cost, 0).toLocaleString('es-AR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) }}</p>
                    </div>
                    <Activity :size="32" class="text-slate-100" />
                </div>

                <div class="bg-emerald-50 border border-emerald-100 rounded-3xl p-5 flex-1 min-w-[200px] flex items-center justify-between group cursor-pointer hover:bg-emerald-100 transition-colors">
                    <div>
                        <p class="text-[9px] font-black text-emerald-600/60 uppercase tracking-[0.2em] mb-1">Mayor Impacto</p>
                        <p class="text-xl font-black text-emerald-900 truncate max-w-[140px]">{{ filteredData[0]?.name || '-' }}</p>
                    </div>
                    <TrendingUp :size="32" class="text-emerald-200 group-hover:scale-110 transition-transform" />
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-white rounded-[56px] border border-slate-100 shadow-2xl shadow-slate-200/40 overflow-hidden">
                <div class="p-6 lg:p-10 border-b border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-8">
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
                                <th class="px-6 lg:px-10 py-5 text-[10px] font-black text-slate-300 uppercase tracking-[0.15em]">Equipo</th>
                                <th class="px-6 lg:px-10 py-5 text-[10px] font-black text-slate-300 uppercase tracking-[0.15em]">Área / Categoría</th>
                                <th class="px-6 lg:px-10 py-5 text-[10px] font-black text-slate-300 uppercase tracking-[0.15em] text-center">Uso/Día</th>
                                <th class="px-6 lg:px-10 py-5 text-[10px] font-black text-slate-300 uppercase tracking-[0.15em] text-right">Consumo</th>
                                <th class="px-6 lg:px-10 py-5 text-[10px] font-black text-slate-300 uppercase tracking-[0.15em] text-center">Tendencia</th>
                                <th class="px-6 lg:px-10 py-5 text-[10px] font-black text-emerald-400 uppercase tracking-[0.15em] text-right">Impacto en Pesos</th>
                                <th class="px-4 lg:px-6 py-5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <template v-for="item in filteredData" :key="item.id">
                                <tr @click="toggleRow(item.id)" class="group hover:bg-emerald-50/40 transition-all duration-300 cursor-pointer" :class="{ 'bg-emerald-50/20': expandedRow === item.id }">
                                    <td class="px-4 py-4 lg:px-10 lg:py-6">
                                        <p class="text-base font-black text-slate-900 tracking-tight group-hover:text-emerald-600 transition-colors" :class="{ 'text-emerald-600': expandedRow === item.id }">{{ item.name }}</p>
                                    </td>
                                    <td class="px-4 py-4 lg:px-10 lg:py-6">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2 text-slate-900 font-bold text-xs uppercase tracking-tight">
                                                <Building :size="14" class="text-slate-400" />
                                                {{ item.room }}
                                            </div>
                                            <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.1em]">{{ item.category }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 lg:px-10 lg:py-6 text-center">
                                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 rounded-xl text-slate-600 group-hover:bg-white transition-colors">
                                            <Clock :size="14" />
                                            <span class="text-sm font-black">{{ typeof item.hours === 'number' ? item.hours.toLocaleString('es-ES', { maximumFractionDigits: 1 }) : item.hours }}h</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 lg:px-10 lg:py-6 text-right">
                                        <div class="text-sm font-black text-slate-500 group-hover:text-slate-900 transition-colors">
                                            {{ Math.round(item.kwh) }} <span class="text-[10px] text-slate-300">kWh</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 lg:px-10 lg:py-6 text-center">
                                        <!-- Sparkline -->
                                        <div v-if="item.history && item.history.length > 1" class="h-10 w-24 inline-block opacity-60 group-hover:opacity-100 transition-opacity">
                                            <Line :data="getSparklineData(item.history)" :options="sparklineOptions" />
                                        </div>
                                        <span v-else class="text-[10px] font-bold text-slate-300">Sin historial</span>
                                    </td>
                                    <td class="px-4 py-4 lg:px-10 lg:py-6 text-right">
                                        <div class="flex flex-col items-end">
                                            <span class="text-xl font-black text-slate-900 group-hover:text-emerald-600 transition-colors" :class="{ 'text-emerald-600': expandedRow === item.id }">${{ item.cost.toLocaleString('es-AR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) }}</span>
                                            <div class="h-1 w-20 bg-emerald-100 rounded-full mt-2 overflow-hidden">
                                                <div class="h-full bg-emerald-500" :style="{ width: (item.cost / filteredData[0].cost * 100) + '%' }"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <ChevronUp v-if="expandedRow === item.id" :size="20" />
                                        <ChevronDown v-else :size="20" class="group-hover:text-emerald-500" />
                                    </td>
                                </tr>
                                
                                <!-- Expanded Details Row -->
                                <tr v-if="expandedRow === item.id" class="bg-slate-50/50 border-b-2 border-emerald-500/10">
                                    <td colspan="7" class="px-10 py-10">
                                        <div class="bg-white rounded-[32px] p-8 border border-slate-100 shadow-xl shadow-slate-200/20 flex flex-col lg:flex-row gap-10">
                                            
                                            <!-- Stats Resumen -->
                                            <div class="lg:w-1/3 space-y-6">
                                                <div>
                                                    <h4 class="text-lg font-black text-slate-900 tracking-tight">Auditoría Histórica</h4>
                                                    <p class="text-xs text-slate-400 font-medium">Evolución de {{ item.name }} a lo largo de {{ item.history?.length || 0 }} periodos analizados.</p>
                                                </div>
                                                
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Gasto Histórico Total</p>
                                                        <p class="text-lg font-black text-emerald-600">${{ item.history?.reduce((acc, h) => acc + h.cost, 0).toLocaleString('es-AR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) || '0' }}</p>
                                                    </div>
                                                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Consumo Total</p>
                                                        <p class="text-lg font-black text-slate-700">{{ Math.round(item.history?.reduce((acc, h) => acc + h.kwh, 0) || 0) }} <span class="text-xs text-slate-400">kWh</span></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Gráfico Detallado -->
                                            <div class="lg:w-2/3 h-64 border-l border-slate-100 pl-10">
                                                <div v-if="item.history && item.history.length > 0" class="h-full w-full">
                                                    <Bar :data="getDetailedChartData(item.history)" :options="detailedChartOptions" />
                                                </div>
                                                <div v-else class="h-full w-full flex items-center justify-center text-slate-300 font-bold text-sm">
                                                    No hay historial suficiente para este equipo.
                                                </div>
                                            </div>

                                        </div>
                                    </td>
                                </tr>
                            </template>
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
