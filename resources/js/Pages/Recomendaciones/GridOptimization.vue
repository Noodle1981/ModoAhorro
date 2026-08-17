<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    Clock, 
    Zap, 
    TrendingDown, 
    ArrowRight, 
    Info, 
    Sun, 
    Play, 
    Timer, 
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

const themeColors = computed(() => {
    const type = props.entity?.type;
    if (type === 'comercio') {
        return {
            text: 'text-purple-600',
            textMuted: 'text-purple-400',
            textDark: 'text-purple-900',
            textMedium: 'text-purple-700',
            textLight: 'text-purple-100',
            bg: 'bg-purple-600',
            bg500: 'bg-purple-500',
            bgLight: 'bg-purple-50',
            borderLight: 'border-purple-100',
            borderMuted: 'border-purple-200',
            badgeBg: 'bg-purple-100',
            gradient: 'from-purple-600 to-indigo-700',
            hoverBg: 'hover:bg-purple-700',
            shadow: 'shadow-purple-500/20',
            buttonText: 'text-purple-600',
            buttonBgHover: 'hover:bg-purple-50'
        };
    }
    if (type === 'oficina') {
        return {
            text: 'text-blue-600',
            textMuted: 'text-blue-400',
            textDark: 'text-blue-900',
            textMedium: 'text-blue-700',
            textLight: 'text-blue-100',
            bg: 'bg-blue-600',
            bg500: 'bg-blue-500',
            bgLight: 'bg-blue-50',
            borderLight: 'border-blue-100',
            borderMuted: 'border-blue-200',
            badgeBg: 'bg-blue-100',
            gradient: 'from-blue-600 to-indigo-700',
            hoverBg: 'hover:bg-blue-700',
            shadow: 'shadow-blue-500/20',
            buttonText: 'text-blue-600',
            buttonBgHover: 'hover:bg-blue-50'
        };
    }
    // Default / hogar (Emerald theme)
    return {
        text: 'text-emerald-600',
        textMuted: 'text-emerald-400',
        textDark: 'text-emerald-900',
        textMedium: 'text-emerald-700',
        textLight: 'text-emerald-100',
        bg: 'bg-emerald-600',
        bg500: 'bg-emerald-500',
        bgLight: 'bg-emerald-50',
        borderLight: 'border-emerald-100',
        borderMuted: 'border-emerald-200',
        badgeBg: 'bg-emerald-100',
        gradient: 'from-emerald-600 to-teal-700',
        hoverBg: 'hover:bg-emerald-700',
        shadow: 'shadow-emerald-500/20',
        buttonText: 'text-emerald-600',
        buttonBgHover: 'hover:bg-emerald-50'
    };
});
</script>

<template>
    <MainLayout>
        <Head title="Optimización de Horarios" />

        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Action Toolbar -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm font-medium text-slate-500">
                    <span>Ajuste técnico para aprovechar tarifas diferenciales y evitar picos de carga.</span>
                </div>

                <div class="flex items-center gap-2.5 bg-slate-900 text-white px-5 py-2.5 rounded-2xl shadow-sm">
                    <Zap :size="16" class="text-energy-solar shadow-sm" />
                    <div class="text-left">
                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none mb-0.5">Tarifa Actual</p>
                        <p class="text-xs font-black leading-none">{{ selectedTariff }}</p>
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
                                <div class="w-3 h-3 rounded-full shadow-md" :class="themeColors.bg500"></div>
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pico</span>
                            </div>
                        </div>
                    </div>

                    <!-- Simplified Hourly Bar Chart -->
                    <div class="flex flex-1 items-end justify-between h-64 gap-2">
                        <div v-for="item in hourlyData" :key="item.hour" class="flex-1 group relative">
                            <div 
                                class="w-full rounded-t-xl transition-all duration-500 hover:scale-x-110"
                                :class="[item.peak ? [themeColors.bg500, 'shadow-lg', themeColors.shadow] : 'bg-slate-100 group-hover:bg-slate-200']"
                                :style="{ height: `${item.level}%` }"
                            >
                                <div v-if="item.peak" class="absolute -top-10 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity bg-slate-900 text-white text-[9px] font-black py-1 px-2 rounded-lg whitespace-nowrap z-10">
                                    HORA PICO
                                </div>
                            </div>
                            <p class="text-center text-[10px] font-black text-slate-400 mt-4">{{ item.hour }}h</p>
                        </div>
                    </div>

                    <div class="rounded-3xl p-6 sm:p-8 flex items-center gap-6 border" :class="[themeColors.bgLight, themeColors.borderLight]">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center shrink-0" :class="[themeColors.badgeBg, themeColors.text]">
                            <TrendingDown :size="28" />
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-lg font-black tracking-tight" :class="themeColors.textDark">Oportunidad de Cambio</h4>
                            <p class="text-sm font-medium leading-relaxed" :class="themeColors.textMedium">
                                Aprovechá la **Tarifa Valle (18:00 a 06:00 y fines de semana)**: trasladar el uso de electrodomésticos de alta demanda a este horario te permite ahorrar hasta un **15% adicional** en tu factura.
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
                                <p class="text-[8px] font-black uppercase mb-1" :class="themeColors.textMuted">Sugerido</p>
                                <p class="text-sm font-black leading-none" :class="themeColors.text">{{ rec.suggested }}</p>
                            </div>
                        </div>

                        <button class="w-full bg-slate-50 text-slate-400 font-black text-[10px] uppercase tracking-widest py-3 rounded-xl hover:bg-slate-900 hover:text-white transition-all cursor-pointer">
                            Programar Aviso
                        </button>
                    </div>

                    <!-- Upsell to Smart Meter -->
                    <div class="rounded-[40px] p-8 text-white relative overflow-hidden" :class="themeColors.gradient">
                        <ZapOff :size="80" class="absolute -right-4 -bottom-4 text-white/10 rotate-12" />
                        <div class="relative z-10 space-y-4">
                            <h4 class="text-xl font-black leading-tight">¿Quieres automatizar esto?</h4>
                            <p class="text-xs font-medium leading-relaxed" :class="themeColors.textLight">Integra un medidor inteligente para que tus dispositivos se activen solos en horas valle.</p>
                            <button class="bg-white px-6 py-3 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-colors cursor-pointer" :class="[themeColors.buttonText, themeColors.buttonBgHover]">
                                Consultar Integraciones
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
