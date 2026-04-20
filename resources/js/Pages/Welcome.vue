<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { 
    Zap, 
    BarChart3, 
    CreditCard, 
    Globe, 
    ChevronRight,
    PlayCircle,
    ShieldCheck
} from 'lucide-vue-next';

defineProps({
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>
    <Head title="Toma el control de tu energía" />

    <div class="min-h-screen bg-white font-sans text-slate-900 selection:bg-energy-consumption/10 selection:text-energy-consumption">
        <!-- Public Navbar -->
        <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-slate-100">
            <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-linear-to-br from-energy-consumption to-blue-700 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-200">
                        <Zap :size="22" stroke-width="3" />
                    </div>
                    <span class="text-xl font-black tracking-tight">Modo<span class="text-energy-consumption">Ahorro</span></span>
                </div>

                <div class="flex items-center gap-6">
                    <Link 
                        v-if="$page.props.auth.user" 
                        :href="route('dashboard')" 
                        class="text-sm font-black uppercase tracking-widest text-slate-900 hover:text-energy-consumption transition-colors"
                    >
                        Ir a Entidades
                    </Link>
                    <template v-else>
                        <Link 
                            :href="route('login')" 
                            class="px-6 py-3 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-energy-consumption transition-all shadow-xl shadow-slate-200 hover:-translate-y-0.5"
                        >
                            Acceso Cliente
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <header class="relative overflow-hidden pt-20 pb-32 lg:pt-32">
            <div class="max-w-7xl mx-auto px-6 lg:grid lg:grid-cols-2 lg:gap-16 items-center">
                <div class="relative z-10 space-y-10">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-energy-success/5 text-energy-success rounded-full border border-energy-success/10 text-[11px] font-black uppercase tracking-widest">
                        <Globe :size="14" />
                        Software de Eficiencia Energética v3.0
                    </div>

                    <h1 class="text-6xl lg:text-7xl font-black tracking-tighter leading-[0.9] text-slate-900">
                        Desmitifica <br>
                        tu consumo <br>
                        <span class="text-energy-consumption">eléctrico.</span>
                    </h1>

                    <p class="text-xl text-slate-500 leading-relaxed max-w-xl font-medium">
                        ModoAhorro transforma tus datos de energía en ahorros reales. Gestiona facturas, optimiza equipos y reduce tu impacto ambiental.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 pt-4">
                        <Link :href="route('login')" class="group flex items-center justify-center px-8 py-5 bg-energy-consumption text-white rounded-3xl font-black text-sm uppercase tracking-widest hover:bg-blue-700 transition-all shadow-2xl shadow-blue-200 hover:-translate-y-1">
                            Ver Demo Gratuita
                            <ChevronRight :size="18" class="ml-2 group-hover:translate-x-1 transition-transform" />
                        </Link>
                        <a href="#features" class="flex items-center justify-center px-8 py-5 bg-slate-50 text-slate-900 rounded-3xl font-black text-sm uppercase tracking-widest hover:bg-slate-100 transition-all border border-slate-200/50">
                            Explorar Funciones
                        </a>
                    </div>

                    <div class="flex items-center gap-4 py-8 border-t border-slate-100">
                        <div class="flex -space-x-3">
                            <div v-for="i in 4" :key="i" class="w-10 h-10 rounded-full border-2 border-white bg-slate-100 ring-2 ring-slate-50 flex items-center justify-center overflow-hidden">
                                <img :src="`https://i.pravatar.cc/100?u=${i}`" alt="user" class="w-full h-full object-cover grayscale opacity-80" />
                            </div>
                        </div>
                        <p class="text-xs font-bold text-slate-400">
                            Únete a <span class="text-slate-900 font-black">+2,500 hogares</span> ahorrando hoy.
                        </p>
                    </div>
                </div>

                <div class="mt-20 lg:mt-0 relative">
                    <div class="absolute -top-24 -right-24 w-96 h-96 bg-energy-consumption/10 rounded-full blur-[100px]"></div>
                    <div class="relative bg-slate-900 rounded-[48px] p-4 shadow-3xl shadow-blue-900/20 transform lg:rotate-3 hover:rotate-0 transition-transform duration-700">
                        <div class="bg-white rounded-[36px] overflow-hidden aspect-square flex items-center justify-center p-8 border border-white/10">
                            <!-- Visual placeholder for App UI -->
                            <div class="space-y-6 w-full">
                                <div class="h-8 w-2/3 bg-slate-100 rounded-full"></div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="h-32 bg-energy-consumption/5 rounded-3xl p-4 space-y-4">
                                        <div class="h-6 w-1/2 bg-energy-consumption/10 rounded-lg"></div>
                                        <div class="h-10 w-full bg-energy-consumption/20 rounded-xl"></div>
                                    </div>
                                    <div class="h-32 bg-energy-success/5 rounded-3xl p-4 space-y-4">
                                        <div class="h-6 w-1/2 bg-energy-success/10 rounded-lg"></div>
                                        <div class="h-10 w-full bg-energy-success/20 rounded-xl"></div>
                                    </div>
                                </div>
                                <div class="h-32 bg-slate-50 rounded-3xl border border-slate-100 flex items-center justify-center italic text-slate-300 font-bold">
                                    Dashboard v3 Interactive Preview
                                </div>
                            </div>
                        </div>
                        <div class="absolute -bottom-8 -left-8 bg-white p-6 rounded-3xl shadow-2xl border border-slate-100 max-w-[200px]">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="p-2 bg-energy-success text-white rounded-lg">
                                    <ShieldCheck :size="16" />
                                </div>
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Status</span>
                            </div>
                            <p class="text-sm font-black text-slate-900 leading-tight tracking-tight">Optimización al 98% alcanzada.</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Features Quick Grid -->
        <section id="features" class="py-32 bg-slate-50/50">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center space-y-4 mb-20">
                    <h2 class="text-sm font-black text-energy-consumption uppercase tracking-[0.3em]">Eficiencia en 3 Pasos</h2>
                    <p class="text-4xl font-black text-slate-900 tracking-tight">Potencia tus ahorros de inmediato.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Step 1 -->
                    <div class="p-10 bg-white rounded-[40px] border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-2 group">
                        <div class="w-16 h-16 bg-blue-50 text-energy-consumption rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 transition-transform duration-500 shadow-sm">
                            <BarChart3 :size="32" stroke-width="2.5" />
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">Monitoreo de Consumo</h3>
                        <p class="text-slate-500 font-medium leading-relaxed">
                            Visualiza gráficos detallados de tu consumo eléctrico y detecta patrones ocultos automáticamente.
                        </p>
                    </div>

                    <!-- Step 2 -->
                    <div class="p-10 bg-white rounded-[40px] border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-2 group">
                        <div class="w-16 h-16 bg-emerald-50 text-energy-success rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 transition-transform duration-500 shadow-sm">
                            <CreditCard :size="32" stroke-width="2.5" />
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">Gestión de Facturas</h3>
                        <p class="text-slate-500 font-medium leading-relaxed">
                            Sincroniza tus facturas y compáralas mes a mes para evitar sorpresas y optimizar tu tarifa actual.
                        </p>
                    </div>

                    <!-- Step 3 -->
                    <div class="p-10 bg-white rounded-[40px] border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-2 group">
                        <div class="w-16 h-16 bg-amber-50 text-energy-warning rounded-2xl flex items-center justify-center mb-8 group-hover:scale-110 transition-transform duration-500 shadow-sm">
                            <Zap :size="32" stroke-width="2.5" />
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 mb-4 tracking-tight">Motor de Eficiencia</h3>
                        <p class="text-slate-500 font-medium leading-relaxed">
                            Recibe recomendaciones precisas basadas en IA para modernizar equipos y reducir fugas de energía.
                        </p>
                    </div>
                </div>
            </div>
        </section>

    </div>
</template>
