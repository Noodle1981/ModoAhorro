<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    Palmtree, 
    ArrowRight, 
    TrendingDown, 
    Zap, 
    ZapOff, 
    Calendar, 
    CheckCircle2, 
    ShieldCheck,
    Info,
    ArrowUpRight,
    Plane,
    Map
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    plan: Object
});

const vacationDays = ref(15);
const dailyStandbySaving = computed(() => props.plan.daily_standby_saving || 0);
const totalPotentialSaving = computed(() => dailyStandbySaving.value * vacationDays.value);

const formatMoney = (val) => {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(val);
};
</script>

<template>
    <MainLayout>
        <Head title="Modo Vacaciones" />

        <div class="max-w-7xl mx-auto space-y-12">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-sky-100 text-sky-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-sky-200">
                        <Palmtree :size="14" />
                        Plan de Ausencia
                    </div>
                    <h1 class="text-5xl font-black text-slate-900 tracking-tighter leading-none">
                        Modo <span class="text-sky-500">Vacaciones</span>
                    </h1>
                    <p class="text-lg text-slate-500 font-medium">Maximiza el ahorro desconectando lo que no necesitas mientras no estás.</p>
                </div>
            </div>

            <!-- Vacation Calculator Hero -->
            <div class="bg-slate-950 rounded-[64px] p-12 md:p-20 text-white overflow-hidden relative group">
                <div class="absolute top-0 right-1/4 w-96 h-96 bg-sky-500/20 rounded-full blur-[120px]"></div>
                <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 opacity-50"></div>

                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-20 items-center">
                    <div class="space-y-10">
                        <div class="space-y-6">
                            <h2 class="text-4xl font-black tracking-tight leading-none italic text-sky-400">¿Cuántos días estarás fuera?</h2>
                            <div class="flex items-center gap-8">
                                <input 
                                    v-model="vacationDays" 
                                    type="range" 
                                    min="1" 
                                    max="60" 
                                    class="flex-1 accent-sky-400 h-2 bg-white/10 rounded-full appearance-none cursor-pointer"
                                />
                                <div class="bg-white/10 px-8 py-4 rounded-[28px] border border-white/10">
                                    <span class="text-4xl font-black text-white">{{ vacationDays }}</span>
                                    <span class="text-xs font-bold text-slate-400 ml-2">DÍAS</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-10 bg-white/5 border border-white/10 rounded-[48px] backdrop-blur-md space-y-6">
                            <div class="flex items-center justify-between">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Ahorro Potencial Total</p>
                                <TrendingDown :size="20" class="text-energy-success" />
                            </div>
                            <div class="flex items-baseline gap-2">
                                <h4 class="text-6xl font-black text-white tracking-tighter">{{ formatMoney(totalPotentialSaving) }}</h4>
                                <span class="text-sm font-bold text-slate-400">ARS</span>
                            </div>
                            <p class="text-sm text-slate-400 font-medium">Equivale a reducir tu emisión de CO2 en **{{ (vacationDays * 0.8).toFixed(1) }}kg**.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6">
                        <div class="bg-indigo-500/20 border border-indigo-400/20 p-8 rounded-[40px] flex items-center gap-6">
                            <div class="w-14 h-14 bg-indigo-500/30 text-indigo-400 rounded-3xl flex items-center justify-center shrink-0">
                                <ZapOff :size="28" />
                            </div>
                            <div>
                                <h4 class="text-xl font-black text-white">Cero Standby</h4>
                                <p class="text-sm text-indigo-200/70 font-medium">Todos los equipos vampiro detectados serán desconectados del cálculo.</p>
                            </div>
                        </div>
                        <div class="bg-energy-solar/10 border border-energy-solar/20 p-8 rounded-[40px] flex items-center gap-6">
                            <div class="w-14 h-14 bg-energy-solar/20 text-energy-solar rounded-3xl flex items-center justify-center shrink-0">
                                <ShieldCheck :size="28" />
                            </div>
                            <div>
                                <h4 class="text-xl font-black text-white">Base Crítica</h4>
                                <p class="text-sm text-amber-200/70 font-medium">El sistema mantiene solo Heladeras y Alarmas (Tanque 1) funcionando.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vacation Checklist -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/30 p-10 md:p-16 space-y-12">
                    <div class="flex items-center justify-between">
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight text-center md:text-left">Checklist de Desconexión</h3>
                        <div class="hidden md:flex items-center gap-2 px-4 py-2 bg-slate-50 text-slate-400 rounded-xl text-[10px] font-black uppercase tracking-widest">
                            <CheckCircle2 :size="14" />
                            SEGURIDAD ANTE TODO
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div v-for="item in ['Desenchufar Modems y Routers', 'Apagar Termotanques Eléctricos', 'Desconectar TV y Consolas', 'Cerrar llave de paso de Gas', 'Vaciar Heladera (opcional)', 'Configurar Luces Smart']" :key="item" class="flex items-center gap-4 group cursor-pointer">
                            <div class="w-10 h-10 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-200 group-hover:bg-energy-success/10 group-hover:text-energy-success transition-all">
                                <div class="w-2 h-2 bg-current rounded-full"></div>
                            </div>
                            <span class="text-sm font-bold text-slate-600 group-hover:text-slate-900 transition-colors">{{ item }}</span>
                        </div>
                    </div>

                    <div class="p-8 bg-sky-50 rounded-[32px] border border-sky-100 flex items-center gap-8">
                        <div class="w-16 h-16 bg-white rounded-2xl shadow-lg flex items-center justify-center text-sky-500 shrink-0">
                            <Plane :size="32" />
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-lg font-black text-sky-900 tracking-tight">Reporte de Ausencia</h4>
                            <p class="text-sm text-sky-700 font-medium">Al volver, carga tu factura y el sistema la marcará automáticamente como periodo "Calibrado por Ausencia".</p>
                        </div>
                    </div>
                </div>

                <!-- Side Info -->
                <div class="bg-white rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/30 p-12 space-y-8 flex flex-col justify-between overflow-hidden relative group">
                    <div class="absolute -right-12 -top-12 w-48 h-48 bg-energy-solar/5 rounded-full group-hover:scale-110 transition-transform duration-1000"></div>
                    <div class="space-y-6">
                        <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center">
                            <Map :size="28" />
                        </div>
                        <h4 class="text-3xl font-black text-slate-900 tracking-tight leading-none">Seguro y Eficiente</h4>
                        <p class="text-slate-500 font-medium leading-relaxed italic">
                            "Si todos los hogares de la ciudad aplicaran el Modo Vacaciones este verano, ahorraríamos la energía de una central eléctrica completa durante 15 días."
                        </p>
                    </div>
                    <div class="space-y-4">
                        <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">Plan Sugerido por ModoAhorro</p>
                        <button class="w-full bg-slate-900 text-white py-5 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-sky-500 transition-all shadow-xl shadow-slate-200">
                            Imprimir Checklist 
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
