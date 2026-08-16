<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    Settings, 
    Database, 
    TrendingUp, 
    Zap, 
    ShieldCheck, 
    ArrowRight,
    Users,
    FileText
} from 'lucide-vue-next';

defineProps({
    stats: Object
});
</script>

<template>
    <Head title="Panel de Administración" />

    <MainLayout>
        <div class="max-w-6xl mx-auto space-y-6">

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all group">
                    <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-emerald-600 group-hover:text-white transition-all mb-6">
                        <Database :size="28" />
                    </div>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">Catálogo Maestro</p>
                    <h3 class="text-4xl font-black text-slate-900">{{ stats.total_types }} <span class="text-lg font-bold text-slate-400">Tipos</span></h3>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all group">
                    <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-amber-500 group-hover:text-white transition-all mb-6">
                        <TrendingUp :size="28" />
                    </div>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">Benchmarks</p>
                    <h3 class="text-4xl font-black text-slate-900">{{ stats.total_benchmarks }} <span class="text-lg font-bold text-slate-400">Referencias</span></h3>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all group">
                    <div class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-sky-500 group-hover:text-white transition-all mb-6">
                        <Zap :size="28" />
                    </div>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest mb-1">Coeficientes</p>
                    <h3 class="text-4xl font-black text-slate-900">{{ stats.total_coefficients }} <span class="text-lg font-bold text-slate-400">Niveles</span></h3>
                </div>
            </div>

            <!-- Management Sections -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Section: Catalogue -->
                <Link :href="route('sistema.catalogue')" class="group relative bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm hover:shadow-2xl transition-all flex flex-col justify-between overflow-hidden min-h-[400px]">
                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 mb-8">
                            <Settings :size="32" />
                        </div>
                        <h2 class="text-4xl font-black text-slate-900 mb-4 tracking-tighter">Catálogo Maestro</h2>
                        <p class="text-slate-500 text-lg leading-relaxed mb-8">
                            Edita las potencias nominales, penalidades térmicas y puntajes de determinismo para cada tipo de equipo.
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-emerald-600 font-black uppercase tracking-widest text-sm relative z-10">
                        Gestionar ahora <ArrowRight :size="18" class="group-hover:translate-x-2 transition-transform" />
                    </div>
                    <Settings :size="300" class="absolute -bottom-20 -right-20 text-slate-50 rotate-12 group-hover:rotate-45 transition-transform duration-700" />
                </Link>

                <!-- Section: Efficiency -->
                <Link :href="route('sistema.efficiency')" class="group relative bg-white p-10 rounded-[3rem] border border-slate-100 shadow-sm hover:shadow-2xl transition-all flex flex-col justify-between overflow-hidden min-h-[400px]">
                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 mb-8">
                            <Zap :size="32" />
                        </div>
                        <h2 class="text-4xl font-black text-slate-900 mb-4 tracking-tighter">Matriz Eficiencia</h2>
                        <p class="text-slate-500 text-lg leading-relaxed mb-8">
                            Configura los multiplicadores para las etiquetas A+++ a G. Define el impacto real de cada letra en el gasto.
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-amber-600 font-black uppercase tracking-widest text-sm relative z-10">
                        Configurar matriz <ArrowRight :size="18" class="group-hover:translate-x-2 transition-transform" />
                    </div>
                    <Zap :size="300" class="absolute -bottom-20 -right-20 text-slate-50 -rotate-12 group-hover:rotate-0 transition-transform duration-700" />
                </Link>
            </div>
        </div>
    </MainLayout>
</template>
