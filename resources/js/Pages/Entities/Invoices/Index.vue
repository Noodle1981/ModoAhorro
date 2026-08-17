<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    Receipt, 
    Plus, 
    Calendar, 
    Activity, 
     
    ArrowRight, 
    Pencil, 
    Trash2, 
    X,
    CheckCircle2,
    Search,
    AlertTriangle
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    contracts: Array,
    invoices: Array,
    flash: Object
});

const themeColors = computed(() => {
    const type = props.entity?.type;
    if (type === 'comercio') {
        return {
            text: 'text-purple-600',
            bg: 'bg-purple-600',
            bgLight: 'bg-purple-600/10',
            borderLight: 'border-purple-600/20',
            hoverBg: 'hover:bg-purple-600',
            focusRing: 'focus:ring-purple-600/10',
            focusBorder: 'focus:border-purple-600/30',
            focusText: 'group-focus-within:text-purple-600',
            checkboxText: 'text-purple-600 focus:ring-purple-600/20',
            focusRingForm: 'focus:ring-purple-600/20',
            focusRingInput: 'focus:ring-purple-600',
            focusBorderInput: 'focus:border-purple-600/50',
            textSoftAccent: 'text-purple-600 bg-purple-50',
            bgSoftLight: 'bg-purple-600/5',
            hoverText: 'hover:text-purple-600'
        };
    }
    if (type === 'oficina') {
        return {
            text: 'text-blue-600',
            bg: 'bg-blue-600',
            bgLight: 'bg-blue-600/10',
            borderLight: 'border-blue-600/20',
            hoverBg: 'hover:bg-blue-600',
            focusRing: 'focus:ring-blue-600/10',
            focusBorder: 'focus:border-blue-600/30',
            focusText: 'group-focus-within:text-blue-600',
            checkboxText: 'text-blue-600 focus:ring-blue-600/20',
            focusRingForm: 'focus:ring-blue-600/20',
            focusRingInput: 'focus:ring-blue-600',
            focusBorderInput: 'focus:border-blue-600/50',
            textSoftAccent: 'text-blue-600 bg-blue-50',
            bgSoftLight: 'bg-blue-600/5',
            hoverText: 'hover:text-blue-600'
        };
    }
    // Default / hogar
    return {
        text: 'text-emerald-600',
        bg: 'bg-emerald-600',
        bgLight: 'bg-emerald-600/10',
        borderLight: 'border-emerald-600/20',
        hoverBg: 'hover:bg-emerald-600',
        focusRing: 'focus:ring-emerald-600/10',
        focusBorder: 'focus:border-emerald-600/30',
        focusText: 'group-focus-within:text-emerald-600',
        checkboxText: 'text-emerald-600 focus:ring-emerald-600/20',
        focusRingForm: 'focus:ring-emerald-600/20',
        focusRingInput: 'focus:ring-emerald-600',
        focusBorderInput: 'focus:border-emerald-600/50',
        textSoftAccent: 'text-emerald-600 bg-emerald-50',
        bgSoftLight: 'bg-emerald-600/5',
        hoverText: 'hover:text-emerald-600'
    };
});

const showModal = ref(false);
const showDeleteModal = ref(false);
const showAdvanced = ref(false);
const editingInvoice = ref(null);
const invoiceToDelete = ref(null);
const isGuidedInstallment2 = ref(false);

const form = useForm({
    id: null,
    contract_id: '',
    invoice_number: '',
    tariff: '',
    invoice_date: '',
    issue_date: '',
    start_date: '',
    end_date: '',
    total_energy_consumed_kwh: '',
    total_amount: '',
    cost_for_energy: 0,
    cost_for_power: 0,
    taxes: 0,
    other_charges: 0,
    installment_number: 1,
    total_installments: 2,
    bimonthly_consumption_kwh: '',
});

// Setup default contract if only one exists
if (props.contracts.length === 1) {
    form.contract_id = props.contracts[0].id;
}

// Auto-calculate total amount based on breakdown
watch(() => [
    form.cost_for_energy, 
    form.cost_for_power, 
    form.taxes, 
    form.other_charges
], () => {
    const total = (parseFloat(form.cost_for_energy) || 0) + 
                  (parseFloat(form.cost_for_power) || 0) + 
                  (parseFloat(form.taxes) || 0) + 
                  (parseFloat(form.other_charges) || 0);
    
    // Only update total_amount if the breakdown has values
    // to avoid overwriting manual total when starting a new carga
    if (total > 0) {
        form.total_amount = Number(total.toFixed(2));
    }
});

const stats = computed(() => {
    if (props.invoices.length === 0) {
        return { 
            avgAmount: 0, 
            avgConsumption: 0, 
            maxConsumption: 0, 
            minConsumption: 0, 
            count: 0 
        };
    }
    
    const consumptions = props.invoices.map(inv => parseFloat(inv.total_energy_consumed_kwh) || 0);
    const amounts = props.invoices.map(inv => parseFloat(inv.total_amount) || 0);
    
    const totalConsumption = consumptions.reduce((acc, val) => acc + val, 0);
    const totalAmount = amounts.reduce((acc, val) => acc + val, 0);
    
    return {
        avgAmount: totalAmount / props.invoices.length,
        avgConsumption: totalConsumption / props.invoices.length,
        maxConsumption: Math.max(...consumptions),
        minConsumption: Math.min(...consumptions),
        count: props.invoices.length
    };
});

const toISODate = (dateString) => {
    if (!dateString) return '';
    // Split by T to get only the date part and avoid timezone shifting
    return dateString.split('T')[0];
};

const openCreateModal = () => {
    editingInvoice.value = null;
    isGuidedInstallment2.value = false;
    form.reset();
    if (props.contracts.length === 1) form.contract_id = props.contracts[0].id;
    showModal.value = true;
};

const openEditModal = (invoice) => {
    editingInvoice.value = invoice;
    isGuidedInstallment2.value = false;
    form.id = invoice.id;
    form.contract_id = invoice.contract_id;
    form.invoice_number = invoice.invoice_number;
    form.tariff = invoice.tariff || '';
    form.invoice_date = toISODate(invoice.invoice_date);
    form.issue_date = toISODate(invoice.issue_date || invoice.invoice_date);
    form.start_date = toISODate(invoice.start_date);
    form.end_date = toISODate(invoice.end_date);
    form.total_energy_consumed_kwh = invoice.total_energy_consumed_kwh;
    form.total_amount = invoice.total_amount;
    form.cost_for_energy = invoice.cost_for_energy || 0;
    form.cost_for_power = invoice.cost_for_power || 0;
    form.taxes = invoice.taxes || 0;
    form.other_charges = invoice.other_charges || 0;
    form.installment_number = invoice.installment_number || 1;
    form.total_installments = invoice.total_installments || 2;
    form.bimonthly_consumption_kwh = invoice.bimonthly_consumption_kwh || '';
    showModal.value = true;
};

const submit = () => {
    // Clear previous errors
    form.clearErrors();

    // Ensure invoice_date is always synced with issue_date
    if (!form.invoice_date) form.invoice_date = form.issue_date;
    if (!form.issue_date) form.issue_date = form.invoice_date;

    // Frontend validations
    const start = form.start_date ? new Date(form.start_date) : null;
    const end = form.end_date ? new Date(form.end_date) : null;
    const issue = form.issue_date ? new Date(form.issue_date) : null;

    if (issue && end && issue < end) {
        form.setError('issue_date', 'La fecha de emisión debe ser posterior al cierre del período.');
        return;
    }

    if (issue && start && (issue.getFullYear() - start.getFullYear() > 1)) {
        form.setError('issue_date', 'El año de la factura no puede ser más de un año posterior al período de consumo.');
        return;
    }

    if (issue && start && issue < start) {
        form.setError('issue_date', 'La fecha de emisión no puede ser anterior al inicio del período.');
        return;
    }

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

const deleteInvoice = (invoice) => {
    invoiceToDelete.value = invoice;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!invoiceToDelete.value) return;
    
    router.delete(route('gestion.invoices.destroy', invoiceToDelete.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            invoiceToDelete.value = null;
        }
    });
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    // Parse as local date by splitting the ISO string
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

const searchQuery = ref('');
const filteredInvoices = computed(() => {
    if (!searchQuery.value) return props.invoices;
    const query = searchQuery.value.toLowerCase();
    return props.invoices.filter(inv => 
        inv.invoice_number.toLowerCase().includes(query) || 
        formatDate(inv.start_date).includes(query)
    );
});

const hasPartner = (invoice) => {
    if (invoice.installment_number !== 1) return true;
    return props.invoices.some(inv => 
        inv.contract_id === invoice.contract_id &&
        inv.start_date === invoice.start_date &&
        inv.end_date === invoice.end_date &&
        inv.installment_number === 2
    );
};

const openCreateInstallment2Modal = (invoice) => {
    editingInvoice.value = null;
    isGuidedInstallment2.value = true;
    form.reset();
    form.contract_id = invoice.contract_id;
    form.tariff = invoice.tariff || '';
    form.start_date = toISODate(invoice.start_date);
    form.end_date = toISODate(invoice.end_date);
    form.installment_number = 2;
    form.total_installments = 2;
    form.bimonthly_consumption_kwh = invoice.bimonthly_consumption_kwh || '';
    
    // Ensure these are empty for the new entry
    form.invoice_number = '';
    form.issue_date = '';
    form.total_energy_consumed_kwh = '';
    form.total_amount = '';
    form.cost_for_energy = 0;
    form.taxes = 0;
    
    showModal.value = true;
};
</script>

<template>
    <MainLayout>
        <Head title="Gestión de Facturas" />

        <div class="h-full flex flex-col gap-3.5 max-w-7xl mx-auto w-full min-h-0 overflow-hidden">
            <!-- Action Toolbar (Search & Add Invoice) -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
                <div class="text-sm font-medium text-slate-500">
                    <span>Control de consumos mensuales para <strong class="text-slate-900 font-bold">{{ entity.name }}</strong></span>
                </div>
                
                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <div class="relative group flex-1 sm:flex-initial">
                        <Search :class="['absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 transition-colors', themeColors.focusText]" :size="16" />
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Buscar por número o fecha..." 
                            :class="['bg-white border-slate-200/80 rounded-2xl py-2 pl-10 pr-4 text-xs font-bold shadow-sm focus:ring-2 transition-all w-full sm:w-72', themeColors.focusRing, themeColors.focusBorder]"
                        />
                    </div>
                    <button 
                        v-if="contracts.length > 0"
                        @click="openCreateModal"
                        :class="['bg-slate-900 text-white px-5 py-2 rounded-2xl flex items-center gap-2 font-black text-xs uppercase tracking-wider shadow-sm transition-all hover:scale-105 active:scale-95 shrink-0 cursor-pointer', themeColors.hoverBg]"
                    >
                        <Plus :size="16" stroke-width="3" />
                        <span>Cargar Factura</span>
                    </button>
                </div>
            </div>

            <!-- Stats Bar (Compact & Fixed) -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 shrink-0">
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Gasto Promedio</p>
                    <h4 class="text-xl font-black text-slate-900 leading-none">${{ stats.avgAmount.toLocaleString('es-AR', { maximumFractionDigits: 0 }) }}</h4>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Consumo Promedio</p>
                    <h4 class="text-xl font-black text-slate-900 leading-none">{{ stats.avgConsumption.toLocaleString('es-AR', { maximumFractionDigits: 0 }) }} <span class="text-xs font-bold text-slate-400">kWh</span></h4>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm border-b-2 border-b-energy-success">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Mínimo Facturado</p>
                    <h4 class="text-xl font-black text-energy-success leading-none">{{ stats.minConsumption.toLocaleString('es-AR', { maximumFractionDigits: 0 }) }} <span class="text-xs font-bold text-slate-300">kWh</span></h4>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm border-b-2 border-b-energy-critical">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Máximo Facturado</p>
                    <h4 class="text-xl font-black text-energy-critical leading-none">{{ stats.maxConsumption.toLocaleString('es-AR', { maximumFractionDigits: 0 }) }} <span class="text-xs font-bold text-slate-300">kWh</span></h4>
                </div>
            </div>

            <!-- No Contracts Warning -->
            <div v-if="contracts.length === 0" class="flex-1 bg-amber-50 rounded-3xl p-12 text-center border border-dashed border-amber-200 flex flex-col items-center justify-center">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                    <AlertTriangle :size="36" class="text-amber-500" />
                </div>
                <h3 class="text-xl font-black text-amber-900 tracking-tight mb-2">Falta Contrato de Suministro</h3>
                <p class="text-amber-700/70 font-medium max-w-sm mx-auto mb-8 text-xs">No puede cargar facturas sin antes registrar un contrato o medidor activo para esta entidad.</p>
                <Link :href="route('gestion.contracts')" class="bg-amber-600 text-white px-6 py-3 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-amber-700 transition-all shadow-md">
                    Configurar Contrato
                </Link>
            </div>

            <!-- Empty State -->
            <div v-else-if="filteredInvoices.length === 0" class="flex-1 bg-white rounded-3xl p-12 text-center border border-dashed border-slate-200 shadow-inner flex flex-col items-center justify-center">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <Receipt :size="36" class="text-slate-300" />
                </div>
                <h3 class="text-xl font-black text-slate-900 tracking-tight mb-2">Historial vacío</h3>
                <p class="text-slate-400 font-medium max-w-sm mx-auto mb-8 text-xs">Comience cargando sus facturas de electricidad para realizar el análisis de ahorro.</p>
                <button @click="openCreateModal" :class="['bg-slate-900 text-white px-6 py-3 rounded-2xl font-black uppercase tracking-widest text-xs transition-all shadow-md', themeColors.hoverBg]">
                    Cargar Primera Factura
                </button>
            </div>

            <!-- Invoices Table (Scrollable Container) -->
            <div v-else class="flex-1 min-h-0 bg-white rounded-3xl border border-slate-100 shadow-xl overflow-hidden flex flex-col">
                <div class="flex-1 min-h-0 overflow-y-auto overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-slate-50 border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest z-10">
                            <tr>
                                <th class="px-6 py-3.5">Factura / Fecha</th>
                                <th class="px-6 py-3.5">Período de Consumo</th>
                                <th class="px-6 py-3.5 text-center">Energía (kWh)</th>
                                <th class="px-6 py-3.5 text-right">Monto Total</th>
                                <th class="px-6 py-3.5 text-center">Estado</th>
                                <th class="px-6 py-3.5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="invoice in filteredInvoices" :key="invoice.id" class="group hover:bg-slate-50/40 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="font-black text-slate-900 text-sm">#{{ invoice.invoice_number }}</span>
                                        <span class="text-[10px] font-bold text-slate-400">{{ formatDate(invoice.invoice_date) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                                            <Calendar :size="12" class="text-slate-300 shrink-0" />
                                            <div class="flex items-center gap-1.5 text-xs font-bold text-slate-700 whitespace-nowrap shrink-0">
                                                <span>{{ formatDate(invoice.start_date) }}</span>
                                                <ArrowRight :size="10" class="text-slate-300 shrink-0" />
                                                <span>{{ formatDate(invoice.end_date) }}</span>
                                            </div>
                                            <span class="px-2 py-0.5 bg-slate-900/5 rounded-md text-[8px] font-black text-slate-500 uppercase tracking-tight whitespace-nowrap shrink-0 inline-flex items-center leading-none">
                                                {{ calculateDays(invoice.start_date, invoice.end_date) }} días
                                            </span>
                                        </div>
                                        <div v-if="invoice.installment_number || invoice.tariff" class="flex items-center gap-3">
                                            <div v-if="invoice.installment_number" class="flex items-center gap-1">
                                                <div class="w-1.5 h-1.5 rounded-full bg-energy-success/40"></div>
                                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Cuota {{ invoice.installment_number }}/{{ invoice.total_installments }}</span>
                                            </div>
                                            <div v-if="invoice.tariff" class="flex items-center gap-1">
                                                <div class="w-1.5 h-1.5 rounded-full bg-slate-200"></div>
                                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">{{ invoice.tariff }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span class="text-base font-black text-slate-900">{{ Math.round(invoice.total_energy_consumed_kwh) }}</span>
                                    <span class="text-[10px] font-bold text-slate-300 ml-1 uppercase">kWh</span>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <span class="text-base font-black text-energy-success">${{ parseFloat(invoice.total_amount).toLocaleString('es-AR', { maximumFractionDigits: 0 }) }}</span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div v-if="invoice.usage_locked" class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-energy-success/10 text-energy-success rounded-full text-[8px] font-black uppercase tracking-widest border border-energy-success/20">
                                        <CheckCircle2 :size="11" /> Calibrado
                                    </div>
                                    <div v-else class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-slate-100 text-slate-400 rounded-full text-[8px] font-black uppercase tracking-widest border border-slate-200">
                                        <Activity :size="11" /> Pendiente
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <button 
                                            v-if="invoice.installment_number === 1 && !hasPartner(invoice)"
                                            @click="openCreateInstallment2Modal(invoice)"
                                            :class="['h-8 px-3 rounded-lg flex items-center gap-1.5 transition-all text-[9px] font-black uppercase tracking-wider', themeColors.bgLight, themeColors.text, themeColors.hoverBg, 'hover:text-white']"
                                            title="Cargar Cuota 2 para este periodo"
                                        >
                                            <Plus :size="12" stroke-width="3" />
                                            Cargar Cuota 2
                                        </button>
                                        <div class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-all">
                                            <button @click="openEditModal(invoice)" :class="['w-8 h-8 rounded-lg bg-slate-50 text-slate-400 flex items-center justify-center transition-all', themeColors.hoverText, themeColors.bgSoftLight]">
                                                <Pencil :size="14" />
                                            </button>
                                            <button @click="deleteInvoice(invoice)" class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 hover:text-energy-critical hover:bg-energy-critical/5 flex items-center justify-center transition-all">
                                                <Trash2 :size="14" />
                                            </button>
                                        </div>
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
                                <select v-model="form.contract_id" :class="['w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-slate-900 focus:ring-2 transition-all appearance-none', themeColors.focusRingForm]">
                                    <option v-for="c in contracts" :key="c.id" :value="c.id">#{{ c.supply_number }} - {{ c.proveedor.name }}</option>
                                </select>
                                <p v-if="form.errors.contract_id" class="text-[10px] text-energy-critical font-bold ml-1">{{ form.errors.contract_id }}</p>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">N° de Factura</label>
                                    <input v-model="form.invoice_number" type="text" :class="['w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-black text-slate-900 focus:ring-2 transition-all', themeColors.focusRingForm]" />
                                    <p v-if="form.errors.invoice_number" class="text-[10px] text-energy-critical font-bold ml-1">{{ form.errors.invoice_number }}</p>
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tarifa</label>
                                    <input v-model="form.tariff" type="text" placeholder="Ej: T1R2" :class="['w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-black text-slate-900 focus:ring-2 transition-all uppercase', themeColors.focusRingForm]" />
                                    <p v-if="form.errors.tariff" class="text-[10px] text-energy-critical font-bold ml-1">{{ form.errors.tariff }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Fecha de Emisión</label>
                                <input v-model="form.issue_date" type="date" :class="['w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-slate-900 focus:ring-2 transition-all', themeColors.focusRingForm]" />
                                <p v-if="form.errors.issue_date || form.errors.invoice_date" class="text-[10px] text-energy-critical font-bold ml-1">{{ form.errors.issue_date || form.errors.invoice_date }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Inicia</label>
                                    <input v-model="form.start_date" type="date" :class="['w-full bg-slate-50 border-none rounded-2xl p-4 text-xs font-bold text-slate-900 focus:ring-2 transition-all', themeColors.focusRingForm]" />
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Finaliza</label>
                                    <input v-model="form.end_date" type="date" :class="['w-full bg-slate-50 border-none rounded-2xl p-4 text-xs font-bold text-slate-900 focus:ring-2 transition-all', themeColors.focusRingForm]" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Primary Financial Card -->
                    <div class="p-8 bg-slate-900 rounded-[32px] text-white space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Total Energía ($)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-600">$</span>
                                    <input v-model="form.cost_for_energy" type="number" step="0.01" :class="['w-full bg-slate-800 border-none rounded-2xl pl-8 pr-4 py-4 text-xl font-black text-white focus:ring-1 transition-all', themeColors.focusRingInput]" />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Impuestos ($)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-600">$</span>
                                    <input v-model="form.taxes" type="number" step="0.01" :class="['w-full bg-slate-800 border-none rounded-2xl pl-8 pr-4 py-4 text-xl font-black text-white focus:ring-1 transition-all', themeColors.focusRingInput]" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="pt-8 border-t border-slate-800">
                            <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Importe Final (Total a Pagar)</label>
                            <div class="relative mt-2">
                                <span :class="['absolute left-6 top-1/2 -translate-y-1/2 text-3xl font-black', themeColors.text]">$</span>
                                <input v-model="form.total_amount" type="number" step="0.01" :class="['w-full bg-slate-800 border-none rounded-2xl pl-14 pr-6 py-6 text-4xl font-black focus:ring-1 transition-all shadow-2xl shadow-emerald-500/5', themeColors.text, themeColors.focusRingInput]" />
                            </div>
                            <p v-if="form.errors.total_amount" class="text-[9px] text-energy-critical font-bold mt-2">{{ form.errors.total_amount }}</p>
                        </div>
                    </div>

                    <!-- Consumption Card -->
                    <div class="p-8 bg-slate-50 rounded-[32px] border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Consumo Total del Período</label>
                            <p class="text-xs text-slate-400 font-medium ml-1">Valor expresado en kilovatios hora.</p>
                        </div>
                        <div class="relative w-full md:w-64">
                            <input v-model="form.total_energy_consumed_kwh" type="number" step="0.1" :class="['w-full bg-white border-slate-200 rounded-2xl pl-6 pr-14 py-5 text-3xl font-black text-slate-900 focus:ring-2 transition-all shadow-sm', themeColors.focusRingForm, themeColors.focusBorderInput]" />
                            <span class="absolute right-6 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-300 uppercase">kWh</span>
                        </div>
                        <p v-if="form.errors.total_energy_consumed_kwh" class="text-[9px] text-energy-critical font-bold">{{ form.errors.total_energy_consumed_kwh }}</p>
                    </div>

                    <!-- Bimonthly Info (Argentina Specific) -->
                    <div class="p-8 bg-slate-50/50 rounded-[32px] border border-dashed border-slate-200">
                        <div class="flex items-center gap-2 mb-6">
                            <Calendar :size="16" class="text-slate-400" />
                            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Modalidad Cuotas / Bimestre</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">N° Cuota</label>
                                <select 
                                    v-model="form.installment_number" 
                                    :disabled="!editingInvoice"
                                    class="w-full bg-white border-slate-100 rounded-xl p-3 text-sm font-bold text-slate-900 focus:ring-2 transition-all disabled:opacity-60 disabled:bg-slate-50"
                                >
                                    <option :value="1">Cuota 1</option>
                                    <option :value="2">Cuota 2</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Total Cuotas</label>
                                <input v-model="form.total_installments" type="number" disabled class="w-full bg-slate-50 border-slate-100 rounded-xl p-3 text-sm font-bold text-slate-400 cursor-not-allowed" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Consumo Bimestre Real</label>
                                <div class="relative">
                                    <input v-model="form.bimonthly_consumption_kwh" type="number" step="0.1" placeholder="Consumo total" class="w-full bg-white border-slate-100 rounded-xl pl-3 pr-10 py-3 text-sm font-bold text-slate-900 group-focus:ring-2 transition-all" />
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[9px] font-black text-slate-300">kWh</span>
                                </div>
                            </div>
                        </div>
                        <p class="text-[9px] text-slate-400 font-medium mt-4 italic">* Complete si su factura es mensual pero la medición es bimestral (Ej: Edesur/Edenor).</p>
                    </div>

                    <!-- Secondary Charges -->
                    <div class="space-y-6 pt-4 border-t border-slate-50">
                        <button 
                            type="button" 
                            @click="showAdvanced = !showAdvanced"
                            class="text-[10px] font-black text-slate-300 uppercase tracking-widest hover:text-slate-900 flex items-center gap-2 group transition-all"
                        >
                            <Plus v-if="!showAdvanced" :size="14" class="group-hover:rotate-90 transition-transform" />
                            <X v-else :size="14" />
                            {{ showAdvanced ? 'Otros Cargos (Potencia y Cargos Adicionales)' : 'Ocultar Cargos Secundarios' }}
                        </button>

                        <div v-if="showAdvanced" class="grid grid-cols-1 md:grid-cols-2 gap-6 animate-in slide-in-from-top-2">
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Costo por Potencia</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-300">$</span>
                                    <input v-model="form.cost_for_power" type="number" step="0.01" class="w-full bg-slate-50 border-none rounded-xl pl-8 pr-4 py-3 text-sm font-bold text-slate-900 group-focus:ring-2 group-focus:ring-slate-100 transition-all" />
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Otros Cargos / Ajustes</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-300">$</span>
                                    <input v-model="form.other_charges" type="number" step="0.01" class="w-full bg-slate-50 border-none rounded-xl pl-8 pr-4 py-3 text-sm font-bold text-slate-900 group-focus:ring-2 group-focus:ring-slate-100 transition-all" />
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="px-12 py-8 bg-slate-50 flex gap-4">
                    <button 
                        @click="submit"
                        :disabled="form.processing"
                        :class="['flex-1 bg-slate-900 text-white py-5 rounded-[24px] font-black text-xs uppercase tracking-widest shadow-xl shadow-slate-200 transition-all disabled:opacity-50', themeColors.hoverBg]"
                    >
                        {{ editingInvoice ? 'Actualizar Registro' : 'Confirmar Carga' }}
                    </button>
                    <button @click="closeModal" class="px-8 py-5 rounded-[24px] font-black text-xs uppercase tracking-widest text-slate-400 hover:bg-white transition-all">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-xl animate-in fade-in duration-300" @click="showDeleteModal = false"></div>
            
            <div class="relative bg-white rounded-[40px] shadow-2xl w-full max-w-lg overflow-hidden animate-in zoom-in-95 duration-300 border border-slate-100">
                <div class="p-12 text-center">
                    <div class="mb-8 flex justify-center">
                        <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center relative">
                            <AlertTriangle :size="48" class="text-energy-critical relative z-10" />
                            <div class="absolute inset-0 bg-red-200 rounded-full animate-ping opacity-20"></div>
                        </div>
                    </div>
                    
                    <h2 class="text-2xl font-black text-slate-900 mb-4">¿Eliminar esta factura?</h2>
                    <p class="text-slate-400 font-medium mb-8">Esta acción no se puede deshacer. Los datos de consumo asociados se borrarán permanentemente del sistema.</p>
                    
                    <div v-if="invoiceToDelete" class="bg-slate-50 rounded-3xl p-6 mb-8 text-left border border-slate-100">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Factura N°</span>
                            <span class="text-xs font-bold text-slate-900">{{ invoiceToDelete.invoice_number }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Periodo</span>
                            <span class="text-xs font-bold text-slate-900">{{ formatDate(invoiceToDelete.start_date) }} - {{ formatDate(invoiceToDelete.end_date) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Importe Total</span>
                            <span class="text-xs font-black text-energy-critical">${{ parseFloat(invoiceToDelete.total_amount).toLocaleString('es-AR') }}</span>
                        </div>
                    </div>
                    
                    <div class="flex flex-col gap-4">
                        <button 
                            @click="confirmDelete"
                            class="w-full bg-slate-900 text-white py-5 rounded-3xl font-black text-xs uppercase tracking-widest hover:bg-energy-critical transition-all shadow-xl shadow-red-200/20"
                        >
                            Eliminar Permanentemente
                        </button>
                        <button 
                            @click="showDeleteModal = false"
                            class="w-full py-5 rounded-3xl font-black text-xs uppercase tracking-widest text-slate-400 hover:bg-slate-50 transition-all"
                        >
                            Mantener Registro
                        </button>
                    </div>
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
