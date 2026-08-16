<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    Calendar, 
    Zap, 
    CheckCircle2, 
    AlertCircle, 
    ArrowRight, 
    Activity, 
    History 
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    unifications: Array
});

const themeColors = computed(() => {
    const type = props.entity?.type;
    if (type === 'comercio') {
        return {
            text: 'text-purple-600',
            textLight: 'text-purple-200',
            bg: 'bg-purple-600',
            bgLight: 'bg-purple-600/10',
            borderLight: 'border-purple-600/20',
            hoverBg: 'hover:bg-purple-600',
            hoverBorderLight: 'hover:border-purple-500/20',
            tankCard: 'bg-purple-600 shadow-purple-900/10',
            tankZap: 'text-purple-500',
            progressbarBg: 'bg-purple-500'
        };
    }
    if (type === 'oficina') {
        return {
            text: 'text-blue-600',
            textLight: 'text-blue-200',
            bg: 'bg-blue-600',
            bgLight: 'bg-blue-600/10',
            borderLight: 'border-blue-600/20',
            hoverBg: 'hover:bg-blue-600',
            hoverBorderLight: 'hover:border-blue-500/20',
            tankCard: 'bg-blue-600 shadow-blue-900/10',
            tankZap: 'text-blue-500',
            progressbarBg: 'bg-blue-500'
        };
    }
    // Default / hogar
    return {
        text: 'text-emerald-600',
        textLight: 'text-emerald-200',
        bg: 'bg-emerald-600',
        bgLight: 'bg-emerald-500/10',
        borderLight: 'border-emerald-500/20',
        hoverBg: 'hover:bg-emerald-600',
        hoverBorderLight: 'hover:border-emerald-500/20',
        tankCard: 'bg-emerald-600 shadow-emerald-900/10',
        tankZap: 'text-emerald-500',
        progressbarBg: 'bg-emerald-500'
    };
});

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


</script>

<template>
    <MainLayout>
        <Head title="Unificaciones Bimestrales" />

        <div class="max-w-7xl mx-auto space-y-4 pb-12">
            <!-- Empty State -->
            <div v-if="unifications.length === 0" class="flex flex-col items-center justify-center py-28 text-center bg-white rounded-3xl border border-slate-100 p-8">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-5">
                    <History :size="36" class="text-slate-300" />
                </div>
                <h3 class="text-lg font-black text-slate-900 mb-1.5">No hay unificaciones todavía</h3>
                <p class="text-xs text-slate-400 max-w-sm">Cargue facturas con modalidad de cuotas para que el sistema las unifique automáticamente por período.</p>
                <Link :href="route('gestion.invoices')" :class="['mt-6 px-6 py-3 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-md', themeColors.hoverBg]">Cargar Facturas</Link>
            </div>

            <div v-else class="grid grid-cols-1 gap-4">
                <!-- Unification List -->
                <div v-for="period in unifications" :key="period.id" 
                    :class="['bg-white rounded-3xl border border-slate-100 shadow-md overflow-hidden group transition-all', themeColors.hoverBorderLight]">
                    <div class="p-5 sm:p-6">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                            <!-- Period Info -->
                            <div class="space-y-1.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="p-2 bg-slate-50 rounded-xl text-slate-400 shrink-0">
                                        <Calendar :size="16" />
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">{{ formatDate(period.start_date) }}</h3>
                                        <ArrowRight :size="14" class="text-slate-300 shrink-0" />
                                        <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">{{ formatDate(period.end_date) }}</h3>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2.5 ml-9">
                                    <span class="px-2 py-0.5 bg-slate-100 rounded-md text-[8px] font-black text-slate-500 uppercase tracking-tight whitespace-nowrap">{{ calculateDays(period.start_date, period.end_date) }} Días de Medición</span>
                                    <span class="w-1 h-1 bg-slate-200 rounded-full"></span>
                                    <span class="text-xs font-bold text-slate-400 truncate">{{ period.contract_name }}</span>
                                </div>
                            </div>

                            <!-- Status & Main KPI -->
                            <div class="flex items-center gap-5 justify-end">
                                <div class="text-right">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Consumo Real (Físico)</p>
                                    <div class="flex items-baseline justify-end gap-1">
                                        <span class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-none">{{ (period.real_bimonthly_kwh || period.total_kwh).toLocaleString('es-AR', { maximumFractionDigits: 0 }) }}</span>
                                        <span class="text-xs font-black text-slate-400 uppercase">kWh</span>
                                    </div>
                                </div>
                                <div v-if="period.is_complete" class="w-12 h-12 rounded-2xl bg-emerald-50 flex flex-col items-center justify-center text-emerald-600 border border-emerald-100 shrink-0">
                                    <CheckCircle2 :size="18" />
                                    <span class="text-[7px] font-black uppercase mt-0.5">Completo</span>
                                </div>
                                <div v-else class="w-12 h-12 rounded-2xl bg-amber-50 flex flex-col items-center justify-center text-amber-600 border border-amber-100 shrink-0">
                                    <AlertCircle :size="18" class="animate-pulse" />
                                    <span class="text-[7px] font-black uppercase mt-0.5">Pendiente</span>
                                </div>
                            </div>
                        </div>

                        <!-- Installments Detail -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 pt-4 border-t border-slate-100">
                            <!-- Installments Sum -->
                            <div class="p-4 bg-slate-50/60 rounded-2xl border border-slate-100 space-y-2.5">
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Composición Financiera</p>
                                <div class="space-y-1.5">
                                    <div v-for="inv in period.invoices" :key="inv.id" class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-slate-500">Factura Cuota {{ inv.installment || '?' }}</span>
                                        <span class="font-black text-slate-900">${{ inv.amount.toLocaleString('es-AR') }}</span>
                                    </div>
                                    <div class="pt-2 border-t border-slate-200/80 flex items-center justify-between">
                                        <span class="text-xs font-black text-slate-900">Total Bimestre</span>
                                        <span :class="['text-xs font-black', themeColors.text]">${{ period.total_amount.toLocaleString('es-AR') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Consumption Breakdown -->
                            <div class="p-4 bg-slate-50/60 rounded-2xl border border-slate-100 space-y-2">
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Energía Facturada vs Real</p>
                                <div class="space-y-1">
                                    <div class="flex justify-between items-baseline text-xs">
                                        <span class="font-bold text-slate-500">Suma Mensual:</span>
                                        <span class="font-bold text-slate-900">{{ period.total_kwh }} kWh</span>
                                    </div>
                                    <div class="flex justify-between items-baseline text-xs">
                                        <span class="font-bold text-slate-500">Medición Física:</span>
                                        <span :class="['font-black', themeColors.text]">{{ period.real_bimonthly_kwh || period.total_kwh }} kWh</span>
                                    </div>
                                    <!-- Progress Bar -->
                                    <div class="mt-2.5 h-1.5 w-full bg-slate-200 rounded-full overflow-hidden">
                                        <div 
                                            :class="['h-full rounded-full transition-all duration-1000', themeColors.progressbarBg]" 
                                            :style="{ width: (period.is_complete ? '100' : '50') + '%' }"
                                        ></div>
                                    </div>
                                    <p class="text-[8px] text-slate-400 font-medium italic mt-1 leading-tight">
                                        {{ period.is_complete ? 'Ciclo físico cerrado correctamente.' : 'Esperando carga de la 2da cuota.' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Tank Input Card -->
                            <div :class="['p-4 rounded-2xl text-white shadow-md relative overflow-hidden flex flex-col justify-between', themeColors.tankCard]">
                                <Zap :class="['absolute -right-3 -bottom-3 opacity-20', themeColors.tankZap]" :size="80" />
                                <p class="text-[8px] font-black text-white/80 uppercase tracking-widest relative z-10 mb-1">Dato para el Motor</p>
                                <div class="relative z-10 space-y-2">
                                    <div>
                                        <h4 class="text-[10px] font-bold opacity-80 leading-none">Consumo Integrado</h4>
                                        <p class="text-xl font-black tracking-tight leading-tight mt-0.5">{{ (period.real_bimonthly_kwh || period.total_kwh).toLocaleString('es-AR') }} <span class="text-xs font-bold">kWh</span></p>
                                    </div>
                                    <div class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-white/20 rounded-full text-[8px] font-black uppercase tracking-widest backdrop-blur-sm">
                                        <Activity :size="10" /> Calibración Óptima
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
.animate-spin-slow {
    animation: spin 6s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
