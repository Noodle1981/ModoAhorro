<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    ZapOff, 
    Radar,
    Activity,
    ShieldAlert,
    Plug,
    CheckCircle2,
    Lock
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
            textLight: 'text-purple-400',
            bgDark: 'bg-purple-950',
            textDark: 'text-purple-950',
            groupHoverText: 'group-hover:text-purple-600',
            hoverShadow: 'hover:shadow-purple-500/10',
            groupHoverBg: 'group-hover:bg-purple-600',
            borderBottom: 'border-purple-600',
            blurBg: 'bg-purple-500/10',
            text100: 'text-purple-100',
            text300: 'text-purple-300',
            bg200Opacity: 'bg-purple-500/20',
            shadowEmerald: 'shadow-purple-500/30'
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
            textLight: 'text-blue-400',
            bgDark: 'bg-blue-950',
            textDark: 'text-blue-950',
            groupHoverText: 'group-hover:text-blue-600',
            hoverShadow: 'hover:shadow-blue-500/10',
            groupHoverBg: 'group-hover:bg-blue-600',
            borderBottom: 'border-blue-600',
            blurBg: 'bg-blue-500/10',
            text100: 'text-blue-100',
            text300: 'text-blue-300',
            bg200Opacity: 'bg-blue-500/20',
            shadowEmerald: 'shadow-blue-500/30'
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
        textLight: 'text-emerald-400',
        bgDark: 'bg-emerald-950',
        textDark: 'text-emerald-950',
        groupHoverText: 'group-hover:text-emerald-600',
        hoverShadow: 'hover:shadow-emerald-500/10',
        groupHoverBg: 'group-hover:bg-emerald-600',
        borderBottom: 'border-emerald-600',
        blurBg: 'bg-emerald-500/10',
        text100: 'text-emerald-100',
        text300: 'text-emerald-300',
        bg200Opacity: 'bg-emerald-500/20',
        shadowEmerald: 'shadow-emerald-500/30'
    };
});

const toggleStandby = (id) => {
    router.post(route('recomendaciones.standby.toggle', id), {}, {
        preserveScroll: true
    });
};

// Cálculo de ahorro para cada equipo (coincidente con StandbyAnalysisService)
const getEquipmentStats = (eq) => {
    const dbStandby = eq.type?.default_standby_power_w;
    const standbyPowerW = (dbStandby && dbStandby > 0) ? dbStandby : 5;
    
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

        <div class="max-w-7xl mx-auto h-full flex flex-col min-h-0 space-y-4">
            <!-- Fixed Top Header & KPI Deck (shrink-0) -->
            <div class="shrink-0 space-y-3.5">
                <!-- Main KPI Deck -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 sm:gap-4">
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-5 sm:p-6 rounded-3xl shadow-lg shadow-slate-900/10 text-white flex flex-col justify-between overflow-hidden relative">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/5 rounded-full blur-xl pointer-events-none"></div>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Consumo Actual</p>
                        <div class="flex items-baseline gap-1.5 my-1">
                            <h4 class="text-3xl sm:text-4xl font-black text-white tracking-tighter">{{ totalStandbyKwh }}</h4>
                            <span class="text-xs font-bold text-slate-400">kWh / mes</span>
                        </div>
                        <p class="text-[11px] text-slate-400 font-medium">Consumo mensual por Stand By activo.</p>
                    </div>

                    <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-100 shadow-md flex flex-col justify-between">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5">Costo Estimado</p>
                        <div class="flex items-baseline gap-1.5 my-1">
                            <h4 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tighter">{{ formatMoney(totalStandbyCost) }}</h4>
                            <span class="text-xs font-bold text-slate-400">/ mes</span>
                        </div>
                        <p class="text-[11px] text-slate-400 font-medium">Gasto adicional en tu factura por espera.</p>
                    </div>

                    <div :class="[themeColors.bg, themeColors.shadowEmerald]" class="p-5 sm:p-6 rounded-3xl shadow-md text-white flex flex-col justify-between relative group overflow-hidden">
                        <CheckCircle2 :size="60" class="absolute -right-2 -bottom-2 text-white/15 -rotate-12 group-hover:rotate-0 transition-transform duration-700 pointer-events-none" />
                        <p class="text-[9px] font-black text-white/70 uppercase tracking-widest mb-1.5">{{ totalRealizedSavings > 0 ? 'Ahorro Logrado' : 'Ahorro Potencial' }}</p>
                        <div class="flex items-baseline gap-1.5 my-1">
                            <h4 class="text-3xl sm:text-4xl font-black text-white tracking-tighter">{{ formatMoney(totalRealizedSavings > 0 ? totalRealizedSavings : totalPotentialSavings) }}</h4>
                            <span :class="themeColors.text100" class="text-xs font-bold">/ mes</span>
                        </div>
                        <p :class="themeColors.text100" class="text-[11px] font-medium opacity-90 truncate">
                            {{ totalRealizedSavings > 0 ? '¡Monto que ya ahorraste desenchufando!' : 'Monto que podrías ahorrar desenchufando.' }}
                        </p>
                    </div>
                </div>

                <!-- Section Header & Status Legend Toolbar -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-1">
                    <div>
                        <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">Auditoría de Equipos</h3>
                        <p class="text-xs text-slate-400 font-medium">Marcá el estado real de cada aparato para actualizar tu consumo fantasma en vivo.</p>
                    </div>

                    <!-- Clean Tailwind Legend -->
                    <div class="flex flex-wrap items-center gap-2 text-[9px] font-black uppercase tracking-wider">
                        <span class="flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 text-amber-800 rounded-lg border border-amber-200/80 shadow-xs">
                            <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></div>
                            <span>Enchufado</span>
                        </span>
                        <span class="flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-800 rounded-lg border border-emerald-200/80 shadow-xs">
                            <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                            <span>Desenchufado</span>
                        </span>
                        <span class="flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg border border-slate-200/80 shadow-xs">
                            <div class="w-2 h-2 rounded-full bg-slate-400"></div>
                            <span>Pendiente</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Scrollable Equipment Grid & Tips Container -->
            <div class="flex-1 min-h-0 overflow-y-auto pr-1.5 pb-8 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="eq in equipmentList" :key="eq.id"
                        class="relative group bg-white rounded-3xl border-2 p-5 transition-all duration-300 cursor-pointer overflow-hidden shadow-xs hover:shadow-lg select-none"
                        :class="[
                            eq.is_standby === 1 ? 'border-amber-400 bg-amber-50/20 shadow-amber-500/10' : 
                            eq.is_standby === 0 ? 'border-emerald-300 bg-emerald-50/10' :
                            'border-slate-100 bg-slate-50/40 grayscale hover:grayscale-0 opacity-85 hover:opacity-100'
                        ]"
                        @click="toggleStandby(eq.id)"
                    >
                        <!-- Background Glow when active -->
                        <div v-if="eq.is_standby === 1" class="absolute -right-8 -bottom-8 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>

                        <div class="relative z-10 flex flex-col h-full justify-between gap-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-all duration-500 shadow-xs shrink-0"
                                    :class="[
                                        eq.is_standby === 1 ? 'bg-amber-500 text-white shadow-amber-500/20' : 
                                        eq.is_standby === 0 ? 'bg-emerald-600 text-white shadow-emerald-600/20' :
                                        'bg-slate-200 text-slate-400'
                                    ]">
                                    <Plug v-if="eq.is_standby === 1" :size="24" />
                                    <ZapOff v-else-if="eq.is_standby === 0" :size="24" />
                                    <Lock v-else :size="20" class="opacity-40" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-black text-slate-900 truncate tracking-tight leading-tight">{{ eq.name }}</h4>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide mt-0.5">
                                        {{ eq.room?.name || 'General' }} · {{ getEquipmentStats(eq).standbyPowerW }}W
                                    </p>
                                </div>
                                <div class="shrink-0">
                                    <div class="w-7 h-7 rounded-full border-2 flex items-center justify-center transition-all duration-300 shadow-xs"
                                        :class="[
                                            eq.is_standby === 1 ? 'bg-amber-500 border-amber-500 text-white' : 
                                            eq.is_standby === 0 ? 'bg-emerald-600 border-emerald-600 text-white' :
                                            'border-slate-200 bg-white text-slate-300'
                                        ]">
                                        <CheckCircle2 v-if="eq.is_standby !== null" :size="16" />
                                        <span v-else class="text-xs font-black">?</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-end justify-between border-t border-slate-100 pt-3 mt-auto">
                                <div>
                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-0.5">
                                        {{ eq.is_standby === null ? 'Estado Desconocido' : 'Impacto Mensual' }}
                                    </p>
                                    <div class="flex items-baseline gap-1">
                                        <template v-if="eq.is_standby === 1">
                                            <span class="text-lg font-black text-amber-700">{{ formatMoney(getEquipmentStats(eq).monthlyCost) }}</span>
                                            <span class="text-[9px] font-bold text-slate-400">/ mes</span>
                                        </template>
                                        <template v-else-if="eq.is_standby === 0">
                                            <span class="text-sm font-black text-emerald-700">¡Optimizado!</span>
                                        </template>
                                        <template v-else>
                                            <span class="text-sm font-black text-slate-400">Declarar</span>
                                        </template>
                                    </div>
                                </div>
                                <div v-if="eq.is_standby === 1" class="text-right">
                                    <p class="text-[8px] font-black text-amber-700 uppercase tracking-widest mb-0.5">Consumo Activo</p>
                                    <p class="text-xs font-black text-amber-800 tracking-tight">{{ getEquipmentStats(eq).monthlyKwh.toFixed(1) }} kWh</p>
                                </div>
                                <div v-if="eq.is_standby === 0" class="text-right">
                                    <p class="text-[8px] font-black text-emerald-700 uppercase tracking-widest mb-0.5">Evitaste pagar</p>
                                    <p class="text-xs font-black text-emerald-800 tracking-tight">{{ formatMoney(getEquipmentStats(eq).monthlyCost) }}</p>
                                </div>
                                <div v-else-if="eq.is_standby === null" class="text-right">
                                    <p class="text-[8px] font-bold text-slate-300 uppercase leading-tight">Clic para<br>declarar</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="equipmentList.length === 0" class="bg-white rounded-3xl border border-slate-100 p-16 text-center space-y-3 shadow-md">
                    <div :class="[themeColors.bgLight, themeColors.text, themeColors.shadow]" class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto shadow-md">
                        <CheckCircle2 :size="32" />
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-lg font-black text-slate-900">Tu casa está limpia</h4>
                        <p class="text-xs text-slate-400 font-medium">No detectamos equipos para analizar.</p>
                    </div>
                </div>

                <!-- Recommendation Alert -->
                <div :class="themeColors.bgDark" class="rounded-3xl p-8 text-white grid grid-cols-1 md:grid-cols-2 gap-8 items-center relative overflow-hidden shadow-xl shadow-black/10">
                    <div :class="themeColors.blurBg" class="absolute top-0 left-1/3 w-80 h-80 rounded-full blur-[80px] pointer-events-none"></div>
                    <div class="space-y-4 relative z-10">
                        <div :class="themeColors.textLight" class="w-10 h-10 backdrop-blur-md bg-white/10 border border-white/20 rounded-2xl flex items-center justify-center">
                            <Activity :size="20" />
                        </div>
                        <h3 class="text-2xl font-black tracking-tight leading-tight">Automatiza la eficiencia</h3>
                        <p :class="themeColors.text100" class="text-xs font-medium leading-relaxed opacity-80">
                            No hace falta que desenchufes todo manualmente. Recomendamos usar un **Smart Plug** o un **Relé IOT** en tu centro de entretenimiento y oficina para cortar la energía automáticamente durante la noche.
                        </p>
                        <div class="flex items-center gap-6 pt-2">
                            <div class="space-y-0.5">
                                <p class="text-[8px] font-black text-slate-400 uppercase">Inversión Smart</p>
                                <p class="text-lg font-black">~ USD 15</p>
                            </div>
                            <div class="h-8 w-px bg-white/10"></div>
                            <div class="space-y-0.5">
                                <p class="text-[8px] font-black text-slate-400 uppercase">Payback</p>
                                <p class="text-lg font-black">4 Meses</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-3 relative z-10">
                        <div 
                            v-for="tip in ['Zapatillas con interruptor', 'Timers mecánicos', 'Deshabilitar luz de espera', 'Cargadores inteligentes']" 
                            :key="tip"
                            class="bg-white/5 border border-white/10 p-3.5 rounded-xl flex items-center gap-3 hover:bg-white/10 transition-colors text-xs"
                        >
                            <CheckCircle2 :size="16" :class="themeColors.text" />
                            <span class="font-bold">{{ tip }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

