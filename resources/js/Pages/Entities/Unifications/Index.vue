<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    RefreshCw, 
    Calendar, 
    Zap, 
    DollarSign, 
    CheckCircle2, 
    AlertCircle, 
    ArrowRight,
    ChevronDown,
    Building,
    Activity,
    Info,
    History
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    unifications: Array
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

// Global Stats
const stats = computed(() => {
    const complete = props.unifications.filter(u => u.status === 'complete').length;
    const totalKwh = props.unifications.reduce((acc, u) => acc + (u.real_bimonthly_kwh || u.total_kwh), 0);
    const totalAmount = props.unifications.reduce((acc, u) => acc + u.total_amount, 0);
    
    return {
        complete,
        totalCount: props.unifications.length,
        totalKwh,
        totalAmount
    };
});
</script>

<template>
    <MainLayout>
        <Head title="Unificaciones Bimestrales" />

        <div class="max-w-7xl mx-auto space-y-10 pb-20">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-500/10 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest border border-emerald-500/20">
                        <RefreshCw :size="14" class="animate-spin-slow" />
                        Consolidación de Datos
                    </div>
                    <h1 class="text-5xl font-black text-slate-900 tracking-tighter leading-none">
                        Unificaciones <span class="text-emerald-600">Bimestrales</span>
                    </h1>
                    <p class="text-lg text-slate-500 font-medium">Control físico de consumos por medidor (60 días).</p>
                </div>

                <div class="p-6 bg-slate-900 rounded-[32px] text-white flex items-center gap-6 shadow-2xl shadow-slate-900/20">
                    <div class="flex flex-col">
                        <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest leading-none mb-2">Ciclos Completados</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-3xl font-black">{{ stats.complete }}</span>
                            <span class="text-xs font-bold text-slate-500">de {{ stats.totalCount }}</span>
                        </div>
                    </div>
                    <div class="w-[1px] h-10 bg-slate-800"></div>
                    <div class="flex flex-col">
                        <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest leading-none mb-2">Energía Total</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-black text-emerald-400">{{ stats.totalKwh.toLocaleString('es-AR', { maximumFractionDigits: 0 }) }}</span>
                            <span class="text-xs font-bold text-slate-500 uppercase">kWh</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div v-if="unifications.length === 0" class="flex flex-col items-center justify-center py-40 text-center">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6">
                    <History :size="48" class="text-slate-200" />
                </div>
                <h3 class="text-xl font-black text-slate-900 mb-2">No hay unificaciones todavía</h3>
                <p class="text-slate-400 max-w-sm">Cargue facturas con modalidad de cuotas para que el sistema las unifique automáticamente por período.</p>
                <Link :href="route('gestion.invoices')" class="mt-8 px-8 py-4 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-emerald-600 transition-all">Cargar Facturas</Link>
            </div>

            <div v-else class="grid grid-cols-1 gap-8">
                <!-- Instruction Alert -->
                <div class="p-6 bg-blue-50 border border-blue-100 rounded-[32px] flex items-center gap-6">
                    <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-blue-600 shadow-sm">
                        <Info :size="24" />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-blue-900 mb-1">Cálculo Físico para Motores de IA</p>
                        <p class="text-xs text-blue-700 font-medium">Estos registros bimestrales son los que utilizan los "tanques" de cálculo para calibrar tu consumo. Unificar garantiza que la intensidad de uso sea real y no fraccionada.</p>
                    </div>
                </div>

                <!-- Unification List -->
                <div v-for="period in unifications" :key="period.id" 
                    class="bg-white rounded-[40px] border border-slate-100 shadow-xl shadow-slate-200/50 overflow-hidden group hover:border-emerald-500/20 transition-all">
                    <div class="p-10">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 mb-10">
                            <!-- Period Info -->
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="p-2.5 bg-slate-50 rounded-xl text-slate-400">
                                        <Calendar :size="18" />
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ formatDate(period.start_date) }}</h3>
                                        <ArrowRight :size="14" class="text-slate-300" />
                                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ formatDate(period.end_date) }}</h3>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 ml-12">
                                    <span class="px-2 py-0.5 bg-slate-100 rounded text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ calculateDays(period.start_date, period.end_date) }} Días de Medición</span>
                                    <span class="w-1 h-1 bg-slate-200 rounded-full"></span>
                                    <span class="text-xs font-bold text-slate-400">{{ period.contract_name }}</span>
                                </div>
                            </div>

                            <!-- Status & Main KPI -->
                            <div class="flex items-center gap-8">
                                <div class="text-right">
                                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-1">Consumo Real (Physical)</p>
                                    <div class="flex items-baseline justify-end gap-1">
                                        <span class="text-4xl font-black text-slate-900 tracking-tighter">{{ (period.real_bimonthly_kwh || period.total_kwh).toLocaleString('es-AR', { maximumFractionDigits: 0 }) }}</span>
                                        <span class="text-sm font-black text-slate-300 uppercase">kWh</span>
                                    </div>
                                </div>
                                <div v-if="period.status === 'complete'" class="w-20 h-20 rounded-full bg-emerald-50 flex flex-col items-center justify-center text-emerald-600 border border-emerald-100">
                                    <CheckCircle2 :size="24" />
                                    <span class="text-[8px] font-black uppercase mt-1">Completo</span>
                                </div>
                                <div v-else class="w-20 h-20 rounded-full bg-amber-50 flex flex-col items-center justify-center text-amber-600 border border-amber-100">
                                    <AlertCircle :size="24" class="animate-pulse" />
                                    <span class="text-[8px] font-black uppercase mt-1">Pendiente</span>
                                </div>
                            </div>
                        </div>

                        <!-- Installments Detail -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-10 border-t border-slate-50">
                            <!-- Installments Sum -->
                            <div class="p-6 bg-slate-50/50 rounded-3xl border border-slate-100/50">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-4">Composición Financiera</p>
                                <div class="space-y-3">
                                    <div v-for="inv in period.invoices" :key="inv.id" class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-slate-500">Factura Cuota {{ inv.installment || '?' }}</span>
                                        <span class="text-xs font-black text-slate-900">${{ inv.amount.toLocaleString('es-AR') }}</span>
                                    </div>
                                    <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                                        <span class="text-xs font-black text-slate-900">Total Bimestre</span>
                                        <span class="text-sm font-black text-emerald-600">${{ period.total_amount.toLocaleString('es-AR') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Consumption Breakdown -->
                            <div class="p-6 bg-slate-50/50 rounded-3xl border border-slate-100/50">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-4">Energía Facturada vs Real</p>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-baseline">
                                        <span class="text-xs font-bold text-slate-500">Suma Mensual:</span>
                                        <span class="text-xs font-bold text-slate-900">{{ period.total_kwh }} kWh</span>
                                    </div>
                                    <div class="flex justify-between items-baseline">
                                        <span class="text-xs font-bold text-slate-500">Medición Física:</span>
                                        <span class="text-xs font-black text-emerald-600">{{ period.real_bimonthly_kwh || period.total_kwh }} kWh</span>
                                    </div>
                                    <!-- Progress Bar -->
                                    <div class="mt-4 h-2 w-full bg-slate-200 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full bg-emerald-500 rounded-full transition-all duration-1000" 
                                            :style="{ width: (period.status === 'complete' ? '100' : '50') + '%' }"
                                        ></div>
                                    </div>
                                    <p class="text-[9px] text-slate-400 font-medium italic mt-2">
                                        {{ period.status === 'complete' ? 'Ciclo físico cerrado correctamente.' : 'Esperando carga de la segunda cuota para cerrar ciclo.' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Tank Input Card -->
                            <div class="p-6 bg-emerald-600 rounded-3xl text-white shadow-xl shadow-emerald-900/10 relative overflow-hidden">
                                <Zap class="absolute -right-4 -bottom-4 text-emerald-500 opacity-20" :size="120" />
                                <p class="text-[9px] font-black text-emerald-200 uppercase tracking-widest mb-4 relative z-10">Dato para el Motor</p>
                                <div class="relative z-10">
                                    <h4 class="text-xs font-bold mb-1">Consumo Integrado</h4>
                                    <p class="text-2xl font-black tracking-tight mb-4">{{ (period.real_bimonthly_kwh || period.total_kwh).toLocaleString('es-AR') }} <span class="text-sm">kWh</span></p>
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/20 rounded-full text-[9px] font-black uppercase tracking-widest backdrop-blur-sm">
                                        <Activity :size="12" /> Calibración Óptima
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
