<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    ShieldCheck, 
    Zap, 
    ArrowRight,
    TrendingUp,
    Leaf,
    Activity
} from 'lucide-vue-next';

defineProps({
    currentEntity: Object
});
</script>

<template>
    <MainLayout>
        <Head title="Inicio" />
        
        <div class="max-w-4xl">
            <!-- Hero / Welcome -->
            <div class="mb-16">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-energy-consumption/10 text-energy-consumption rounded-full text-xs font-black uppercase tracking-widest mb-6">
                    <TrendingUp :size="14" />
                    Gestión de Entidades v2.0
                </div>
                
                <h1 class="text-5xl font-black text-slate-900 tracking-tight mb-6">
                    Resumen de <br>
                    <span class="text-energy-consumption">{{ currentEntity?.name || 'su Entidad' }}</span>
                </h1>
                
                <p class="text-xl text-slate-500 leading-relaxed max-w-2xl">
                    Utilice el motor de eficiencia de ModoAhorro para desmitificar su consumo, 
                    optimizar su factura y reducir su impacto ambiental en este espacio.
                </p>
            </div>

            <!-- Dashboard Overview / Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Feature 1: Diagnóstico -->
                <Link :href="route('thermal.wizard', currentEntity?.id)" class="group p-8 bg-white border border-slate-100 rounded-3xl shadow-sm hover:shadow-xl hover:shadow-blue-900/5 transition-all text-left relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-energy-warning/10 text-energy-warning rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-500">
                            <ShieldCheck :size="28" stroke-width="2.5" />
                        </div>
                        <h3 class="font-black text-slate-900 text-2xl tracking-tight mb-2">Diagnóstico Térmico</h3>
                        <p class="text-slate-500 font-medium text-sm leading-relaxed mb-6">
                            Analice la envolvente de su vivienda y reciba recomendaciones estratégicas de inversión.
                        </p>
                        <div class="flex items-center gap-2 text-energy-consumption font-black text-xs uppercase tracking-widest group-hover:gap-4 transition-all">
                            Iniciar Análisis <ArrowRight :size="16" stroke-width="3" />
                        </div>
                    </div>
                    
                    <!-- Decorative element -->
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-energy-warning/5 rounded-full blur-2xl group-hover:bg-energy-warning/10 transition-colors"></div>
                </Link>

                <!-- Feature 2: Consumo (Próximamente) -->
                <div class="group p-8 bg-white/50 border border-slate-100/50 rounded-3xl shadow-sm opacity-60 grayscale border-dashed text-left">
                    <div class="w-14 h-14 bg-energy-success/10 text-energy-success rounded-2xl flex items-center justify-center mb-6">
                        <Zap :size="28" stroke-width="2.5" />
                    </div>
                    <h3 class="font-black text-slate-900 text-2xl tracking-tight mb-2">Análisis de Consumo</h3>
                    <p class="text-slate-500 font-medium text-sm leading-relaxed mb-6">
                        Sincronice sus facturas y reciba alertas preventivas sobre anomalías de consumo.
                    </p>
                    <div class="flex items-center gap-2 text-slate-400 font-black text-xs uppercase tracking-widest">
                        Próximamente
                    </div>
                </div>
            </div>

            <!-- Core Stats / KPIs -->
            <div class="mt-12 grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="p-6 bg-white border border-slate-100 rounded-3xl">
                    <div class="flex items-center gap-3 text-energy-success mb-2">
                        <Leaf :size="18" />
                        <span class="text-[10px] font-black uppercase tracking-widest">Huella CO2</span>
                    </div>
                    <div class="text-3xl font-black text-slate-900 tracking-tight">0,45 <span class="text-sm text-slate-400">kg</span></div>
                </div>
                
                <div class="p-6 bg-white border border-slate-100 rounded-3xl">
                    <div class="flex items-center gap-3 text-energy-consumption mb-2">
                        <Zap :size="18" />
                        <span class="text-[10px] font-black uppercase tracking-widest">Costo Est.</span>
                    </div>
                    <div class="text-3xl font-black text-slate-900 tracking-tight">$42,50</div>
                </div>

                <div class="p-6 bg-white border border-slate-100 rounded-3xl">
                    <div class="flex items-center gap-3 text-energy-warning mb-2">
                        <Activity :size="18" />
                        <span class="text-[10px] font-black uppercase tracking-widest">Eficiencia</span>
                    </div>
                    <div class="text-3xl font-black text-slate-900 tracking-tight">B+</div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
