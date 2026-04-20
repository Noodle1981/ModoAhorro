<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    Thermometer, 
    ChevronLeft, 
    TrendingDown,
    Zap,
    Paintbrush,
    Sun,
    Wind,
    Maximize,
    AlertTriangle,
    CheckCircle2,
    Calendar,
    ArrowRight
} from 'lucide-vue-next';

// Map icon strings to components
const iconComponents = {
    Wind,
    Sun,
    Paintbrush,
    Maximize,
    AlertTriangle,
    Zap
};

const props = defineProps({
    entity: Object,
    profile: Object,
    scoreResult: Object,
    recommendations: Array,
    config: Object,
});

const categories = [
    { label: 'G', color: 'bg-rose-600', score: 0 },
    { label: 'F', color: 'bg-rose-400', score: 20 },
    { label: 'E', color: 'bg-orange-500', score: 40 },
    { label: 'D', color: 'bg-amber-400', score: 60 },
    { label: 'C', color: 'bg-lime-500', score: 75 },
    { label: 'B', color: 'bg-emerald-500', score: 85 },
    { label: 'A', color: 'bg-emerald-600', score: 95 },
];

const getCategoryColor = (label) => {
    return categories.find(c => c.label === label)?.color || 'bg-slate-400';
};
</script>

<template>
    <MainLayout>
        <Head title="Diagnóstico Térmico" />

        <div class="h-full flex flex-col gap-4">
            <!-- Header Section (Compact) -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('dashboard')" class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                        <ChevronLeft :size="20" stroke-width="3" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-3xl font-black text-slate-900 tracking-tighter">Resultado <span class="text-emerald-600">Térmico</span></h1>
                            <div class="px-2 py-0.5 bg-emerald-100 text-emerald-600 rounded-full text-[9px] font-black uppercase tracking-widest border border-emerald-200">
                                Diagnóstico Finalizado
                            </div>
                        </div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Entidad: {{ entity.name }}</p>
                    </div>
                </div>
            </div>

            <!-- Main Grid Layout (Scroll-Free optimized) -->
            <div class="flex-1 grid grid-cols-1 lg:grid-cols-4 gap-4 min-h-0">
                
                <!-- Left Column: Performance Gauge & Label (Compact) -->
                <div class="lg:col-span-1 flex flex-col gap-4 min-h-0">
                    <!-- Gauge Card -->
                    <div class="bg-white rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/20 p-4 flex flex-col items-center justify-center relative overflow-hidden group shrink-0">
                        <div class="absolute -right-10 -bottom-10 w-24 h-24 bg-slate-50 rounded-full blur-2xl group-hover:bg-emerald-50 transition-colors"></div>
                        
                        <div class="relative w-36 h-36 mb-2">
                            <svg class="w-full h-full transform -rotate-90">
                                <circle cx="72" cy="72" r="64" stroke="currentColor" stroke-width="10" fill="transparent" class="text-slate-50" />
                                <circle cx="72" cy="72" r="64" stroke="currentColor" stroke-width="12" fill="transparent" 
                                    :stroke-dasharray="2 * Math.PI * 64" 
                                    :stroke-dashoffset="2 * Math.PI * 64 * (1 - scoreResult.thermal_score / 100)"
                                    class="text-emerald-600 transition-all duration-1000 ease-out stroke-round drop-shadow-[0_0_8px_rgba(16,185,129,0.3)]" 
                                />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-4xl font-black text-slate-900 tracking-tighter leading-none">{{ scoreResult.thermal_score }}</span>
                                <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest mt-1">Eficiencia</span>
                            </div>
                        </div>

                        <div :class="['w-full py-3 rounded-2xl text-white font-black text-xl text-center shadow-md transition-all', getCategoryColor(scoreResult.energy_label)]">
                            Categoría {{ scoreResult.energy_label }}
                        </div>
                    </div>

                    <!-- Category Ladder (Refined & Clear) -->
                    <div class="bg-white rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/20 p-5 flex flex-col flex-1 min-h-0 overflow-y-auto scrollbar-hide">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest text-center mb-4">Referencia de Eficiencia</p>
                        <div class="flex flex-col gap-1">
                            <div v-for="cat in [...categories].reverse()" :key="cat.label" 
                                :class="[
                                    'relative flex items-center gap-3 p-1 rounded-xl transition-all',
                                    scoreResult.energy_label === cat.label ? 'bg-slate-50 ring-1 ring-slate-100 shadow-sm' : 'opacity-40 grayscale-[0.5]'
                                ]"
                            >
                                <!-- The Color Bar -->
                                <div :class="['h-6 rounded-lg flex items-center px-3 text-[10px] font-black text-white shadow-sm transition-all', cat.color, scoreResult.energy_label === cat.label ? 'w-24 shadow-md' : 'w-16']">
                                    {{ cat.label }}
                                </div>
                                
                                <!-- Indicator for current level -->
                                <div v-if="scoreResult.energy_label === cat.label" class="flex flex-col">
                                    <span class="text-[9px] font-black text-emerald-600 uppercase tracking-tighter leading-none">Nivel Actual</span>
                                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-tighter leading-none italic">Su entidad</span>
                                </div>

                                <!-- Subtle checkmark for active -->
                                <div v-if="scoreResult.energy_label === cat.label" class="absolute -right-1 top-1/2 -translate-y-1/2 w-6 h-6 bg-emerald-600 text-white rounded-full flex items-center justify-center shadow-lg border-2 border-white">
                                    <CheckCircle2 :size="12" stroke-width="3" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Recommendations Grid (Denser) -->
                <div class="lg:col-span-3 flex flex-col gap-4 overflow-hidden">
                    <div class="bg-white rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/20 p-6 flex-1 flex flex-col min-h-0 overflow-hidden">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-xl font-black text-slate-900 tracking-tight">Acciones Prioritarias</h2>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Impacto en Factura Estimado</p>
                            </div>
                            <div class="flex items-center gap-2 px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[9px] font-black uppercase tracking-widest border border-emerald-100">
                                <CheckCircle2 :size="12" /> Plan Personalizado
                            </div>
                        </div>

                        <!-- Dense Grid of Recommendations -->
                        <div class="flex-1 overflow-y-auto pr-2 grid grid-cols-1 md:grid-cols-2 gap-3 scrollbar-hide">
                            <div v-for="advice in recommendations" :key="advice.title" class="group bg-slate-50/50 p-4 rounded-2xl border border-slate-100 hover:border-emerald-200 hover:bg-white transition-all flex gap-3 relative overflow-hidden">
                                <div :class="['w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm transition-transform group-hover:scale-110', {
                                    'bg-rose-100 text-rose-600': advice.color === 'danger',
                                    'bg-amber-100 text-amber-600': advice.color === 'warning',
                                    'bg-emerald-100 text-emerald-600': advice.color === 'info',
                                }]">
                                    <component :is="iconComponents[advice.icon] || Zap" :size="20" stroke-width="2.5" />
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-black text-slate-900 leading-tight mb-0.5 truncate">{{ advice.title }}</h3>
                                    <p class="text-[10px] font-bold text-slate-400 mb-2 truncate uppercase tracking-tighter">{{ advice.problem }}</p>
                                    
                                    <div class="bg-white/50 p-2.5 rounded-lg border border-slate-100/50 mb-2 group-hover:bg-emerald-50/30">
                                        <p class="text-[11px] font-bold text-slate-700 leading-tight">{{ advice.solution }}</p>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <div class="px-1.5 py-0.5 bg-slate-900 text-[8px] text-white rounded font-black uppercase tracking-widest">Impacto {{ advice.impact }}</div>
                                        <div class="text-[9px] font-bold text-emerald-600">{{ advice.cost_level }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Quick Action -->
                    <div class="bg-emerald-600 rounded-[24px] p-4 flex items-center justify-between text-white shadow-lg shadow-emerald-900/20 group hover:bg-emerald-500 transition-all cursor-pointer">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                                <TrendingDown :size="20" stroke-width="3" />
                            </div>
                            <div>
                                <p class="text-xs font-black uppercase tracking-widest">Ahorro Estimado Anual</p>
                                <p class="text-lg font-black leading-none">$125.400 <span class="text-[10px] font-medium opacity-70">Sujeto a implementación</span></p>
                            </div>
                        </div>
                        <Link :href="route('gestion.thermal.wizard', entity.id)" class="px-4 py-2 bg-white text-emerald-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:scale-105 transition-transform flex items-center gap-2">
                            Re-Diagnosticar <ArrowRight :size="12" stroke-width="3" />
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
.stroke-round {
    stroke-linecap: round;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
