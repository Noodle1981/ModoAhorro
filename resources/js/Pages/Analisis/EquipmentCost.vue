<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    DollarSign, 
    Zap, 
    Building, 
    Search,
    Clock,
    Activity,
    ChevronDown,
    ChevronUp,
    Trophy,
    SlidersHorizontal,
    Layers
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
const selectedCategory = ref('all');
const showAll = ref(false);

const changePeriod = (id) => {
    router.get(route('analisis.equipment-cost'), { period_id: id }, { preserveState: true });
};

const formatDate = (start, end) => {
    if (!start || !end) return '';
    const s = new Date(start.split('T')[0]);
    const e = new Date(end.split('T')[0]);
    return `${s.toLocaleDateString('es-ES', { day: '2-digit', month: 'short' })} - ${e.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: '2-digit' })}`;
};

const formatCurrency = (val) => {
    if (val === null || val === undefined) return '$0';
    if (val === 0) return '$0';
    if (val < 0.01) return '< $0.01';
    if (val < 10) return `$${val.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    if (val < 100) return `$${val.toLocaleString('es-AR', { minimumFractionDigits: 1, maximumFractionDigits: 2 })}`;
    return `$${Math.round(val).toLocaleString('es-AR')}`;
};

const formatKwh = (val) => {
    if (!val) return '0';
    if (val < 0.1) return val.toFixed(2);
    if (val < 10) return val.toFixed(1);
    return Math.round(val).toLocaleString('es-AR');
};

const totalCostSum = computed(() => {
    return (props.equipmentData || []).reduce((acc, d) => acc + (d.cost || 0), 0);
});

const top3Consumers = computed(() => {
    return (props.equipmentData || [])
        .filter(d => (d.cost || 0) > 0)
        .slice(0, 3);
});

const categoriesSummary = computed(() => {
    const map = {};
    (props.equipmentData || []).forEach(d => {
        const cat = d.category || 'Sin categoría';
        if (!map[cat]) {
            map[cat] = { name: cat, count: 0, cost: 0, kwh: 0 };
        }
        map[cat].count++;
        map[cat].cost += d.cost || 0;
        map[cat].kwh += d.kwh || 0;
    });
    return Object.values(map).sort((a, b) => b.cost - a.cost);
});

const filteredData = computed(() => {
    let list = props.equipmentData || [];

    if (selectedCategory.value === 'with_consumption') {
        list = list.filter(d => (d.cost || 0) > 0 || (d.kwh || 0) > 0);
    } else if (selectedCategory.value !== 'all') {
        list = list.filter(d => (d.category || 'Sin categoría') === selectedCategory.value);
    }

    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter(d => 
            (d.name || '').toLowerCase().includes(q) || 
            (d.room || '').toLowerCase().includes(q) || 
            (d.category || '').toLowerCase().includes(q)
        );
    }

    return list;
});

const isSearchingOrFiltered = computed(() => {
    return !!searchQuery.value || selectedCategory.value !== 'all';
});

const visibleData = computed(() => {
    if (showAll.value || isSearchingOrFiltered.value || filteredData.value.length <= 7) {
        return filteredData.value;
    }
    return filteredData.value.slice(0, 7);
});

// Lógica de Historial (Sparklines & Expanded)
const expandedRow = ref(null);

const toggleRow = (id) => {
    expandedRow.value = expandedRow.value === id ? null : id;
};

const getSparklineData = (history) => {
    return {
        labels: history.map(h => h.days ? `${h.label} (${h.days}d)` : h.label),
        datasets: [{
            data: history.map(h => h.cost),
            borderColor: themeColors.value.hex,
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
        labels: history.map(h => h.days ? `${h.label} · ${h.days}d` : h.label),
        datasets: [
            {
                label: 'Coste ($)',
                data: history.map(h => h.cost),
                backgroundColor: themeColors.value.hex,
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
        tooltip: {
            backgroundColor: '#0f172a',
            padding: 12,
            cornerRadius: 12,
            callbacks: {
                title: (items) => items[0]?.label ?? '',
                label: (item) => {
                    if (item.datasetIndex === 0) return ` Coste: ${formatCurrency(item.raw)}`;
                    return ` Consumo: ${formatKwh(item.raw)} kWh`;
                }
            }
        }
    },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 10, weight: '600' } } },
        y: { position: 'left', grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 } }, beginAtZero: true },
        y1: { position: 'right', grid: { display: false }, ticks: { font: { size: 10 } }, beginAtZero: true }
    }
};

const selectedPeriod = computed(() => {
    if (props.selectedPeriodId === 'all') return null;
    return props.periods.find(p => p.id === props.selectedPeriodId);
});

const totalKwh = computed(() => {
    if (selectedPeriod.value) return selectedPeriod.value.total_kwh;
    return props.equipmentData.reduce((acc, d) => acc + (d.kwh || 0), 0);
});

const themeColors = computed(() => {
    const type = props.entity?.type;
    if (type === 'comercio') {
        return {
            text: 'text-purple-600',
            bg: 'bg-purple-600',
            hoverBg: 'hover:bg-purple-700',
            shadow: 'shadow-purple-500/20',
            bgLight: 'bg-purple-500/10',
            borderLight: 'border-purple-500/20',
            textLight: 'text-purple-100',
            bgDark: 'bg-purple-950',
            tableHoverBg: 'hover:bg-purple-50/40',
            tableActiveBg: 'bg-purple-50/20',
            groupHoverText: 'group-hover:text-purple-600',
            groupHoverText500: 'group-hover:text-purple-500',
            focusRing: 'focus:ring-purple-500/10',
            hoverText: 'hover:text-purple-500',
            text400: 'text-purple-400',
            hex: '#9333ea'
        };
    }
    if (type === 'oficina') {
        return {
            text: 'text-blue-600',
            bg: 'bg-blue-600',
            hoverBg: 'hover:bg-blue-700',
            shadow: 'shadow-blue-500/20',
            bgLight: 'bg-blue-500/10',
            borderLight: 'border-blue-500/20',
            textLight: 'text-blue-100',
            bgDark: 'bg-blue-950',
            tableHoverBg: 'hover:bg-blue-50/40',
            tableActiveBg: 'bg-blue-50/20',
            groupHoverText: 'group-hover:text-blue-600',
            groupHoverText500: 'group-hover:text-blue-500',
            focusRing: 'focus:ring-blue-500/10',
            hoverText: 'hover:text-blue-500',
            text400: 'text-blue-400',
            hex: '#2563eb'
        };
    }
    // Default / hogar (Emerald theme)
    return {
        text: 'text-emerald-600',
        bg: 'bg-emerald-600',
        hoverBg: 'hover:bg-emerald-700',
        shadow: 'shadow-emerald-500/20',
        bgLight: 'bg-emerald-500/10',
        borderLight: 'border-emerald-500/20',
        textLight: 'text-emerald-100',
        bgDark: 'bg-emerald-950',
        tableHoverBg: 'hover:bg-emerald-50/40',
        tableActiveBg: 'bg-emerald-50/20',
        groupHoverText: 'group-hover:text-emerald-600',
        groupHoverText500: 'group-hover:text-emerald-500',
        focusRing: 'focus:ring-emerald-500/10',
        hoverText: 'hover:text-emerald-500',
        text400: 'text-emerald-400',
        hex: '#059669'
    };
});
</script>

<template>
    <MainLayout>
        <Head title="Coste por Equipo" />

        <div class="max-w-7xl mx-auto space-y-6 pb-20">
            <!-- Toolbar: Period Filter -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm font-medium text-slate-500">
                    <span>Desglose financiero por artefacto con asignación de tarifa real.</span>
                </div>

                <div class="relative min-w-[280px] w-full sm:w-auto">
                    <select 
                        :value="selectedPeriodId" 
                        @change="changePeriod($event.target.value)"
                        :class="themeColors.focusRing"
                        class="w-full bg-white border border-slate-200/80 rounded-2xl py-2.5 px-4 text-xs font-bold text-slate-900 shadow-sm outline-none focus:ring-2 transition-all appearance-none pr-10 cursor-pointer"
                    >
                        <option value="all">Promedio Histórico (Global)</option>
                        <option v-for="p in periods" :key="p.id" :value="p.id">
                            Periodo: {{ formatDate(p.start_date, p.end_date) }}
                        </option>
                    </select>
                    <ChevronDown :size="14" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" />
                </div>
            </div>

            <!-- Compact Stats Bar -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-slate-900 rounded-3xl p-5 text-white relative overflow-hidden flex items-center justify-between shadow-lg shadow-slate-900/10">
                    <div>
                        <p class="text-[9px] font-black text-white/40 uppercase tracking-[0.2em] mb-1">{{ selectedPeriodId === 'all' ? 'Tarifa Promedio' : 'Tarifa del Periodo' }}</p>
                        <p class="text-xl font-black">${{ pricePerKwh.toFixed(2) }}<span class="text-[10px] font-medium text-white/30 ml-1">/kWh</span></p>
                    </div>
                    <DollarSign :size="28" class="text-white/10" />
                </div>
                
                <div class="bg-white border border-slate-100 rounded-3xl p-5 flex items-center justify-between shadow-md">
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">{{ selectedPeriodId === 'all' ? 'Gasto Prom. Periodo' : 'Total Facturado' }}</p>
                        <p class="text-xl font-black text-slate-900">{{ formatCurrency(selectedPeriod?.total_amount || totalCostSum) }}</p>
                    </div>
                    <Activity :size="28" class="text-slate-200" />
                </div>

                <div :class="[themeColors.bgLight, themeColors.borderLight]" class="border rounded-3xl p-5 flex items-center justify-between group shadow-sm">
                    <div>
                        <p :class="themeColors.text" class="text-[9px] font-black opacity-60 uppercase tracking-[0.2em] mb-1">{{ selectedPeriodId === 'all' ? 'Consumo Promedio' : 'Consumo del Periodo' }}</p>
                        <p class="text-xl font-black text-slate-900">{{ formatKwh(totalKwh) }} <span :class="themeColors.text" class="text-[10px] font-bold opacity-50 ml-0.5">kWh</span></p>
                    </div>
                    <Zap :size="28" :class="themeColors.text" class="opacity-30 group-hover:scale-110 transition-transform" />
                </div>
            </div>

            <!-- Top 3 Grandes Consumidores (Podio de Mayor Impacto) -->
            <div v-if="top3Consumers.length > 0" class="space-y-3">
                <div class="flex items-center justify-between px-1">
                    <div class="flex items-center gap-2">
                        <Trophy :size="15" class="text-amber-500" />
                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Top 3 Mayores Consumidores</h3>
                    </div>
                    <span class="text-[10px] font-bold text-slate-400">
                        Concentran el {{ Math.round((top3Consumers.reduce((acc, d) => acc + d.cost, 0) / (totalCostSum || 1)) * 100) }}% del gasto
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div 
                        v-for="(topItem, index) in top3Consumers" 
                        :key="topItem.id"
                        @click="toggleRow(topItem.id)"
                        class="bg-white rounded-3xl border border-slate-100 shadow-md hover:shadow-lg p-5 flex flex-col justify-between transition-all cursor-pointer relative overflow-hidden group"
                        :class="[themeColors.hoverBorder]"
                    >
                        <!-- Top Accent Line -->
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div 
                                    class="w-7 h-7 rounded-xl flex items-center justify-center text-xs font-black shrink-0"
                                    :class="index === 0 ? 'bg-amber-100 text-amber-700' : (index === 1 ? 'bg-slate-200 text-slate-700' : 'bg-orange-100 text-orange-700')"
                                >
                                    #{{ index + 1 }}
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-black text-slate-900 tracking-tight truncate group-hover:text-emerald-600 transition-colors">
                                        {{ topItem.name }}
                                    </h4>
                                    <p class="text-[10px] text-slate-400 font-medium truncate flex items-center gap-1">
                                        <span>{{ topItem.room }}</span>
                                        <span class="opacity-40">•</span>
                                        <span>{{ topItem.category }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2 mt-auto pt-2 border-t border-slate-50">
                            <div class="flex items-baseline justify-between">
                                <span class="text-lg font-black text-slate-900">{{ formatCurrency(topItem.cost) }}</span>
                                <span class="text-[10px] font-bold text-slate-500">
                                    {{ formatKwh(topItem.kwh) }} kWh
                                </span>
                            </div>

                            <!-- Progress Bar of Total Bill -->
                            <div class="space-y-1">
                                <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                    <div 
                                        :class="themeColors.bg" 
                                        class="h-full rounded-full transition-all duration-700"
                                        :style="{ width: Math.min(100, Math.round((topItem.cost / (totalCostSum || 1)) * 100)) + '%' }"
                                    ></div>
                                </div>
                                <div class="flex justify-between text-[9px] font-bold text-slate-400">
                                    <span>{{ topItem.hours }}h/día</span>
                                    <span>{{ Math.round((topItem.cost / (totalCostSum || 1)) * 100) }}% del total</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Section with Category Breakdown Filters -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-xl overflow-hidden">
                <!-- Header Toolbar & Category Pills -->
                <div class="p-5 sm:p-6 border-b border-slate-100 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Desglose Detallado de Equipos</h3>
                            <p class="text-xs text-slate-400 font-medium">Ordenado por impacto económico descendente</p>
                        </div>

                        <!-- Search Input -->
                        <div class="relative max-w-xs w-full">
                            <Search :size="14" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none" />
                            <input 
                                v-model="searchQuery" 
                                type="text" 
                                placeholder="Buscar equipo o área..." 
                                :class="themeColors.focusRing"
                                class="w-full bg-slate-50 border border-slate-200/60 rounded-xl py-2 pl-9 pr-4 text-xs font-bold text-slate-900 focus:ring-2 transition-all outline-none placeholder:text-slate-300"
                            />
                        </div>
                    </div>

                    <!-- Category Pills Filter (Cloud Tags Wrapping) -->
                    <div class="flex flex-wrap items-center gap-2 text-xs pt-1">
                        <button 
                            @click="selectedCategory = 'all'"
                            :class="[
                                'px-2.5 py-1.5 rounded-xl font-black text-[10px] uppercase tracking-wider transition-all flex items-center gap-1.5 cursor-pointer',
                                selectedCategory === 'all' 
                                    ? 'bg-slate-900 text-white shadow-xs' 
                                    : 'bg-slate-100/90 text-slate-600 hover:bg-slate-200/70'
                            ]"
                        >
                            <span>Todos</span>
                            <span class="opacity-60 text-[9px]">({{ equipmentData.length }})</span>
                        </button>

                        <button 
                            @click="selectedCategory = 'with_consumption'"
                            :class="[
                                'px-2.5 py-1.5 rounded-xl font-black text-[10px] uppercase tracking-wider transition-all flex items-center gap-1.5 cursor-pointer',
                                selectedCategory === 'with_consumption' 
                                    ? 'bg-emerald-600 text-white shadow-xs' 
                                    : 'bg-slate-100/90 text-slate-600 hover:bg-slate-200/70'
                            ]"
                        >
                            <span>Con Consumo</span>
                            <span class="opacity-60 text-[9px]">({{ equipmentData.filter(d => (d.cost || 0) > 0).length }})</span>
                        </button>

                        <button 
                            v-for="cat in categoriesSummary" 
                            :key="cat.name"
                            @click="selectedCategory = cat.name"
                            :class="[
                                'px-2.5 py-1.5 rounded-xl font-black text-[10px] uppercase tracking-wider transition-all flex items-center gap-1.5 cursor-pointer',
                                selectedCategory === cat.name 
                                    ? 'bg-slate-900 text-white shadow-xs' 
                                    : 'bg-slate-100/90 text-slate-600 hover:bg-slate-200/70'
                            ]"
                        >
                            <span>{{ cat.name }}</span>
                            <span class="opacity-60 text-[9px]">({{ formatCurrency(cat.cost) }})</span>
                        </button>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100">
                                <th class="px-5 py-3.5 text-[9px] font-black text-slate-400 uppercase tracking-wider">Equipo</th>
                                <th class="px-5 py-3.5 text-[9px] font-black text-slate-400 uppercase tracking-wider">Área / Categoría</th>
                                <th class="px-5 py-3.5 text-[9px] font-black text-slate-400 uppercase tracking-wider text-center">Uso/Día</th>
                                <th class="px-5 py-3.5 text-[9px] font-black text-slate-400 uppercase tracking-wider text-right">Consumo</th>
                                <th class="px-5 py-3.5 text-[9px] font-black text-slate-400 uppercase tracking-wider text-center">Tendencia</th>
                                <th :class="themeColors.text400" class="px-5 py-3.5 text-[9px] font-black uppercase tracking-wider text-right">Impacto en Pesos</th>
                                <th class="px-3 py-3.5 w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template v-for="item in visibleData" :key="item.id">
                                <tr @click="toggleRow(item.id)" :class="[themeColors.tableHoverBg, expandedRow === item.id ? themeColors.tableActiveBg : '']" class="group transition-colors cursor-pointer">
                                    <td class="px-5 py-4">
                                        <p :class="[themeColors.groupHoverText, expandedRow === item.id ? themeColors.text : '']" class="text-sm font-black text-slate-900 tracking-tight transition-colors">
                                            {{ item.name }}
                                        </p>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="space-y-0.5">
                                            <div class="flex items-center gap-1.5 text-slate-800 font-bold text-xs">
                                                <Building :size="12" class="text-slate-400 shrink-0" />
                                                <span class="truncate">{{ item.room }}</span>
                                            </div>
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">{{ item.category }}</p>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <div class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 rounded-lg text-slate-600 text-xs font-black">
                                            <Clock :size="11" class="text-slate-400" />
                                            <span>{{ typeof item.hours === 'number' ? item.hours.toLocaleString('es-ES', { maximumFractionDigits: 1 }) : item.hours }}h</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="text-xs font-black text-slate-600">
                                            <span>{{ formatKwh(item.kwh) }}</span>
                                            <span class="text-[9px] text-slate-400 ml-0.5">kWh</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <!-- Sparkline -->
                                        <div v-if="item.history && item.history.length > 1" class="h-7 w-20 inline-block opacity-70 group-hover:opacity-100 transition-opacity">
                                            <Line :data="getSparklineData(item.history)" :options="sparklineOptions" />
                                        </div>
                                        <span v-else class="text-[9px] font-bold text-slate-300">—</span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex flex-col items-end">
                                            <div class="flex items-center gap-1.5">
                                                <span :class="[themeColors.groupHoverText, expandedRow === item.id ? themeColors.text : '', (item.cost || 0) === 0 ? 'text-slate-300' : 'text-slate-900']" class="text-base font-black transition-colors">
                                                    {{ formatCurrency(item.cost) }}
                                                </span>
                                                <span v-if="(item.cost || 0) === 0" class="px-1.5 py-0.5 bg-slate-100 text-slate-400 rounded text-[8px] font-black uppercase">
                                                    Sin uso
                                                </span>
                                            </div>
                                            <div v-if="(item.cost || 0) > 0" class="h-1 w-16 bg-slate-100 rounded-full mt-1.5 overflow-hidden">
                                                <div :class="themeColors.bg" class="h-full" :style="{ width: Math.min(100, Math.round((item.cost / (filteredData[0]?.cost || 1)) * 100)) + '%' }"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 text-center">
                                        <ChevronUp v-if="expandedRow === item.id" :size="16" class="text-slate-600" />
                                        <ChevronDown v-else :size="16" :class="themeColors.groupHoverText500" class="text-slate-300" />
                                    </td>
                                </tr>
                                
                                <!-- Expanded Details Row -->
                                <tr v-if="expandedRow === item.id" :class="[themeColors.borderLight]" class="bg-slate-50/60 border-b">
                                    <td colspan="7" class="p-6">
                                        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-md flex flex-col lg:flex-row gap-6">
                                            <!-- Stats Resumen -->
                                            <div class="lg:w-1/3 space-y-4">
                                                <div>
                                                    <h4 class="text-base font-black text-slate-900 tracking-tight">Auditoría Histórica</h4>
                                                    <p class="text-xs text-slate-400 font-medium">Evolución de {{ item.name }} a lo largo de {{ item.history?.length || 0 }} periodos analizados.</p>
                                                </div>
                                                
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Gasto Histórico</p>
                                                        <p :class="themeColors.text" class="text-base font-black">{{ formatCurrency(item.history?.reduce((acc, h) => acc + h.cost, 0) || 0) }}</p>
                                                    </div>
                                                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Consumo Total</p>
                                                        <p class="text-base font-black text-slate-700">{{ formatKwh(item.history?.reduce((acc, h) => acc + h.kwh, 0) || 0) }} <span class="text-[10px] text-slate-400">kWh</span></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Gráfico Detallado -->
                                            <div class="lg:w-2/3 h-52 border-t lg:border-t-0 lg:border-l border-slate-100 pt-4 lg:pt-0 lg:pl-6">
                                                <div v-if="item.history && item.history.length > 0" class="h-full w-full">
                                                    <Bar :data="getDetailedChartData(item.history)" :options="detailedChartOptions" />
                                                </div>
                                                <div v-else class="h-full w-full flex items-center justify-center text-slate-300 font-bold text-xs">
                                                    No hay historial suficiente para este equipo.
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <!-- Empty State -->
                            <tr v-if="visibleData.length === 0">
                                <td colspan="7" class="p-12 text-center text-slate-400 text-xs font-bold">
                                    No se encontraron equipos para el criterio seleccionado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer: Show More / Show Less Toggle -->
                <div v-if="filteredData.length > 7 && !isSearchingOrFiltered" class="p-4 border-t border-slate-100 bg-slate-50/50 flex justify-center">
                    <button 
                        @click="showAll = !showAll"
                        class="px-5 py-2 rounded-xl bg-white border border-slate-200 text-xs font-black text-slate-700 hover:bg-slate-100 transition-all shadow-xs flex items-center gap-2"
                    >
                        <span>{{ showAll ? 'Ver Menos (Top 7)' : `Ver Todos los Equipos (${filteredData.length})` }}</span>
                        <ChevronUp v-if="showAll" :size="14" />
                        <ChevronDown v-else :size="14" />
                    </button>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
