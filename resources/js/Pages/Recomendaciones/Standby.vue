<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    Zap, 
    ZapOff, 
    ArrowRight, 
    AlertCircle, 
    BarChart3, 
    AlertTriangle,
    CheckCircle2,
    Radar,
    Info,
    TrendingDown,
    Activity,
    ShieldAlert
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    analysis: Object
});

const formatMoney = (val) => {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(val);
};

const totalStandbyKwh = computed(() => props.analysis.total_standby_kwh || 0);
const totalStandbyCost = computed(() => props.analysis.total_standby_cost || 0);
const percentageOfTotal = computed(() => props.analysis.percentage_of_total || 0);
</script>

<template>
    <MainLayout>
        <Head title="Consumo Fantasma" />

        <div class="max-w-7xl mx-auto space-y-12">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-100 text-amber-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-amber-200">
                        <Radar :size="14" />
                        Radar de Ineficiencia
                    </div>
                    <h1 class="text-5xl font-black text-slate-900 tracking-tighter leading-none">
                        Consumo <span class="text-amber-500">Fantasma</span>
                    </h1>
                    <p class="text-lg text-slate-500 font-medium">Equipos que sangran energía mientras están "apagados" o en reposo.</p>
                </div>

                <div class="bg-rose-50 border border-rose-100 p-6 rounded-[32px] max-w-sm flex items-start gap-4 shadow-xl shadow-rose-200/20">
                    <div class="w-10 h-10 rounded-2xl bg-rose-200/50 flex items-center justify-center text-rose-600 shrink-0">
                        <ShieldAlert :size="20" />
                    </div>
                    <div class="space-y-1 text-rose-900">
                        <p class="text-sm font-black leading-tight">Marea Silenciosa</p>
                        <p class="text-[11px] font-medium leading-relaxed">
                            El standby representa el **{{ percentageOfTotal }}%** de tu factura anual. Es el consumo más fácil de eliminar.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main KPI Deck -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-10 rounded-[40px] border border-slate-100 shadow-2xl shadow-slate-200/30 flex flex-col justify-between space-y-6">
                    <div>
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-2">Desperdicio Mensual</p>
                        <div class="flex items-baseline gap-1">
                            <h4 class="text-5xl font-black text-slate-900 tracking-tighter">{{ Math.round(totalStandbyKwh) }}</h4>
                            <span class="text-sm font-bold text-slate-400">kWh</span>
                        </div>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-amber-400" :style="{ width: `${percentageOfTotal}%` }"></div>
                    </div>
                </div>

                <div class="bg-slate-900 p-10 rounded-[40px] shadow-2xl shadow-slate-400/20 text-white flex flex-col justify-between space-y-6 overflow-hidden relative">
                    <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/5 rounded-full blur-2xl"></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Impacto Monetario</p>
                        <div class="flex items-baseline gap-1">
                            <h4 class="text-5xl font-black text-white tracking-tighter">{{ formatMoney(totalStandbyCost) }}</h4>
                            <span class="text-sm font-bold text-slate-500">/ mes</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 font-medium">Equivale a {{ Math.round(totalStandbyKwh / 30) }} laptops encendidas 24/7 sin hacer nada.</p>
                </div>

                <div class="bg-amber-500 p-10 rounded-[40px] shadow-2xl shadow-amber-500/30 text-white flex flex-col justify-between space-y-6 relative group overflow-hidden">
                    <ZapOff :size="80" class="absolute -left-4 -bottom-4 text-white/20 -rotate-12 group-hover:rotate-0 transition-transform duration-700" />
                    <div>
                        <p class="text-[10px] font-black text-white/60 uppercase tracking-widest mb-2">Ahorro Fácil</p>
                        <h4 class="text-3xl font-black tracking-tight leading-tight">Elimina este gasto hoy mismo.</h4>
                    </div>
                    <button class="bg-white text-amber-600 px-6 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest w-fit">
                        Ver Guía de Desconexión
                    </button>
                </div>
            </div>

            <!-- Detected Vampire Devices -->
            <div class="space-y-6">
                <h3 class="text-[10px] font-black text-slate-300 uppercase tracking-widest ml-4">Dispositivos "Vampiro" Detectados</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="item in analysis.vampire_devices" 
                        :key="item.id"
                        class="bg-white rounded-[40px] border border-slate-100 shadow-xl shadow-slate-200/20 p-8 space-y-6 group hover:shadow-2xl transition-all"
                    >
                        <div class="flex items-start justify-between">
                            <div class="space-y-1">
                                <h4 class="text-lg font-black text-slate-900">{{ item.name }}</h4>
                                <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">{{ item.category }}</p>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-200 group-hover:text-amber-500 transition-colors">
                                <ZapOff :size="24" />
                            </div>
                        </div>

                        <div class="p-6 bg-slate-50/50 rounded-3xl space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-tight">Carga en Standby</span>
                                <span class="text-sm font-black text-slate-700">{{ item.standby_power_w }}W</span>
                            </div>
                            <div class="h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-amber-400" :style="{ width: `${(item.standby_power_w / 20) * 100}%` }"></div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <div class="space-y-0.5">
                                <p class="text-[9px] font-black text-slate-300 uppercase">Costo Anual</p>
                                <p class="text-base font-black text-slate-900">{{ formatMoney(item.daily_cost * 365) }}</p>
                            </div>
                            <span class="text-xs font-bold text-amber-600 bg-amber-100/50 px-3 py-1 rounded-full">Revisar</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommendation Alert -->
            <div class="bg-indigo-900 rounded-[48px] p-10 md:p-16 text-white grid grid-cols-1 md:grid-cols-2 gap-12 items-center relative overflow-hidden">
                <div class="absolute top-0 left-1/3 w-96 h-96 bg-energy-solar/10 rounded-full blur-[100px] pointer-events-none"></div>
                <div class="space-y-6 relative z-10">
                    <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center text-energy-solar">
                        <Activity :size="24" />
                    </div>
                    <h3 class="text-3xl font-black tracking-tight leading-tight">Automatiza la eficiencia</h3>
                    <p class="text-slate-400 font-medium leading-relaxed">
                        No hace falta que desenchufes todo manualmente. Recomendamos usar un **Smart Plug** o un **Relé IOT** en tu centro de entretenimiento y oficina para cortar la energía automáticamente durante la noche.
                    </p>
                    <div class="flex items-center gap-6 pt-4">
                        <div class="space-y-1">
                            <p class="text-[9px] font-black text-slate-500 uppercase">Inversión Smart</p>
                            <p class="text-xl font-black">~ USD 15</p>
                        </div>
                        <div class="h-10 w-px bg-white/10"></div>
                        <div class="space-y-1">
                            <p class="text-[9px] font-black text-slate-500 uppercase">Payback</p>
                            <p class="text-xl font-black">4 Meses</p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 relative z-10">
                    <div 
                        v-for="tip in ['Zapatillas con interruptor', 'Timers mecánicos', 'Deshabilitar luz de espera', 'Cargadores inteligentes']" 
                        :key="tip"
                        class="bg-white/5 border border-white/10 p-5 rounded-2xl flex items-center gap-4 hover:bg-white/10 transition-colors"
                    >
                        <CheckCircle2 :size="18" class="text-energy-success" />
                        <span class="text-sm font-bold">{{ tip }}</span>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
