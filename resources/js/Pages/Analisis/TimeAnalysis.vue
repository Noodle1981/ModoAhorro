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

// Computed properties para procesar la evolución con la nueva lógica de exceso
const processedEvolution = computed(() => {
    return (props.evolution || []).map(d => {
        let newTanks = { ...d.tanks };
        let calculated = Object.values(newTanks).reduce((a, b) => a + (b || 0), 0);
        let excess = Math.max(0, calculated - d.billed);
        let shortfall = Math.max(0, d.billed - calculated);

        let t4_base = newTanks['t4'] || 0;
        let t4_excess = 0;

        if (excess > 0) {
            let climate = newTanks['t3'] || 0;
            let deductible = Math.min(climate, excess);
            newTanks['t3'] = climate - deductible;
            t4_excess = deductible;
        }

        return { 
            ...d, 
            adjustedTanks: {
                t1: newTanks.t1 || 0,
                t2: newTanks.t2 || 0,
                t3: newTanks.t3 || 0,
                t4_base,
                t4_excess
            },
            shortfall
        };
    });
});

// 2. Chart: Thermodynamic Composition (Stacked Tanks with Excess Shading)
const tanksData = computed(() => {
    const data = processedEvolution.value;
    
    // Configuración de Tanques
    const tankInfo = {
        1: { label: 'Tanque 1 (Certeza)', color: '#059669' },
        2: { label: 'Tanque 2 (Base)', color: '#f97316' },
        3: { label: 'Tanque 3 (Clima)', color: '#38bdf8' }
    };

    const order = [1, 4, 2, 3];
    const labels = data.map(d => d.label);
    const datasets = [];

    // Datasets para la parte "Normal" y el exceso absorbido
    order.forEach(key => {
        if (key === 4) {
            datasets.push({
                label: 'Tanque 4 (Variable)',
                data: data.map(d => d.adjustedTanks.t4_base),
                backgroundColor: '#84cc16', // lime-500
                stack: 'combined',
                borderRadius: 0
            });
            datasets.push({
                label: 'Tanque 4 (Exceso Absorbido)',
                data: data.map(d => d.adjustedTanks.t4_excess),
                backgroundColor: '#4d7c0f', // lime-700
                stack: 'combined',
                borderRadius: 0
            });
        } else {
            datasets.push({
                label: tankInfo[key].label,
                data: data.map(d => d.adjustedTanks[`t${key}`]),
                backgroundColor: tankInfo[key].color,
                stack: 'combined',
                borderRadius: 0
            });
        }
    });

    // Dataset para el Faltante (Residual) si la factura fue mayor al calculado
    datasets.push({
        label: 'Residual Faltante',
        data: data.map(d => d.shortfall),
        backgroundColor: '#a855f7', // purple-500
        stack: 'combined',
        borderRadius: 0
    });

    // 3. Línea de Factura (Frontera)
    datasets.push({
        label: 'Límite Factura',
        data: data.map(d => d.billed),
        type: 'line',
        borderColor: '#0f172a',
        borderWidth: 2,
        borderDash: [5, 5],
        pointRadius: 0,
        fill: false,
        order: -1
    });

    return {
        labels,
        datasets
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

// 5. Chart: Brecha de Eficiencia (Facturado - Recomendado)
const breachData = computed(() => {
    const data = props.evolution || [];
    return {
        labels: data.map(d => d.label),
        datasets: [{
            label: 'Desvío (kWh)',
            data: data.map(d => Math.round(d.billed - (d.recommended || d.billed))),
            backgroundColor: data.map(d => (d.billed - (d.recommended || d.billed)) > 0 ? 'rgba(244, 63, 94, 0.8)' : 'rgba(16, 185, 129, 0.8)'),
            borderRadius: 8,
        }]
    };
});

// 6. Chart: Composición 100% de Tanques por periodo
const pctTanksData = computed(() => {
    const data = processedEvolution.value;
    const tankInfo = {
        1: { label: 'Certeza %', color: '#059669' },
        2: { label: 'Base %',    color: '#f97316' },
        3: { label: 'Clima %',   color: '#38bdf8' },
    };
    const order = [1, 4, 2, 3];
    const datasets = [];

    order.forEach(key => {
        if (key === 4) {
            datasets.push({
                label: 'Variable Base %',
                data: data.map(d => {
                    const total = Object.values(d.adjustedTanks).reduce((a, b) => a + b, 0);
                    return total > 0 ? +((d.adjustedTanks.t4_base / total) * 100).toFixed(1) : 0;
                }),
                backgroundColor: '#84cc16',
                stack: 'pct',
                borderRadius: 0,
            });
            datasets.push({
                label: 'Variable Exceso %',
                data: data.map(d => {
                    const total = Object.values(d.adjustedTanks).reduce((a, b) => a + b, 0);
                    return total > 0 ? +((d.adjustedTanks.t4_excess / total) * 100).toFixed(1) : 0;
                }),
                backgroundColor: '#4d7c0f',
                stack: 'pct',
                borderRadius: 0,
            });
        } else {
            datasets.push({
                label: tankInfo[key].label,
                data: data.map(d => {
                    const total = Object.values(d.adjustedTanks).reduce((a, b) => a + b, 0);
                    return total > 0 ? +((d.adjustedTanks[`t${key}`] / total) * 100).toFixed(1) : 0;
                }),
                backgroundColor: tankInfo[key].color,
                stack: 'pct',
                borderRadius: 0,
            });
        }
    });
    return { labels: data.map(d => d.label), datasets };
});

// 7. Chart: Gasto Bimestral Total en Pesos
const billedCostData = computed(() => {
    const data = props.evolution || [];
    const totals = data.map(d => Math.round((d.costs.per_kwh || 0) * (d.billed || 0)));
    // Tendencia: media móvil de ventana 2
    const trend = totals.map((v, i) => i === 0 ? v : Math.round((v + totals[i - 1]) / 2));
    return {
        labels: data.map(d => d.label),
        datasets: [
            {
                label: 'Gasto Bimestral ($)',
                data: totals,
                backgroundColor: 'rgba(99, 102, 241, 0.75)',
                borderRadius: 8,
                yAxisID: 'y',
            },
            {
                label: 'Tendencia',
                data: trend,
                type: 'line',
                borderColor: '#f59e0b',
                backgroundColor: 'transparent',
                borderWidth: 2,
                tension: 0.4,
                pointRadius: 0,
                yAxisID: 'y',
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

const breachOptions = {
    ...chartOptions,
    plugins: {
        ...chartOptions.plugins,
        tooltip: {
            ...chartOptions.plugins.tooltip,
            callbacks: {
                label: (item) => {
                    const val = item.raw;
                    return val > 0 ? ` +${val} kWh sobre lo recomendado` : ` ${val} kWh bajo lo recomendado`;
                }
            }
        }
    },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 10, weight: '600' } } },
        y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 } }, beginAtZero: false }
    }
};

const pctOptions = {
    ...chartOptions,
    scales: {
        x: { stacked: true, grid: { display: false }, ticks: { font: { size: 10, weight: '600' } } },
        y: { stacked: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 }, callback: (v) => `${v}%` }, max: 100 }
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

const billedCostOptions = {
    ...chartOptions,
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 10, weight: '600' } } },
        y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 }, callback: (v) => `$${v.toLocaleString('es-AR')}` } }
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

            <!-- Row 3: Nuevos gráficos -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <!-- Chart 5: Brecha de Eficiencia -->
                <div class="bg-white p-8 rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/30">
                    <div class="flex items-center justify-between mb-8">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-rose-50 text-rose-500 rounded-xl">
                                    <TrendingDown :size="18" />
                                </div>
                                <h3 class="text-xl font-black text-slate-900 tracking-tight">Brecha de Eficiencia</h3>
                            </div>
                            <p class="text-xs text-slate-400 font-medium pl-10">Desvío entre Facturado y Recomendado por periodo <span class="text-rose-400 font-bold">● Exceso</span> <span class="text-emerald-500 font-bold">● Ahorro</span></p>
                        </div>
                    </div>
                    <div class="h-80 w-full">
                        <Bar :data="breachData" :options="breachOptions" />
                    </div>
                </div>

                <!-- Chart 6: Composición % de Tanques -->
                <div class="bg-white p-8 rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/30">
                    <div class="flex items-center justify-between mb-8">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-amber-50 text-amber-500 rounded-xl">
                                    <Layers :size="18" />
                                </div>
                                <h3 class="text-xl font-black text-slate-900 tracking-tight">Composición % por Tanque</h3>
                            </div>
                            <p class="text-xs text-slate-400 font-medium pl-10">Participación relativa de cada tanque — independiente del total</p>
                        </div>
                    </div>
                    <div class="h-80 w-full">
                        <Bar :data="pctTanksData" :options="pctOptions" />
                    </div>
                </div>

            </div>

            <!-- Chart 7: Gasto Bimestral en Pesos (full width) -->
            <div class="bg-white p-8 rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/30">
                <div class="flex items-center justify-between mb-8">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <div class="p-2 bg-violet-50 text-violet-500 rounded-xl">
                                <DollarSign :size="18" />
                            </div>
                            <h3 class="text-xl font-black text-slate-900 tracking-tight">Gasto Bimestral en Pesos</h3>
                        </div>
                        <p class="text-xs text-slate-400 font-medium pl-10">Costo total facturado por periodo con línea de tendencia suavizada</p>
                    </div>
                </div>
                <div class="h-72 w-full">
                    <Bar :data="billedCostData" :options="billedCostOptions" />
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
