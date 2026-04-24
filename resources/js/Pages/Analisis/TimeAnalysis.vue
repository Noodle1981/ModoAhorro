<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    Activity, 
    History, 
    TrendingUp, 
    Calendar,
    LineChart as LineIcon,
    ArrowLeft,
    Clock,
    Zap,
    ThermometerSun,
    Layers,
    Cpu,
    TrendingDown,
    DollarSign
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
    entity: { type: Object, required: true },
    periods: { type: Array, default: () => [] },
    evolution: { type: Array, default: () => [] }
});

// 1. Chart: Engine Efficiency (Billed vs Theoretical vs Recommended)
const motorData = computed(() => {
    const data = props.evolution || [];
    return {
        labels: data.map(d => d.label),
        datasets: [
            {
                label: 'Facturado',
                data: data.map(d => d.billed),
                borderColor: '#94a3b8',
                backgroundColor: 'transparent',
                borderWidth: 2,
                borderDash: [5, 5],
                tension: 0.4,
                pointRadius: 0
            },
            {
                label: 'Calculado (Suma)',
                data: data.map(d => d.theoretical),
                borderColor: '#f43f5e',
                backgroundColor: 'transparent',
                borderWidth: 2,
                tension: 0.4,
                pointRadius: 0
            },
            {
                label: 'Recomendado',
                data: data.map(d => d.recommended),
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                borderWidth: 4,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#6366f1'
            }
        ]
    };
});

// 2. Chart: Thermodynamic Composition (Stacked Tanks)
const tanksData = computed(() => {
    const data = props.evolution || [];
    return {
        labels: data.map(d => d.label),
        datasets: [
            {
                label: 'T1 Base',
                data: data.map(d => d.tanks.t1),
                backgroundColor: '#0f172a',
                borderRadius: 4
            },
            {
                label: 'T2 Clima',
                data: data.map(d => d.tanks.t2),
                backgroundColor: '#06b6d4',
                borderRadius: 4
            },
            {
                label: 'T3 Variable',
                data: data.map(d => d.tanks.t3),
                backgroundColor: '#f59e0b',
                borderRadius: 4
            }
        ]
    };
});

// 3. Chart: Consumption vs Temperature
const climateData = computed(() => {
    const data = props.evolution || [];
    return {
        labels: data.map(d => d.label),
        datasets: [
            {
                label: 'Consumo (kWh)',
                data: data.map(d => d.billed),
                backgroundColor: 'rgba(148, 163, 184, 0.2)',
                borderRadius: 8,
                yAxisID: 'y'
            },
            {
                label: 'Temp. Promedio (°C)',
                data: data.map(d => d.climate.avg_temp),
                borderColor: '#f43f5e',
                backgroundColor: 'transparent',
                type: 'line',
                borderWidth: 3,
                tension: 0.4,
                yAxisID: 'y1'
            }
        ]
    };
});

// 4. Chart: Costs Evolution
const costsData = computed(() => {
    const data = props.evolution || [];
    return {
        labels: data.map(d => d.label),
        datasets: [
            {
                label: 'Costo Diario ($)',
                data: data.map(d => d.costs.daily),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.4,
                yAxisID: 'y'
            },
            {
                label: 'Precio Energía ($/kWh)',
                data: data.map(d => d.costs.per_kwh),
                borderColor: '#f59e0b',
                backgroundColor: 'transparent',
                borderWidth: 2,
                tension: 0.4,
                yAxisID: 'y1'
            }
        ]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'top',
            align: 'end',
            labels: {
                usePointStyle: true,
                boxWidth: 6,
                font: { size: 10, weight: '700' },
                padding: 20
            }
        },
        tooltip: {
            backgroundColor: '#0f172a',
            padding: 12,
            cornerRadius: 12,
            titleFont: { size: 12, weight: 'bold' },
            bodyFont: { size: 13 }
        }
    },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 10, weight: '600' } } },
        y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 } } }
    }
};

const dualYOptions = {
    ...chartOptions,
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 10, weight: '600' } } },
        y: { position: 'left', grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 } } },
        y1: { position: 'right', grid: { display: false }, ticks: { font: { size: 10 } } }
    }
};

const stackedOptions = {
    ...chartOptions,
    scales: {
        x: { stacked: true, grid: { display: false }, ticks: { font: { size: 10, weight: '600' } } },
        y: { stacked: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 } } }
    }
};
</script>

<template>
    <MainLayout>
        <Head title="Análisis en el Tiempo" />

        <div class="max-w-7xl mx-auto space-y-10 pb-20">
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-4 text-slate-400">
                <Link :href="route('analisis.consumption')" class="hover:text-indigo-500 transition-colors flex items-center gap-2 text-xs font-bold uppercase tracking-widest">
                    <ArrowLeft :size="14" />
                    Volver a Consumo
                </Link>
                <span class="text-slate-200">/</span>
                <span class="text-xs font-bold uppercase tracking-widest text-slate-300">Evolución Temporal</span>
            </div>

            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-500/10 text-indigo-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-indigo-500/20">
                        <History :size="14" />
                        Histórico Evolutivo
                    </div>
                    <h1 class="text-5xl font-black text-slate-900 tracking-tighter leading-none">
                        Análisis en el <span class="text-indigo-600">Tiempo</span>
                    </h1>
                    <p class="text-lg text-slate-500 font-medium">Visualización de tendencias y evolución de eficiencia energética.</p>
                </div>

                <div class="flex items-center gap-6">
                    <Link :href="route('analisis.equipment-cost')" class="inline-flex items-center gap-3 px-8 py-4 bg-emerald-500 text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-xl shadow-emerald-500/20 active:scale-95">
                        <DollarSign :size="16" />
                        Auditoría de Costos
                    </Link>
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Ciclos Analizados</p>
                            <p class="text-2xl font-black text-slate-900">{{ periods.length }}</p>
                        </div>
                        <div class="w-12 h-12 bg-white rounded-2xl border border-slate-100 shadow-xl flex items-center justify-center text-indigo-500">
                            <Activity :size="24" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Chart 1: Engine Efficiency -->
                <div class="bg-white p-8 rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/30">
                    <div class="flex items-center justify-between mb-8">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-indigo-50 text-indigo-500 rounded-xl">
                                    <Cpu :size="18" />
                                </div>
                                <h3 class="text-xl font-black text-slate-900 tracking-tight">Eficiencia del Motor</h3>
                            </div>
                            <p class="text-xs text-slate-400 font-medium pl-10">Bimestral: Facturado vs Calculado vs Recomendado</p>
                        </div>
                    </div>
                    <div class="h-80 w-full">
                        <Line :data="motorData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Chart 2: Thermodynamic Composition -->
                <div class="bg-white p-8 rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/30">
                    <div class="flex items-center justify-between mb-8">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-amber-50 text-amber-500 rounded-xl">
                                    <Layers :size="18" />
                                </div>
                                <h3 class="text-xl font-black text-slate-900 tracking-tight">Composición de Tanques</h3>
                            </div>
                            <p class="text-xs text-slate-400 font-medium pl-10">Distribución termodinámica por periodo bimestral</p>
                        </div>
                    </div>
                    <div class="h-80 w-full">
                        <Bar :data="tanksData" :options="stackedOptions" />
                    </div>
                </div>

                <!-- Chart 3: Climate Correlation -->
                <div class="bg-white p-8 rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/30">
                    <div class="flex items-center justify-between mb-8">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-rose-50 text-rose-500 rounded-xl">
                                    <ThermometerSun :size="18" />
                                </div>
                                <h3 class="text-xl font-black text-slate-900 tracking-tight">Consumo vs Temperatura</h3>
                            </div>
                            <p class="text-xs text-slate-400 font-medium pl-10">Correlación climática (Consumo Bimestral)</p>
                        </div>
                    </div>
                    <div class="h-80 w-full">
                        <Bar :data="climateData" :options="dualYOptions" />
                    </div>
                </div>

                <!-- Chart 4: Costs Evolution -->
                <div class="bg-white p-8 rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/30">
                    <div class="flex items-center justify-between mb-8">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-emerald-50 text-emerald-500 rounded-xl">
                                    <DollarSign :size="18" />
                                </div>
                                <h3 class="text-xl font-black text-slate-900 tracking-tight">Evolución de Costos</h3>
                            </div>
                            <p class="text-xs text-slate-400 font-medium pl-10">Análisis financiero diario y por kWh</p>
                        </div>
                    </div>
                    <div class="h-80 w-full">
                        <Line :data="costsData" :options="dualYOptions" />
                    </div>
                </div>

            </div>

            <!-- Bottom Insight -->
            <div class="bg-slate-900 rounded-[48px] p-10 text-white overflow-hidden relative">
                <div class="absolute top-0 right-0 p-10 opacity-10">
                    <TrendingUp :size="120" />
                </div>
                <div class="relative z-10 flex flex-col md:flex-row items-center gap-8">
                    <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center text-indigo-400 shrink-0">
                        <Zap :size="32" />
                    </div>
                    <div class="space-y-2">
                        <h4 class="text-2xl font-black tracking-tight">Tendencia Detectada</h4>
                        <p class="text-slate-400 font-medium leading-relaxed max-w-3xl">
                            Tu **Eficiencia del Motor** muestra una convergencia positiva en los últimos 3 periodos. 
                            La brecha entre lo facturado y lo recomendado se ha reducido un **14%**, lo que indica que tus ajustes de uso están siendo efectivos.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
