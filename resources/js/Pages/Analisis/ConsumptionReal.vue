<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
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
    ArrowUpRight
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
    history: Array,
    latestInvoice: Object
});

// Pie Chart Data: Breakdown by category
const pieData = computed(() => ({
    labels: props.categoryBreakdown.map(c => c.name),
    datasets: [{
        data: props.categoryBreakdown.map(c => c.value),
        backgroundColor: [
            '#06b6d4', // energy-consumption (Cyan)
            '#10b981', // energy-success (Emerald)
            '#f59e0b', // energy-solar (Amber)
            '#f43f5e', // energy-critical (Rose)
            '#6366f1', // Indigo
            '#a855f7'  // Purple
        ],
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

        <div class="max-w-7xl mx-auto space-y-10">
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
                    <div>
                        <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest leading-none mb-1">Último Cierre</p>
                        <p class="text-sm font-black text-slate-700 leading-none">{{ latestInvoice.invoice_number }}</p>
                    </div>
                </div>
            </div>

            <!-- Stats Deck -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-10 rounded-[40px] border border-slate-100 shadow-2xl shadow-slate-200/30 space-y-6 relative overflow-hidden group">
                    <div class="absolute -right-6 -top-6 w-32 h-32 bg-energy-consumption/5 rounded-full group-hover:scale-110 transition-transform"></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-2">Promedio Mensual</p>
                        <div class="flex items-baseline gap-2">
                            <h4 class="text-5xl font-black text-slate-900 tracking-tighter">{{ Math.round(avgMonthlyKwh) }}</h4>
                            <span class="text-sm font-bold text-slate-400">kWh/Mes</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-energy-success bg-energy-success/10 px-3 py-1.5 rounded-xl w-fit">
                        <TrendingDown :size="14" stroke-width="3" />
                        <span class="text-[10px] font-black uppercase tracking-widest">-8% vs periodo anterior</span>
                    </div>
                </div>

                <div class="bg-slate-900 p-10 rounded-[40px] shadow-2xl shadow-slate-400/20 space-y-6 text-white overflow-hidden relative group">
                    <div class="absolute -right-6 -bottom-6 w-40 h-40 bg-white/5 rounded-full blur-2xl"></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Intensidad Energética</p>
                        <div class="flex items-baseline gap-2">
                            <h4 class="text-5xl font-black text-white tracking-tighter">Baja</h4>
                            <span class="text-sm font-bold text-slate-500">Nivel A+</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 font-medium leading-relaxed">Tu vivienda consume menos que el 85% de propiedades similares en tu zona.</p>
                </div>

                <div class="bg-white p-10 rounded-[40px] border border-slate-100 shadow-2xl shadow-slate-200/30 space-y-6 relative group overflow-hidden">
                    <div class="absolute -left-6 -bottom-6 w-32 h-32 bg-energy-success/5 rounded-full"></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-2">Ahorro Estimado</p>
                        <div class="flex items-baseline gap-2">
                            <h4 class="text-5xl font-black text-energy-success tracking-tighter">$14.2k</h4>
                            <span class="text-sm font-bold text-slate-400">Anual</span>
                        </div>
                    </div>
                    <Link :href="route('analisis.usage')" class="flex items-center justify-between group-hover:translate-x-2 transition-transform">
                        <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Optimizar Ahora</span>
                        <ArrowRight :size="16" class="text-energy-consumption" />
                    </Link>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                <!-- Breakdown Pie -->
                <div class="lg:col-span-2 bg-white p-10 rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/30 flex flex-col items-center">
                    <div class="w-full mb-8 flex items-center justify-between">
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Desglose por Categoría</h3>
                        <PieIcon :size="20" class="text-slate-200" />
                    </div>
                    
                    <div class="relative w-64 h-64 mb-10">
                        <Pie :data="pieData" :options="pieOptions" />
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-3xl font-black text-slate-900">Total</span>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Energía 100%</span>
                        </div>
                    </div>

                    <div class="w-full space-y-3">
                        <div v-for="(cat, idx) in categoryBreakdown" :key="cat.name" class="flex items-center justify-between p-3 rounded-2xl hover:bg-slate-50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: pieData.datasets[0].backgroundColor[idx] }"></div>
                                <span class="text-sm font-bold text-slate-600">{{ cat.name }}</span>
                            </div>
                            <span class="text-sm font-black text-slate-900">{{ Math.round(cat.value) }} <span class="text-[10px] text-slate-300">kWh</span></span>
                        </div>
                    </div>
                </div>

                <!-- History Bar -->
                <div class="lg:col-span-3 bg-white p-10 rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/30 flex flex-col">
                    <div class="w-full mb-8 flex items-center justify-between">
                        <div class="space-y-1">
                            <h3 class="text-xl font-black text-slate-900 tracking-tight">Serie Histórica</h3>
                            <p class="text-xs text-slate-400 font-medium">Últimos 12 meses de consumo reconciliado.</p>
                        </div>
                        <TrendingUp :size="20" class="text-slate-200" />
                    </div>

                    <div class="flex-1 min-h-[400px]">
                        <Bar :data="barData" :options="barOptions" />
                    </div>

                    <div class="mt-8 pt-8 border-t border-slate-50 grid grid-cols-2 gap-8">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-2xl bg-energy-consumption/10 flex items-center justify-center text-energy-consumption">
                                <Zap :size="20" />
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest mb-1">Peak Consumption</p>
                                <p class="text-lg font-black text-slate-900">May 26 <span class="text-xs text-slate-400 font-bold ml-1">940 kWh</span></p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 text-right">
                            <div class="flex-1">
                                <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest mb-1">Efficiency Trend</p>
                                <div class="flex items-center justify-end gap-1 text-energy-success font-black">
                                    <ArrowUpRight :size="16" />
                                    <span>Mejorando</span>
                                </div>
                            </div>
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
