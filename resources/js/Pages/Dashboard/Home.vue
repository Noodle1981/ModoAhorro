<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { computed } from 'vue';
import { 
    ShieldCheck, 
    Zap, 
    ArrowRight,
    TrendingUp,
    Leaf,
    Activity,
    DollarSign,
    Info,
    ThermometerSnowflake,
    ZapOff,
    BarChart3,
    CheckCircle2,
    Lock,
    Globe,
    Thermometer,
    Sun,
    CloudSun,
    Wind,
    Cloud,
    CloudRain,
    CloudLightning,
    AlertCircle,
    Building
} from 'lucide-vue-next';

const props = defineProps({
    currentEntity: Object,
    currentWeather: Object,
    climateProfile: Object
});

const categories = [
    { label: 'G', color: 'bg-rose-600' },
    { label: 'F', color: 'bg-rose-400' },
    { label: 'E', color: 'bg-orange-500' },
    { label: 'D', color: 'bg-amber-400' },
    { label: 'C', color: 'bg-lime-500' },
    { label: 'B', color: 'bg-emerald-500' },
    { label: 'A', color: 'bg-emerald-600' },
];

const getCategoryColor = (label) => {
    return categories.find(c => c.label === label)?.color || 'bg-slate-300';
};

const hasProfile = props.currentEntity?.thermal_profile;
const profile = props.currentEntity?.thermal_profile || {};

// Weather Visuals
const weatherIcon = computed(() => {
    if (!props.currentWeather?.success) return Globe;
    const code = props.currentWeather.condition_code;
    if (code === 0) return Sun;
    if (code <= 3) return Cloud;
    if (code >= 51 && code <= 67) return CloudRain;
    if (code >= 95) return CloudLightning;
    return Sun;
});

const weatherDesc = computed(() => {
    if (!props.currentWeather?.success) return 'Sincronizando...';
    if (props.currentWeather.is_fallback) return 'Clima Estacional';
    const code = props.currentWeather.condition_code;
    if (code === 0) return 'Cielo Despejado';
    if (code <= 3) return 'Parcialmente Nublado';
    if (code >= 51 && code <= 67) return 'Lluvia / Llovizna';
    if (code >= 95) return 'Tormenta Eléctrica';
    return 'Luz Solar Activa';
});

const climateZoneColor = computed(() => {
    const zone = props.climateProfile?.climate_zone || '';
    if (zone.includes('I')) return 'bg-orange-100 text-orange-600 border-orange-200';
    if (zone.includes('II')) return 'bg-amber-100 text-amber-600 border-amber-200';
    if (zone.includes('III')) return 'bg-emerald-100 text-emerald-600 border-emerald-200';
    return 'bg-blue-100 text-blue-600 border-blue-200';
});
</script>

<template>
    <MainLayout>
        <Head title="Inicio" />
        
        <div class="max-w-6xl mx-auto space-y-4 pb-4">
            <!-- Hero / Welcome Section -->
            <div class="relative overflow-hidden group flex items-center justify-between">
                <div class="space-y-1 relative z-10">
                    <div class="inline-flex items-center gap-2 px-2 py-0.5 bg-emerald-600/10 text-emerald-600 rounded-full text-[9px] font-black uppercase tracking-widest border border-emerald-600/20">
                        <Activity :size="14" />
                        Motor de Eficiencia v3.1
                    </div>
                    
                    <h1 class="text-3xl font-black text-slate-900 tracking-tighter leading-none">
                        Resumen de <span class="text-emerald-600">{{ currentEntity?.name || 'su Entidad' }}</span>
                    </h1>
                </div>
                
                <p class="hidden md:block text-[10px] text-slate-400 font-bold max-w-xs text-right uppercase tracking-widest leading-tight">
                    Gemelo Digital centralizado.<br/>Optimizando consumo en tiempo real.
                </p>
            </div>

            <!-- Main Layout: Grid 12 for high density -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                
                <!-- Left Column: KPI & 3 Tanks (Col 1-8) -->
                <div class="lg:col-span-8 space-y-4">
                    <!-- KPI Cards row -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <!-- Efficiency -->
                        <div class="bg-white p-4 rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/10 flex flex-col items-center justify-center relative overflow-hidden group h-32">
                            <div v-if="hasProfile" class="h-full flex flex-col items-center justify-center gap-1">
                                <span class="text-3xl font-black text-slate-900 tracking-tighter">{{ profile.energy_label || '?' }}</span>
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none">Etiqueta</p>
                            </div>
                            <div v-else class="h-full flex flex-col items-center justify-center gap-2 text-slate-300">
                                <Lock :size="20" />
                                <p class="text-[8px] font-black uppercase tracking-widest">Bloqueado</p>
                            </div>
                            <div class="absolute -right-4 -top-4 w-12 h-12 bg-emerald-50 rounded-full blur-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        </div>

                        <!-- CO2 -->
                        <div class="bg-white p-4 rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/10 flex flex-col justify-center gap-1 h-32">
                            <Leaf :size="16" class="text-emerald-500 mb-2" />
                            <h4 class="text-2xl font-black text-slate-900 tracking-tighter">0.45 <span class="text-xs font-bold text-slate-400">kg</span></h4>
                            <span class="text-[8px] font-black text-emerald-500 uppercase tracking-widest">CO2 Reducido</span>
                        </div>

                        <!-- Cost -->
                        <div class="bg-slate-900 p-4 rounded-[32px] shadow-xl shadow-slate-400/20 flex flex-col justify-center gap-1 h-32 text-white">
                            <DollarSign :size="16" class="text-emerald-400 mb-2" />
                            <h4 class="text-2xl font-black tracking-tighter leading-none">$42.50</h4>
                            <span class="text-[8px] font-black text-emerald-400 uppercase tracking-widest">Diario Est.</span>
                        </div>

                        <!-- Energy -->
                        <div class="bg-white p-4 rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/10 flex flex-col justify-center gap-1 h-32">
                            <Zap :size="16" class="text-amber-500 mb-2" />
                            <h4 class="text-2xl font-black text-slate-900 tracking-tighter">185 <span class="text-xs font-bold text-slate-400">kWh</span></h4>
                            <span class="text-[8px] font-black text-amber-500 uppercase tracking-widest">Consumo Mes</span>
                        </div>
                    </div>

                    <!-- 3 Tanks Visualization -->
                    <div class="bg-white p-6 rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/20 relative overflow-hidden">
                        <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                            <div class="space-y-2 max-w-sm">
                                <h3 class="text-xl font-black text-slate-900 tracking-tighter">Desglose <span class="text-emerald-600">3 Tanques</span></h3>
                                <p class="text-[10px] text-slate-500 font-medium leading-relaxed">Puntajes de eficiencia basados en su inventario y clima.</p>
                                <div class="pt-2 flex items-center gap-4 text-[8px] font-black uppercase tracking-widest text-slate-300">
                                    <div class="flex items-center gap-1">
                                        <div class="w-2 h-2 bg-emerald-600 rounded-full"></div> Base
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <div class="w-2 h-2 bg-teal-400 rounded-full"></div> Clima
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <div class="w-2 h-2 bg-amber-500 rounded-full"></div> Hábitos
                                    </div>
                                </div>
                            </div>

                            <div class="flex-1 w-full grid grid-cols-3 gap-6 h-32 items-end px-4">
                                <div class="bg-slate-50 rounded-[20px] h-full relative p-1 border border-slate-100 flex flex-col justify-end">
                                    <div class="bg-emerald-600 w-full rounded-[16px] h-[48%]"></div>
                                </div>
                                <div class="bg-slate-50 rounded-[20px] h-full relative p-1 border border-slate-100 flex flex-col justify-end">
                                    <div class="bg-teal-400 w-full rounded-[16px] h-[32%]"></div>
                                </div>
                                <div class="bg-slate-50 rounded-[20px] h-full relative p-1 border border-slate-100 flex flex-col justify-end">
                                    <div class="bg-amber-500 w-full rounded-[16px] h-[20%]"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Bioclimatic context (Col 9-12) -->
                <div class="lg:col-span-4 space-y-4">
                    <!-- Real-time Weather Monitor -->
                    <div :class="['p-5 text-white rounded-[40px] shadow-lg relative overflow-hidden group transition-all', currentWeather?.is_fallback ? 'bg-slate-800' : 'bg-emerald-600']">
                        <div class="absolute right-0 top-0 opacity-10 translate-x-4 -translate-y-4">
                            <Globe :size="100" />
                        </div>
                        <div class="relative z-10 flex flex-col gap-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full bg-white animate-pulse"></div>
                                    <span class="text-[9px] font-black uppercase tracking-widest opacity-80">{{ currentWeather?.is_fallback ? 'Modo Offline' : 'En Sincronía' }}</span>
                                </div>
                                <span class="text-[9px] font-black uppercase opacity-60">Monitor</span>
                            </div>
                            
                            <div class="flex items-end justify-between">
                                <div class="flex flex-col">
                                    <span class="text-4xl font-black tracking-tighter leading-none">{{ currentWeather?.temp || '--' }}°</span>
                                    <span class="text-[9px] font-bold opacity-80 mt-1 flex items-center gap-1">
                                        <component :is="weatherIcon" :size="12" /> {{ weatherDesc }}
                                    </span>
                                </div>
                                <div class="flex flex-col items-end">
                                    <div class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center mb-1"><Wind :size="18" /></div>
                                    <span class="text-[10px] font-black">{{ currentWeather?.windspeed || '0' }} km/h</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Characteristic Bioclimatic Profile -->
                    <div v-if="climateProfile" class="p-6 bg-white rounded-[40px] border border-slate-100 shadow-2xl shadow-slate-200/20 space-y-5 relative overflow-hidden group">
                        <div v-if="climateProfile.is_fallback" class="absolute -top-4 -right-4 opacity-5 rotate-12 pointer-events-none">
                            <AlertCircle :size="80" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <div class="flex items-center justify-between">
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Perfil Bioclimático</h4>
                                <div :class="['px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-tighter border shadow-xs', climateZoneColor]">
                                    Zona {{ climateProfile.climate_zone.split(' ')[0] }}
                                </div>
                            </div>
                            <span class="text-[11px] font-black text-slate-900">{{ currentEntity?.locality?.name || 'Localidad' }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Temp Media Anual</p>
                                <div class="flex items-center gap-1.5">
                                    <Thermometer :size="14" class="text-emerald-600" />
                                    <span class="text-sm font-black text-slate-900 tracking-tighter">{{ climateProfile.avg_temperature }}°c</span>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">S. Invernal</p>
                                <div class="flex items-center gap-1.5">
                                    <CloudSun :size="14" class="text-blue-500" />
                                    <span class="text-sm font-black text-slate-900 tracking-tighter">HDD {{ Math.round(climateProfile.hdd || 0) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-50">
                            <div class="flex items-center gap-2">
                                <Sun :size="14" class="text-amber-500" />
                                <div>
                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Recurso Solar</p>
                                    <p class="text-[10px] font-bold text-slate-800">{{ climateProfile.avg_radiation }} kWh/m²</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Actions / Quick Links -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Link :href="route('gestion.thermal.index', currentEntity?.id)" class="bg-emerald-600 rounded-[32px] p-4 text-white flex items-center justify-between hover:bg-emerald-500 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                            <CheckCircle2 :size="20" />
                        </div>
                        <div class="text-left">
                            <p class="text-[10px] font-black uppercase tracking-widest opacity-70">Salud Térmica</p>
                            <p class="text-sm font-bold">Diagnosticar Vivienda</p>
                        </div>
                    </div>
                    <ArrowRight :size="16" class="transform group-hover:translate-x-1 transition-transform" />
                </Link>
                <Link :href="route('gestion.entity.edit')" class="bg-slate-900 rounded-[32px] p-4 text-white flex items-center justify-between hover:bg-slate-800 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                            <Building :size="20" />
                        </div>
                        <div class="text-left">
                            <p class="text-[10px] font-black uppercase tracking-widest opacity-70">Mi Casa</p>
                            <p class="text-sm font-bold">Configurar Perfil</p>
                        </div>
                    </div>
                    <ArrowRight :size="16" class="transform group-hover:translate-x-1 transition-transform" />
                </Link>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
