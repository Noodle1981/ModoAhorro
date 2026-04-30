<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    Activity, 
    ArrowLeft, 
    CheckCircle2, 
    Zap, 
    ShieldCheck, 
    ThermometerSun, 
    Gamepad2, 
    Info,
    AlertCircle,
    TrendingDown,
    TrendingUp,
    LayoutGrid,
    PieChart,
    ChevronRight,
    ArrowRight
} from 'lucide-vue-next';

const props = defineProps({
    engine: Object,
    entity: Object,
    period: Object
});

// Alerta: energía sin explicar > 5% del total facturado
const unassignedPercent = computed(() => {
    if (!props.engine?.invoiced_kwh || props.engine.invoiced_kwh <= 0) return 0;
    return Math.abs((props.engine.unassigned_remainder ?? 0) / props.engine.invoiced_kwh) * 100;
});

const hasUnassignedAlert = computed(() => unassignedPercent.value > 5);

const formatKwh = (val) => Number(val || 0).toFixed(1);

const calibratedTotal = computed(() => {
    if (props.engine?.calibrated_total > 0) return props.engine.calibrated_total;
    return (props.engine?.tanks || []).reduce((acc, t) => acc + (t.total_kwh || 0), 0);
});

const getTankIcon = (key) => {
    switch (key) {
        case 1: return ShieldCheck;
        case 2: return ThermometerSun;
        case 3: return Gamepad2;
        case 4: return Zap;
        default: return Zap;
    }
};

const getTankBarClass = (key) => {
    switch (key) {
        case 1: return 'bg-emerald-600'; // Certeza (Verde Esmeralda)
        case 2: return 'bg-rose-500';    // Base Crítica (Rojo)
        case 3: return 'bg-sky-400';     // Climatización (Celeste)
        case 4: return 'bg-lime-500';    // Uso Variable (Verde Lima)
        default: return 'bg-slate-400';
    }
};

const getTankColorClass = (key) => {
    switch (key) {
        case 1: return 'text-emerald-600 bg-emerald-50 border-emerald-100';
        case 2: return 'text-rose-500 bg-rose-50 border-rose-100';
        case 3: return 'text-sky-500 bg-sky-50 border-sky-100';
        case 4: return 'text-lime-600 bg-lime-50 border-lime-100';
        default: return 'text-slate-500 bg-slate-50 border-slate-100';
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const [year, month, day] = dateString.split('T')[0].split('-');
    return `${day}/${month}/${year.slice(-2)}`;
};
</script>

<template>
    <Head title="Resultados del Motor - ModoAhorro" />

    <MainLayout>
        <div class="max-w-[1400px] mx-auto px-6 py-12 space-y-12">
            
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                <div class="space-y-4">
                    <Link 
                        :href="route('analisis.usage')" 
                        class="inline-flex items-center gap-2 text-xs font-black text-slate-400 uppercase tracking-widest hover:text-slate-900 transition-colors group"
                    >
                        <ArrowLeft :size="14" class="group-hover:-translate-x-1 transition-transform" /> Volver al Ajuste
                    </Link>
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-[28px] bg-energy-solar flex items-center justify-center text-white shadow-xl shadow-energy-solar/20">
                            <Activity :size="32" />
                        </div>
                        <div>
                            <h1 class="text-4xl font-black text-slate-900 tracking-tighter leading-none">Resultados de Calibración</h1>
                            <p class="text-slate-500 font-bold mt-2 flex items-center gap-2">
                                Periodo {{ formatDate(period.start_date) }} - {{ formatDate(period.end_date) }}
                                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                {{ period.days }} días
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="px-6 py-4 bg-energy-success/10 border border-energy-success/20 rounded-[32px] flex items-center gap-4">
                        <CheckCircle2 class="text-energy-success" :size="24" />
                        <div>
                            <p class="text-[10px] font-black text-energy-success uppercase tracking-widest">Estado</p>
                            <p class="text-lg font-black text-slate-900 leading-none">Calibrado con Éxito</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                <!-- Left Column: Tank Visualization -->
                <div class="lg:col-span-8 space-y-8">
                    
                    <!-- Energy Distribution Card -->
                    <div class="bg-white rounded-[48px] border border-slate-100 shadow-2xl p-10 space-y-10">
                        <div class="flex items-center justify-between">
                            <h2 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                                <LayoutGrid :size="24" class="text-energy-solar" /> Distribución de la Bolsa
                            </h2>
                            <div class="text-right">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Bolsa Total (Factura)</p>
                                <p class="text-4xl font-black text-slate-900 leading-none">{{ Math.round(engine.invoiced_kwh) }} <span class="text-lg font-normal text-slate-400">kWh</span></p>
                            </div>
                        </div>

                        <!-- Horizontal Tank Bar -->
                        <div class="space-y-4">
                            <div class="h-16 w-full bg-slate-50 rounded-[24px] p-2 flex gap-1.5 overflow-hidden border border-slate-100">
                                <div 
                                    v-for="tank in engine.tanks" 
                                    :key="tank.key"
                                    class="h-full rounded-[14px] transition-all duration-1000 ease-out group relative cursor-help"
                                    :class="getTankBarClass(tank.key)"
                                    :style="{ width: (tank.total_kwh / calibratedTotal * 100) + '%' }"
                                >
                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <span class="text-[10px] font-black text-white">{{ Math.round(tank.total_kwh / calibratedTotal * 100) }}%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-4 gap-4 px-2">
                                <div v-for="tank in engine.tanks" :key="tank.key" class="text-center space-y-1">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <div class="w-2 h-2 rounded-full" :class="getTankBarClass(tank.key)"></div>
                                        <span class="text-[10px] font-black text-slate-900 uppercase tracking-tighter">{{ tank.label }}</span>
                                    </div>
                                    <p class="text-sm font-black text-slate-400">{{ Math.round(tank.total_kwh) }} kWh</p>
                                </div>
                            </div>
                        </div>

                        <!-- Tank Details Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div 
                                v-for="tank in engine.tanks" 
                                :key="tank.key"
                                class="p-6 rounded-[32px] border border-slate-100 bg-slate-50/50 hover:bg-white hover:shadow-xl transition-all group"
                            >
                                <div class="flex items-start justify-between mb-6">
                                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center" :class="getTankColorClass(tank.key)">
                                        <component :is="getTankIcon(tank.key)" :size="24" />
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Peso en Bolsa</p>
                                        <p class="text-2xl font-black text-slate-900 leading-none">{{ Math.round(tank.total_kwh / engine.invoiced_kwh * 100) }}%</p>
                                    </div>
                                </div>
                                <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">{{ tank.label }}</h3>
                                <p class="text-xs text-slate-500 font-medium leading-relaxed mt-2 mb-6 h-8 overflow-hidden">
                                    {{ tank.description || 'Consumo identificado y procesado por el motor.' }}
                                </p>
                                
                                <div class="space-y-3">
                                    <div v-for="item in tank.top_items" :key="item.name" class="flex justify-between items-center bg-white p-3 rounded-2xl border border-slate-100 shadow-sm">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400">
                                                <Activity :size="14" />
                                            </div>
                                            <span class="text-[10px] font-bold text-slate-700 truncate w-24 md:w-32">{{ item.name }}</span>
                                        </div>
                                        <span class="text-[10px] font-black text-slate-900">{{ formatKwh(item.kwh) }} kWh</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alerta: Energía sin explicar -->
                    <div 
                        v-if="hasUnassignedAlert"
                        class="bg-amber-50 border border-amber-200 rounded-[32px] p-6 flex items-start gap-4"
                    >
                        <AlertCircle :size="24" class="text-amber-500 shrink-0 mt-0.5" />
                        <div>
                            <p class="text-sm font-black text-amber-900 leading-tight">{{ Math.round(unassignedPercent) }}% sin explicar</p>
                            <p class="text-[10px] font-medium text-amber-700 leading-relaxed mt-1">
                                {{ Math.abs(Math.round(engine.unassigned_remainder)) }} kWh no están atribuidos a ningún equipo declarado. 
                                ¿Hay un equipo sin declarar o con un error de potencia?
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Balance & Summary -->
                <div class="lg:col-span-4 space-y-8">
                    
                    <!-- Balance Card -->
                    <div class="bg-slate-900 rounded-[48px] p-10 text-white space-y-8 shadow-2xl">
                        <div class="space-y-2">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Balance del Motor</p>
                            <h2 class="text-3xl font-black tracking-tight">Sintonía Fina</h2>
                        </div>

                        <div class="space-y-6">
                            <div class="flex justify-between items-center py-4 border-b border-white/10">
                                <span class="text-sm font-bold text-slate-400">Declarado Inicial</span>
                                <span class="text-lg font-black">{{ Math.round(engine.declared_kwh) }} kWh</span>
                            </div>
                            <div class="flex justify-between items-center py-4 border-b border-white/10">
                                <span class="text-sm font-bold text-slate-400">Ajuste por Motor (T4)</span>
                                <div class="flex items-center gap-2">
                                    <TrendingDown v-if="engine.adjustment_kwh < 0" class="text-energy-success" :size="20" />
                                    <TrendingUp v-else class="text-energy-solar" :size="20" />
                                    <span class="text-lg font-black" :class="engine.adjustment_kwh < 0 ? 'text-energy-success' : 'text-energy-solar'">
                                        {{ engine.adjustment_kwh > 0 ? '+' : '' }}{{ Math.round(engine.adjustment_kwh) }} kWh
                                    </span>
                                </div>
                            </div>
                            <div class="flex justify-between items-center pt-4">
                                <span class="text-sm font-bold text-white">Consumo Final Real</span>
                                <span class="text-3xl font-black text-energy-solar">{{ Math.round(engine.invoiced_kwh) }} kWh</span>
                            </div>
                        </div>

                        <div class="bg-white/5 rounded-3xl p-6 space-y-4">
                            <div class="flex items-start gap-3">
                                <Info :size="18" class="text-sky-400 shrink-0 mt-1" />
                                <p class="text-xs text-slate-300 font-medium leading-relaxed">
                                    El motor ha absorbido un error de declaración del **{{ Math.round(Math.abs(engine.adjustment_kwh / engine.invoiced_kwh) * 100) }}%** mediante la sintonía elástica de los equipos en el **Tanque 4**.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Next Step Card -->
                    <div class="bg-white rounded-[40px] border border-slate-100 p-8 shadow-xl space-y-6">
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Próximos Pasos</h3>
                        <div class="space-y-4">
                            <Link :href="route('analisis.consumption')" class="w-full p-6 bg-slate-50 hover:bg-slate-100 rounded-[28px] border border-slate-100 flex items-center justify-between group transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-slate-400">
                                        <PieChart :size="20" />
                                    </div>
                                    <div class="text-left">
                                        <p class="text-[10px] font-black text-slate-900 uppercase">Análisis Detallado</p>
                                        <p class="text-[9px] text-slate-400 font-bold uppercase">Ver costos y ahorros</p>
                                    </div>
                                </div>
                                <ArrowRight :size="18" class="text-slate-300 group-hover:text-slate-900 transition-colors" />
                            </Link>

                            <Link :href="route('home')" class="w-full p-6 bg-slate-900 text-white rounded-[28px] flex items-center justify-between group hover:bg-energy-solar transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-white">
                                        <CheckCircle2 :size="20" />
                                    </div>
                                    <div class="text-left">
                                        <p class="text-[10px] font-black uppercase">Finalizar Sintonía</p>
                                        <p class="text-[9px] text-white/60 font-bold uppercase">Volver al panel principal</p>
                                    </div>
                                </div>
                                <ChevronRight :size="18" class="group-hover:translate-x-1 transition-transform" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
.animate-in {
    animation-duration: 0.8s;
    animation-fill-mode: both;
}

@keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slide-in-top {
    from { transform: translateY(-20px); }
    to { transform: translateY(0); }
}

.fade-in { animation-name: fade-in; }
.slide-in-from-top-4 { animation-name: slide-in-top; }
</style>
