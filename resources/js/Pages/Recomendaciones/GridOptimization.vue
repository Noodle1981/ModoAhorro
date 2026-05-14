<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    Clock, 
    Zap, 
    TrendingDown, 
    Calendar, 
    ArrowRight, 
    Info, 
    Sun, 
    Moon,
    Battery,
    Play,
    Timer,
    AlertTriangle,
    ZapOff
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    contract: Object,
    recommendations: Array
});

const iconMap = {
    Clock, Sun, Timer, Zap, Play
};

const hourlyData = ref([
    { hour: '00', level: 20, peak: false },
    { hour: '02', level: 15, peak: false },
    { hour: '04', level: 15, peak: false },
    { hour: '06', level: 30, peak: false },
    { hour: '08', level: 60, peak: true },
    { hour: '10', level: 45, peak: false },
    { hour: '12', level: 50, peak: false },
    { hour: '14', level: 40, peak: false },
    { hour: '16', level: 35, peak: false },
    { hour: '18', level: 80, peak: true },
    { hour: '20', level: 90, peak: true },
    { hour: '22', level: 40, peak: false },
]);

// Recommendations are now coming from props
const selectedTariff = ref(props.contract?.supply_type === 'trifasico' ? 'T1-G (Trifásica)' : 'T1-R (Simple)');
</script>

<template>
    <MainLayout>
        <Head title="Optimización de Horarios" />

        <div class="max-w-7xl mx-auto space-y-12">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-100 text-indigo-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-indigo-200">
                        <Timer :size="14" />
                        Smart Scheduling
                    </div>
                    <h1 class="text-5xl font-black text-slate-900 tracking-tighter leading-none">
                        Optimización <span class="text-indigo-600">de Horarios</span>
                    </h1>
                    <p class="text-lg text-slate-500 font-medium">Ajuste técnico para aprovechar tarifas diferenciales y evitar picos de carga.</p>
                </div>

                <div class="flex items-center gap-2 bg-slate-900 text-white px-6 py-4 rounded-3xl shadow-xl shadow-slate-200">
                    <Zap :size="18" class="text-energy-solar shadow-sm" />
                    <div class="text-left">
                        <p class="text-[9px] font-black text-slate-500 uppercase tracking-widest leading-none mb-1">Tarifa Actual</p>
                        <p class="text-sm font-black leading-none">{{ selectedTariff }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Schedule Chart -->
                <div class="lg:col-span-2 bg-white rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/30 p-10 flex flex-col space-y-10">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-black text-slate-900 tracking-tight">Curva de Carga Estimada (Día Típico)</h3>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-slate-200"></div>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Base</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-indigo-500 shadow-md"></div>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pico</span>
                            </div>
                        </div>
                    </div>

                    <!-- Simplified Hourly Bar Chart -->
                    <div class="flex flex-1 items-end justify-between h-64 gap-2">
                        <div v-for="item in hourlyData" :key="item.hour" class="flex-1 group relative">
                            <div 
                                class="w-full rounded-t-xl transition-all duration-500 hover:scale-x-110"
                                :class="[item.peak ? 'bg-indigo-500 shadow-lg shadow-indigo-100' : 'bg-slate-100 group-hover:bg-slate-200']"
                                :style="{ height: `${item.level}%` }"
                            >
                                <div v-if="item.peak" class="absolute -top-10 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-slate-900 text-white text-[9px] font-black py-1 px-2 rounded-lg whitespace-nowrap z-10">
                                    HORA PICO
                                </div>
                            </div>
                            <p class="text-center text-[10px] font-black text-slate-400 mt-4">{{ item.hour }}h</p>
                        </div>
                    </div>

                    <div class="bg-indigo-50 rounded-3xl p-8 flex items-center gap-8 border border-indigo-100">
                        <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center text-indigo-600 shrink-0">
                            <TrendingDown :size="28" />
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-lg font-black text-indigo-900 tracking-tight">Oportunidad de Cambio</h4>
                            <p class="text-sm text-indigo-700 font-medium leading-relaxed">
                                Si mueves el uso de la **Bomba de Calor** de las 18h a las 02h, podrías calificar para una Tarifa Trihoraria y ahorrar un **15% adicional**.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Recommendations Sidebar -->
                <div class="space-y-6">
                    <h3 class="text-[10px] font-black text-slate-300 uppercase tracking-widest ml-4">Acciones de Desplazamiento</h3>
                    
                    <div v-for="rec in recommendations" :key="rec.title" class="bg-white rounded-[40px] border border-slate-100 shadow-xl shadow-slate-200/30 p-8 space-y-6 group hover:shadow-2xl transition-all">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div :class="['w-12 h-12 rounded-2xl flex items-center justify-center', rec.bg, rec.color]">
                                    <component :is="iconMap[rec.icon] || Info" :size="24" />
                                </div>
                                <h4 class="text-lg font-black text-slate-900 leading-none">{{ rec.title }}</h4>
                            </div>
                            <div class="bg-energy-success/10 text-energy-success px-2 py-1 rounded-lg text-[10px] font-black">
                                {{ rec.saving }}
                            </div>
                        </div>

                        <div class="flex items-center justify-between bg-slate-50 p-4 rounded-2xl">
                            <div class="text-center flex-1">
                                <p class="text-[8px] font-black text-slate-400 uppercase mb-1">Actual</p>
                                <p class="text-xs font-black text-slate-500 leading-none">{{ rec.current }}</p>
                            </div>
                            <ArrowRight :size="14" class="text-slate-300" />
                            <div class="text-center flex-1">
                                <p class="text-[8px] font-black text-indigo-400 uppercase mb-1">Sugerido</p>
                                <p class="text-sm font-black text-indigo-600 leading-none">{{ rec.suggested }}</p>
                            </div>
                        </div>

                        <button class="w-full bg-slate-50 text-slate-400 font-black text-[10px] uppercase tracking-widest py-3 rounded-xl hover:bg-slate-900 hover:text-white transition-all">
                            Programar Aviso
                        </button>
                    </div>

                    <!-- Upsell to Smart Meter -->
                    <div class="bg-gradient-to-br from-indigo-600 to-violet-700 rounded-[40px] p-8 text-white relative overflow-hidden">
                        <ZapOff :size="80" class="absolute -right-4 -bottom-4 text-white/10 rotate-12" />
                        <div class="relative z-10 space-y-4">
                            <h4 class="text-xl font-black leading-tight">¿Quieres automatizar esto?</h4>
                            <p class="text-xs text-indigo-100 font-medium leading-relaxed">Integra un medidor inteligente para que tus dispositivos se activen solos en horas valle.</p>
                            <button class="bg-white text-indigo-600 px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-50 transition-colors">
                                Consultar Integraciones
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Caution area -->
            <div class="flex items-center gap-8 bg-slate-50 border border-slate-100 rounded-[40px] p-10">
                <div class="w-16 h-16 rounded-2xl bg-white shadow-lg flex items-center justify-center text-rose-500 shrink-0">
                    <AlertTriangle :size="32" />
                </div>
                <div class="space-y-1">
                    <h5 class="text-lg font-black text-slate-900 tracking-tight leading-none mb-1">Sobre las Tarifas Diferenciales</h5>
                    <p class="text-sm text-slate-500 font-medium leading-relaxed">
                        Tenga en cuenta que el cambio a una tarifa bihoraria o trihoraria requiere un análisis de al menos 3 meses de consumo estable. No recomendamos el cambio si su consumo nocturno es inferior al 30% del total.
                    </p>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
