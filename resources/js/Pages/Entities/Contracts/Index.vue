<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    FileText, 
    Plus, 
    Zap, 
    Building2, 
    Pencil, 
    Trash2, 
    Activity, 
    MoreHorizontal,
    X,
    CheckCircle2,
    Search,
    MapPin,
    AlertCircle,
    Cpu,
    Hash,
    Calendar
} from 'lucide-vue-next';

const props = defineProps({
    contracts: Array,
    entities: Array,
    proveedores: Array,
    active_entity_id: [Number, String],
    flash: Object
});

const showModal = ref(false);
const editingContract = ref(null);

const form = useForm({
    id: null,
    entity_id: props.active_entity_id || '',
    proveedor_id: '',
    supply_number: '',
    meter_number: '',
    contract_number: '',
    rate_name: '',
    start_date: '',
    is_three_phase: false,
    contracted_power_kw_p1: 0,
    contracted_power_kw_p2: 0,
    contracted_power_kw_p3: 0,
    is_active: true,
});

const activeEntity = computed(() => {
    return props.entities.find(e => e.id === props.active_entity_id);
});

const openCreateModal = () => {
    editingContract.value = null;
    form.reset();
    form.entity_id = props.active_entity_id;
    form.is_active = props.contracts.length === 0;
    showModal.value = true;
};

const openEditModal = (contract) => {
    editingContract.value = contract;
    form.id = contract.id;
    form.entity_id = contract.entity_id;
    form.proveedor_id = contract.proveedor_id;
    form.supply_number = contract.supply_number || '';
    form.meter_number = contract.meter_number || '';
    form.contract_number = contract.contract_number || '';
    form.rate_name = contract.rate_name || '';
    form.start_date = contract.start_date || '';
    form.is_three_phase = !!contract.is_three_phase;
    form.contracted_power_kw_p1 = contract.contracted_power_kw_p1;
    form.contracted_power_kw_p2 = contract.contracted_power_kw_p2 || 0;
    form.contracted_power_kw_p3 = contract.contracted_power_kw_p3 || 0;
    form.is_active = !!contract.is_active;
    showModal.value = true;
};

const submit = () => {
    if (editingContract.value) {
        form.put(route('gestion.contracts.update', editingContract.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('gestion.contracts.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const closeModal = () => {
    showModal.value = false;
    editingContract.value = null;
    form.reset();
    form.entity_id = props.active_entity_id;
};

const toggleActive = (contract) => {
    router.patch(route('gestion.contracts.toggle', contract.id));
};

const deleteContract = (contract) => {
    if (confirm('¿Estás seguro de eliminar este contrato? Se perderá el acceso histórico a las facturas asociadas.')) {
        router.delete(route('gestion.contracts.destroy', contract.id));
    }
};

const searchQuery = ref('');
const filteredContracts = computed(() => {
    if (!searchQuery.value) return props.contracts;
    const query = searchQuery.value.toLowerCase();
    return props.contracts.filter(c => 
        c.entity.name.toLowerCase().includes(query) || 
        c.supply_number.toLowerCase().includes(query) ||
        (c.meter_number && c.meter_number.toLowerCase().includes(query)) ||
        c.proveedor.name.toLowerCase().includes(query)
    );
});

const stats = computed(() => {
    return {
        total: props.contracts.length,
        active: props.contracts.filter(c => c.is_active).length,
        power: props.contracts.filter(c => c.is_active).reduce((acc, c) => acc + parseFloat(c.contracted_power_kw_p1), 0)
    };
});

const formatDate = (dateString) => {
    if (!dateString) return '--/--';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('es-AR', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(date);
};
</script>

<template>
    <MainLayout>
        <Head title="Gestión de Contratos" />

        <div class="max-w-7xl mx-auto space-y-10">
            <!-- Top Header & Search -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-energy-consumption/10 text-energy-consumption rounded-full text-[10px] font-black uppercase tracking-widest border border-energy-consumption/20">
                        <FileText :size="14" />
                        Administración Técnica
                    </div>
                    <h1 class="text-5xl font-black text-slate-900 tracking-tighter leading-none">
                        Gestión de <span class="text-energy-consumption">Contratos</span>
                    </h1>
                    <p v-if="activeEntity" class="text-lg text-slate-500 font-medium">
                        Configurando suministros para <span class="text-slate-900 font-black">{{ activeEntity.name }}</span>
                    </p>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="relative group">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-energy-consumption transition-colors" :size="18" />
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Buscar propiedad o NIU..." 
                            class="bg-white border-slate-100 rounded-2xl py-4 pl-12 pr-6 text-sm font-bold shadow-xl shadow-slate-200/50 focus:ring-2 focus:ring-energy-consumption/10 focus:border-energy-consumption/30 transition-all w-64 md:w-80"
                        />
                    </div>
                    <button 
                        @click="openCreateModal"
                        class="bg-slate-900 text-white w-14 h-14 rounded-2xl flex items-center justify-center shadow-xl shadow-slate-300 hover:bg-energy-consumption transition-all hover:-translate-y-1"
                    >
                        <Plus :size="24" stroke-width="3" />
                    </button>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/40 flex items-center gap-6">
                    <div class="w-16 h-16 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                        <FileText :size="28" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-1">Total Registrados</p>
                        <h4 class="text-3xl font-black text-slate-900 leading-none">{{ stats.total }}</h4>
                    </div>
                </div>
                <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/40 flex items-center gap-6">
                    <div class="w-16 h-16 rounded-2xl bg-energy-success/10 flex items-center justify-center text-energy-success">
                        <Activity :size="28" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-1">Suministros Activos</p>
                        <h4 class="text-3xl font-black text-slate-900 leading-none">{{ stats.active }}</h4>
                    </div>
                </div>
                <div class="bg-white p-8 rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/40 flex items-center gap-6">
                    <div class="w-16 h-16 rounded-2xl bg-energy-consumption/10 flex items-center justify-center text-energy-consumption">
                        <Zap :size="28" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-1">Potencia Total (P1)</p>
                        <h4 class="text-3xl font-black text-slate-900 leading-none">{{ stats.power.toFixed(1) }} <span class="text-sm font-bold text-slate-300">kW</span></h4>
                    </div>
                </div>
            </div>

            <!-- Contracts Grid -->
            <div v-if="filteredContracts.length === 0" class="bg-white rounded-[48px] p-20 text-center border border-dashed border-slate-200 shadow-inner">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-8">
                    <FileText :size="40" class="text-slate-200" />
                </div>
                <h3 class="text-2xl font-black text-slate-900 tracking-tight mb-2">No se encontraron contratos</h3>
                <p class="text-slate-400 font-medium max-w-sm mx-auto mb-10">Comience agregando el suministro eléctrico de sus propiedades para habilitar el análisis de eficiencia.</p>
                <button @click="openCreateModal" class="bg-slate-900 text-white px-8 py-4 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-energy-consumption transition-all shadow-xl shadow-slate-200">
                    Registrar Primer Contrato
                </button>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div 
                    v-for="contract in filteredContracts" 
                    :key="contract.id"
                    class="group bg-white rounded-[40px] border border-slate-100 shadow-xl shadow-slate-200/30 hover:shadow-2xl hover:shadow-slate-300 transition-all overflow-hidden relative flex flex-col"
                >
                    <!-- Status Header -->
                    <div class="p-8 pb-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 border border-slate-100">
                                <Building2 :size="20" />
                            </div>
                            <div>
                                <h4 class="font-black text-slate-900 leading-tight">{{ contract.entity.name }}</h4>
                                <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">{{ contract.proveedor.name }}</p>
                            </div>
                        </div>
                        <button 
                            @click="toggleActive(contract)"
                            :class="['px-3 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest transition-all', 
                                contract.is_active ? 'bg-energy-success/10 text-energy-success border border-energy-success/20' : 'bg-slate-50 text-slate-300 border border-slate-100'
                            ]"
                        >
                            {{ contract.is_active ? 'Activo' : 'Inactivo' }}
                        </button>
                    </div>

                    <!-- Tech Details -->
                    <div class="px-8 flex-1 space-y-4">
                        <div class="bg-slate-50/50 rounded-2xl p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">N° Suministro / NIU</span>
                                <span class="text-xs font-mono font-black text-slate-900">{{ contract.supply_number }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Inicio Contrato</span>
                                <span class="text-[10px] font-bold text-slate-600">{{ formatDate(contract.start_date) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Tarifa</span>
                                <span class="text-xs font-bold text-slate-600">{{ contract.rate_name }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Tecnología</span>
                                <span class="text-[9px] font-black px-2 py-0.5 bg-white text-slate-500 rounded-md border border-slate-100">
                                    {{ contract.is_three_phase ? 'Trifásico' : 'Monofásico' }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div class="text-center p-3 rounded-2xl bg-white border border-slate-100 border-b-2 border-b-energy-consumption/30">
                                <p class="text-[8px] font-black text-slate-300 uppercase mb-1">P1</p>
                                <p class="text-sm font-black text-slate-900">{{ parseFloat(contract.contracted_power_kw_p1).toFixed(1) }}<span class="text-[9px] ml-0.5">kW</span></p>
                            </div>
                            <div class="text-center p-3 rounded-2xl bg-white border border-slate-100">
                                <p class="text-[8px] font-black text-slate-300 uppercase mb-1">P2</p>
                                <p class="text-sm font-black text-slate-900">{{ parseFloat(contract.contracted_power_kw_p2 || 0).toFixed(1) }}</p>
                            </div>
                            <div class="text-center p-3 rounded-2xl bg-white border border-slate-100 text-slate-300">
                                <p class="text-[8px] font-black text-slate-300 uppercase mb-1">P3</p>
                                <p class="text-sm font-black">{{ parseFloat(contract.contracted_power_kw_p3 || 0).toFixed(1) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="p-8 pt-4 mt-auto flex gap-3">
                        <button 
                            @click="openEditModal(contract)"
                            class="flex-1 bg-slate-50 hover:bg-energy-consumption/5 text-slate-400 hover:text-energy-consumption py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all border border-slate-50 hover:border-energy-consumption/20"
                        >
                            <Pencil :size="14" class="inline mr-1" /> Editar
                        </button>
                        <button 
                            @click="deleteContract(contract)"
                            class="w-12 bg-slate-50 hover:bg-energy-critical/5 text-slate-300 hover:text-energy-critical py-3 rounded-xl transition-all border border-slate-50 hover:border-energy-critical/20"
                        >
                            <Trash2 :size="14" class="mx-auto" />
                        </button>
                    </div>
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
                        {{ editingContract ? 'Modo Edición' : 'Nuevo Registro' }}
                    </div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Especificaciones Técnicas</h2>
                    <p class="text-sm text-slate-400 font-medium">Configure los parámetros de red para el medidor.</p>
                </div>

                <form @submit.prevent="submit" class="px-12 py-10 space-y-8 max-h-[60vh] overflow-y-auto custom-scrollbar overflow-x-hidden">
                    
                    <!-- Smart Context Info -->
                    <div v-if="activeEntity && !editingContract" class="p-4 bg-emerald-50 rounded-3xl border border-emerald-100 flex items-center gap-4 text-emerald-900">
                        <div class="w-12 h-12 bg-emerald-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-200">
                            <Building2 :size="24" />
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest leading-none mb-1">Propiedad Seleccionada</p>
                            <h4 class="text-sm font-black tracking-tight">{{ activeEntity.name }}</h4>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Entity & Provider -->
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Propiedad / Entidad</label>
                                <div v-if="activeEntity && !editingContract" class="w-full bg-slate-100 border-none rounded-2xl p-4 text-sm font-black text-slate-400 cursor-not-allowed">
                                    {{ activeEntity.name }}
                                </div>
                                <select v-else v-model="form.entity_id" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-energy-consumption/20 transition-all appearance-none">
                                    <option value="">Seleccionar entidad...</option>
                                    <option v-for="e in entities" :key="e.id" :value="e.id">{{ e.name }}</option>
                                </select>
                                <p v-if="form.errors.entity_id" class="text-[10px] text-energy-critical font-bold ml-1">{{ form.errors.entity_id }}</p>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between ml-1">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Distribuidora Energética</label>
                                    <span v-if="activeEntity" class="text-[8px] font-black text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded uppercase tracking-tighter">Provincial</span>
                                </div>
                                <select v-model="form.proveedor_id" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-energy-consumption/20 transition-all appearance-none">
                                    <option value="">Seleccionar proveedor...</option>
                                    <option v-for="p in proveedores" :key="p.id" :value="p.id">{{ p.name }}</option>
                                </select>
                                <p v-if="form.errors.proveedor_id" class="text-[10px] text-energy-critical font-bold ml-1">{{ form.errors.proveedor_id }}</p>
                            </div>
                        </div>

                        <!-- ID Fields -->
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 flex items-center gap-1"><Hash :size="10" /> N° de Suministro (NIU)</label>
                                <input v-model="form.supply_number" type="text" placeholder="Ej: 0718220" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-black text-slate-900 focus:ring-2 focus:ring-energy-consumption/20 transition-all" />
                                <p v-if="form.errors.supply_number" class="text-[10px] text-energy-critical font-bold ml-1">{{ form.errors.supply_number }}</p>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 flex items-center gap-1"><Cpu :size="10" /> N° de Serie Medidor</label>
                                <input v-model="form.meter_number" type="text" placeholder="Ej: 9618495" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-black text-slate-900 focus:ring-2 focus:ring-energy-consumption/20 transition-all" />
                            </div>
                        </div>
                    </div>

                    <!-- Contract Info -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                         <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 flex items-center gap-1"><Calendar :size="10" /> Fecha Inicio</label>
                            <input v-model="form.start_date" type="date" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-xs font-black text-slate-900 focus:ring-2 focus:ring-energy-consumption/20 transition-all" />
                        </div>
                         <div class="space-y-2 md:col-span-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">ID Contrato</label>
                            <input v-model="form.contract_number" type="text" placeholder="Ej: 36697" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-black text-slate-900 focus:ring-2 focus:ring-energy-consumption/20 transition-all" />
                        </div>
                         <div class="space-y-2 md:col-span-1">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nombre Tarifa</label>
                            <input v-model="form.rate_name" type="text" placeholder="Ej: T1-R1" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-black text-slate-900 focus:ring-2 focus:ring-energy-consumption/20 transition-all" />
                        </div>
                    </div>

                    <!-- Technical Panel -->
                    <div class="p-8 bg-slate-900 rounded-[32px] text-white space-y-8">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black uppercase tracking-widest flex items-center gap-2">
                                <Zap :size="16" />
                                Potencias Contratadas (kW)
                            </h4>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 group-hover:text-white transition-colors">Conexión Trifásica</span>
                                <input type="checkbox" v-model="form.is_three_phase" class="hidden" />
                                <div :class="['w-10 h-5 rounded-full relative transition-colors', form.is_three_phase ? 'bg-energy-consumption' : 'bg-slate-700']">
                                    <div :class="['absolute top-1 w-3 h-3 bg-white rounded-full transition-all', form.is_three_phase ? 'left-6' : 'left-1']"></div>
                                </div>
                            </label>
                        </div>

                        <div class="grid grid-cols-3 gap-6">
                            <div class="space-y-2">
                                <label class="text-[8px] font-black text-slate-500 uppercase tracking-widest text-center block">Pico (P1)</label>
                                <input v-model="form.contracted_power_kw_p1" type="number" step="0.1" class="w-full bg-slate-800 border-none rounded-xl p-3 text-center text-sm font-black text-white focus:ring-1 focus:ring-energy-consumption" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-[8px] font-black text-slate-500 uppercase tracking-widest text-center block">Valle (P2)</label>
                                <input v-model="form.contracted_power_kw_p2" :disabled="!form.is_three_phase" type="number" step="0.1" :class="['w-full border-none rounded-xl p-3 text-center text-sm font-black transition-all', form.is_three_phase ? 'bg-slate-800 text-white focus:ring-1 focus:ring-energy-consumption' : 'bg-slate-900 text-slate-700 cursor-not-allowed']" />
                            </div>
                            <div class="space-y-2">
                                <label class="text-[8px] font-black text-slate-500 uppercase tracking-widest text-center block">Resto (P3)</label>
                                <input v-model="form.contracted_power_kw_p3" :disabled="!form.is_three_phase" type="number" step="0.1" :class="['w-full border-none rounded-xl p-3 text-center text-sm font-black transition-all', form.is_three_phase ? 'bg-slate-800 text-white focus:ring-1 focus:ring-energy-consumption' : 'bg-slate-900 text-slate-700 cursor-not-allowed']" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-3xl">
                        <input type="checkbox" v-model="form.is_active" class="w-5 h-5 rounded-lg border-slate-200 text-energy-consumption focus:ring-energy-consumption/20" />
                        <span class="text-xs font-bold text-slate-700">Contrato activo actualmente (para proyecciones)</span>
                    </div>
                </form>

                <div class="px-12 py-8 bg-slate-50 flex gap-4">
                    <button 
                        @click="submit"
                        :disabled="form.processing"
                        class="flex-1 bg-slate-900 text-white py-5 rounded-[24px] font-black text-xs uppercase tracking-widest shadow-xl shadow-slate-200 hover:bg-energy-consumption transition-all disabled:opacity-50"
                    >
                        {{ editingContract ? 'Aplicar Cambios' : 'Confirmar Registro' }}
                    </button>
                    <button @click="closeModal" class="px-8 py-5 rounded-[24px] font-black text-xs uppercase tracking-widest text-slate-400 hover:bg-white transition-all text-[10px]">
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
