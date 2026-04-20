<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    Sun, 
    Zap, 
    ArrowRight, 
    Waves, 
    Thermometer, 
    TrendingDown, 
    Info, 
    LayoutGrid, 
    Building2,
    CheckCircle2,
    Calendar,
    ArrowUpRight,
    Droplets
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    photovoltaic: Object,
    thermal: Object
});

const activeTab = ref('panels');

const solarScore = computed(() => {
    return Math.round((props.photovoltaic.coverage_summer + props.photovoltaic.coverage_winter) / 2);
});

// Formatting money
const formatMoney = (val) => {
    return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(val);
};
</script>

<template>
    <MainLayout>
        <Head title="Proyecto Solar" />

        <div class="max-w-7xl mx-auto space-y-12">
            <!-- Hero Section -->
            <div class="relative bg-slate-950 rounded-[64px] p-12 md:p-20 text-white overflow-hidden group">
                <!-- Background decor -->
                <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-energy-solar/20 rounded-full blur-[120px] -translate-y-1/2 translate-x-1/3 group-hover:bg-energy-solar/30 transition-all duration-1000"></div>
                <div class="absolute bottom-0 left-0 w-[300px] h-[300px] bg-sky-500/10 rounded-full blur-[80px] translate-y-1/2 -translate-x-1/4"></div>

                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <div class="space-y-8">
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-energy-solar text-slate-900 rounded-full text-xs font-black uppercase tracking-widest shadow-xl shadow-energy-solar/20">
                            <Sun :size="16" />
                            Independencia Energética
                        </div>
                        <h1 class="text-6xl md:text-7xl font-black tracking-tighter leading-[0.9]">
                            Energía <span class="text-energy-solar italic">Solar</span>
                        </h1>
                        <p class="text-xl text-slate-400 font-medium leading-relaxed max-w-lg">
                            Diseñamos tu planta fotovoltaica y térmica basada en tu consumo real de {{ entity.name }}.
                        </p>
                        <div class="flex flex-wrap gap-4 pt-4">
                            <div class="bg-white/5 border border-white/10 px-6 py-4 rounded-3xl backdrop-blur-sm">
                                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Ahorro Anual</p>
                                <p class="text-2xl font-black text-energy-success">~ 45%</p>
                            </div>
                            <div class="bg-white/5 border border-white/10 px-6 py-4 rounded-3xl backdrop-blur-sm">
                                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Score Solar</p>
                                <p class="text-2xl font-black text-energy-solar">{{ solarScore }}/100</p>
                            </div>
                        </div>
                    </div>

                    <!-- Visual KPI -->
                    <div class="bg-white/5 border border-white/10 rounded-[48px] p-10 backdrop-blur-md space-y-10">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-black uppercase tracking-tight">Estatus del Proyecto</h3>
                            <div class="w-12 h-12 rounded-2xl bg-energy-solar/20 text-energy-solar flex items-center justify-center">
                                <Zap :size="24" />
                            </div>
                        </div>
                        
                        <div class="space-y-6">
                            <div class="space-y-3">
                                <div class="flex justify-between text-xs font-black uppercase tracking-widest text-slate-400">
                                    <span>Cobertura Verano</span>
                                    <span class="text-white">{{ photovoltaic.coverage_summer }}%</span>
                                </div>
                                <div class="h-4 bg-white/5 rounded-full overflow-hidden">
                                    <div class="h-full bg-energy-solar shadow-[0_0_20px_rgba(245,158,11,0.5)] transition-all duration-1000" :style="{ width: `${photovoltaic.coverage_summer}%` }"></div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="flex justify-between text-xs font-black uppercase tracking-widest text-slate-400">
                                    <span>Cobertura Invierno</span>
                                    <span class="text-white">{{ photovoltaic.coverage_winter }}%</span>
                                </div>
                                <div class="h-4 bg-white/5 rounded-full overflow-hidden">
                                    <div class="h-full bg-sky-400 shadow-[0_0_20px_rgba(56,189,248,0.5)] transition-all duration-1000" :style="{ width: `${photovoltaic.coverage_winter}%` }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Section -->
            <div class="space-y-8">
                <div class="flex items-center justify-center gap-4">
                    <button 
                        @click="activeTab = 'panels'"
                        :class="['px-10 py-5 rounded-[32px] font-black text-sm uppercase tracking-widest transition-all', activeTab === 'panels' ? 'bg-slate-900 text-white shadow-2xl' : 'bg-white text-slate-400 hover:bg-slate-50']"
                    >
                        Paneles (Eléctrico)
                    </button>
                    <button 
                        @click="activeTab = 'thermal'"
                        :class="['px-10 py-5 rounded-[32px] font-black text-sm uppercase tracking-widest transition-all', activeTab === 'thermal' ? 'bg-slate-900 text-white shadow-2xl' : 'bg-white text-slate-400 hover:bg-slate-50']"
                    >
                        Termotanque (Agua)
                    </button>
                </div>

                <!-- Photovoltaic Tab -->
                <div v-if="activeTab === 'panels'" class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-in fade-in slide-in-from-bottom-4">
                    <div class="lg:col-span-2 bg-white rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/40 p-12 space-y-12">
                        <div class="flex items-center justify-between">
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight">Dimensión del Sistema</h3>
                            <LayoutGrid :size="24" class="text-slate-200" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="space-y-2">
                                <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Potencia Instalada</p>
                                <p class="text-4xl font-black text-slate-900 tracking-tighter">{{ photovoltaic.system_size_kwp }}<span class="text-lg text-slate-400 ml-1">KwP</span></p>
                            </div>
                            <div class="space-y-2">
                                <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Cantidad de Paneles</p>
                                <p class="text-4xl font-black text-slate-900 tracking-tighter">{{ photovoltaic.panels_count }}<span class="text-sm text-slate-400 ml-2">x 550W</span></p>
                            </div>
                            <div class="space-y-2">
                                <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Generación Mensual</p>
                                <p class="text-4xl font-black text-energy-solar tracking-tighter">{{ Math.round(photovoltaic.monthly_generation_kwh) }}<span class="text-lg text-slate-400 ml-1">kWh</span></p>
                            </div>
                        </div>

                        <div class="p-8 bg-slate-50 rounded-[32px] flex items-center gap-8">
                            <div class="w-16 h-16 bg-white rounded-2xl shadow-lg flex items-center justify-center text-energy-solar">
                                <Building2 :size="32" />
                            </div>
                            <div class="space-y-1">
                                <h4 class="text-lg font-black text-slate-900 tracking-tight">Superficie Necesaria: {{ photovoltaic.area_used }}m²</h4>
                                <p class="text-sm text-slate-500 font-medium">Basado en paneles Tier 1 de alta eficiencia monocristalinos.</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-energy-success text-white rounded-[48px] p-12 space-y-8 shadow-2xl shadow-energy-success/20 relative overflow-hidden group">
                        <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-1000"></div>
                        <h3 class="text-2xl font-black tracking-tight leading-none">Estimación de Inversión</h3>
                        <div class="space-y-2">
                            <p class="text-[10px] font-black text-white/60 uppercase tracking-widest">Oportunidad de Mercado</p>
                            <p class="text-5xl font-black tracking-tighter">USD {{ Math.round(photovoltaic.investment_estimate) }}</p>
                        </div>
                        <p class="text-sm text-energy-success-800 font-medium leading-relaxed bg-white/20 p-6 rounded-3xl">
                            Incluye Inversor On-Grid, Estructura de aluminio, Protecciones y Mano de Obra certificada. 
                            **Payback: ~ 4.2 años.**
                        </p>
                        <button class="w-full bg-slate-950 text-white py-5 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-energy-solar transition-all">
                            Solicitar Cotización 
                        </button>
                    </div>
                </div>

                <!-- Thermal Tab -->
                <div v-if="activeTab === 'thermal'" class="grid grid-cols-1 lg:grid-cols-2 gap-8 animate-in fade-in slide-in-from-bottom-4">
                    <div class="bg-white rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/40 p-12 space-y-10">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-sky-50 text-sky-500 rounded-2xl flex items-center justify-center">
                                <Waves :size="28" />
                            </div>
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight">Termotanque Solar</h3>
                        </div>
                        
                        <div v-if="thermal.is_recommended" class="space-y-8">
                            <div class="p-8 bg-sky-50 border border-sky-100 rounded-[32px] space-y-4">
                                <h4 class="text-lg font-black text-sky-900">Altamente Recomendado</h4>
                                <p class="text-sm text-sky-700 font-medium leading-relaxed">
                                    Debido a tu historial de consumo de climatización y agua sanitaria, un termotanque solar de **{{ thermal.size_liters }} Litros** cubriría el 80% de tu demanda anual de agua caliente.
                                </p>
                            </div>
                            <div class="grid grid-cols-2 gap-8">
                                <div class="space-y-1">
                                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Ahorro en Gas/Elec</p>
                                    <p class="text-3xl font-black text-slate-900">~ 75%<span class="text-sm text-slate-400 italic font-bold"> en ACS</span></p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Tecnología Sugerida</p>
                                    <p class="text-3xl font-black text-slate-900">Heat-Pipe</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="p-20 text-center space-y-4 border-2 border-dashed border-slate-100 rounded-[40px]">
                            <Droplets :size="40" class="text-slate-100 mx-auto" />
                            <h4 class="text-xl font-black text-slate-300">Análisis no determinante</h4>
                            <p class="text-sm text-slate-400">No hay suficientes datos de consumo de agua caliente para generar una recomendación precisa.</p>
                        </div>
                    </div>

                    <div class="bg-indigo-900 text-white rounded-[48px] p-12 space-y-10 shadow-2xl shadow-indigo-900/20 relative">
                        <div class="space-y-4">
                            <h3 class="text-2xl font-black tracking-tight">Beneficios de Reducción</h3>
                            <p class="text-indigo-200 font-medium">Al calentar el agua con el sol, reduces drásticamente el uso de tu termotanque eléctrico, extendiendo su vida útil por 10 años más.</p>
                        </div>

                        <div class="space-y-4">
                            <div v-for="benefit in ['Sin mantenimiento anual', 'Estructura antigranizo', 'Agua a 60°C en invierno', 'Garantía de 10 años']" :key="benefit" class="flex items-center gap-4">
                                <CheckCircle2 :size="18" class="text-energy-solar" />
                                <span class="text-sm font-bold">{{ benefit }}</span>
                            </div>
                        </div>

                        <div class="pt-10 flex items-center justify-between border-t border-white/10">
                            <div>
                                <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest mb-1">Costo Estimado</p>
                                <p class="text-3xl font-black tracking-tighter">USD 750</p>
                            </div>
                            <button class="bg-white text-indigo-900 px-8 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest">
                                Ver Catálogo
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
