<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    AlertCircle, 
    BarChart3, 
    FileText, 
    Layers, 
    ArrowRight, 
    Calendar,
    ChevronDown
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    unifications: Array,
    flash: Object
});

const themeColors = computed(() => {
    const type = props.entity?.type;
    if (type === 'comercio') {
        return {
            text: 'text-purple-600',
            bg: 'bg-purple-600',
            bgLight: 'bg-purple-600/5',
            borderLight: 'border-purple-600/20',
            hoverBorder: 'hover:border-purple-600/20',
            focusRing: 'focus:ring-purple-600/10',
            borderBottom: 'border-purple-600',
            groupHoverText: 'group-hover:text-purple-600',
            groupHoverBg: 'group-hover:bg-purple-600/5',
        };
    }
    if (type === 'oficina') {
        return {
            text: 'text-blue-600',
            bg: 'bg-blue-600',
            bgLight: 'bg-blue-600/5',
            borderLight: 'border-blue-600/20',
            hoverBorder: 'hover:border-blue-600/20',
            focusRing: 'focus:ring-blue-600/10',
            borderBottom: 'border-blue-600',
            groupHoverText: 'group-hover:text-blue-600',
            groupHoverBg: 'group-hover:bg-blue-600/5',
        };
    }
    // Default / hogar (Emerald theme)
    return {
        text: 'text-emerald-600',
        bg: 'bg-emerald-600',
        bgLight: 'bg-emerald-600/5',
        borderLight: 'border-emerald-600/20',
        hoverBorder: 'hover:border-emerald-600/20',
        focusRing: 'focus:ring-emerald-600/10',
        borderBottom: 'border-emerald-600',
        groupHoverText: 'group-hover:text-emerald-600',
        groupHoverBg: 'group-hover:bg-emerald-600/5',
    };
});

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

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const [year, month, day] = dateString.split('T')[0].split('-');
    return `${day}/${month}/${year.slice(-2)}`;
};

const calculateDays = (start, end) => {
    if (!start || !end) return 0;
    const s = new Date(start.split('T')[0]);
    const e = new Date(end.split('T')[0]);
    const diffTime = Math.abs(e - s);
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
};

const selectedPeriodKey = ref('all');

const availablePeriods = computed(() => {
    return (props.unifications || []).map(p => ({
        key: `${p.contract_id}_${p.start_date}_${p.end_date}`,
        label: `${formatDate(p.start_date)} - ${formatDate(p.end_date)}`,
        contractName: p.contract_name,
        period: p
    }));
});

const filteredUnifications = computed(() => {
    let list = props.unifications || [];
    if (selectedPeriodKey.value && selectedPeriodKey.value !== 'all') {
        list = list.filter(p => `${p.contract_id}_${p.start_date}_${p.end_date}` === selectedPeriodKey.value);
    }
    return list;
});
</script>

<template>
    <MainLayout>
        <Head title="Ajuste de Uso y Calibración" />

        <div class="max-w-6xl mx-auto space-y-4">
            <!-- Main Interactive List -->
            <div class="space-y-3.5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-black text-slate-900 uppercase tracking-wider">Periodos de Medición</span>
                        <span class="px-2 py-0.5 bg-slate-200/80 rounded-full text-[9px] font-black text-slate-600 leading-none">
                            {{ filteredUnifications.length }}
                        </span>
                    </div>

                    <!-- Dropdown Filter: Registered Dates Selector -->
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <div class="relative flex items-center w-full sm:w-auto">
                            <Calendar :size="13" class="absolute left-3 text-slate-400 pointer-events-none" />
                            <select 
                                v-model="selectedPeriodKey" 
                                :class="['bg-white border border-slate-200/80 rounded-xl py-1.5 pl-8 pr-8 text-xs font-bold shadow-xs transition-all focus:ring-2 appearance-none cursor-pointer text-slate-700 w-full sm:w-auto', themeColors.focusRing]"
                            >
                                <option value="all">Todos los períodos ({{ (unifications || []).length }})</option>
                                <option v-for="item in availablePeriods" :key="item.key" :value="item.key">
                                    {{ item.label }} — {{ item.contractName }}
                                </option>
                            </select>
                            <ChevronDown :size="12" class="absolute right-2.5 text-slate-400 pointer-events-none" />
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3.5">
                    <div 
                        v-for="period in filteredUnifications" 
                        :key="period.id"
                        class="bg-white rounded-3xl border border-slate-100 shadow-md p-4 sm:p-5 flex flex-col lg:flex-row items-center justify-between gap-4 group hover:shadow-lg transition-all"
                        :class="themeColors.hoverBorder"
                    >
                        <div class="flex items-center gap-3.5 w-full lg:w-auto">
                            <div class="w-11 h-11 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 shrink-0 transition-colors" :class="themeColors.groupHoverBg">
                                <Layers :size="22" />
                            </div>
                            <div class="space-y-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                                    <div class="flex items-center gap-1.5 text-base sm:text-lg font-black text-slate-900 whitespace-nowrap">
                                        <span>{{ formatDate(period.start_date) }}</span>
                                        <ArrowRight :size="13" class="text-slate-300 shrink-0" />
                                        <span>{{ formatDate(period.end_date) }}</span>
                                    </div>
                                    <span class="px-2 py-0.5 bg-slate-100 rounded-md text-[8px] font-black text-slate-500 uppercase tracking-tight whitespace-nowrap shrink-0">
                                        {{ calculateDays(period.start_date, period.end_date) }} días
                                    </span>
                                    <span :class="['px-2.5 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider border shrink-0', getStatusClass(period)]">
                                        {{ getStatusText(period) }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-400 font-medium flex items-center gap-1.5 truncate">
                                    <FileText :size="11" class="shrink-0" />
                                    <span>{{ period.installments_count }} de {{ period.total_expected_installments }} cuotas</span>
                                    <span class="opacity-40">|</span>
                                    <span class="truncate">{{ period.contract_name }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-5 w-full lg:w-auto px-2 items-center justify-start lg:justify-end">
                            <div>
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Energía Periodo</p>
                                <p class="text-lg font-black text-slate-900 leading-none">{{ Math.round(period.total_kwh) }}<span class="text-[10px] ml-0.5 text-slate-400">kWh</span></p>
                            </div>
                            <div v-if="period.has_usages_saved">
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Base Teórica</p>
                                <div class="flex items-center gap-1.5">
                                    <p class="text-lg font-black text-slate-700 leading-none">{{ Math.round(period.theoretical_kwh) }}<span class="text-[10px] ml-0.5 text-slate-400">kWh</span></p>
                                    <span :class="[
                                        'px-1.5 py-0.5 rounded-md text-[8px] font-black leading-none',
                                        Math.abs((period.theoretical_kwh - period.total_kwh) / period.total_kwh) > 0.1 
                                            ? 'bg-rose-500 text-white' 
                                            : 'bg-emerald-500 text-white'
                                    ]">
                                        {{ (period.theoretical_kwh > period.total_kwh ? '+' : '') }}{{ Math.round(((period.theoretical_kwh - period.total_kwh) / period.total_kwh) * 100) }}%
                                    </span>
                                </div>
                            </div>
                            <div v-else-if="period.recommended_kwh">
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Ref. Anterior</p>
                                <p class="text-lg font-black text-slate-500 leading-none">{{ Math.round(period.recommended_kwh) }}<span class="text-[10px] ml-0.5 text-slate-400">kWh</span></p>
                            </div>
                            
                            <template v-if="period.has_usages_saved">
                                <div class="hidden lg:block w-px h-8 bg-slate-100 self-center"></div>
                                <div>
                                    <p class="text-[8px] font-black text-rose-400 uppercase tracking-widest leading-none mb-1">T1 Base</p>
                                    <p class="text-base font-black text-rose-600 leading-none">{{ Math.round(period.tanks[1]) }}<span class="text-[9px] ml-0.5 text-rose-300">kWh</span></p>
                                </div>
                                <div>
                                    <p class="text-[8px] font-black text-sky-400 uppercase tracking-widest leading-none mb-1">T2 Clima</p>
                                    <p class="text-base font-black text-sky-600 leading-none">{{ Math.round(period.tanks[2]) }}<span class="text-[9px] ml-0.5 text-sky-300">kWh</span></p>
                                </div>
                                <div>
                                    <p class="text-[8px] font-black uppercase tracking-widest leading-none mb-1" :class="themeColors.text">T3 Variable</p>
                                    <p class="text-base font-black leading-none" :class="themeColors.text">{{ Math.round(period.tanks[3]) }}<span class="text-[9px] ml-0.5 opacity-70">kWh</span></p>
                                </div>
                            </template>

                            <div v-if="!period.is_complete" class="flex flex-col justify-center">
                                <div class="flex items-center gap-1.5 text-amber-500">
                                    <AlertCircle :size="14" />
                                    <span class="text-[9px] font-black uppercase tracking-tight">Incompleto</span>
                                </div>
                            </div>
                        </div>

                        <div class="w-full lg:w-auto shrink-0 flex gap-2">
                            <Link 
                                :href="route('analisis.usage.detail', { contract: period.contract_id, start_date: period.start_date, end_date: period.end_date })"
                                :class="[
                                    'w-full lg:w-auto px-5 py-2.5 rounded-xl font-black text-[10px] uppercase tracking-wider transition-all shadow-sm text-center hover:scale-105 active:scale-95 cursor-pointer',
                                    period.is_calibrated 
                                        ? 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-emerald-600/20' 
                                        : (period.has_usages_saved 
                                            ? 'bg-sky-600 text-white hover:bg-sky-700 shadow-sky-600/20' 
                                            : (period.is_complete ? 'bg-amber-500 text-white hover:bg-amber-600 shadow-amber-500/20' : 'bg-slate-50 text-slate-300 cursor-not-allowed pointer-events-none')
                                          )
                                ]"
                            >
                                {{ period.is_calibrated ? 'Ajustar Sintonía' : (period.has_usages_saved ? 'Revisar Ajuste' : 'Configurar Ajuste') }}
                            </Link>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="filteredUnifications.length === 0" class="bg-slate-50/50 rounded-3xl p-16 text-center border-2 border-dashed border-slate-100 flex flex-col items-center gap-4">
                        <div class="w-16 h-16 bg-white rounded-2xl shadow-md flex items-center justify-center text-slate-200">
                            <BarChart3 :size="32" />
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-lg font-black text-slate-900 tracking-tight">Sin periodos registrados</h4>
                            <p class="text-xs text-slate-400 font-medium max-w-sm mx-auto">Debes cargar y unificar facturas en la sección de Gestión antes de poder calibrar.</p>
                        </div>
                        <Link :href="route('gestion.unifications')" class="text-xs font-black uppercase tracking-widest border-b-2 pb-0.5 mt-2" :class="[themeColors.text, themeColors.borderBottom]">
                            Ir a Unificaciones
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
