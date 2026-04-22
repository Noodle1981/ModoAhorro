<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    Settings2, 
    Zap, 
    ArrowLeft, 
    CheckCircle2, 
    Save,
    Clock, 
    Activity,
    Info,
    LayoutGrid,
    ThermometerSun,
    ShieldCheck,
    Gamepad2,
    DoorOpen,
    Lock,
    AlertCircle,
    ChevronDown,
    ChevronUp,
    Loader2
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    invoice: Object,
    tanks: Array,
    period: Object,
    is_complete: Boolean
});

// Inicializamos el formulario con los datos de los tanques
const form = useForm({
    invoice_id: props.invoice.id,
    usages: props.tanks.reduce((acc, tank) => {
        tank.items.forEach(item => {
            acc[item.id] = {
                avg_daily_use_hours: item.usage.avg_daily_use_hours || 0,
                usage_frequency: item.usage.usage_frequency || 'diario',
                is_standby: item.usage.is_standby || false,
                nominal_power_w: item.nominal_power_w
            };
        });
        return acc;
    }, {}),
    lock_period: true,
    notes: ''
});

// Factores de frecuencia (Legacy Sync)
const frequencyFactors = {
    'diario': 1.0,
    'diariamente': 1.0,
    'casi_frecuentemente': 0.85,
    'frecuentemente': 0.60,
    'ocasionalmente': 0.30,
    'raramente': 0.10,
    'nunca': 0.0
};

// Cálculo reactivo de kWh por equipo
const calculateKwh = (eqId) => {
    const data = form.usages[eqId];
    if (!data) return 0;
    
    const powerKw = data.nominal_power_w / 1000;
    const factor = frequencyFactors[data.usage_frequency] || 0.60;
    const effectiveDays = props.period.days * factor;
    
    return powerKw * data.avg_daily_use_hours * effectiveDays;
};

// Totales reactivos
const totalCalculatedKwh = computed(() => {
    return Object.keys(form.usages).reduce((sum, id) => sum + calculateKwh(id), 0);
});

const tankTotals = computed(() => {
    return props.tanks.map(tank => {
        const kwh = tank.items.reduce((sum, item) => sum + calculateKwh(item.id), 0);
        return { ...tank, current_kwh: kwh };
    });
});

const diffPercentage = computed(() => {
    const invoiced = props.invoice.total_energy_consumed_kwh || 1;
    return ((totalCalculatedKwh.value - invoiced) / invoiced) * 100;
});

const submit = () => {
    form.post(route('analisis.usage.save'), {
        preserveScroll: true
    });
};

const getTankIcon = (key) => {
    switch (key) {
        case 1: return ShieldCheck;
        case 2: return ThermometerSun;
        case 3: return Gamepad2;
        default: return Zap;
    }
};

const getTankColor = (key) => {
    switch (key) {
        case 1: return 'text-rose-500 bg-rose-50 border-rose-100'; // Base
        case 2: return 'text-energy-water bg-energy-water/10 border-energy-water/20'; // Clima
        case 3: return 'text-energy-solar bg-energy-solar/10 border-energy-solar/20'; // Variable
        default: return 'text-slate-500 bg-slate-50 border-slate-100';
    }
};
</script>

<template>
    <MainLayout>
        <Head title="Sintonía Fina - ModoAhorro" />

        <div class="max-w-7xl mx-auto space-y-8 pb-32">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <Link :href="route('analisis.usage')" class="flex items-center gap-2 text-slate-400 font-bold text-xs uppercase tracking-widest hover:text-slate-900 transition-colors mb-4 group">
                        <ArrowLeft :size="14" class="group-hover:-translate-x-1 transition-transform" /> Volver a Ajustes
                    </Link>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tighter leading-none">
                        Sintonía <span class="text-energy-solar">Fina</span>
                    </h1>
                    <p class="text-slate-500 font-medium">Ajusta el uso real de tus equipos para este bimestre.</p>
                </div>

                <div class="flex items-center gap-4">
                    <div class="bg-white border border-slate-100 p-4 rounded-[24px] shadow-sm">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Periodo Unificado</p>
                        <p class="text-sm font-bold text-slate-900">{{ period.days }} días de medición</p>
                    </div>
                    <div class="bg-slate-900 text-white p-4 rounded-[24px] shadow-xl shadow-slate-200/50">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Total Facturado</p>
                        <p class="text-xl font-black">{{ Math.round(invoice.total_energy_consumed_kwh) }}<span class="text-xs ml-1 opacity-50">kWh</span></p>
                    </div>
                </div>
            </div>

            <!-- Incomplete Period Warning -->
            <div v-if="!is_complete" class="bg-amber-50 border border-amber-200 rounded-[32px] p-6 flex flex-col md:flex-row items-center gap-6 shadow-sm shadow-amber-200/20">
                <div class="w-16 h-16 rounded-[24px] bg-amber-200/50 flex items-center justify-center text-amber-700 shrink-0">
                    <AlertCircle :size="32" />
                </div>
                <div class="space-y-1">
                    <h4 class="text-xl font-black text-amber-900 tracking-tight leading-none mb-1">Periodo Bimestral Incompleto</h4>
                    <p class="text-sm text-amber-700 font-medium leading-relaxed">
                        Faltan cuotas por cargar para completar este periodo de medición físico. Los kWh facturados ({{ Math.round(invoice.total_energy_consumed_kwh) }} kWh) representan solo una parte del total real. Puedes configurar el uso ahora, pero el diagnóstico final será más preciso cuando cargues el bimestre completo.
                    </p>
                </div>
                <div class="md:ml-auto">
                    <Link :href="route('gestion.invoices')" class="px-6 py-3 bg-amber-900/5 hover:bg-amber-900/10 text-amber-900 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-colors">
                        Cargar Factura
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Main Controls Area -->
                <div class="lg:col-span-8 space-y-12">
                    <div v-for="tank in tankTotals" :key="tank.key" class="space-y-6">
                        <!-- Tank Header -->
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                            <div class="flex items-center gap-4">
                                <div :class="['w-12 h-12 rounded-2xl flex items-center justify-center border', getTankColor(tank.key)]">
                                    <component :is="getTankIcon(tank.key)" :size="24" />
                                </div>
                                <div>
                                    <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ tank.label }}</h3>
                                    <p class="text-xs text-slate-400 font-medium">{{ tank.items.length }} equipos detectados</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-1">Subtotal</p>
                                <p class="text-2xl font-black text-slate-900">{{ Math.round(tank.current_kwh) }}<span class="text-xs ml-1 text-slate-400">kWh</span></p>
                            </div>
                        </div>

                        <!-- Equipment Grid -->
                        <div class="grid grid-cols-1 gap-4">
                            <div 
                                v-for="item in tank.items" 
                                :key="item.id"
                                class="bg-white rounded-[32px] border border-slate-100 shadow-sm hover:shadow-md transition-shadow p-6 flex flex-col md:flex-row items-center gap-6"
                            >
                                <!-- Eq Info -->
                                <div class="md:w-1/3 flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 shrink-0">
                                        <Zap :size="20" v-if="!item.is_standby" />
                                        <Activity :size="20" v-else class="text-rose-400" />
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-black text-slate-900 truncate tracking-tight">{{ item.name }}</h4>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase flex items-center gap-1">
                                            <DoorOpen :size="10" /> {{ item.room_name }}
                                        </p>
                                        <div class="flex items-center gap-2 mt-2">
                                            <span class="text-[9px] font-black px-2 py-0.5 bg-slate-50 border border-slate-100 rounded-full text-slate-500">{{ item.nominal_power_w }}W</span>
                                            <span v-if="item.is_validated" class="text-[9px] font-black px-2 py-0.5 bg-energy-success/10 text-energy-success rounded-full">VALIDADO</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Inputs -->
                                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-6 w-full">
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center px-1">
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Horas de Uso/Día</label>
                                            <span class="text-sm font-black text-slate-900 bg-slate-50 px-2 rounded-md">{{ form.usages[item.id].avg_daily_use_hours }}h</span>
                                        </div>
                                        <input 
                                            type="range" 
                                            v-model.number="form.usages[item.id].avg_daily_use_hours" 
                                            min="0" 
                                            max="24" 
                                            step="0.5"
                                            class="w-full h-1.5 bg-slate-100 rounded-lg appearance-none cursor-pointer accent-slate-900" 
                                        />
                                    </div>
                                    <div class="space-y-3">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Frecuencia de Uso</label>
                                        <select 
                                            v-model="form.usages[item.id].usage_frequency"
                                            @change="form.usages[item.id].avg_daily_use_hours = ($event.target.value === 'nunca' ? 0 : form.usages[item.id].avg_daily_use_hours)"
                                            class="w-full bg-slate-50 border-none rounded-2xl text-xs font-bold focus:ring-2 focus:ring-energy-solar/20 py-3 appearance-none px-4"
                                        >
                                            <option value="diario">Diaria (Todos los días)</option>
                                            <option value="casi_frecuentemente">Casi Frecuente (85%)</option>
                                            <option value="frecuentemente">Frecuente (60%)</option>
                                            <option value="ocasionalmente">Ocasional (30%)</option>
                                            <option value="raramente">Raramente (10%)</option>
                                            <option value="nunca">No se usó</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Result -->
                                <div class="md:w-32 text-right shrink-0">
                                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest leading-none mb-1">Calculado</p>
                                    <p class="text-lg font-black text-slate-900">{{ calculateKwh(item.id).toFixed(1) }} <span class="text-[10px] font-normal text-slate-400">kWh</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Sidebar -->
                <div class="lg:col-span-4 space-y-6 sticky top-8">
                    <div class="bg-white rounded-[40px] border border-slate-100 shadow-2xl p-8 space-y-8">
                        <div class="space-y-4">
                            <h3 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                                <Activity :size="20" class="text-energy-solar" /> Resumen de Ajuste
                            </h3>
                            <p class="text-xs text-slate-500 font-medium">Estás ajustando {{ Object.keys(form.usages).length }} equipos para el periodo unificado.</p>
                        </div>

                        <!-- Metering -->
                        <div class="space-y-6">
                            <div class="flex justify-between items-end">
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Calculado</p>
                                    <p class="text-3xl font-black text-slate-900">{{ Math.round(totalCalculatedKwh) }}<span class="text-sm font-normal ml-1">kWh</span></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">vs Facturado</p>
                                    <span :class="['text-sm font-black px-2 py-1 rounded-lg', Math.abs(diffPercentage) < 5 ? 'text-energy-success bg-green-50' : 'text-amber-600 bg-amber-50']">
                                        {{ diffPercentage > 0 ? '+' : '' }}{{ Math.round(diffPercentage) }}%
                                    </span>
                                </div>
                            </div>

                            <!-- Progress comparison -->
                            <div class="h-4 bg-slate-100 rounded-full overflow-hidden flex">
                                <div class="h-full bg-slate-900 transition-all duration-500" :style="{ width: Math.min(100, (totalCalculatedKwh / invoice.total_energy_consumed_kwh) * 100) + '%' }"></div>
                            </div>
                            <p v-if="Math.abs(diffPercentage) > 20" class="text-[10px] text-amber-600 font-bold italic leading-relaxed flex items-start gap-2 bg-amber-50 p-4 rounded-2xl border border-amber-100">
                                <AlertCircle :size="14" class="shrink-0" />
                                Nota: Hay una diferencia importante ({{ Math.round(diffPercentage) }}%) entre lo declarado y lo facturado.
                            </p>
                        </div>

                        <!-- Notes -->
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Notas de Calibración</label>
                            <textarea 
                                v-model="form.notes"
                                rows="3"
                                placeholder="Ej: Invitados en casa, vacaciones..."
                                class="w-full bg-slate-50 border-none rounded-[24px] text-xs font-medium focus:ring-2 focus:ring-energy-solar/20 p-4 outline-none"
                            ></textarea>
                        </div>

                        <!-- Actions -->
                        <div class="space-y-4">
                            <label class="flex items-center gap-4 p-4 bg-slate-50 rounded-[28px] border border-slate-100 cursor-pointer hover:bg-slate-100 transition-colors">
                                <div :class="['w-10 h-10 rounded-2xl flex items-center justify-center transition-colors', form.lock_period ? 'bg-slate-900 text-white' : 'bg-white text-slate-300']">
                                    <Lock :size="18" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-[10px] font-black text-slate-900 uppercase">Cerrar Periodo</p>
                                    <p class="text-[9px] text-slate-400 font-bold uppercase">No permitir más cambios</p>
                                </div>
                                <input type="checkbox" v-model="form.lock_period" class="hidden" />
                            </label>

                            <button 
                                @click="submit"
                                :disabled="form.processing"
                                class="w-full py-6 bg-slate-900 text-white rounded-[32px] font-black text-xs uppercase tracking-widest hover:bg-energy-solar shadow-2xl shadow-slate-200/50 flex items-center justify-center gap-3 group transition-all outline-none"
                            >
                                <span v-if="form.processing" class="flex items-center gap-2"><Loader2 :size="16" class="animate-spin" /> Procesando</span>
                                <span v-else class="flex items-center gap-3">
                                    <Save :size="16" /> Guardar y Calibrar
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="p-6 bg-energy-solar/5 border border-energy-solar/20 rounded-[32px] flex items-start gap-3">
                        <Info :size="16" class="text-energy-solar shrink-0 mt-0.5" />
                        <p class="text-[10px] text-amber-900/60 font-medium leading-relaxed">
                            Al calibrar, el motor de **ModoAhorro** distribuirá el consumo excedente o faltante priorizando tu **Tanque 3 (Uso Variable)**.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
/* Custom range slider styling */
input[type=range]::-webkit-slider-thumb {
    -webkit-appearance: none;
    height: 18px;
    width: 18px;
    border-radius: 50%;
    background: #0f172a;
    cursor: pointer;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    border: 2px solid white;
}
input[type=range]:hover::-webkit-slider-thumb {
    background: #f59e0b; /* energy-solar */
}
</style>
