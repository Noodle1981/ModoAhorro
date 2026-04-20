<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    Thermometer, 
    ThermometerSnowflake, 
    Home, 
    ShieldCheck, 
    ArrowRight, 
    AlertCircle, 
    Wind, 
    CheckCircle2,
    Activity,
    Info,
    LayoutGrid,
    TrendingDown,
    Map,
    AirVent, 
    Layers,
    Waves
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    profile: Object
});

const getScoreColor = (score) => {
    if (score >= 80) return 'text-energy-success';
    if (score >= 50) return 'text-energy-solar';
    return 'text-energy-critical';
};

const getInertiaLabel = (level) => {
    const labels = {
        'A': 'Excelente (Alta Inercia)',
        'B': 'Muy Buena',
        'C': 'Regular',
        'D': 'Deficiente',
        'E': 'Crítica (Pérdidas Masivas)'
    };
    return labels[level] || 'No diagnosticado';
};
</script>

<template>
    <MainLayout>
        <Head title="Salud Térmica" />

        <div class="max-w-7xl mx-auto space-y-12">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-sky-100 text-sky-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-sky-200">
                        <Thermometer :size="14" />
                        Diagnóstico de Envolvente
                    </div>
                    <h1 class="text-5xl font-black text-slate-900 tracking-tighter leading-none">
                        Salud <span class="text-sky-500">Térmica</span>
                    </h1>
                    <p class="text-lg text-slate-500 font-medium">Análisis de la capacidad de tu propiedad para retener temperatura y ahorrar en climatización.</p>
                </div>
            </div>

            <!-- Profile Summary Card -->
            <div class="bg-slate-950 rounded-[64px] p-12 md:p-20 text-white overflow-hidden relative group">
                <div class="absolute top-0 right-0 w-96 h-96 bg-sky-500/10 rounded-full blur-[100px]"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-rose-500/5 rounded-full blur-[80px]"></div>

                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <div class="space-y-10">
                        <div class="space-y-4">
                            <h2 class="text-4xl font-black tracking-tight leading-none">Perfil de Aislamiento</h2>
                            <p class="text-slate-400 font-medium text-lg leading-relaxed">
                                Tu propiedad se comporta como una **Etiqueta {{ profile.thermal_inertia_level || 'C' }}**. Esto determina cuánta energía se "escapa" por paredes, techos y ventanas.
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-8">
                            <div class="bg-white/5 border border-white/10 p-6 rounded-[32px] backdrop-blur-md">
                                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Puntaje Térmico</p>
                                <div class="flex items-baseline gap-2">
                                    <h4 :class="['text-4xl font-black tracking-tighter', getScoreColor(profile.score)]">{{ profile.score }}</h4>
                                    <span class="text-xs font-bold text-slate-500">/ 100</span>
                                </div>
                            </div>
                            <div class="bg-white/5 border border-white/10 p-6 rounded-[32px] backdrop-blur-md">
                                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Inercia Térmica</p>
                                <h4 class="text-2xl font-black text-white tracking-tight">{{ profile.thermal_inertia_level || 'C' }}</h4>
                            </div>
                        </div>

                        <Link v-if="!profile.is_completed" :href="route('gestion.thermal.wizard', entity.id)" class="inline-flex items-center gap-3 bg-white text-slate-950 px-10 py-5 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-energy-solar hover:text-white transition-all shadow-2xl">
                            Refinar Diagnóstico
                            <ArrowRight :size="16" />
                        </Link>
                    </div>

                    <!-- Visual Comparison / Heat Map Placeholder -->
                    <div class="bg-white/5 border border-white/10 rounded-[48px] p-10 backdrop-blur-md flex flex-col items-center gap-10">
                        <div class="relative w-48 h-48">
                            <div class="absolute inset-0 border-[16px] border-white/5 rounded-full"></div>
                            <div class="absolute inset-0 border-[16px] border-energy-solar rounded-full border-t-transparent border-l-transparent rotate-45"></div>
                            <div class="absolute inset-0 flex flex-col items-center justify-center space-y-1">
                                <ThermometerSnowflake :size="48" class="text-sky-400" />
                                <span class="text-[10px] font-black text-slate-500 uppercase">Eficiencia</span>
                            </div>
                        </div>
                        <div class="w-full space-y-4">
                            <div class="flex justify-between items-center text-xs font-bold">
                                <span class="text-slate-400">Pérdida por Ventanas</span>
                                <div class="w-32 h-2 bg-white/5 rounded-full overflow-hidden">
                                    <div class="h-full bg-rose-500" style="width: 65%"></div>
                                </div>
                            </div>
                            <div class="flex justify-between items-center text-xs font-bold">
                                <span class="text-slate-400">Aislación en Techos</span>
                                <div class="w-32 h-2 bg-white/5 rounded-full overflow-hidden">
                                    <div class="h-full bg-energy-success" style="width: 80%"></div>
                                </div>
                            </div>
                            <div class="flex justify-between items-center text-xs font-bold">
                                <span class="text-slate-400">Exposición Solar</span>
                                <div class="w-32 h-2 bg-white/5 rounded-full overflow-hidden">
                                    <div class="h-full bg-energy-solar" style="width: 40%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recommendations Grid -->
            <div class="space-y-8">
                <div class="flex items-center justify-between px-4">
                    <h3 class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Plan de Mejora Térmica</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Windows -->
                    <div class="bg-white rounded-[40px] border border-slate-100 shadow-xl shadow-slate-200/30 p-8 space-y-8 group hover:shadow-2xl transition-all">
                        <div class="w-14 h-14 bg-sky-50 text-sky-500 rounded-2xl flex items-center justify-center group-hover:bg-sky-500 group-hover:text-white transition-colors">
                            <Layers :size="28" />
                        </div>
                        <div class="space-y-2">
                            <h4 class="text-xl font-black text-slate-900 tracking-tight">Doble Vidriado (DVH)</h4>
                            <p class="text-sm text-slate-400 font-medium leading-relaxed">
                                Reducirás las filtraciones de aire un **40%**, lo que impacta directamente en menos horas de uso del aire acondicionado.
                            </p>
                        </div>
                        <div class="pt-4 border-t border-slate-50 flex items-center justify-between">
                            <div class="space-y-0.5">
                                <p class="text-[9px] font-black text-slate-300 uppercase">Impacto en Factura</p>
                                <p class="text-lg font-black text-energy-success">-18%</p>
                            </div>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Prioridad ALTA</span>
                        </div>
                    </div>

                    <!-- Curtains -->
                    <div class="bg-white rounded-[40px] border border-slate-100 shadow-xl shadow-slate-200/30 p-8 space-y-8 group hover:shadow-2xl transition-all">
                        <div class="w-14 h-14 bg-energy-solar/5 text-energy-solar rounded-2xl flex items-center justify-center group-hover:bg-energy-solar group-hover:text-white transition-colors">
                            <Waves :size="28" />
                        </div>
                        <div class="space-y-2">
                            <h4 class="text-xl font-black text-slate-900 tracking-tight">Cortinas BlackOut</h4>
                            <p class="text-sm text-slate-400 font-medium leading-relaxed">
                                Bloquea la radiación infrarroja en verano y retiene el calor en invierno. Una mejora de bajo costo con gran efecto.
                            </p>
                        </div>
                        <div class="pt-4 border-t border-slate-50 flex items-center justify-between">
                            <div class="space-y-0.5">
                                <p class="text-[9px] font-black text-slate-300 uppercase">Impacto en Factura</p>
                                <p class="text-lg font-black text-energy-success">-5%</p>
                            </div>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Low Cost</span>
                        </div>
                    </div>

                    <!-- Air Leaks -->
                    <div class="bg-white rounded-[40px] border border-slate-100 shadow-xl shadow-slate-200/30 p-8 space-y-8 group hover:shadow-2xl transition-all">
                        <div class="w-14 h-14 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center group-hover:bg-rose-500 group-hover:text-white transition-colors">
                            <AirVent :size="28" />
                        </div>
                        <div class="space-y-2">
                            <h4 class="text-xl font-black text-slate-900 tracking-tight">Sellado de Fisuras</h4>
                            <p class="text-sm text-slate-400 font-medium leading-relaxed">
                                Hemos detectado puentes térmicos en aberturas. El uso de burletes y poliuretano puede eliminar corrientes parásitas.
                            </p>
                        </div>
                        <div class="pt-4 border-t border-slate-50 flex items-center justify-between">
                            <div class="space-y-0.5">
                                <p class="text-[9px] font-black text-slate-300 uppercase">Impacto en Factura</p>
                                <p class="text-lg font-black text-energy-success">-12%</p>
                            </div>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Inmediato</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Quote / Science -->
            <div class="bg-white border border-slate-100 rounded-[48px] p-12 flex items-center gap-12 overflow-hidden relative">
                <div class="absolute right-0 top-0 w-32 h-32 bg-slate-50 rotate-45 translate-x-16 -translate-y-16"></div>
                <div class="w-16 h-16 bg-slate-900 rounded-2xl flex items-center justify-center text-white shrink-0 shadow-xl shadow-slate-200">
                    <Info :size="32" />
                </div>
                <div class="space-y-2">
                    <h5 class="text-xl font-black text-slate-900 tracking-tight">¿Sabías qué?</h5>
                    <p class="text-slate-500 font-medium leading-relaxed max-w-3xl">
                        Un hogar eficientemente aislado puede llegar a requerir hasta un **70% menos de energía** para mantener una temperatura confortable de 24°C que uno con pérdidas masivas, independientemente de qué tan modernos sean tus aires acondicionados.
                    </p>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
