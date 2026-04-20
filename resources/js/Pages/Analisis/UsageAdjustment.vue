<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
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
    Search
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    invoices: Array,
    flash: Object
});

const selectedInvoice = ref(null);
const processing = ref(false);

const runCalibration = (invoice) => {
    selectedInvoice.value = invoice;
    processing.value = true;
    router.post(route('analisis.usage.run', invoice.id), {}, {
        onFinish: () => processing.value = false,
        onSuccess: () => {
            // Logic after successful calibration
        }
    });
};

const getStatusClass = (invoice) => {
    if (invoice.calibrated_at) return 'bg-energy-success/10 text-energy-success border-energy-success/20';
    return 'bg-slate-100 text-slate-400 border-slate-200';
};
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
                        Afinación del Modelo
                    </div>
                    <h1 class="text-5xl font-black text-slate-900 tracking-tighter leading-none">
                        Ajuste <span class="text-energy-solar">de Uso</span>
                    </h1>
                    <p class="text-xl text-slate-500 font-medium">Sincroniza tu Gemelo Digital con la realidad de tus facturas energéticas.</p>
                </div>

                <div class="bg-amber-50 border border-amber-100 p-6 rounded-[32px] max-w-sm flex items-start gap-4">
                    <div class="w-10 h-10 rounded-2xl bg-amber-200/50 flex items-center justify-center text-amber-600 shrink-0">
                        <Info :size="20" />
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-black text-amber-900 leading-tight">Calibración Activa</p>
                        <p class="text-[11px] text-amber-700 font-medium leading-relaxed">
                            Al calibrar una factura, el sistema ajusta automáticamente el consumo de tus equipos para que coincida con el total facturado.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Steps / Breadcrumb for process -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/20 flex items-center gap-5">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center font-black text-xl italic">1</div>
                    <div>
                        <p class="text-xs font-black text-slate-900 uppercase tracking-tight">Carga de Factura</p>
                        <p class="text-[10px] text-slate-400 font-medium">Referencia oficial del gasto real.</p>
                    </div>
                    <CheckCircle2 :size="20" class="ml-auto text-energy-success" />
                </div>
                <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/20 flex items-center gap-5">
                    <div class="w-12 h-12 rounded-2xl bg-energy-solar/10 text-energy-solar flex items-center justify-center font-black text-xl italic">2</div>
                    <div>
                        <p class="text-xs font-black text-slate-900 uppercase tracking-tight">Reconciliación</p>
                        <p class="text-[10px] text-slate-400 font-medium">Ajuste técnico del inventario.</p>
                    </div>
                    <Loader2 v-if="processing" :size="20" class="ml-auto text-energy-solar animate-spin" />
                </div>
                <div class="bg-white p-6 rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/20 flex items-center gap-5 opacity-40">
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center font-black text-xl italic">3</div>
                    <div>
                        <p class="text-xs font-black text-slate-900 uppercase tracking-tight">Reporte Final</p>
                        <p class="text-[10px] text-slate-400 font-medium">Consumo histórico verificado.</p>
                    </div>
                </div>
            </div>

            <!-- Main Interactive List -->
            <div class="space-y-6">
                <div class="flex items-center justify-between px-4">
                    <h3 class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em]">Historial de Facturación</h3>
                    <div class="relative group">
                        <Search :size="12" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-energy-solar transition-colors" />
                        <input type="text" placeholder="Buscar factura..." class="bg-white border border-slate-100 rounded-full py-2 pl-8 pr-4 text-[10px] font-bold focus:ring-2 focus:ring-energy-solar/10 transition-all outline-none" />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div 
                        v-for="invoice in invoices" 
                        :key="invoice.id"
                        class="bg-white rounded-[40px] border border-slate-100 shadow-xl shadow-slate-200/30 p-8 flex flex-col lg:flex-row items-center justify-between gap-8 group hover:shadow-2xl hover:border-energy-solar/10 transition-all"
                    >
                        <div class="flex items-center gap-8 w-full lg:w-auto">
                            <div class="w-16 h-16 rounded-[24px] bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-energy-solar/5 transition-colors">
                                <FileText :size="32" />
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center gap-3">
                                    <h4 class="text-xl font-black text-slate-900 tracking-tight">{{ invoice.invoice_number }}</h4>
                                    <span :class="['px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border', getStatusClass(invoice)]">
                                        {{ invoice.calibrated_at ? 'Calibrado' : 'Pendiente' }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-400 font-medium flex items-center gap-2">
                                    <Activity :size="12" />
                                    Periodo: {{ invoice.period || 'No especificado' }}
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-10 w-full lg:w-auto px-4">
                            <div>
                                <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest leading-none mb-2">Energía Facturada</p>
                                <p class="text-xl font-black text-slate-900">{{ invoice.total_energy_consumed_kwh }}<span class="text-xs ml-1 text-slate-400">kWh</span></p>
                            </div>
                            <div v-if="invoice.recommended_kwh">
                                <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest leading-none mb-2">Base Teórica</p>
                                <p class="text-xl font-black text-slate-700">{{ invoice.recommended_kwh }}<span class="text-xs ml-1 text-slate-400">kWh</span></p>
                            </div>
                            <div v-if="invoice.calibrated_at" class="hidden md:block">
                                <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest leading-none mb-2">Fecha Calibración</p>
                                <p class="text-sm font-bold text-slate-500">{{ new Date(invoice.calibrated_at).toLocaleDateString() }}</p>
                            </div>
                        </div>

                        <div class="w-full lg:w-auto shrink-0 flex gap-3">
                            <button 
                                @click="runCalibration(invoice)"
                                :disabled="processing"
                                :class="[
                                    'flex-1 lg:flex-none px-8 py-5 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-xl shadow-slate-200/50',
                                    invoice.calibrated_at 
                                        ? 'bg-slate-50 text-slate-400 hover:bg-slate-900 hover:text-white' 
                                        : 'bg-slate-900 text-white hover:bg-energy-solar shadow-energy-solar/20'
                                ]"
                            >
                                <span v-if="processing && selectedInvoice?.id === invoice.id" class="flex items-center gap-2">
                                    <Loader2 :size="14" class="animate-spin" /> Procesando
                                </span>
                                <span v-else>
                                    {{ invoice.calibrated_at ? 'Recalibrar' : 'Calibrar Ahora' }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="invoices.length === 0" class="bg-slate-50/50 rounded-[40px] p-24 text-center border-2 border-dashed border-slate-100 flex flex-col items-center gap-6">
                        <div class="w-20 h-20 bg-white rounded-3xl shadow-xl flex items-center justify-center text-slate-200 rotate-6">
                            <BarChart3 :size="40" />
                        </div>
                        <div class="space-y-2">
                            <h4 class="text-2xl font-black text-slate-900 tracking-tight">Sin facturas registradas</h4>
                            <p class="text-slate-400 font-medium max-w-sm mx-auto">Debes cargar facturas en la sección de **Gestión Física** antes de poder realizar ajustes de uso.</p>
                        </div>
                        <Link :href="route('gestion.invoices')" class="text-xs font-black text-energy-solar uppercase tracking-widest border-b-2 border-energy-solar pb-1 mt-4">
                            Ir a Gestión de Facturas
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Footer Help -->
            <div class="bg-slate-950 rounded-[48px] p-12 text-white relative overflow-hidden group">
                <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-energy-solar/10 rounded-full blur-[100px] group-hover:bg-energy-solar/20 transition-all duration-700"></div>
                <div class="relative z-10 grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                    <div class="space-y-6">
                        <div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center text-energy-solar">
                            <Activity :size="24" />
                        </div>
                        <h4 class="text-3xl font-black tracking-tight tracking-tighter">¿Por qué es importante calibrar?</h4>
                        <p class="text-slate-400 font-medium leading-relaxed">
                            El sistema de 3 tanques analiza tu consumo inmutable (heladeras), climático (clima exterior) y elástico (uso diario) para detectar ineficiencias reales. Sin calibración, el gemelo digital solo genera cálculos teóricos.
                        </p>
                    </div>
                    <div class="bg-white/5 border border-white/10 rounded-[32px] p-8 space-y-6 backdrop-blur-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-energy-success/20 text-energy-success flex items-center justify-center">
                                <CheckCircle2 :size="20" />
                            </div>
                            <p class="text-sm font-bold text-slate-300 italic">"He bajado un 20% mi factura solo ajustando los hábitos que el calibrador me señaló."</p>
                        </div>
                        <div class="h-px bg-white/10"></div>
                        <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Consejo del Motor:</p>
                        <p class="text-sm font-medium text-slate-300">Si el **Tanque 3 (Elasticidad)** sobrepasa el 40%, revisa tus equipos con mayor intensidad energética (Alto/Crítico).</p>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
