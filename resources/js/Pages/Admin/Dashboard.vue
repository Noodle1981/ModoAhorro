<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    Users, 
    Layers, 
    Award, 
    Zap, 
    Sparkles,
    Tag,
    ArrowRight
} from 'lucide-vue-next';

defineProps({
    stats: Object
});
</script>

<template>
    <Head title="Panel de Administración" />

    <MainLayout>
        <div class="max-w-7xl mx-auto space-y-8">

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-5">
                <div class="bg-white p-5 sm:p-6 rounded-[2rem] border border-slate-100 shadow-xs hover:shadow-lg transition-all group">
                    <div class="w-11 h-11 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-purple-600 group-hover:text-white transition-all mb-4">
                        <Users :size="22" />
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Usuarios</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900">{{ stats.total_users || 0 }} <span class="text-xs font-bold text-slate-400">Cuentas</span></h3>
                </div>

                <div class="bg-white p-5 sm:p-6 rounded-[2rem] border border-slate-100 shadow-xs hover:shadow-lg transition-all group">
                    <div class="w-11 h-11 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-emerald-600 group-hover:text-white transition-all mb-4">
                        <Layers :size="22" />
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Catálogo Maestro</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900">{{ stats.total_types }} <span class="text-xs font-bold text-slate-400">Tipos</span></h3>
                </div>

                <div class="bg-white p-5 sm:p-6 rounded-[2rem] border border-slate-100 shadow-xs hover:shadow-lg transition-all group">
                    <div class="w-11 h-11 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 group-hover:text-white transition-all mb-4">
                        <Tag :size="22" />
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Modelos Oficiales</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900">{{ stats.total_models || 0 }} <span class="text-xs font-bold text-slate-400">Modelos</span></h3>
                </div>

                <div class="bg-white p-5 sm:p-6 rounded-[2rem] border border-slate-100 shadow-xs hover:shadow-lg transition-all group">
                    <div class="w-11 h-11 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-amber-500 group-hover:text-white transition-all mb-4">
                        <Award :size="22" />
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Benchmarks</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900">{{ stats.total_benchmarks }} <span class="text-xs font-bold text-slate-400">Refs</span></h3>
                </div>

                <div class="bg-white p-5 sm:p-6 rounded-[2rem] border border-slate-100 shadow-xs hover:shadow-lg transition-all group col-span-2 sm:col-span-1">
                    <div class="w-11 h-11 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-sky-500 group-hover:text-white transition-all mb-4">
                        <Zap :size="22" />
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Coeficientes</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900">{{ stats.total_coefficients }} <span class="text-xs font-bold text-slate-400">Niveles</span></h3>
                </div>
            </div>

            <!-- Management Sections -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Section: Users Management -->
                <Link :href="route('sistema.users')" class="group relative bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xs hover:shadow-2xl transition-all flex flex-col justify-between overflow-hidden min-h-[320px]">
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-purple-50 rounded-2xl flex items-center justify-center text-purple-600 mb-6">
                            <Users :size="28" />
                        </div>
                        <h2 class="text-2xl font-black text-slate-900 mb-3 tracking-tight">Gestión de Usuarios</h2>
                        <p class="text-slate-500 text-sm leading-relaxed mb-6">
                            Administrá las cuentas de la plataforma, roles de Super Admin y credenciales de acceso.
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-purple-600 font-black uppercase tracking-widest text-xs relative z-10">
                        Gestionar usuarios <ArrowRight :size="16" class="group-hover:translate-x-2 transition-transform" />
                    </div>
                </Link>

                <!-- Section: Catalogue -->
                <Link :href="route('sistema.catalogue')" class="group relative bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xs hover:shadow-2xl transition-all flex flex-col justify-between overflow-hidden min-h-[320px]">
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 mb-6">
                            <Layers :size="28" />
                        </div>
                        <h2 class="text-2xl font-black text-slate-900 mb-3 tracking-tight">Catálogo Maestro</h2>
                        <p class="text-slate-500 text-sm leading-relaxed mb-6">
                            Edita las potencias nominales, penalidades térmicas y puntajes de determinismo para cada tipo de equipo.
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-emerald-600 font-black uppercase tracking-widest text-xs relative z-10">
                        Gestionar catálogo <ArrowRight :size="16" class="group-hover:translate-x-2 transition-transform" />
                    </div>
                </Link>

                <!-- Section: Models & Community Intelligence -->
                <Link :href="route('sistema.models')" class="group relative bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xs hover:shadow-2xl transition-all flex flex-col justify-between overflow-hidden min-h-[320px]">
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 mb-6">
                            <Sparkles :size="28" />
                        </div>
                        <h2 class="text-2xl font-black text-slate-900 mb-3 tracking-tight">Modelos & Inteligencia</h2>
                        <p class="text-slate-500 text-sm leading-relaxed mb-6">
                            Bandeja de aportes de la comunidad y catálogo de electrodomésticos oficiales para autocompletar.
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-indigo-600 font-black uppercase tracking-widest text-xs relative z-10">
                        Revisar aportes <ArrowRight :size="16" class="group-hover:translate-x-2 transition-transform" />
                    </div>
                </Link>

                <!-- Section: Efficiency -->
                <Link :href="route('sistema.efficiency')" class="group relative bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xs hover:shadow-2xl transition-all flex flex-col justify-between overflow-hidden min-h-[320px]">
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-sky-50 rounded-2xl flex items-center justify-center text-sky-600 mb-6">
                            <Zap :size="28" />
                        </div>
                        <h2 class="text-2xl font-black text-slate-900 mb-3 tracking-tight">Matriz Eficiencia</h2>
                        <p class="text-slate-500 text-sm leading-relaxed mb-6">
                            Configura los multiplicadores para las etiquetas A+++ a G. Define el impacto real de cada letra en el gasto.
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-sky-600 font-black uppercase tracking-widest text-xs relative z-10">
                        Configurar matriz <ArrowRight :size="16" class="group-hover:translate-x-2 transition-transform" />
                    </div>
                </Link>

                <!-- Section: Benchmarks -->
                <Link :href="route('sistema.benchmarks')" class="group relative bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xs hover:shadow-2xl transition-all flex flex-col justify-between overflow-hidden min-h-[320px]">
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-600 mb-6">
                            <Award :size="28" />
                        </div>
                        <h2 class="text-2xl font-black text-slate-900 mb-3 tracking-tight">Benchmarks & ROI</h2>
                        <p class="text-slate-500 text-sm leading-relaxed mb-6">
                            Modelos estándar de referencia y precios de mercado para simular y justificar reemplazos eficientes.
                        </p>
                    </div>
                    <div class="flex items-center gap-2 text-amber-600 font-black uppercase tracking-widest text-xs relative z-10">
                        Gestionar benchmarks <ArrowRight :size="16" class="group-hover:translate-x-2 transition-transform" />
                    </div>
                </Link>
            </div>
        </div>
    </MainLayout>
</template>

