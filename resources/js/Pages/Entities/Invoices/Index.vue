<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    Receipt, 
    Plus, 
    Calendar, 
    DollarSign, 
    Activity, 
    History, 
    TrendingUp, 
    ArrowRight, 
    Pencil, 
    Trash2, 
    X,
    CheckCircle2,
    Search,
    ChevronLeft,
    AlertTriangle,
    Eye,
    ChevronDown,
    Zap
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    contracts: Array,
    invoices: Array,
    flash: Object
});

const showModal = ref(false);
const showAdvanced = ref(false);
const editingInvoice = ref(null);

const form = useForm({
    id: null,
    contract_id: '',
    invoice_number: '',
    invoice_date: '',
    start_date: '',
    end_date: '',
    total_energy_consumed_kwh: '',
    total_amount: '',
    cost_for_energy: 0,
    cost_for_power: 0,
    taxes: 0,
    other_charges: 0,
});

// Setup default contract if only one exists
if (props.contracts.length === 1) {
    form.contract_id = props.contracts[0].id;
}

const stats = computed(() => {
    const totalConsumption = props.invoices.reduce((acc, inv) => acc + parseFloat(inv.total_energy_consumed_kwh), 0);
    const totalAmount = props.invoices.reduce((acc, inv) => acc + parseFloat(inv.total_amount), 0);
    const avgAmount = props.invoices.length > 0 ? totalAmount / props.invoices.length : 0;
    
    return {
        totalConsumption,
        totalAmount,
        avgAmount,
        count: props.invoices.length
    };
});

const openCreateModal = () => {
    editingInvoice.value = null;
    form.reset();
    if (props.contracts.length === 1) form.contract_id = props.contracts[0].id;
    showModal.value = true;
};

const openEditModal = (invoice) => {
    editingInvoice.value = invoice;
    form.id = invoice.id;
    form.contract_id = invoice.contract_id;
    form.invoice_number = invoice.invoice_number;
    form.invoice_date = invoice.invoice_date;
    form.start_date = invoice.start_date;
    form.end_date = invoice.end_date;
    form.total_energy_consumed_kwh = invoice.total_energy_consumed_kwh;
    form.total_amount = invoice.total_amount;
    form.cost_for_energy = invoice.cost_for_energy || 0;
    form.cost_for_power = invoice.cost_for_power || 0;
    form.taxes = invoice.taxes || 0;
    form.other_charges = invoice.other_charges || 0;
    showModal.value = true;
};

const submit = () => {
    if (editingInvoice.value) {
        form.put(route('gestion.invoices.update', editingInvoice.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('gestion.invoices.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const closeModal = () => {
    showModal.value = false;
    showAdvanced.value = false;
    editingInvoice.value = null;
    form.reset();
};

const deleteInvoice = (id) => {
    if (confirm('¿Seguro que deseas eliminar esta factura?')) {
        router.delete(route('gestion.invoices.destroy', id));
    }
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: '2-digit' });
};

const calculateDays = (start, end) => {
    const diffTime = Math.abs(new Date(end) - new Date(start));
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
};

const searchQuery = ref('');
const filteredInvoices = computed(() => {
    if (!searchQuery.value) return props.invoices;
    const query = searchQuery.value.toLowerCase();
    return props.invoices.filter(inv => 
        inv.invoice_number.toLowerCase().includes(query) || 
        formatDate(inv.start_date).includes(query)
    );
});
</script>

<template>
    <MainLayout>
        <Head title="Gestión de Facturas" />

        <div class="max-w-7xl mx-auto space-y-10">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-energy-success/10 text-energy-success rounded-full text-[10px] font-black uppercase tracking-widest border border-energy-success/20">
                        <History :size="14" />
                        Historial Energético
                    </div>
                    <h1 class="text-5xl font-black text-slate-900 tracking-tighter leading-none">
                        Gestión de <span class="text-energy-success">Facturas</span>
                    </h1>
                    <p class="text-lg text-slate-500 font-medium">{{ entity.name }} — Control de consumos mensuales.</p>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="relative group">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-energy-success transition-colors" :size="18" />
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Buscar por número o fecha..." 
                            class="bg-white border-slate-100 rounded-2xl py-4 pl-12 pr-6 text-sm font-bold shadow-xl shadow-slate-200/50 focus:ring-2 focus:ring-energy-success/10 focus:border-energy-success/30 transition-all w-64 md:w-80"
                        />
                    </div>
                    <button 
                        v-if="contracts.length > 0"
                        @click="openCreateModal"
                        class="bg-slate-900 text-white w-14 h-14 rounded-2xl flex items-center justify-center shadow-xl shadow-slate-300 hover:bg-energy-success transition-all hover:-translate-y-1"
                    >
                        <Plus :size="24" stroke-width="3" />
                    </button>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/40">
                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-2">Total Facturado</p>
                    <h4 class="text-3xl font-black text-slate-900 leading-none">${{ stats.totalAmount.toLocaleString('es-AR', { maximumFractionDigits: 0 }) }}</h4>
                </div>
                <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/40">
                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-2">Consumo Acumulado</p>
                    <h4 class="text-3xl font-black text-slate-900 leading-none">{{ stats.totalConsumption.toLocaleString('es-AR', { maximumFractionDigits: 0 }) }} <span class="text-sm font-bold text-slate-300">kWh</span></h4>
                </div>
                <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/40">
                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-2">Promedio Mensual</p>
                    <h4 class="text-3xl font-black text-slate-900 leading-none">${{ stats.avgAmount.toLocaleString('es-AR', { maximumFractionDigits: 0 }) }}</h4>
                </div>
                <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/40">
                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-2">Total Registros</p>
                    <h4 class="text-3xl font-black text-slate-900 leading-none">{{ stats.count }}</h4>
                </div>
            </div>

            <!-- No Contracts Warning -->
            <div v-if="contracts.length === 0" class="bg-amber-50 rounded-[48px] p-20 text-center border border-dashed border-amber-200">
                <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-8 shadow-sm">
                    <AlertTriangle :size="40" class="text-amber-500" />
                </div>
                <h3 class="text-2xl font-black text-amber-900 tracking-tight mb-2">Falta Contrato de Suministro</h3>
                <p class="text-amber-700/70 font-medium max-w-sm mx-auto mb-10">No puede cargar facturas sin antes registrar un contrato o medidor activo para esta entidad.</p>
                <Link :href="route('gestion.contracts')" class="bg-amber-600 text-white px-8 py-4 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-amber-700 transition-all shadow-xl shadow-amber-200">
                    Configurar Contrato
                </Link>
            </div>

            <!-- Empty State -->
            <div v-else-if="filteredInvoices.length === 0" class="bg-white rounded-[48px] p-20 text-center border border-dashed border-slate-200 shadow-inner">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-8">
                    <Receipt :size="40" class="text-slate-200" />
                </div>
                <h3 class="text-2xl font-black text-slate-900 tracking-tight mb-2">Historial vacío</h3>
                <p class="text-slate-400 font-medium max-w-sm mx-auto mb-10">Comience cargando sus facturas de electricidad para realizar el análisis de ahorro.</p>
                <button @click="openCreateModal" class="bg-slate-900 text-white px-8 py-4 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-energy-success transition-all shadow-xl shadow-slate-200">
                    Cargar Primera Factura
                </button>
            </div>

            <!-- Invoices Table -->
            <div v-else class="bg-white rounded-[40px] border border-slate-100 shadow-2xl shadow-slate-200/30 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-50 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                <th class="px-8 py-6">Factura / Fecha</th>
                                <th class="px-8 py-6">Período de Consumo</th>
                                <th class="px-8 py-6 text-center">Energía (kWh)</th>
                                <th class="px-8 py-6 text-right">Monto Total</th>
                                <th class="px-8 py-6 text-center">Estado</th>
                                <th class="px-8 py-6 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="invoice in filteredInvoices" :key="invoice.id" class="group hover:bg-slate-50/30 transition-colors">
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span class="font-black text-slate-900">#{{ invoice.invoice_number }}</span>
                                        <span class="text-[10px] font-bold text-slate-400">{{ formatDate(invoice.invoice_date) }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-3">
                                        <span class="text-sm font-bold text-slate-600">{{ formatDate(invoice.start_date) }}</span>
                                        <ArrowRight :size="12" class="text-slate-200" />
                                        <span class="text-sm font-bold text-slate-600">{{ formatDate(invoice.end_date) }}</span>
                                        <span class="px-2 py-0.5 bg-slate-100 rounded-md text-[9px] font-black text-slate-400 ml-2">
                                            {{ calculateDays(invoice.start_date, invoice.end_date) }}D
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span class="text-xl font-black text-slate-900">{{ Math.round(invoice.total_energy_consumed_kwh) }}</span>
                                    <span class="text-[10px] font-bold text-slate-300 ml-1 uppercase">kWh</span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <span class="text-xl font-black text-energy-success">${{ parseFloat(invoice.total_amount).toLocaleString('es-AR', { maximumFractionDigits: 0 }) }}</span>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <div v-if="invoice.usage_locked" class="inline-flex items-center gap-1.5 px-3 py-1 bg-energy-success/10 text-energy-success rounded-full text-[9px] font-black uppercase tracking-widest border border-energy-success/20">
                                        <CheckCircle2 :size="12" /> Calibrado
                                    </div>
                                    <div v-else class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 text-slate-400 rounded-full text-[9px] font-black uppercase tracking-widest border border-slate-200">
                                        <Activity :size="12" /> Pendiente
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                        <button @click="openEditModal(invoice)" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 hover:text-energy-consumption hover:bg-energy-consumption/5 flex items-center justify-center transition-all">
                                            <Pencil :size="16" />
                                        </button>
                                        <button @click="deleteInvoice(invoice.id)" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 hover:text-energy-critical hover:bg-energy-critical/5 flex items-center justify-center transition-all">
                                            <Trash2 :size="16" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-6 sm:p-12">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" @click="closeModal"></div>
            
            <div class="relative bg-white w-full max-w-2xl rounded-[48px] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
                <div class="absolute right-8 top-8">
                    <button @click="closeModal" class="w-10 h-10 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-slate-100 hover:text-slate-900 transition-all">
                        <X :size="20" />
                    </button>
                </div>

                <div class="px-12 pt-12 pb-8 border-b border-slate-50">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-slate-900 text-white rounded-full text-[9px] font-black uppercase tracking-widest mb-4">
                        {{ editingInvoice ? 'Modo Edición' : 'Nueva Carga' }}
                    </div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Detalle de Factura</h2>
                    <p class="text-sm text-slate-400 font-medium">{{ entity.name }} — Registre los valores del suministro.</p>
                </div>

                <form @submit.prevent="submit" class="px-12 py-10 space-y-8 max-h-[60vh] overflow-y-auto custom-scrollbar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Contract & Number -->
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Contrato de Suministro</label>
                                <select v-model="form.contract_id" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-energy-success/20 transition-all appearance-none">
                                    <option v-for="c in contracts" :key="c.id" :value="c.id">#{{ c.supply_number }} - {{ c.proveedor.name }}</option>
                                </select>
                                <p v-if="form.errors.contract_id" class="text-[10px] text-energy-critical font-bold ml-1">{{ form.errors.contract_id }}</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">N° de Factura</label>
                                <input v-model="form.invoice_number" type="text" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-black text-slate-900 focus:ring-2 focus:ring-energy-success/20 transition-all" />
                                <p v-if="form.errors.invoice_number" class="text-[10px] text-energy-critical font-bold ml-1">{{ form.errors.invoice_number }}</p>
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Fecha de Emisión</label>
                                <input v-model="form.invoice_date" type="date" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-energy-success/20 transition-all" />
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Inicia</label>
                                    <input v-model="form.start_date" type="date" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-energy-success/20 transition-all" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Finaliza</label>
                                    <input v-model="form.end_date" type="date" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-energy-success/20 transition-all" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Values -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-8 bg-slate-900 rounded-[32px] text-white">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Consumo Total (kWh)</label>
                            <div class="relative">
                                <input v-model="form.total_energy_consumed_kwh" type="number" step="0.1" class="w-full bg-slate-800 border-none rounded-2xl pl-6 pr-14 py-5 text-3xl font-black text-white focus:ring-1 focus:ring-energy-success" />
                                <span class="absolute right-6 top-1/2 -translate-y-1/2 text-xs font-black text-slate-500 uppercase">kWh</span>
                            </div>
                            <p v-if="form.errors.total_energy_consumed_kwh" class="text-[9px] text-energy-critical font-bold">{{ form.errors.total_energy_consumed_kwh }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Importe Final ($)</label>
                            <div class="relative">
                                <span class="absolute left-6 top-1/2 -translate-y-1/2 text-2xl font-black text-energy-success">$</span>
                                <input v-model="form.total_amount" type="number" step="0.1" class="w-full bg-slate-800 border-none rounded-2xl pl-12 pr-6 py-5 text-3xl font-black text-energy-success focus:ring-1 focus:ring-energy-success" />
                            </div>
                            <p v-if="form.errors.total_amount" class="text-[9px] text-energy-critical font-bold">{{ form.errors.total_amount }}</p>
                        </div>
                    </div>

                    <!-- Advanced Breakdown -->
                    <div class="space-y-6 pt-4 border-t border-slate-50">
                        <button 
                            type="button" 
                            @click="showAdvanced = !showAdvanced"
                            class="text-[10px] font-black text-slate-300 uppercase tracking-widest hover:text-slate-900 flex items-center gap-2 group transition-all"
                        >
                            <Plus v-if="!showAdvanced" :size="14" class="group-hover:rotate-90 transition-transform" />
                            <X v-else :size="14" />
                            {{ showAdvanced ? 'Ocultar Desglose de Gastos' : 'Añadir Desglose de Gastos (Opcional)' }}
                        </button>

                        <div v-if="showAdvanced" class="grid grid-cols-2 md:grid-cols-4 gap-4 animate-in slide-in-from-top-2">
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Energía</label>
                                <input v-model="form.cost_for_energy" type="number" class="w-full bg-slate-50 border-none rounded-xl p-3 text-xs font-bold" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Potencia</label>
                                <input v-model="form.cost_for_power" type="number" class="w-full bg-slate-50 border-none rounded-xl p-3 text-xs font-bold" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Impuestos</label>
                                <input v-model="form.taxes" type="number" class="w-full bg-slate-50 border-none rounded-xl p-3 text-xs font-bold" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Otros</label>
                                <input v-model="form.other_charges" type="number" class="w-full bg-slate-50 border-none rounded-xl p-3 text-xs font-bold" />
                            </div>
                        </div>
                    </div>
                </form>

                <div class="px-12 py-8 bg-slate-50 flex gap-4">
                    <button 
                        @click="submit"
                        :disabled="form.processing"
                        class="flex-1 bg-slate-900 text-white py-5 rounded-[24px] font-black text-xs uppercase tracking-widest shadow-xl shadow-slate-200 hover:bg-energy-success transition-all disabled:opacity-50"
                    >
                        {{ editingInvoice ? 'Actualizar Registro' : 'Confirmar Carga' }}
                    </button>
                    <button @click="closeModal" class="px-8 py-5 rounded-[24px] font-black text-xs uppercase tracking-widest text-slate-400 hover:bg-white transition-all">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f8fafc;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
</style>
