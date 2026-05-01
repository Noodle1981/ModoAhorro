<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    Sun, Zap, Waves, Info, LayoutGrid, Users, Maximize2, TrendingDown, CheckCircle2, ChevronRight, ThermometerSun
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    photovoltaic: Object,
    thermal: Object,
    filters: Object
});

const activeTab = ref('panels');
const thermalFuelTab = ref('electric');

const localArea = ref(props.filters.available_area);
const localPeople = ref(props.filters.people_count);

let updateTimeout;
const updateFilters = () => {
    clearTimeout(updateTimeout);
    updateTimeout = setTimeout(() => {
        router.get(route('recomendaciones.solar'), {
            available_area: localArea.value,
            people_count: localPeople.value
        }, { preserveScroll: true, preserveState: true, only: ['photovoltaic', 'thermal', 'filters'] });
    }, 400);
};

const formatMoney = (val) => new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 }).format(val);
const waterData = computed(() => props.thermal.waterHeaterData);
</script>

<template>
    <MainLayout>
        <Head title="Solar" />

        <div class="h-[calc(100vh-100px)] flex flex-col gap-3 overflow-hidden text-slate-900 px-2 pb-2">
            <!-- Ultra Thin Header -->
            <div class="bg-slate-900 text-white rounded-3xl p-4 flex items-center justify-between shadow-2xl">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-energy-solar rounded-2xl flex items-center justify-center text-slate-900 shadow-lg shadow-energy-solar/20">
                        <Sun :size="20" stroke-width="3" />
                    </div>
                    <div>
                        <h1 class="text-xl font-black tracking-tighter leading-none">Proyecto <span class="text-energy-solar italic">Solar</span></h1>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">{{ entity.name }}</p>
                    </div>
                </div>

                <div class="hidden lg:flex items-center gap-8 bg-white/5 px-6 py-2 rounded-2xl border border-white/10">
                    <div class="space-y-0.5">
                        <p class="text-[8px] font-black text-slate-500 uppercase tracking-widest">Área Techo</p>
                        <div class="flex items-center gap-3">
                            <input type="range" v-model="localArea" min="4" max="200" step="2" @input="updateFilters" class="w-24 h-1 bg-white/10 rounded-full appearance-none accent-energy-solar">
                            <span class="text-xs font-black text-energy-solar">{{ localArea }}m²</span>
                        </div>
                    </div>
                    <div class="w-px h-6 bg-white/10"></div>
                    <div class="space-y-0.5">
                        <p class="text-[8px] font-black text-slate-500 uppercase tracking-widest">Habitantes</p>
                        <div class="flex items-center gap-3">
                            <input type="range" v-model="localPeople" min="1" max="12" step="1" @input="updateFilters" class="w-24 h-1 bg-white/10 rounded-full appearance-none accent-energy-solar">
                            <span class="text-xs font-black text-energy-solar">{{ localPeople }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-[8px] font-black text-slate-500 uppercase tracking-widest">Ahorro Estimado</p>
                        <p class="text-lg font-black text-energy-success leading-none">~ 55%</p>
                    </div>
                    <div class="w-10 h-10 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-energy-solar">
                        <Zap :size="18" />
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-3 min-h-0">
                <!-- Photovoltaic Card (Left) -->
                <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-100 shadow-sm flex flex-col overflow-hidden">
                    <div class="p-4 border-b border-slate-50 flex items-center justify-between bg-slate-50/50">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 flex items-center gap-2">
                            <LayoutGrid :size="14" /> Generación Fotovoltaica
                        </h3>
                        <span class="text-[10px] font-bold text-slate-400">Paneles Tier 1 (550W)</span>
                    </div>
                    
                    <div class="flex-1 p-5 flex flex-col gap-4">
                        <div class="grid grid-cols-3 gap-3">
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <p class="text-[8px] font-black text-slate-400 uppercase mb-1">Paneles Nec.</p>
                                <p class="text-2xl font-black text-slate-900 leading-none">{{ photovoltaic.panels_count }} <span class="text-[10px] text-slate-400">u.</span></p>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <p class="text-[8px] font-black text-slate-400 uppercase mb-1">Área en Uso</p>
                                <p class="text-2xl font-black text-slate-900 leading-none">{{ photovoltaic.area_used }}<span class="text-[10px] text-slate-400">m²</span></p>
                                <p class="text-[7px] font-bold text-slate-400 uppercase mt-1">de {{ localArea }}m² disp.</p>
                            </div>
                            <div class="p-4 bg-slate-900 text-white rounded-2xl shadow-xl shadow-slate-900/10">
                                <p class="text-[8px] font-black text-slate-500 uppercase mb-1">Inversión Rec.</p>
                                <p class="text-2xl font-black text-energy-solar leading-none">USD {{ Math.round(photovoltaic.investment_estimate) }}</p>
                            </div>
                        </div>

                        <div class="flex-1 bg-slate-50 rounded-2xl p-5 flex flex-col justify-center gap-4 border border-slate-100/50">
                            <div class="flex justify-between items-center">
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-slate-400">Cobertura de Factura Anual</h4>
                                <div v-if="photovoltaic.scenario === 'FULL_COVERAGE'" class="flex items-center gap-1.5 px-2 py-0.5 bg-energy-success/10 text-energy-success rounded-full border border-energy-success/20 animate-pulse">
                                    <CheckCircle2 :size="10" />
                                    <span class="text-[8px] font-black uppercase">Consumo Cubierto</span>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="space-y-1.5">
                                    <div class="flex justify-between text-[10px] font-black uppercase text-slate-500">
                                        <span>Máximo (Verano)</span>
                                        <span class="text-energy-solar">{{ photovoltaic.coverage_summer }}%</span>
                                    </div>
                                    <div class="h-2 bg-slate-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-energy-solar shadow-[0_0_10px_rgba(245,158,11,0.5)]" :style="{ width: photovoltaic.coverage_summer + '%' }"></div>
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <div class="flex justify-between text-[10px] font-black uppercase text-slate-500">
                                        <span>Mínimo (Invierno)</span>
                                        <span class="text-sky-500">{{ photovoltaic.coverage_winter }}%</span>
                                    </div>
                                    <div class="h-2 bg-slate-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-sky-500 shadow-[0_0_10px_rgba(14,165,233,0.5)]" :style="{ width: photovoltaic.coverage_winter + '%' }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 p-3 bg-energy-success/5 border border-energy-success/10 rounded-xl">
                            <div class="w-8 h-8 bg-energy-success rounded-lg flex items-center justify-center text-white shrink-0">
                                <CheckCircle2 :size="16" />
                            </div>
                            <p class="text-[10px] font-bold text-energy-success-800 leading-tight">Sistema optimizado: Generación promedio de {{ Math.round(photovoltaic.monthly_generation_kwh) }} kWh/mes.</p>
                        </div>
                    </div>
                </div>

                <!-- Thermal Card (Right) -->
                <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-100 shadow-sm flex flex-col overflow-hidden">
                    <div class="p-4 border-b border-slate-50 flex items-center justify-between bg-sky-50/30">
                        <h3 class="text-xs font-black uppercase tracking-widest text-sky-500 flex items-center gap-2">
                            <Waves :size="14" /> Calefacción Solar (ACS)
                        </h3>
                        <span class="text-[10px] font-bold text-sky-400">Heat-Pipe Technology</span>
                    </div>

                    <div class="flex-1 p-5 flex flex-col gap-4">
                        <div class="p-4 bg-sky-50 border border-sky-100 rounded-2xl flex items-center justify-between">
                            <div class="space-y-1">
                                <p class="text-[8px] font-black text-sky-400 uppercase tracking-widest">Equipo Sugerido</p>
                                <p class="text-2xl font-black text-sky-900 leading-none">{{ waterData.recommended_equipment_liters }} Litros</p>
                            </div>
                            <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-sky-500">
                                <ThermometerSun :size="20" />
                            </div>
                        </div>

                        <div class="bg-slate-50 rounded-2xl border border-slate-100 flex flex-col">
                            <div class="flex p-1 border-b border-slate-100">
                                <button v-for="f in [{id:'gas_natural', n:'Gas Natural'},{id:'gas', n:'Garrafa'},{id:'electric', n:'Electricidad'}]" :key="f.id"
                                    @click="thermalFuelTab = f.id"
                                    :class="['flex-1 py-1.5 text-[8px] font-black uppercase tracking-widest rounded-lg transition-all', thermalFuelTab === f.id ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400 hover:text-slate-600']"
                                >{{ f.n }}</button>
                            </div>
                            <div class="p-4 space-y-3">
                                <div class="flex justify-between items-end">
                                    <div class="space-y-1">
                                        <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Ahorro Mensual</p>
                                        <p class="text-3xl font-black text-slate-900 leading-none">{{ formatMoney(waterData.savings[thermalFuelTab].monthly_savings) }}</p>
                                    </div>
                                    <div class="text-right space-y-1">
                                        <p class="text-[8px] font-black text-energy-success uppercase tracking-widest">Anual</p>
                                        <p class="text-sm font-black text-energy-success leading-none">{{ formatMoney(waterData.savings[thermalFuelTab].annual_savings) }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 text-[10px] font-bold text-slate-500 bg-white/50 p-2 rounded-lg border border-slate-100">
                                    <TrendingDown :size="12" class="text-energy-success" />
                                    <template v-if="thermalFuelTab === 'gas_natural'">{{ waterData.savings.gas_natural.m3_per_month }} m³/mes evitados</template>
                                    <template v-if="thermalFuelTab === 'gas'">{{ waterData.savings.gas.garrafas_per_month }} garrafas/mes evitadas</template>
                                    <template v-if="thermalFuelTab === 'electric'">{{ Math.round(waterData.monthly_energy_kwh * 0.75) }} kWh/mes evitados</template>
                                </div>
                            </div>
                        </div>

                        <div class="mt-auto bg-indigo-900 text-white p-4 rounded-2xl flex items-center justify-between">
                            <div>
                                <p class="text-[8px] font-black text-indigo-400 uppercase tracking-widest">Payback</p>
                                <p class="text-lg font-black text-energy-solar leading-none">< 12 meses</p>
                            </div>
                            <button class="bg-white text-indigo-900 px-5 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-energy-solar hover:text-white transition-all">
                                Solicitar Cotización <ChevronRight :size="12" class="inline ml-1" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
input[type=range]::-webkit-slider-thumb {
    -webkit-appearance: none;
    height: 12px;
    width: 12px;
    border-radius: 50%;
    background: #f59e0b;
    cursor: pointer;
    border: 2px solid white;
}
</style>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #f1f5f9;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #e2e8f0;
}
</style>
