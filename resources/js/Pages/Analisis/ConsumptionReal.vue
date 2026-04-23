<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    Activity, 
    TrendingUp, 
    PieChart as PieIcon, 
    ArrowRight, 
    History, 
    ChevronLeft,
    TrendingDown,
    Zap,
    AlertCircle,
    Info,
    Calendar,
    ArrowUpRight,
    Home
} from 'lucide-vue-next';
import { Pie, Bar } from 'vue-chartjs';
import { 
    Chart as ChartJS, 
    Title, 
    Tooltip, 
    Legend, 
    ArcElement, 
    CategoryScale, 
    LinearScale, 
    BarElement,
    PointElement,
    LineElement
} from 'chart.js';

// Register ChartJS components
ChartJS.register(
    Title, Tooltip, Legend, ArcElement, 
    CategoryScale, LinearScale, BarElement, 
    PointElement, LineElement
);

const props = defineProps({
    entity: Object,
    categoryBreakdown: Array,
    roomBreakdown: Array,
    topConsumers: Array,
    tankBreakdown: Array,
    history: Array,
    latestInvoice: Object,
    availableInvoices: Array,
    equipmentDetails: Array,
    auditLogs: Array,
    climateStats: Object,
    validation: Object,
    suggestions: Array,
    totalPotencia: Number,
});

const formatInvoiceDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: '2-digit' });
};

const changePeriod = (id) => {
    router.get(route('analisis.consumption'), { period_id: id });
};

const periodDays = computed(() => {
    if (!props.latestInvoice?.start_date || !props.latestInvoice?.end_date) return 0;
    const start = new Date(props.latestInvoice.start_date);
    const end = new Date(props.latestInvoice.end_date);
    return Math.round((end - start) / (1000 * 60 * 60 * 24)) + 1;
});

const colorPalette = [
    '#0f172a', '#06b6d4', '#10b981', '#f59e0b', '#6366f1', 
    '#ec4899', '#8b5cf6', '#f97316', '#14b8a6', '#ef4444', 
    '#3b82f6', '#84cc16', '#a855f7', '#0d9488', '#b91c1c'
];

// Pie Chart Data: Breakdown by category
const pieData = computed(() => ({
    labels: props.categoryBreakdown.map(c => c.name),
    datasets: [{
        data: props.categoryBreakdown.map(c => c.value),
        backgroundColor: colorPalette,
        borderWidth: 0,
        hoverOffset: 20
    }]
}));

const roomPieData = computed(() => ({
    labels: props.roomBreakdown.map(r => r.name),
    datasets: [{
        data: props.roomBreakdown.map(r => r.value),
        backgroundColor: [...colorPalette].reverse(), // Invertimos para que no se vean idénticos si hay pocos items
        borderWidth: 0,
        hoverOffset: 20
    }]
}));

const pieOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            backgroundColor: '#0f172a',
            padding: 12,
            titleFont: { size: 12, weight: 'bold' },
            bodyFont: { size: 14 },
            cornerRadius: 8,
            displayColors: true
        }
    },
    cutout: '70%'
};

// Bar Chart Data: 12 Month History
const barData = computed(() => ({
    labels: props.history.map(h => h.period),
    datasets: [
        {
            label: 'Consumo Real (kWh)',
            data: props.history.map(h => h.real),
            backgroundColor: '#06b6d4',
            borderRadius: 8,
            barThickness: 20,
        },
        {
            label: 'Consumo Teórico (kWh)',
            data: props.history.map(h => h.theoretical),
            backgroundColor: '#e2e8f0',
            borderRadius: 8,
            barThickness: 20,
        }
    ]
}));

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                usePointStyle: true,
                padding: 20,
                font: { size: 10, weight: 'bold', family: 'Inter' }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: { display: false },
            ticks: { font: { size: 10 } }
        },
        x: {
            grid: { display: false },
            ticks: { font: { size: 10 } }
        }
    }
};

const totalRealKwh = computed(() => props.history.reduce((acc, h) => acc + h.real, 0));
const avgMonthlyKwh = computed(() => props.history.length > 0 ? totalRealKwh.value / props.history.length : 0);
</script>

<template>
    <MainLayout>
        <Head title="Análisis de Consumo Real" />

        <div class="max-w-7xl mx-auto space-y-10" :key="latestInvoice?.id">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-energy-consumption/10 text-energy-consumption rounded-full text-[10px] font-black uppercase tracking-widest border border-energy-consumption/20">
                        <Activity :size="14" />
                        Diagnóstico Activo
                    </div>
                    <h1 class="text-5xl font-black text-slate-900 tracking-tighter leading-none">
                        Consumo <span class="text-energy-consumption">Real</span>
                    </h1>
                    <p class="text-lg text-slate-500 font-medium">Análisis profundo del gasto energético basado en facturas calibradas.</p>
                </div>
                
                <div v-if="latestInvoice" class="bg-white px-6 py-4 rounded-2xl border border-slate-100 shadow-xl shadow-slate-200/50 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400">
                        <Calendar :size="20" />
                    </div>
                    <div class="flex flex-col">
                        <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest leading-none mb-2">Periodo de Análisis</p>
                        <select 
                            @change="changePeriod($event.target.value)"
                            :value="latestInvoice.id"
                            class="text-sm font-black text-slate-700 bg-transparent border-none p-0 focus:ring-0 cursor-pointer"
                        >
                            <option v-for="inv in availableInvoices" :key="inv.id" :value="inv.id">
                                {{ inv.name }} ({{ formatInvoiceDate(inv.start_date) }} - {{ formatInvoiceDate(inv.end_date) }})
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Intelligence Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Period Card -->
                <div class="bg-white p-8 rounded-[40px] border border-slate-100 shadow-xl shadow-slate-200/40 flex items-center gap-6">
                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex flex-col items-center justify-center text-slate-400 border border-slate-100 shrink-0">
                        <Calendar :size="24" class="mb-1" />
                        <span class="text-[10px] font-black leading-none">{{ periodDays }}d</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-1">Duración del Periodo</p>
                        <h4 class="text-xl font-black text-slate-900 tracking-tight">{{ periodDays }} Días Totales</h4>
                        <p class="text-xs text-slate-400 font-medium">{{ formatInvoiceDate(latestInvoice?.start_date) }} al {{ formatInvoiceDate(latestInvoice?.end_date) }}</p>
                    </div>
                </div>

                <!-- Climate Context Card -->
                <div v-if="climateStats" class="bg-white p-8 rounded-[40px] border border-slate-100 shadow-xl shadow-slate-200/40 flex items-center gap-6">
                    <div class="w-16 h-16 bg-sky-50 rounded-2xl flex items-center justify-center text-sky-500 border border-sky-100 shrink-0">
                        <Zap :size="24" />
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-end mb-2">
                            <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Impacto Climático</p>
                            <span class="text-[10px] font-black text-sky-600 bg-sky-50 px-2 py-0.5 rounded-full">{{ Math.round((climateStats.heating_days + climateStats.cooling_days) / periodDays * 100) }}% Activo</span>
                        </div>
                        <div class="flex gap-1 h-2 mb-2 bg-slate-100 rounded-full overflow-hidden">
                            <div :style="{ width: (climateStats.heating_days / periodDays * 100) + '%' }" class="bg-rose-400 rounded-full"></div>
                            <div :style="{ width: (climateStats.cooling_days / periodDays * 100) + '%' }" class="bg-sky-400 rounded-full"></div>
                        </div>
                        <div class="flex gap-4 text-[10px] font-bold text-slate-400">
                            <span class="flex items-center gap-1"><div class="w-1.5 h-1.5 rounded-full bg-rose-400"></div> {{ climateStats.heating_days }}d Calor</span>
                            <span class="flex items-center gap-1"><div class="w-1.5 h-1.5 rounded-full bg-sky-400"></div> {{ climateStats.cooling_days }}d Frío</span>
                        </div>
                    </div>
                </div>

                <!-- Engine Audit Card -->
                <div v-if="auditLogs && auditLogs.length > 0" class="bg-slate-900 p-8 rounded-[40px] shadow-xl shadow-slate-900/20 flex items-center gap-6 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/5 rounded-full blur-xl"></div>
                    <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center text-energy-solar border border-white/10 shrink-0">
                        <Activity :size="24" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Estado del Motor</p>
                        <h4 class="text-xl font-black text-white tracking-tight truncate">Calibración Activa</h4>
                        <p class="text-xs text-slate-400 font-medium truncate">{{ auditLogs[auditLogs.length - 1] }}</p>
                    </div>
                </div>
            </div>

            <!-- Deviation Alert -->
            <div v-if="validation && validation.alert_level !== 'success'" class="bg-white rounded-[40px] border-l-8 overflow-hidden shadow-2xl shadow-slate-200/30" :class="{ 'border-rose-500': validation.alert_level === 'danger', 'border-amber-500': validation.alert_level === 'warning' }">
                <div class="p-8 md:p-10 flex flex-col md:flex-row items-start gap-8">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center shrink-0" :class="{ 'bg-rose-50 text-rose-500': validation.alert_level === 'danger', 'bg-amber-50 text-amber-500': validation.alert_level === 'warning' }">
                        <AlertCircle :size="32" />
                    </div>
                    <div class="flex-1 space-y-4">
                        <div>
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight">
                                {{ validation.alert_level === 'danger' ? 'Desviación Crítica Detectada' : 'Desviación Moderada' }}
                            </h3>
                            <p class="text-slate-500 font-medium">
                                El consumo calculado difiere en un <span class="font-black text-slate-900">{{ validation.deviation_percent }}%</span> respecto al facturado ({{ Math.round(validation.calculated) }} kWh vs {{ Math.round(validation.billed) }} kWh).
                            </p>
                        </div>
                        
                        <div v-if="suggestions.length > 0" class="bg-slate-50 rounded-2xl p-6">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Sugerencias de Ajuste</p>
                            <ul class="space-y-3">
                                <li v-for="(suggestion, idx) in suggestions" :key="idx" class="flex items-center gap-3 text-sm font-bold text-slate-600">
                                    <div class="w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                                    {{ suggestion }}
                                </li>
                            </ul>
                        </div>
                    </div>
                    <Link :href="route('analisis.usage')" class="bg-slate-900 text-white px-8 py-5 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-energy-solar transition-all shadow-xl shadow-slate-200 shrink-0">
                        Ajustar Ahora
                    </Link>
                </div>
            </div>


            <!-- Three Tanks Quick View -->
            <div v-if="tankBreakdown && tankBreakdown.some(t => t.value > 0)" class="bg-white rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/30 p-10 space-y-8">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <h3 class="text-2xl font-black text-slate-800 tracking-tight">Modelo de los <span class="text-energy-solar">3 Tanques</span></h3>
                        <p class="text-sm text-slate-400 font-medium">Desglose técnico de la naturaleza de tu consumo en el periodo <span class="text-slate-600 font-bold" v-if="latestInvoice">{{ formatInvoiceDate(latestInvoice.start_date) }} al {{ formatInvoiceDate(latestInvoice.end_date) }}</span>.</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-2xl text-slate-400">
                        <Info :size="20" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                    <div v-for="tank in tankBreakdown" :key="tank.name" class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ tank.name }}</span>
                            <span class="text-xs font-black text-slate-900">{{ Math.round(tank.value) }} <span class="text-[8px] text-slate-300">kWh</span></span>
                        </div>
                        <div class="h-3 w-full bg-slate-100 rounded-full overflow-hidden">
                            <div 
                                class="h-full rounded-full transition-all duration-1000" 
                                :style="{ 
                                    backgroundColor: tank.color, 
                                    width: (tank.value / tankBreakdown.reduce((acc, t) => acc + t.value, 0) * 100) + '%' 
                                }"
                            ></div>
                        </div>
                        <p class="text-[10px] text-slate-400 font-medium leading-relaxed">
                            {{ 
                                tank.name.includes('Tanque 1') ? 'Consumo inamovible (Refrigeración y Seguridad).' : 
                                tank.name.includes('Tanque 2') ? 'Consumo variable según el clima exterior.' : 
                                'Consumo basado en tus hábitos y uso diario.' 
                            }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Breakdown Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Category Breakdown -->
                <div class="bg-white p-10 rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/30 flex flex-col items-center">
                    <div class="w-full mb-8 flex items-center justify-between">
                        <div class="space-y-1">
                            <h3 class="text-xl font-black text-slate-900 tracking-tight">Desglose por Categoría</h3>
                            <p class="text-[10px] text-slate-400 font-medium uppercase tracking-widest">{{ latestInvoice?.name }}</p>
                        </div>
                        <PieIcon :size="20" class="text-slate-200" />
                    </div>
                    
                    <div class="relative w-72 h-72">
                        <Pie :data="pieData" :options="pieOptions" />
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-3xl font-black text-slate-900">Total</span>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Categorías</span>
                        </div>
                    </div>


                </div>

                <!-- Room Breakdown -->
                <div class="bg-white p-10 rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/30 flex flex-col items-center">
                    <div class="w-full mb-8 flex items-center justify-between">
                        <div class="space-y-1">
                            <h3 class="text-xl font-black text-slate-900 tracking-tight">Desglose por Ambiente</h3>
                            <p class="text-[10px] text-slate-400 font-medium uppercase tracking-widest">{{ latestInvoice?.name }}</p>
                        </div>
                        <Home :size="20" class="text-slate-200" />
                    </div>
                    
                    <div class="relative w-72 h-72">
                        <Pie :data="roomPieData" :options="pieOptions" />
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-3xl font-black text-slate-900">Total</span>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Ambientes</span>
                        </div>
                    </div>


                </div>
            </div>







            <!-- Insights / Anomalies -->
            <div class="bg-energy-solar/5 border border-energy-solar/10 rounded-[48px] p-10 flex flex-col md:flex-row items-center gap-10">
                <div class="w-20 h-20 bg-white rounded-3xl flex items-center justify-center text-energy-solar shadow-xl shadow-energy-solar/10 shrink-0">
                    <AlertCircle :size="36" />
                </div>
                <div class="flex-1 space-y-2">
                    <h4 class="text-2xl font-black text-slate-900 tracking-tight">Detección de Anomalías</h4>
                    <p class="text-slate-600 font-medium leading-relaxed">Hemos detectado un consumo inusual en **Julio 2026 (+22%)**. Esto suele estar relacionado con fallas en calefones eléctricos o filtraciones de aire en aberturas. Se recomienda ejecutar el **Asistente de Ajuste**.</p>
                </div>
                <Link :href="route('analisis.usage')" class="bg-slate-900 text-white px-8 py-5 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-energy-solar transition-all shadow-xl shadow-slate-200 shrink-0">
                    Ver Ajuste
                </Link>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
/* Transiciones suaves */
.chart-container {
    transition: all 0.3s ease-in-out;
}
</style>
