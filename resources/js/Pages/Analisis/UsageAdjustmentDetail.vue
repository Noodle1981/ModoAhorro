<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
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
    Loader2,
    RotateCcw,
    Plus,
    Minus
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    invoice: Object,
    tanks: Array,
    period: Object,
    is_complete: Boolean
});

// Helper para encontrar el item en la estructura de tanques
const findItem = (eqId) => {
    for (const tank of props.tanks) {
        const item = tank.items.find(i => i.id === eqId);
        if (item) return item;
    }
    return null;
};

// Inicializamos el formulario con los datos de los tanques
const form = useForm({
    invoice_id: props.invoice.id,
    usages: props.tanks.reduce((acc, tank) => {
        tank.items.forEach(item => {
            acc[item.id] = {
                avg_daily_use_hours: item.usage.avg_daily_use_hours || 0,
                usage_frequency: item.usage.usage_frequency || 'diario',
                is_standby: item.usage.is_standby || false,
                // Lee del uso guardado. Si nunca se guardó → false (USO VARIABLE).
                // El usuario decide manualmente cuáles son Patrón Fijo.
                has_defined_pattern: item.usage.has_defined_pattern ?? false,
                nominal_power_w: item.nominal_power_w,
                use_minutes: (item.usage.avg_daily_use_hours < 1 && item.usage.avg_daily_use_hours > 0),
                cycles_per_period: item.cycles_per_period ?? 0,
                cycle_confirmed: false,
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

// Lógica para saber si un equipo está limitado por la API Climática
const getClimateLimitation = (eqId) => {
    const eqItem = findItem(eqId);
    if (eqItem && eqItem.category_name === 'Climatización') {
        const name = eqItem.type_name.toLowerCase();
        const isCooling = name.includes('aire') || name.includes('ventilador') || name.includes('split');
        
        const climateDays = isCooling ? props.period.cooling_days : props.period.heating_days;
        
        if (climateDays !== undefined && climateDays >= 0 && climateDays < props.period.days) {
            return {
                isLimited: true,
                type: isCooling ? 'Refrigeración' : 'Calefacción',
                days: climateDays
            };
        }
    }
    return { isLimited: false };
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const [year, month, day] = dateString.split('T')[0].split('-');
    return `${day}/${month}/${year.slice(-2)}`;
};

// Funciones de ayuda para Heladeras
const isFridge = (name, typeName) => {
    const n = (name || '').toLowerCase();
    const t = (typeName || '').toLowerCase();
    return n.includes('heladera') || n.includes('freezer') || t.includes('heladera') || t.includes('refrigerador');
};

const getFridgeLoadFactor = () => {
    const baseLoadFactor = 0.25;
    const peopleCount = Math.max(1, Math.min(props.entity?.people_count || 1, 15));
    const activityFactorPerPerson = 0.015;
    return baseLoadFactor + (peopleCount * activityFactorPerPerson);
};

// Cálculo reactivo de kWh por equipo
const calculateKwh = (eqId) => {
    const data = form.usages[eqId];
    const item = findItem(eqId);
    if (!data || !item) return 0;
    
    // 1. CÁLCULO POR CICLOS
    if (item.usage_unit === 'cycles') {
        const energyPerCycle = item.energy_per_cycle ?? (item.nominal_power_w / 1000);
        return energyPerCycle * (data.cycles_per_period || 0);
    }

    // 2. CÁLCULO POR PROPORCIÓN SOCIAL (Actualizado: Ajustable por frecuencia)
    if (item.usage_unit === 'people_proportional') {
        const peopleCount = props.entity?.people_count || 1;
        const factor = frequencyFactors[data.usage_frequency] ?? 1.0;
        return (item.social_coefficient || 0) * peopleCount * props.period.days * factor;
    }

    // 3. CÁLCULO POR HORAS (DEFAULT)
    const powerKw = data.nominal_power_w / 1000;
    const factor = frequencyFactors[data.usage_frequency] || 0.60;
    
    let effectiveDays = props.period.days;
    const climateLimitation = getClimateLimitation(eqId);
    
    if (climateLimitation.isLimited) {
        effectiveDays = climateLimitation.days;
    }
    
    effectiveDays = effectiveDays * factor;
    let consumption = powerKw * data.avg_daily_use_hours * effectiveDays;
    
    if (item && isFridge(item.name, item.type_name)) {
        consumption *= getFridgeLoadFactor();
    }
    
    return consumption;
};

// --- NUEVA LÓGICA DE AGRUPACIÓN PLANA (FASE 1) ---

// 1. Todos los equipos en una sola lista plana
const allItemsFlat = computed(() => {
    const all = [];
    props.tanks.forEach(tank => {
        tank.items.forEach(item => {
            all.push({
                ...item,
                tank_key: tank.key,
                tank_label: tank.label
            });
        });
    });
    return all;
});

// 2. Mapa reactivo de kWh por item — trackea cambios en form.usages
// IMPORTANTE: este computed es la fuente de verdad reactiva.
// calculateKwh() es una función normal, no un computed; Vue no la trackea
// cuando se llama directamente desde el template. Al wrappearla acá,
// todos los accesos a form.usages quedan bajo el tracking del computed.
const kwhMap = computed(() => {
    const map = {};
    // Accedemos a todos los valores del form para que Vue registre
    // la dependencia en cada propiedad relevante
    Object.entries(form.usages).forEach(([id]) => {
        map[id] = calculateKwh(Number(id));
    });
    return map;
});

// 3. Agrupación por Ambiente (Room) — usa kwhMap para ser reactiva
const roomsFlat = computed(() => {
    const roomsMap = {};
    allItemsFlat.value.forEach(item => {
        const roomName = item.room_name || 'Sin Asignar';
        if (!roomsMap[roomName]) {
            roomsMap[roomName] = { 
                name: roomName, 
                items: [], 
                current_kwh: 0 
            };
        }
        roomsMap[roomName].items.push(item);
        roomsMap[roomName].current_kwh += (kwhMap.value[item.id] || 0);
    });
    
    return Object.values(roomsMap).sort((a, b) => a.name.localeCompare(b.name));
});

// Totales reactivos — usan kwhMap
const totalCalculatedKwh = computed(() => {
    return Object.values(kwhMap.value).reduce((sum, v) => sum + v, 0);
});

const tankTotals = computed(() => {
    return props.tanks.map(tank => {
        const kwh = tank.items.reduce((sum, item) => sum + (kwhMap.value[item.id] || 0), 0);
        
        const roomsMap = {};
        tank.items.forEach(item => {
            const roomName = item.room_name || 'Sin Asignar';
            if (!roomsMap[roomName]) {
                roomsMap[roomName] = { name: roomName, items: [], current_kwh: 0 };
            }
            roomsMap[roomName].items.push(item);
            roomsMap[roomName].current_kwh += (kwhMap.value[item.id] || 0);
        });
        
        const rooms = Object.values(roomsMap).sort((a, b) => a.name.localeCompare(b.name));
        
        return { ...tank, current_kwh: kwh, rooms };
    });
});

const diffPercentage = computed(() => {
    const invoiced = props.period.total_kwh || 1;
    return ((totalCalculatedKwh.value - invoiced) / invoiced) * 100;
});

const roomTotals = computed(() => {
    const rooms = {};
    props.tanks.forEach(tank => {
        tank.items.forEach(item => {
            if (!rooms[item.room_name]) {
                rooms[item.room_name] = { name: item.room_name, kwh: 0, count: 0 };
            }
            rooms[item.room_name].kwh += calculateKwh(item.id);
            rooms[item.room_name].count++;
        });
    });
    return Object.values(rooms).sort((a, b) => b.kwh - a.kwh);
});

// Helpers para UI
const getFormula = (eqId) => {
    const item = findItem(eqId);
    if (!item) return '';
    
    const data = form.usages[eqId];

    if (item.usage_unit === 'cycles') {
        const energyPerCycle = item.energy_per_cycle ?? (item.nominal_power_w / 1000);
        return `${energyPerCycle.toFixed(2)}kWh × ${data.cycles_per_period} ciclos`;
    }

    if (item.usage_unit === 'people_proportional') {
        const peopleCount = props.entity?.people_count || 1;
        const factor = frequencyFactors[data.usage_frequency] ?? 1.0;
        const total = calculateKwh(eqId);
        const freqPct = Math.round(factor * 100);
        return `${item.social_coefficient} coeff × ${peopleCount} pers × ${props.period.days}d × ${freqPct}% (Frecuencia) = ${total.toFixed(1)} kWh`;
    }

    const powerKw = item.nominal_power_w / 1000;
    const factor = frequencyFactors[data.usage_frequency] || 0.60;
    
    let effectiveDays = props.period.days;
    const climateLimitation = getClimateLimitation(eqId);
    if (climateLimitation.isLimited) {
        effectiveDays = climateLimitation.days;
    }

    const hours = data.avg_daily_use_hours;
    const freqPct = Math.round(factor * 100);

    if (isFridge(item.name, item.type_name)) {
        const loadFactorPct = Math.round(getFridgeLoadFactor() * 100);
        return `${powerKw}kW × ${hours}h × ${effectiveDays}d × ${loadFactorPct}% (Ciclo Motor)`;
    }

    return `${powerKw}kW × ${hours}h × ${effectiveDays}d × ${freqPct}%`;
};

const getDisplayTime = (eqId) => {
    const usage = form.usages[eqId];
    if (usage.use_minutes) {
        const mins = Math.round(usage.avg_daily_use_hours * 60);
        return `${mins} min`;
    }
    return `${usage.avg_daily_use_hours} h`;
};

const handleMinuteSlider = (event, eqId) => {
    const usage = form.usages[eqId];
    if (usage.use_minutes) {
        usage.avg_daily_use_hours = parseFloat((event.target.value / 60).toFixed(3));
    } else {
        usage.avg_daily_use_hours = parseFloat(event.target.value);
    }
};

// Smart Prompt Helpers
const confirmCycleSuggestion = (eqId, suggestion) => {
    form.usages[eqId].cycles_per_period = suggestion;
    form.usages[eqId].has_defined_pattern = true;
    form.usages[eqId].cycle_confirmed = true;
};

const estimatedCyclesFromFrequency = (eqId) => {
    const freq = form.usages[eqId].usage_frequency;
    const totalDays = props.period.days;
    const factorMap = {
        'diario': 1.0, 'casi_frecuentemente': 0.85,
        'frecuentemente': 0.60, 'ocasionalmente': 0.30, 'raramente': 0.10, 'nunca': 0.0
    };
    return Math.round(totalDays * (factorMap[freq] ?? 0.60));
};

// Acciones del formulario
const submitSave = () => {
    form.post(route('analisis.usage.save'), {
        preserveScroll: true
    });
};

const submitCalibrate = () => {
    form.post(route('analisis.usage.calibrate'));
};

const getTankIcon = (key) => {
    switch (key) {
        case 1: return ShieldCheck;
        case 2: return ThermometerSun;
        case 3: return Gamepad2;
        case 4: return Zap;
        default: return Zap;
    }
};

const getTankColor = (key) => {
    switch (key) {
        case 1: return 'text-slate-900 bg-slate-50 border-slate-200'; // Certeza
        case 2: return 'text-sky-500 bg-sky-50 border-sky-100'; // Base
        case 3: return 'text-energy-water bg-energy-water/10 border-energy-water/20'; // Clima
        case 4: return 'text-energy-solar bg-energy-solar/10 border-energy-solar/20'; // Variable
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
                    <div class="bg-white border border-slate-100 p-4 rounded-[24px] shadow-sm flex flex-col justify-center">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Periodo Unificado</p>
                        <div class="flex items-end gap-2">
                            <p class="text-sm font-bold text-slate-900">{{ period.days }} días</p>
                            <span class="text-[10px] font-medium text-slate-400 mb-0.5">{{ formatDate(period.start) }} al {{ formatDate(period.end) }}</span>
                        </div>
                    </div>
                    <div class="bg-slate-900 text-white p-4 rounded-[24px] shadow-xl shadow-slate-200/50">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Total Facturado</p>
                        <p class="text-xl font-black">{{ Math.round(period.total_kwh) }}<span class="text-xs ml-1 opacity-50">kWh</span></p>
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
                        Faltan cuotas por cargar para completar este periodo de medición físico. Los kWh facturados ({{ Math.round(period.total_kwh) }} kWh) representan solo una parte del total real. Puedes configurar el uso ahora, pero el diagnóstico final será más preciso cuando cargues el bimestre completo.
                    </p>
                </div>
                <div class="md:ml-auto">
                    <Link :href="route('gestion.invoices')" class="px-6 py-3 bg-amber-900/5 hover:bg-amber-900/10 text-amber-900 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-colors">
                        Cargar Factura
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Main Controls Area (Fase 1: Ajuste Libre por Ambientes) -->
                <div class="lg:col-span-8 space-y-12">
                    
                    <div class="bg-sky-50 border border-sky-100 rounded-[32px] p-6 flex items-center gap-4 mb-8">
                        <Info :size="20" class="text-sky-500 shrink-0" />
                        <p class="text-sm font-medium text-sky-900 leading-tight">
                            Ajusta el uso real de cada equipo. La clasificación por <strong>Tanques</strong> se calculará automáticamente al presionar <strong>Sintonizar Motor</strong>.
                        </p>
                    </div>

                    <div v-for="room in roomsFlat" :key="room.name" class="space-y-6">
                        <!-- Room Header -->
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-slate-900 flex items-center justify-center text-white">
                                    <DoorOpen :size="24" />
                                </div>
                                <div>
                                    <h3 class="text-xl font-black text-slate-900 tracking-tight">{{ room.name }}</h3>
                                    <p class="text-xs text-slate-400 font-medium">{{ room.items.length }} equipos en este ambiente</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest mb-1">Subtotal Ambiente</p>
                                <p class="text-2xl font-black text-slate-900">{{ Math.round(room.current_kwh) }}<span class="text-xs ml-1 text-slate-400">kWh</span></p>
                            </div>
                        </div>

                        <!-- Equipment Grid -->
                        <div class="grid grid-cols-1 gap-4">
                            <div 
                                v-for="item in room.items" 
                                :key="item.id"
                                class="bg-white rounded-[32px] border border-slate-100 shadow-sm hover:shadow-md transition-shadow p-6 flex flex-col md:flex-row items-start gap-8 group"
                                :class="{'opacity-50 grayscale-[0.5]': form.usages[item.id].usage_frequency === 'nunca'}"
                            >
                                <!-- Eq Info -->
                                <div class="md:w-1/4 flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-300 shrink-0 mt-1">
                                        <Zap :size="20" v-if="!item.is_standby" />
                                        <Activity :size="20" v-else class="text-rose-400" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="font-black text-slate-900 tracking-tight text-base leading-tight break-words">{{ item.name }}</h4>
                                        <p v-if="item.brand || item.model" class="text-[9px] font-bold text-energy-solar uppercase truncate mt-1">
                                            {{ item.brand }} {{ item.model }}
                                        </p>
                                        
                                        <div class="flex items-center gap-2 mt-2">
                                            <span class="text-[9px] font-black px-2 py-0.5 bg-slate-50 border border-slate-100 rounded-full text-slate-500">
                                                {{ item.usage_unit === 'people_proportional' ? 'AUTO' : item.nominal_power_w + 'W' }}
                                            </span>
                                            
                                            <button 
                                                v-if="item.usage_unit !== 'people_proportional'"
                                                type="button"
                                                @click="form.usages[item.id].has_defined_pattern = !form.usages[item.id].has_defined_pattern"
                                                :class="[
                                                    'text-[9px] font-black px-2 py-0.5 rounded-full border flex items-center gap-1 transition-all',
                                                    form.usages[item.id].has_defined_pattern 
                                                        ? 'bg-sky-50 text-sky-500 border-sky-100' 
                                                        : 'bg-slate-50 text-slate-300 border-slate-100'
                                                ]"
                                            >
                                                <Lock :size="8" /> {{ form.usages[item.id].has_defined_pattern ? 'PATRÓN FIJO' : 'USO VARIABLE' }}
                                            </button>

                                            <span v-else class="text-[9px] font-black px-2 py-0.5 bg-amber-50 text-amber-600 border border-amber-100 rounded-full flex items-center gap-1">
                                                <Activity :size="8" /> AUTOMÁTICO
                                            </span>

                                            <span v-if="item.is_validated" class="text-[9px] font-black px-2 py-0.5 bg-energy-success/10 text-energy-success rounded-full">VALIDADO</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Inputs Adaptativos -->
                                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-6 w-full py-1">
                                    
                                    <!-- === HORAS === -->
                                    <template v-if="item.usage_unit === 'hours'">
                                        <div class="space-y-3">
                                            <div class="flex justify-between items-center px-1">
                                                <div class="flex flex-col">
                                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Uso/Día</label>
                                                    <button 
                                                        type="button"
                                                        @click="form.usages[item.id].use_minutes = !form.usages[item.id].use_minutes"
                                                        class="text-[8px] font-bold text-sky-500 uppercase flex items-center gap-1"
                                                    >
                                                        <Clock :size="8" /> {{ form.usages[item.id].use_minutes ? 'Pasar a Horas' : 'Ajustar Minutos' }}
                                                    </button>
                                                </div>
                                                <span class="text-sm font-black text-slate-900 bg-slate-50 px-2 rounded-md">{{ getDisplayTime(item.id) }}</span>
                                            </div>
                                            <input 
                                                v-if="!form.usages[item.id].use_minutes"
                                                type="range" v-model.number="form.usages[item.id].avg_daily_use_hours" 
                                                min="0" max="24" step="0.5"
                                                class="w-full h-1.5 bg-slate-100 rounded-lg appearance-none cursor-pointer accent-slate-900" 
                                            />
                                            <input 
                                                v-else
                                                type="range" :value="Math.round(form.usages[item.id].avg_daily_use_hours * 60)" 
                                                @input="handleMinuteSlider($event, item.id)"
                                                min="0" max="60" step="1"
                                                class="w-full h-1.5 bg-sky-100 rounded-lg appearance-none cursor-pointer accent-sky-500" 
                                            />
                                        </div>
                                        <div class="space-y-3">
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Frecuencia</label>
                                            <select 
                                                v-model="form.usages[item.id].usage_frequency"
                                                @change="form.usages[item.id].avg_daily_use_hours = ($event.target.value === 'nunca' ? 0 : form.usages[item.id].avg_daily_use_hours)"
                                                class="w-full bg-slate-50 border-none rounded-2xl text-xs font-bold py-3 px-4"
                                            >
                                                <option value="diario">Diaria</option>
                                                <option value="casi_frecuentemente">Casi Frecuente</option>
                                                <option value="frecuentemente">Frecuente</option>
                                                <option value="ocasionalmente">Ocasional</option>
                                                <option value="raramente">Raramente</option>
                                                <option value="nunca">No se usó</option>
                                            </select>
                                        </div>
                                    </template>

                                    <!-- === CICLOS === -->
                                    <template v-else-if="item.usage_unit === 'cycles'">
                                        <div v-if="item.cycle_suggestion > 0 && !form.usages[item.id].cycle_confirmed"
                                             class="col-span-1 md:col-span-2 bg-amber-50 border border-amber-100 rounded-2xl p-4 flex items-center gap-4">
                                            <Zap :size="18" class="text-amber-500 shrink-0" />
                                            <div class="flex-1">
                                                <p class="text-[10px] font-bold text-amber-900">Sugerencia: ~{{ item.cycle_suggestion }} usos este bimestre.</p>
                                            </div>
                                            <button type="button" @click="confirmCycleSuggestion(item.id, item.cycle_suggestion)" class="px-3 py-1 bg-amber-500 text-white text-[9px] font-black rounded-lg">USAR</button>
                                        </div>

                                        <div class="space-y-3">
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Usos / Bimestre</label>
                                            <div class="flex items-center gap-3">
                                                <button type="button" @click="form.usages[item.id].cycles_per_period = Math.max(0, form.usages[item.id].cycles_per_period - 1)" class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600"><Minus :size="14" /></button>
                                                <input type="number" v-model.number="form.usages[item.id].cycles_per_period" class="flex-1 bg-slate-50 border-none rounded-lg text-center font-black py-1 focus:ring-0" />
                                                <button type="button" @click="form.usages[item.id].cycles_per_period++" class="w-8 h-8 rounded-lg bg-slate-900 flex items-center justify-center text-white"><Plus :size="14" /></button>
                                            </div>
                                        </div>
                                        <div class="space-y-3">
                                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Referencia</label>
                                            <select v-model="form.usages[item.id].usage_frequency" class="w-full bg-slate-50 border-none rounded-2xl text-xs font-bold py-3 px-4">
                                                <option value="diario">Diaria</option>
                                                <option value="frecuentemente">Frecuente</option>
                                                <option value="ocasionalmente">Ocasional</option>
                                                <option value="nunca">No se usó</option>
                                            </select>
                                        </div>
                                    </template>

                                    <!-- === PERSONAS (Automático Ajustable) === -->
                                    <template v-else-if="item.usage_unit === 'people_proportional'">
                                        <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="bg-slate-50 rounded-2xl p-4 flex flex-col justify-center gap-1">
                                                <div class="flex items-center gap-2">
                                                    <LayoutGrid :size="14" class="text-slate-400" />
                                                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">Cálculo Automático</p>
                                                </div>
                                                <p class="text-[9px] text-slate-400 font-medium">Basado en {{ entity.people_count }} personas. Ajusta la frecuencia para calibrar.</p>
                                            </div>
                                            <div class="space-y-3">
                                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Frecuencia de Uso</label>
                                                <select 
                                                    v-model="form.usages[item.id].usage_frequency" 
                                                    class="w-full bg-slate-50 border-none rounded-2xl text-xs font-bold py-3 px-4"
                                                >
                                                    <option value="diario">Normal (Diario)</option>
                                                    <option value="frecuentemente">Frecuente</option>
                                                    <option value="ocasionalmente">Ocasional</option>
                                                    <option value="nunca">No se usó</option>
                                                </select>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- Result -->
                                <div class="md:w-40 text-right shrink-0 pt-1">
                                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest leading-none mb-1">Consumo</p>
                                    <p class="text-2xl font-black text-slate-900 leading-none mb-1">
                                        {{ (kwhMap[item.id] || 0) < 1 && (kwhMap[item.id] || 0) > 0
                                            ? (kwhMap[item.id] || 0).toFixed(3)
                                            : (kwhMap[item.id] || 0).toFixed(1)
                                        }}
                                        <span class="text-[10px] font-normal text-slate-400">kWh</span>
                                    </p>
                                    <p class="text-[9px] font-bold text-slate-400 font-mono tracking-tighter opacity-0 group-hover:opacity-100 transition-opacity">
                                        {{ getFormula(item.id) }}
                                    </p>
                                    <div v-if="getClimateLimitation(item.id).isLimited" class="mt-2 inline-flex items-center gap-1 bg-sky-50 text-sky-600 px-2 py-1 rounded-md border border-sky-100">
                                        <ThermometerSun :size="10" />
                                        <span class="text-[8px] font-black uppercase">{{ getClimateLimitation(item.id).days }} Días (API)</span>
                                    </div>
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
                            <div class="space-y-4">
                                <div class="flex justify-between text-[9px] font-bold uppercase tracking-tighter text-slate-400 px-1">
                                    <span>Calculado</span>
                                    <span>Facturado ({{ Math.round(period.total_kwh) }})</span>
                                </div>
                                <div class="relative h-6 bg-slate-100 rounded-full p-1 border border-slate-200 shadow-inner group">
                                    <!-- Target Line (100% Facturado) -->
                                    <div class="absolute top-0 bottom-0 left-[100%] w-0.5 bg-slate-300 z-10 flex flex-col items-center">
                                        <div class="w-2 h-2 bg-slate-400 rounded-full -mt-1 shadow-sm"></div>
                                    </div>
                                    
                                    <!-- Progress Fill -->
                                    <div 
                                        class="h-full rounded-full transition-all duration-1000 ease-out shadow-lg flex items-center justify-end px-2" 
                                        :class="[
                                            Math.abs(diffPercentage) < 5 ? 'bg-energy-success' : 
                                            (totalCalculatedKwh > period.total_kwh ? 'bg-rose-500' : 'bg-slate-900')
                                        ]"
                                        :style="{ width: Math.min(100, (totalCalculatedKwh / period.total_kwh) * 100) + '%' }"
                                    >
                                        <span v-if="totalCalculatedKwh > 0 && (totalCalculatedKwh / period.total_kwh) > 0.15" class="text-[9px] font-black text-white leading-none">
                                            {{ Math.round((totalCalculatedKwh / period.total_kwh) * 100) }}%
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Alert if gap is too big -->
                                <p v-if="Math.abs(diffPercentage) > 20" class="text-[10px] text-amber-600 font-bold italic leading-relaxed flex items-start gap-2 bg-amber-50 p-4 rounded-2xl border border-amber-100">
                                    <AlertCircle :size="14" class="shrink-0" />
                                    Nota: Diferencia importante ({{ Math.round(diffPercentage) }}%) detectada. Revisa los hábitos.
                                </p>
                            </div>

                            <!-- Tank Breakdown Metrics (Oculto en Fase 1) -->
                            <div v-if="false" class="pt-6 border-t border-slate-50 space-y-4">
                                <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest px-1">Desglose por Tanques</p>
                                <div v-for="tank in tankTotals" :key="tank.key" class="space-y-2">
                                    <div class="flex justify-between items-end">
                                        <span class="text-[10px] font-bold text-slate-600">{{ tank.label }}</span>
                                        <span class="text-[10px] font-black text-slate-900">{{ Math.round(tank.current_kwh) }} kWh</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-slate-50 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full transition-all duration-500"
                                            :class="[
                                                tank.key === 1 ? 'bg-rose-400' :
                                                tank.key === 2 ? 'bg-sky-400' : 'bg-indigo-400'
                                            ]"
                                            :style="{ width: (totalCalculatedKwh > 0 ? (tank.current_kwh / totalCalculatedKwh) * 100 : 0) + '%' }"
                                        ></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Room Breakdown Metrics -->
                            <div class="pt-6 border-t border-slate-50 space-y-4">
                                <div class="flex items-center justify-between px-1">
                                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Peso por Ambiente</p>
                                    <span class="text-[8px] font-black text-slate-400">{{ roomTotals.length }} Ambientes</span>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div v-for="room in roomTotals.slice(0, 4)" :key="room.name" class="bg-slate-50 p-3 rounded-2xl space-y-1">
                                        <p class="text-[9px] font-black text-slate-400 truncate uppercase">{{ room.name }}</p>
                                        <p class="text-xs font-black text-slate-900">{{ Math.round(room.kwh) }} <span class="text-[8px] font-normal text-slate-400">kWh</span></p>
                                             <div class="h-1 w-full bg-slate-200 rounded-full overflow-hidden mt-1">
                                            <div class="h-full bg-energy-solar" :style="{ width: (totalCalculatedKwh > 0 ? (room.kwh / totalCalculatedKwh) * 100 : 0) + '%' }"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                            <!-- Botón 1: Guardar Contexto (Solo persiste) -->
                            <button 
                                type="button"
                                @click="submitSave"
                                :disabled="form.processing"
                                class="w-full py-5 bg-slate-100 text-slate-900 rounded-[32px] font-black text-xs uppercase tracking-widest hover:bg-slate-200 flex items-center justify-center gap-3 transition-all outline-none"
                            >
                                <Save :size="16" /> Guardar Contexto
                            </button>

                            <!-- Botón 2: Sintonizar Motor (Persiste + Motor + Resultados) -->
                            <button 
                                type="button"
                                @click="submitCalibrate"
                                :disabled="form.processing"
                                class="w-full py-6 bg-slate-900 text-white rounded-[32px] font-black text-xs uppercase tracking-widest hover:bg-energy-solar shadow-2xl flex items-center justify-center gap-3 transition-all outline-none"
                            >
                                <Zap :size="16" /> Sintonizar Motor →
                            </button>
                        </div>
                    </div>

                    <div class="p-6 bg-energy-solar/5 border border-energy-solar/20 rounded-[32px] flex items-start gap-3">
                        <Info :size="16" class="text-energy-solar shrink-0 mt-0.5" />
                        <p class="text-[10px] text-amber-900/60 font-medium leading-relaxed">
                            Al sintonizar, el motor de **ModoAhorro** calculará tu **Gemelo Digital** distribuyendo el consumo según tus nuevos parámetros.
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
