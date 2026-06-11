<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    RefreshCcw, 
    TrendingDown, 
    Zap, 
    ShoppingCart, 
    Timer, 
    ArrowUpRight,
    Award,
    History
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    opportunities: Array
});

const formatMoney = (val) => {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(val);
};

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
            bg200Opacity: 'bg-purple-500/20'
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
            bg200Opacity: 'bg-blue-500/20'
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
        bg200Opacity: 'bg-emerald-500/20'
    };
});
</script>

<template>
    <MainLayout>
        <Head title="Reemplazos Eficientes" />

        <div class="max-w-7xl mx-auto space-y-12">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                <div class="space-y-4">
                    <div :class="[themeColors.bgLight, themeColors.text, themeColors.borderLight]" class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border">
                        <RefreshCcw :size="14" />
                        Plan de Renovación
                    </div>
                    <h1 class="text-5xl font-black text-slate-900 tracking-tighter leading-none">
                        Reemplazos <span :class="themeColors.text">Eficientes</span>
                    </h1>
                    <p class="text-lg text-slate-500 font-medium">Equipos que generan un retorno de inversión real por su ahorro energético.</p>
                </div>

                <div class="flex flex-wrap gap-4">
                    <div class="bg-white px-6 py-4 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/40 flex items-center gap-4">
                        <div :class="[themeColors.bgLight, themeColors.text]" class="w-10 h-10 rounded-xl flex items-center justify-center">
                            <TrendingDown :size="20" />
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest leading-none mb-1">Ahorro Máximo</p>
                            <p class="text-lg font-black text-slate-900 leading-none">{{ formatMoney(opportunities.reduce((acc, o) => Math.max(acc, o.monthly_savings_amount), 0)) }}<span class="text-xs text-slate-400 font-bold ml-1">/ mes</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Opportunities Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div 
                    v-for="opportunity in opportunities" 
                    :key="opportunity.id"
                    :class="themeColors.hoverShadow"
                    class="bg-white rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/30 overflow-hidden group transition-all duration-500"
                >
                    <div class="p-10 space-y-8">
                        <!-- Top: Equipment and Verdict -->
                        <div class="flex items-start justify-between">
                            <div class="space-y-2">
                                <div class="flex items-center gap-3">
                                    <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ opportunity.name }}</h3>
                                    <div v-if="opportunity.is_estimated" class="p-1.5 bg-slate-50 text-slate-300 rounded-lg group-hover:text-slate-400 transition-colors" title="Estimación teórica">
                                        <History :size="14" />
                                    </div>
                                </div>
                                <p class="text-xs text-slate-400 font-medium tracking-tight">Sugerencia: {{ opportunity.suggestion }}</p>
                            </div>
                            <div :class="['px-4 py-2 rounded-2xl text-[10px] font-black uppercase tracking-widest border', opportunity.verdict.bg, opportunity.verdict.text, 'border-current/10']">
                                {{ opportunity.verdict.label }}
                            </div>
                        </div>

                        <!-- Stats: Before/After Comparison -->
                        <div class="grid grid-cols-2 gap-6">
                            <div class="bg-slate-50 p-6 rounded-[32px] space-y-2">
                                <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Consumo Actual</p>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-2xl font-black text-slate-700">{{ opportunity.current_consumption_kwh }}</span>
                                    <span class="text-xs font-bold text-slate-400">kWh/mes</span>
                                </div>
                            </div>
                            <div :class="themeColors.bgLight" class="p-6 rounded-[32px] space-y-2 relative overflow-hidden">
                                <ArrowUpRight :size="40" :class="themeColors.text" class="absolute -right-4 -bottom-4 opacity-10" />
                                <p :class="themeColors.text" class="text-[10px] font-black opacity-60 uppercase tracking-widest">Ahorro Estimado</p>
                                <div class="flex items-baseline gap-1">
                                    <span :class="themeColors.text" class="text-2xl font-black">{{ opportunity.potential_savings_kwh }}</span>
                                    <span :class="themeColors.text" class="text-xs font-bold opacity-40">kWh/mes</span>
                                </div>
                            </div>
                        </div>

                        <!-- ROI Panel -->
                        <div :class="themeColors.groupHoverBg" class="p-8 bg-slate-900 rounded-[32px] text-white flex items-center justify-between transition-all duration-700">
                            <div class="space-y-1">
                                <p class="text-[9px] font-black text-white/40 uppercase tracking-widest mb-1">Ahorro en Factura</p>
                                <p class="text-2xl font-black">{{ formatMoney(opportunity.monthly_savings_amount) }} <span class="text-xs font-bold text-white/40">/ mes</span></p>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] font-black text-white/40 uppercase tracking-widest mb-1">Payback</p>
                                <p class="text-2xl font-black">{{ opportunity.payback_months }} <span class="text-xs font-bold text-white/40">meses</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Market Link -->
                    <div class="px-10 py-6 bg-slate-50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <Zap :size="14" class="text-energy-solar" />
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Equipos Grado Energético A</span>
                        </div>
                        <a :href="opportunity.affiliate_link" target="_blank" :class="themeColors.groupHoverText" class="flex items-center gap-2 text-xs font-black text-slate-900 transition-colors">
                            Ver Presupuesto
                            <ShoppingCart :size="16" />
                        </a>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="opportunities.length === 0" class="lg:col-span-2 bg-slate-50/50 rounded-[48px] p-24 text-center border-2 border-dashed border-slate-100 space-y-6 flex flex-col items-center">
                    <div class="w-20 h-20 bg-white rounded-3xl shadow-xl flex items-center justify-center text-slate-200">
                        <RefreshCcw :size="40" />
                    </div>
                    <div class="space-y-2">
                        <h4 class="text-2xl font-black text-slate-900 tracking-tight">Sin oportunidades detectadas</h4>
                        <p class="text-slate-400 font-medium max-w-sm mx-auto">Tus equipos actuales ya son altamente eficientes o falta información técnica para realizar el cálculo de ROI.</p>
                    </div>
                    <Link :href="route('gestion.infrastructure')" :class="[themeColors.text, themeColors.borderBottom]" class="text-xs font-black uppercase tracking-widest border-b-2 pb-1 mt-4">
                        Actualizar Inventario
                    </Link>
                </div>
            </div>

            <!-- ROI Education -->
            <div :class="themeColors.bgDark" class="rounded-[64px] p-12 md:p-20 text-white relative overflow-hidden group shadow-2xl shadow-black/20">
                <div :class="themeColors.blurBg" class="absolute -right-20 -bottom-20 w-96 h-96 rounded-full blur-[100px]"></div>
                <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
                    <div class="space-y-8">
                        <div class="w-16 h-16 bg-white/10 rounded-[28px] flex items-center justify-center text-energy-solar">
                            <Award :size="32" />
                        </div>
                        <h3 class="text-4xl font-black tracking-tighter leading-tight">La ley del retorno <br/> energéticamente hablando.</h3>
                        <p :class="themeColors.text100" class="text-lg font-medium leading-relaxed opacity-80">
                            No todos los equipos eficientes se pagan solos. Nuestra lógica de "Payback" cruza el precio de mercado local con tu tarifa de electricidad actual para decirte si el cambio es realmente una inversión o solo un gasto.
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-6">
                        <div class="bg-white/5 border border-white/10 p-8 rounded-[40px] backdrop-blur-md flex items-center gap-6">
                            <div :class="[themeColors.bg200Opacity, themeColors.text300]" class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0">
                                <Timer :size="24" />
                            </div>
                            <p class="text-sm font-bold text-slate-300">Equipos con uso > 8hs diarias tienen un ROI prioritario de nivel diamante.</p>
                        </div>
                        <div class="bg-white/5 border border-white/10 p-8 rounded-[40px] backdrop-blur-md flex items-center gap-6">
                            <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0">
                                <TrendingDown :size="24" />
                            </div>
                            <p class="text-sm font-bold text-slate-300">Un equipo con 15 años consume hasta un 60% más que uno nuevo de clase A.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
