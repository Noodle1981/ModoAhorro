<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    Zap, 
    ZapOff, 
    ArrowRight, 
    AlertCircle, 
    Radar,
    Info,
    TrendingDown,
    Activity,
    ShieldAlert,
    Plug,
    CheckCircle2,
    Power
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    analysis: Object
});

const formatMoney = (val) => {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(val);
};

const totalStandbyKwh = computed(() => props.analysis.totalStandbyKwh || 0);
const totalStandbyCost = computed(() => props.analysis.totalStandbyCost || 0);
const totalPotentialSavings = computed(() => props.analysis.totalPotentialSavings || 0);
const totalRealizedSavings = computed(() => props.analysis.totalRealizedSavings || 0);
const equipmentList = computed(() => props.analysis.equipmentList || []);

// Agrupar por categoría
const groupedEquipment = computed(() => {
    const groups = {};
    equipmentList.value.forEach(eq => {
        const cat = eq.category?.name || 'Otros';
        if (!groups[cat]) groups[cat] = [];
        groups[cat].push(eq);
    });
    return groups;
});

const toggleStandby = (id) => {
    router.post(route('standby.toggle', id), {}, {
        preserveScroll: true
    });
};

// Cálculo de ahorro para cada equipo (basado en lógica de StandbyAnalysisService)
const getEquipmentStats = (eq) => {
    const standbyPowerW = eq.type?.default_standby_power_w ?? 5;
    const standbyPowerKw = standbyPowerW / 1000;
    const activeHours = eq.avg_daily_use_hours ?? 2;
    const standbyHours = Math.max(0, 24 - activeHours);
    const monthlyKwh = standbyPowerKw * standbyHours * 30;
    const monthlyCost = monthlyKwh * (props.analysis.averageTariff || 150);
    return { standbyPowerW, standbyHours, monthlyKwh, monthlyCost };
};
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
                        Consumo <span class="text-energy-solar">Fantasma</span>
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
                            Marcá los equipos que dejas desenchufados para ver cuánto dinero estás ahorrando realmente.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Main KPI Deck -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-10 rounded-[32px] shadow-2xl shadow-slate-900/20 text-white flex flex-col justify-between space-y-6 overflow-hidden relative">
                    <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/5 rounded-full blur-2xl"></div>
                    <div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Consumo Actual</p>
                        <div class="flex items-baseline gap-1">
                            <h4 class="text-5xl font-black text-white tracking-tighter">{{ totalStandbyKwh }}</h4>
                            <span class="text-sm font-bold text-slate-500">kWh / mes</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 font-medium">Consumo mensual por Stand By de equipos conectados.</p>
                </div>

                <div class="bg-white p-10 rounded-[32px] border border-slate-100 shadow-2xl shadow-slate-200/30 flex flex-col justify-between space-y-6">
                    <div>
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-2">Costo Estimado</p>
                        <div class="flex items-baseline gap-1">
                            <h4 class="text-5xl font-black text-slate-900 tracking-tighter">{{ formatMoney(totalStandbyCost) }}</h4>
                            <span class="text-sm font-bold text-slate-400">/ mes</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 font-medium">Gasto adicional en tu factura por equipos en espera.</p>
                </div>

                <div class="bg-energy-success p-10 rounded-[32px] shadow-2xl shadow-emerald-500/30 text-white flex flex-col justify-between space-y-6 relative group overflow-hidden">
                    <CheckCircle2 :size="80" class="absolute -left-4 -bottom-4 text-white/20 -rotate-12 group-hover:rotate-0 transition-transform duration-700" />
                    <div>
                        <p class="text-[10px] font-black text-white/60 uppercase tracking-widest mb-2">{{ totalRealizedSavings > 0 ? 'Ahorro Logrado' : 'Ahorro Potencial' }}</p>
                        <div class="flex items-baseline gap-1">
                            <h4 class="text-5xl font-black text-white tracking-tighter">{{ formatMoney(totalRealizedSavings > 0 ? totalRealizedSavings : totalPotentialSavings) }}</h4>
                            <span class="text-sm font-bold text-emerald-100">/ mes</span>
                        </div>
                    </div>
                    <p class="text-xs text-emerald-100 font-medium">
                        {{ totalRealizedSavings > 0 ? '¡Seguí así! Este es el monto que ya no pagás.' : 'Monto que podrías ahorrar desenchufando equipos.' }}
                    </p>
                </div>
            </div>

            <!-- Interactive Equipment List -->
            <div class="space-y-6">
                <div class="flex items-center justify-between px-4">
                    <h3 class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Mis Equipos en Standby</h3>
                    <div class="flex items-center gap-4 text-[9px] font-black uppercase tracking-widest">
                        <span class="flex items-center gap-1.5 text-energy-solar">
                            <div class="w-2 h-2 rounded-full bg-energy-solar"></div> Enchufado (Consume)
                        </span>
                        <span class="flex items-center gap-1.5 text-energy-success">
                            <div class="w-2 h-2 rounded-full bg-energy-success"></div> Desenchufado (Ahorra)
                        </span>
                    </div>
                </div>
                
                <div class="bg-white rounded-[32px] border border-slate-100 shadow-2xl shadow-slate-200/30 overflow-hidden">
                    <div v-for="(items, categoryName) in groupedEquipment" :key="categoryName">
                        <div class="px-8 py-3 bg-slate-50 border-y border-slate-100 first:border-t-0">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ categoryName }}</span>
                        </div>
                        <div 
                            v-for="eq in items" 
                            :key="eq.id"
                            class="flex flex-col md:flex-row items-center justify-between px-8 py-6 border-b border-slate-50 last:border-b-0 hover:bg-slate-50/50 transition-all group"
                            :class="!eq.is_standby && 'opacity-60 grayscale-[0.5]'"
                        >
                            <!-- Info Equipo -->
                            <div class="flex items-center gap-4 flex-1 w-full md:w-auto">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-colors"
                                    :class="eq.is_standby ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600'">
                                    <Plug :size="24" />
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-base font-black text-slate-900 truncate">{{ eq.name }}</h4>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">
                                        {{ eq.room?.name || 'Ambiente' }} · {{ getEquipmentStats(eq).standbyPowerW }}W · {{ Math.round(getEquipmentStats(eq).standbyHours) }}h en espera
                                    </p>
                                </div>
                            </div>

                            <!-- Impacto -->
                            <div class="flex items-center gap-12 mt-4 md:mt-0 w-full md:w-auto justify-between md:justify-end">
                                <div class="text-right">
                                    <template v-if="eq.is_standby">
                                        <p class="text-sm font-black text-slate-900">{{ getEquipmentStats(eq).monthlyKwh.toFixed(1) }} kWh/mes</p>
                                        <p class="text-[10px] font-black text-energy-danger uppercase">{{ formatMoney(getEquipmentStats(eq).monthlyCost) }}/mes</p>
                                    </template>
                                    <template v-else>
                                        <p class="text-sm font-black text-energy-success">Ahorrando</p>
                                        <p class="text-[10px] font-black text-emerald-400 uppercase">-{{ formatMoney(getEquipmentStats(eq).monthlyCost) }}/mes</p>
                                    </template>
                                </div>

                                <!-- Switch Toggle -->
                                <button 
                                    @click="toggleStandby(eq.id)"
                                    class="relative w-14 h-8 rounded-full transition-colors duration-300 focus:outline-none ring-offset-2 focus:ring-2 focus:ring-energy-solar"
                                    :class="eq.is_standby ? 'bg-energy-solar' : 'bg-slate-200'"
                                >
                                    <div class="absolute top-1 left-1 w-6 h-6 bg-white rounded-full shadow-sm transition-transform duration-300"
                                        :class="eq.is_standby ? 'translate-x-6' : 'translate-x-0'">
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="equipmentList.length === 0" class="p-20 text-center space-y-4">
                        <div class="w-20 h-20 bg-emerald-50 text-emerald-500 rounded-[32px] flex items-center justify-center mx-auto shadow-xl shadow-emerald-500/10">
                            <CheckCircle2 :size="40" />
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-xl font-black text-slate-900">¡Zona Libre de Vampiros!</h4>
                            <p class="text-sm text-slate-500 font-medium">No se detectaron equipos con consumo standby relevante.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommendation Alert -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-[32px] p-10 md:p-16 text-white grid grid-cols-1 md:grid-cols-2 gap-12 items-center relative overflow-hidden">
                <div class="absolute top-0 left-1/3 w-96 h-96 bg-energy-solar/10 rounded-full blur-[100px] pointer-events-none"></div>
                <div class="space-y-6 relative z-10">
                    <div class="w-12 h-12 backdrop-blur-md bg-white/10 border border-white/20 rounded-[24px] flex items-center justify-center text-energy-solar">
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

