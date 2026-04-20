<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import * as Icons from 'lucide-vue-next';
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
    CheckCircle2
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
    { label: 'A', color: 'bg-energy-success', score: 95 },
];

const getCategoryColor = (label) => {
    return categories.find(c => c.label === label)?.color || 'bg-slate-400';
};
</script>

<template>
    <MainLayout>
        <Head title="Diagnóstico Finalizado" />

        <div class="max-w-5xl mx-auto pb-20">
            <!-- Back Button -->
            <Link :href="route('home')" class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-energy-consumption transition-colors mb-8">
                <ChevronLeft :size="14" stroke-width="3" />
                Volver al Inicio
            </Link>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Score Card (Left Column) -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-[40px] border border-slate-100 shadow-2xl shadow-slate-200/50 p-10 flex flex-col items-center text-center sticky top-8">
                        <div class="relative w-48 h-48 mb-8">
                            <!-- SVG Gauge -->
                            <svg class="w-full h-full transform -rotate-90">
                                <circle cx="96" cy="96" r="88" stroke="currentColor" stroke-width="14" fill="transparent" class="text-slate-50" />
                                <circle cx="96" cy="96" r="88" stroke="currentColor" stroke-width="14" fill="transparent" 
                                    :stroke-dasharray="2 * Math.PI * 88" 
                                    :stroke-dashoffset="2 * Math.PI * 88 * (1 - scoreResult.thermal_score / 100)"
                                    class="text-energy-warning transition-all duration-1000 ease-out stroke-round" 
                                />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-6xl font-black text-slate-900 tracking-tighter">{{ scoreResult.thermal_score }}</span>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Efficiency Score</span>
                            </div>
                        </div>

                        <div :class="['w-full py-4 rounded-2xl text-white font-black text-3xl shadow-lg mb-6', getCategoryColor(scoreResult.energy_label)]">
                            Categoría {{ scoreResult.energy_label }}
                        </div>

                        <p class="text-sm font-medium text-slate-500 leading-relaxed">
                            {{ entity.name }} tiene una envolvente con {{ scoreResult.thermal_score > 70 ? 'excelente' : 'media' }} capacidad de aislación térmica.
                        </p>

                        <!-- Ladder of categories -->
                        <div class="w-full mt-10 space-y-1">
                            <div v-for="cat in [...categories].reverse()" :key="cat.label" 
                                :class="['flex items-center justify-between p-2 rounded-lg transition-all', scoreResult.energy_label === cat.label ? 'bg-slate-900 scale-105 shadow-lg' : 'opacity-30']"
                            >
                                <span :class="['w-6 h-6 rounded flex items-center justify-center text-[10px] font-black text-white', cat.color]">{{ cat.label }}</span>
                                <span v-if="scoreResult.energy_label === cat.label" class="text-[9px] font-black text-white uppercase tracking-widest">Nivel Actual</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recommendations (Right Column) -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="space-y-4">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-energy-success/10 text-energy-success rounded-full text-[10px] font-black uppercase tracking-widest border border-energy-success/20">
                            <CheckCircle2 :size="14" />
                            Plan de Acción Personalizado
                        </div>
                        <h2 class="text-4xl font-black text-slate-900 tracking-tighter">Pasos para <span class="text-energy-success">ahorrar</span></h2>
                        <p class="text-lg text-slate-500 font-medium">Basado en el motor de diagnóstico, estas son las inversiones con mayor retorno para tu vivienda.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-6">
                        <div v-for="advice in recommendations" :key="advice.title" class="group bg-white p-8 rounded-[36px] border border-slate-100 shadow-xl shadow-slate-200/40 hover:shadow-2xl hover:shadow-blue-900/5 transition-all flex flex-col md:flex-row gap-6 relative overflow-hidden">
                            <!-- Decorative background -->
                            <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-slate-50 rounded-full blur-2xl group-hover:bg-slate-100 transition-colors"></div>

                            <div :class="['w-16 h-16 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-sm transition-transform group-hover:scale-110', {
                                'bg-energy-critical/10 text-energy-critical': advice.color === 'danger',
                                'bg-energy-warning/10 text-energy-warning': advice.color === 'warning',
                                'bg-energy-consumption/10 text-energy-consumption': advice.color === 'info',
                            }]">
                                <component :is="iconComponents[advice.icon] || Zap" :size="32" stroke-width="2.5" />
                            </div>

                            <div class="flex-1 space-y-4 relative z-10">
                                <div>
                                    <h3 class="text-2xl font-black text-slate-900 leading-tight mb-1">{{ advice.title }}</h3>
                                    <p class="text-slate-500 font-medium text-sm">{{ advice.problem }}</p>
                                </div>
                                
                                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1">Solución Recomendada</p>
                                    <p class="text-sm font-bold text-slate-700">{{ advice.solution }}</p>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div class="px-3 py-1 bg-slate-900 text-white rounded-lg text-[9px] font-black uppercase tracking-widest">Impacto {{ advice.impact }}</div>
                                    <div class="text-xs font-bold text-energy-consumption">{{ advice.cost_level }} Costo estimado</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="pt-12 text-center">
                        <Link :href="route('gestion.thermal.wizard', entity.id)" class="text-xs font-black text-slate-300 uppercase tracking-widest hover:text-energy-warning transition-colors border-b-2 border-transparent hover:border-energy-warning/30 pb-1">
                             ¿Hiciste refacciones? Volver a diagnosticar
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
</style>
