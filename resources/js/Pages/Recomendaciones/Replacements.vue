<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    RefreshCcw, 
    ArrowRight, 
    TrendingDown, 
    Zap, 
    ShoppingCart, 
    Timer, 
    ChevronRight,
    ArrowUpRight,
    Award,
    History,
    Search,
    Filter
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    opportunities: Array
});

const formatMoney = (val) => {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(val);
};
</script>

<template>
    <MainLayout>
        <Head title="Reemplazos Eficientes" />

        <div class="max-w-7xl mx-auto space-y-12">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-energy-success/10 text-energy-success rounded-full text-[10px] font-black uppercase tracking-widest border border-energy-success/20">
                        <RefreshCcw :size="14" />
                        Plan de Renovación
                    </div>
                    <h1 class="text-5xl font-black text-slate-900 tracking-tighter leading-none">
                        Reemplazos <span class="text-energy-success">Eficientes</span>
                    </h1>
                    <p class="text-lg text-slate-500 font-medium">Equipos que generan un retorno de inversión real por su ahorro energético.</p>
                </div>

                <div class="flex flex-wrap gap-4">
                    <div class="bg-white px-6 py-4 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/40 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-energy-success/5 flex items-center justify-center text-energy-success">
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
                    class="bg-white rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/30 overflow-hidden group hover:shadow-energy-success/10 transition-all duration-500"
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
                            <div class="bg-energy-success/5 p-6 rounded-[32px] space-y-2 relative overflow-hidden">
                                <ArrowUpRight :size="40" class="absolute -right-4 -bottom-4 text-energy-success/10" />
                                <p class="text-[10px] font-black text-energy-success/60 uppercase tracking-widest">Ahorro Estimado</p>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-2xl font-black text-energy-success">{{ opportunity.potential_savings_kwh }}</span>
                                    <span class="text-xs font-bold text-energy-success/40">kWh/mes</span>
                                </div>
                            </div>
                        </div>

                        <!-- ROI Panel -->
                        <div class="p-8 bg-slate-900 rounded-[32px] text-white flex items-center justify-between group-hover:bg-energy-success transition-all duration-700">
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
                        <a :href="opportunity.affiliate_link" target="_blank" class="flex items-center gap-2 text-xs font-black text-slate-900 group-hover:text-energy-success transition-colors">
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
                    <Link :href="route('gestion.infrastructure')" class="text-xs font-black text-energy-solar uppercase tracking-widest border-b-2 border-energy-solar pb-1 mt-4">
                        Actualizar Inventario
                    </Link>
                </div>
            </div>

            <!-- ROI Education -->
            <div class="bg-indigo-950 rounded-[64px] p-12 md:p-20 text-white relative overflow-hidden group">
                <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-indigo-500/10 rounded-full blur-[100px]"></div>
                <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-16 items-center">
                    <div class="space-y-8">
                        <div class="w-16 h-16 bg-white/10 rounded-[28px] flex items-center justify-center text-energy-solar">
                            <Award :size="32" />
                        </div>
                        <h3 class="text-4xl font-black tracking-tighter leading-tight">La ley del retorno <br/> energéticamente hablando.</h3>
                        <p class="text-lg text-slate-400 font-medium leading-relaxed">
                            No todos los equipos eficientes se pagan solos. Nuestra lógica de "Payback" cruza el precio de mercado local con tu tarifa de electricidad actual para decirte si el cambio es realmente una inversión o solo un gasto.
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-6">
                        <div class="bg-white/5 border border-white/10 p-8 rounded-[40px] backdrop-blur-md flex items-center gap-6">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
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
