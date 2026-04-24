<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    Settings2, 
    Zap, 
    ArrowRight, 
    Clock, 
    CheckCircle2, 
    AlertCircle, 
    BarChart3, 
    ChevronRight,
    ArrowUpRight,
    Loader2,
    FileText,
    Activity,
    Info,
    Search,
    Layers
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    unifications: Array,
    flash: Object
});

const selectedUnificationId = ref(null);
const processing = ref(false);

const runCalibration = (unification) => {
    selectedUnificationId.value = unification.id;
    processing.value = true;
    
    router.post(route('analisis.usage.run'), {
        contract_id: unification.contract_id,
        start_date: unification.start_date,
        end_date: unification.end_date
    }, {
        onFinish: () => processing.value = false,
    });
};

const getStatusClass = (unification) => {
    if (unification.is_calibrated) return 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20';
    if (unification.has_usages_saved) return 'bg-sky-500/10 text-sky-600 border-sky-500/20';
    return 'bg-amber-500/10 text-amber-600 border-amber-500/20';
};

const getStatusText = (unification) => {
    if (unification.is_calibrated) return 'Calibrado';
    if (unification.has_usages_saved) return 'Configurado';
    return 'Pendiente';
};

const formatDateRange = (start, end) => {
    const s = new Date(start);
    const e = new Date(end);
    const options = { month: 'short', year: '2-digit' };
    return `${s.toLocaleDateString('es-ES', { month: 'short' })} - ${e.toLocaleDateString('es-ES', options)}`;
};

const searchQuery = ref('');

/*
const filteredUnifications = computed(() => {
    if (!searchQuery.value) return props.unifications;
    const q = searchQuery.value.toLowerCase();
    return props.unifications.filter(p => {
        const range = formatDateRange(p.start_date, p.end_date).toLowerCase();
        const name = (p.contract_name || '').toLowerCase();
        const status = getStatusText(p).toLowerCase();
        return range.includes(q) || name.includes(q) || status.includes(q);
    });
});
*/
const filteredUnifications = computed(() => props.unifications);
</script>

<template>
    <MainLayout>
        <Head title="Ajuste de Uso y Calibración" />

        <div class="max-w-6xl mx-auto space-y-12">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-slate-900 text-white rounded-full text-[10px] font-black uppercase tracking-widest border border-slate-800">
                        <Settings2 :size="14" />
                        Calibración Física
                    </div>
                    <h1 class="text-5xl font-black text-slate-900 tracking-tighter leading-none">
                        Ajuste <span class="text-energy-solar">de Ciclos</span>
                    </h1>
                    <p class="text-xl text-slate-500 font-medium">Sincroniza tu Gemelo Digital con tus periodos unificados de consumo.</p>
                </div>

                <div class="bg-amber-50 border border-amber-100 p-6 rounded-[32px] max-w-sm flex items-start gap-4">
                    <div class="w-10 h-10 rounded-2xl bg-amber-200/50 flex items-center justify-center text-amber-600 shrink-0">
                        <Info :size="20" />
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-black text-amber-900 leading-tight">Calibración Unificada</p>
                        <p class="text-[11px] text-amber-700 font-medium leading-relaxed">
                            Ahora calibramos por periodos bi-mensuales unificados para garantizar que los 3 tanques sean 100% precisos.
                        </p>
                    </div>
                </div>
            </div>



            <!-- Main Interactive List -->
            <div class="space-y-6">
                <div class="flex items-center justify-between px-4">
                    <h3 class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em]">Periodos de Medición</h3>
                    <div class="relative group">
                        <Search :size="12" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-energy-solar transition-colors" />
                        <input type="text" v-model="searchQuery" placeholder="Buscar periodo..." class="bg-white border border-slate-100 rounded-full py-2 pl-8 pr-4 text-[10px] font-bold focus:ring-2 focus:ring-energy-solar/10 transition-all outline-none" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <div 
                        v-for="period in filteredUnifications" 
                        :key="period.id"
                        class="bg-white rounded-[40px] border border-slate-100 shadow-xl shadow-slate-200/30 p-8 flex flex-col lg:flex-row items-center justify-between gap-8 group hover:shadow-2xl hover:border-energy-solar/10 transition-all"
                    >
                        <div class="flex items-center gap-8 w-full lg:w-auto">
                            <div class="w-16 h-16 rounded-[24px] bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-energy-solar/5 transition-colors">
                                <Layers :size="32" />
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-3">
                                    <h4 class="text-xl font-black text-slate-900 tracking-tight">{{ formatDateRange(period.start_date, period.end_date) }}</h4>
                                    <span :class="['px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border', getStatusClass(period)]">
                                        {{ getStatusText(period) }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-400 font-medium flex items-center gap-2">
                                    <FileText :size="12" />
                                    {{ period.installments_count }} de {{ period.total_expected_installments }} cuotas | {{ period.contract_name }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-8 w-full lg:w-auto px-4 items-center">
                            <div>
                                <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest leading-none mb-2">Energía Periodo</p>
                                <p class="text-xl font-black text-slate-900">{{ Math.round(period.total_kwh) }}<span class="text-xs ml-1 text-slate-400">kWh</span></p>
                            </div>
                            <div v-if="period.has_usages_saved">
                                <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest leading-none mb-2">Base Teórica</p>
                                <div class="flex items-center gap-2">
                                    <p class="text-xl font-black text-slate-700">{{ Math.round(period.theoretical_kwh) }}<span class="text-xs ml-1 text-slate-400">kWh</span></p>
                                    <span :class="[
                                        'px-2 py-0.5 rounded-lg text-[9px] font-black',
                                        Math.abs((period.theoretical_kwh - period.total_kwh) / period.total_kwh) > 0.1 
                                            ? 'bg-rose-500 text-white' 
                                            : 'bg-emerald-500 text-white'
                                    ]">
                                        {{ (period.theoretical_kwh > period.total_kwh ? '+' : '') }}{{ Math.round(((period.theoretical_kwh - period.total_kwh) / period.total_kwh) * 100) }}%
                                    </span>
                                </div>
                            </div>
                            <div v-else-if="period.recommended_kwh">
                                <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest leading-none mb-2">Ref. Anterior</p>
                                <p class="text-xl font-black text-slate-500">{{ Math.round(period.recommended_kwh) }}<span class="text-xs ml-1 text-slate-400">kWh</span></p>
                            </div>
                            
                            <template v-if="period.has_usages_saved">
                                <div class="hidden lg:block w-px h-10 bg-slate-100 self-center"></div>
                                <div>
                                    <p class="text-[9px] font-black text-rose-300 uppercase tracking-widest leading-none mb-2">T1 Base</p>
                                    <p class="text-xl font-black text-rose-500">{{ Math.round(period.tanks[1]) }}<span class="text-xs ml-1 text-rose-300">kWh</span></p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-sky-300 uppercase tracking-widest leading-none mb-2">T2 Clima</p>
                                    <p class="text-xl font-black text-sky-500">{{ Math.round(period.tanks[2]) }}<span class="text-xs ml-1 text-sky-300">kWh</span></p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-energy-solar/50 uppercase tracking-widest leading-none mb-2">T3 Variable</p>
                                    <p class="text-xl font-black text-energy-solar">{{ Math.round(period.tanks[3]) }}<span class="text-xs ml-1 text-energy-solar/50">kWh</span></p>
                                </div>
                            </template>

                            <div v-if="!period.is_complete" class="flex flex-col justify-center">
                                <div class="flex items-center gap-2 text-amber-500">
                                    <AlertCircle :size="16" />
                                    <span class="text-[10px] font-black uppercase tracking-tight">Incompleto</span>
                                </div>
                            </div>
                        </div>

                        <div class="w-full lg:w-auto shrink-0 flex gap-3">
                            <Link 
                                :href="route('analisis.usage.detail', { contract: period.contract_id, start_date: period.start_date, end_date: period.end_date })"
                                :class="[
                                    'flex-1 lg:flex-none px-8 py-5 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-xl shadow-slate-200/50 text-center',
                                    period.is_calibrated 
                                        ? 'bg-emerald-500 text-white hover:bg-emerald-600 shadow-emerald-500/20' 
                                        : (period.has_usages_saved 
                                            ? 'bg-sky-600 text-white hover:bg-sky-700 shadow-sky-600/20' 
                                            : (period.is_complete ? 'bg-amber-500 text-white hover:bg-amber-600 shadow-amber-500/20' : 'bg-slate-50 text-slate-200 cursor-not-allowed pointer-events-none')
                                          )
                                ]"
                            >
                                {{ period.is_calibrated ? 'Ajustar Sintonía' : (period.has_usages_saved ? 'Revisar Ajuste' : 'Configurar Ajuste') }}
                            </Link>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="filteredUnifications.length === 0" class="bg-slate-50/50 rounded-[40px] p-24 text-center border-2 border-dashed border-slate-100 flex flex-col items-center gap-6">
                        <div class="w-20 h-20 bg-white rounded-3xl shadow-xl flex items-center justify-center text-slate-200 rotate-6">
                            <BarChart3 :size="40" />
                        </div>
                        <div class="space-y-2">
                            <h4 class="text-2xl font-black text-slate-900 tracking-tight">Sin periodos registrados</h4>
                            <p class="text-slate-400 font-medium max-w-sm mx-auto">Debes cargar y unificar facturas en la sección de **Gestión Física** antes de poder calibrar.</p>
                        </div>
                        <Link :href="route('gestion.unifications')" class="text-xs font-black text-energy-solar uppercase tracking-widest border-b-2 border-energy-solar pb-1 mt-4">
                            Ir a Unificaciones
                        </Link>
                    </div>
                </div>
            </div>


        </div>
    </MainLayout>
</template>
