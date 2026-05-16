<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { computed, watch } from 'vue';
import { 
    Home, 
    MapPin, 
    Users, 
    Maximize, 
    ChevronLeft, 
    Save, 
    Building, 
    Info,
    CheckCircle2,
    Globe,
    Calendar,
    Zap,
    Flame,
    Sun,
    Briefcase,
    Store,
    Hammer,
    Package,
    Cloud,
    CloudRain,
    CloudLightning,
    Wind,
    Thermometer,
    CloudSun,
    AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    provinces: Array,
    localities: Array,
    currentWeather: Object,
    climateProfile: Object,
});

const form = useForm({
    name: props.entity.name || '',
    usage_type: props.entity.usage_type || 'residencial',
    address_street: props.entity.address_street || '',
    address_postal_code: props.entity.address_postal_code || '',
    province_id: props.entity.locality?.province_id || '',
    locality_id: props.entity.locality_id || '',
    square_meters: props.entity.square_meters || '',
    people_count: props.entity.people_count || '',
    construction_year: props.entity.construction_year || '',
    has_gas: props.entity.has_gas || false,
    has_solar: props.entity.has_solar || false,
    has_business_activity: props.entity.has_business_activity || false,
    business_type: props.entity.business_type || '',
    description: props.entity.description || '',
    comercio_type: props.entity.comercio_type || 'gastronomia',
    staff_count: props.entity.staff_count || '',
    visitors_count: props.entity.visitors_count || '',
    service_turns: props.entity.service_turns || 1,
    opens_at: props.entity.opens_at || '08:00',
    closes_at: props.entity.closes_at || '20:00',
});

const isCommercial = computed(() => props.entity.type === 'comercio');

// Filter localities based on selected province
const filteredLocalities = computed(() => {
    if (!form.province_id) return [];
    return props.localities.filter(l => l.province_id === parseInt(form.province_id));
});

// Reset locality if province changes
watch(() => form.province_id, (newVal) => {
    const currentLocality = props.localities.find(l => l.id === form.locality_id);
    if (currentLocality && currentLocality.province_id !== parseInt(newVal)) {
        form.locality_id = '';
    }
});

const submit = () => {
    form.put(route('gestion.entity.update'), {
        preserveScroll: true,
    });
};

const constructionEras = [
    { label: 'Antes de 1980', value: 1970 },
    { label: '1980 - 2000', value: 1990 },
    { label: '2000 - 2015', value: 2010 },
    { label: '2015 - Actualidad', value: 2024 },
];

const businessTypes = [
    { id: 'venta', label: 'Local / Venta', icon: Store, desc: 'Atención al público en salón.' },
    { id: 'almacen', label: 'Almacén', icon: Package, desc: 'Depósito de insumos o stock.' },
    { id: 'taller', label: 'Taller', icon: Hammer, desc: 'Producción o reparaciones.' },
];

const weatherIcon = computed(() => {
    if (!props.currentWeather?.success) return Globe;
    const code = props.currentWeather.condition_code;
    if (code === 0) return Sun;
    if (code <= 3) return Cloud;
    if (code >= 51 && code <= 67) return CloudRain;
    if (code >= 95) return CloudLightning;
    return Sun;
});

const weatherDesc = computed(() => {
    if (!props.currentWeather?.success) return 'Sincronizando...';
    if (props.currentWeather.is_fallback) return 'Promedio Estacional';
    const code = props.currentWeather.condition_code;
    if (code === 0) return 'Cielo Despejado';
    if (code <= 3) return 'Parcialmente Nublado';
    if (code >= 51 && code <= 67) return 'Lluvia / Llovizna';
    if (code >= 95) return 'Tormenta Eléctrica';
    return 'Luz Solar Activa';
});

const climateZoneColor = computed(() => {
    const zone = props.climateProfile?.climate_zone || '';
    if (zone.includes('I')) return 'bg-orange-100 text-orange-600 border-orange-200';
    if (zone.includes('II')) return 'bg-amber-100 text-amber-600 border-amber-200';
    if (zone.includes('III')) return 'bg-emerald-100 text-emerald-600 border-emerald-200';
    return 'bg-blue-100 text-blue-600 border-blue-200';
});
</script>

<template>
    <MainLayout>
        <Head title="Perfil de Entidad" />

        <div class="h-full flex flex-col gap-4">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('home')" class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400 hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                        <ChevronLeft :size="20" stroke-width="3" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-4">
                            <h1 class="text-3xl font-black text-slate-900 tracking-tighter">
                                Mi <span :class="isCommercial ? 'text-blue-600' : 'text-emerald-600'">{{ isCommercial ? 'Comercio' : 'Casa' }}</span>
                            </h1>
                            <div :class="['px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest border', isCommercial ? 'bg-blue-100 text-blue-600 border-blue-200' : 'bg-emerald-100 text-emerald-600 border-emerald-200']">
                                {{ isCommercial ? 'Digital Twin B2B' : 'Digital Twin' }}
                            </div>
                        </div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                            {{ isCommercial ? 'Configuración comercial y logística de consumo' : 'Configuración residencial y contexto bioclimático' }}
                        </p>
                    </div>
                </div>

                <button 
                    @click="submit"
                    :disabled="form.processing"
                    :class="['px-6 py-3 text-white rounded-[20px] font-black text-xs uppercase tracking-widest flex items-center gap-2 hover:scale-105 active:scale-95 transition-all shadow-lg disabled:opacity-50', isCommercial ? 'bg-blue-600 shadow-blue-900/20 hover:bg-blue-500' : 'bg-emerald-600 shadow-emerald-900/20 hover:bg-emerald-500']"
                >
                    <Save :size="16" stroke-width="3" />
                    {{ form.processing ? 'Guardando...' : 'Guardar Perfil' }}
                </button>
            </div>

            <!-- Main Card -->
            <div class="flex-1 bg-white rounded-[40px] border border-slate-100 shadow-2xl shadow-slate-200/20 p-8 relative overflow-hidden">
                <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-emerald-50 rounded-full blur-[100px] pointer-events-none opacity-50"></div>
                
                <form @submit.prevent="submit" class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 h-full min-h-0 overflow-y-auto pr-4 scrollbar-hide">
                    
                    <!-- Left Body -->
                    <div class="lg:col-span-7 space-y-6">
                        <!-- Mixed Usage Logic -->
                        <section v-if="!isCommercial">
                            <h3 class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <Briefcase :size="14" /> Actividad Adicional en el Hogar
                            </h3>
                            <!-- ... existing business activity section ... -->
                            <div class="bg-slate-50/50 p-6 rounded-[32px] border border-slate-100 space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest">¿Tienes un negocio o taller en casa?</h4>
                                        <p class="text-[10px] font-bold text-slate-400 italic">Habilita esta opción para sumar ambientes productivos.</p>
                                    </div>
                                    <button 
                                        type="button"
                                        @click="form.has_business_activity = !form.has_business_activity"
                                        :class="['w-14 h-8 rounded-full transition-colors relative flex items-center px-1', form.has_business_activity ? 'bg-emerald-600' : 'bg-slate-200']"
                                    >
                                        <div :class="['w-6 h-6 bg-white rounded-full transition-all shadow-sm', form.has_business_activity ? 'translate-x-6' : 'translate-x-0']"></div>
                                    </button>
                                </div>

                                <div v-if="form.has_business_activity" class="grid grid-cols-3 gap-3 pt-2">
                                    <button 
                                        v-for="bt in businessTypes" :key="bt.id" type="button" @click="form.business_type = bt.id"
                                        :class="['p-3 rounded-2xl border-2 transition-all text-center', form.business_type === bt.id ? 'border-emerald-500 bg-white shadow-sm' : 'border-transparent bg-white/50 hover:bg-white']"
                                    >
                                        <div :class="['w-8 h-8 mx-auto rounded-lg flex items-center justify-center mb-2', form.business_type === bt.id ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-400']">
                                            <component :is="bt.icon" :size="16" />
                                        </div>
                                        <p :class="['text-[9px] font-black uppercase tracking-wider', form.business_type === bt.id ? 'text-emerald-700' : 'text-slate-400']">{{ bt.label }}</p>
                                    </button>
                                </div>
                            </div>
                        </section>

                        <!-- Commercial Specific Config -->
                        <section v-if="isCommercial">
                            <h3 class="text-[10px] font-black text-blue-600 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <Store :size="14" /> Configuración Logística Comercial
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-50/50 p-6 rounded-[32px] border border-slate-100">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Rubro del Comercio</label>
                                        <select v-model="form.comercio_type" class="w-full px-4 py-3 bg-white border border-slate-100 rounded-2xl text-sm font-bold font-sans">
                                            <option value="gastronomia">Gastronomía (Restaurante / Bar)</option>
                                            <option value="retail">Retail / Venta al público</option>
                                            <option value="oficina">Oficina / Corporativo</option>
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Apertura</label>
                                            <input v-model="form.opens_at" type="time" class="w-full px-4 py-3 bg-white border border-slate-100 rounded-2xl text-sm font-bold font-sans"/>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Cierre</label>
                                            <input v-model="form.closes_at" type="time" class="w-full px-4 py-3 bg-white border border-slate-100 rounded-2xl text-sm font-bold font-sans"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Personal (Staff)</label>
                                            <input v-model="form.staff_count" type="number" class="w-full px-4 py-3 bg-white border border-slate-100 rounded-2xl text-sm font-bold font-sans" placeholder="0"/>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">
                                                {{ form.comercio_type === 'gastronomia' ? 'Comensales / día' : (form.comercio_type === 'oficina' ? 'Visitantes / día' : 'Clientes / día') }}
                                            </label>
                                            <input v-model="form.visitors_count" type="number" class="w-full px-4 py-3 bg-white border border-slate-100 rounded-2xl text-sm font-bold font-sans" placeholder="0"/>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Turnos de Servicio</label>
                                        <div class="flex gap-2">
                                            <button v-for="n in 3" :key="n" type="button" @click="form.service_turns = n"
                                                :class="['flex-1 py-3 rounded-2xl border-2 transition-all text-xs font-black uppercase tracking-widest', form.service_turns === n ? 'border-blue-500 bg-white text-blue-600' : 'border-transparent bg-white/50 text-slate-400']"
                                            >
                                                {{ n }} {{ n === 1 ? 'Turno' : 'Turnos' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Basic Attributes -->
                        <section class="grid grid-cols-2 gap-6 pt-2">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Nombre Descriptivo</label>
                                    <input v-model="form.name" type="text" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold font-sans"/>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Sup. Cubierta m²</label>
                                        <input v-model="form.square_meters" type="number" step="0.1" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold font-sans"/>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">{{ isCommercial ? 'Personas en Staff' : 'Habitantes' }}</label>
                                        <input v-model="form.people_count" type="number" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold font-sans"/>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Era de Construcción</label>
                                    <select v-model="form.construction_year" class="w-full px-4 py-3 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-bold font-sans">
                                        <option value="" disabled>Seleccione era</option>
                                        <option v-for="era in constructionEras" :key="era.value" :value="era.value">{{ era.label }}</option>
                                    </select>
                                </div>
                            </div>
                        </section>

                        <section class="pt-2">
                            <h3 class="text-[10px] font-black uppercase tracking-widest mb-4 flex items-center gap-2" :class="isCommercial ? 'text-blue-600' : 'text-emerald-600'">
                                <Zap :size="14" /> Servicios Avanzados
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div @click="form.has_gas = !form.has_gas" :class="['p-4 rounded-3xl border-2 cursor-pointer transition-all flex items-center gap-3', form.has_gas ? 'bg-orange-50 border-orange-200' : 'bg-slate-50 border-slate-100 opacity-60']">
                                    <div :class="['w-9 h-9 rounded-xl flex items-center justify-center', form.has_gas ? 'bg-orange-500 text-white' : 'bg-white text-slate-300']"><Flame :size="18" /></div>
                                    <p :class="['flex-1 text-[10px] font-black uppercase tracking-widest', form.has_gas ? 'text-orange-700' : 'text-slate-400']">Gas Natural</p>
                                </div>
                                <div @click="form.has_solar = !form.has_solar" :class="['p-4 rounded-3xl border-2 cursor-pointer transition-all flex items-center gap-3', form.has_solar ? 'bg-emerald-50 border-emerald-200' : 'bg-slate-50 border-slate-100 opacity-60']">
                                    <div :class="['w-9 h-9 rounded-xl flex items-center justify-center', form.has_solar ? 'bg-emerald-600 text-white' : 'bg-white text-slate-300']"><Sun :size="18" /></div>
                                    <p :class="['flex-1 text-[10px] font-black uppercase tracking-widest', form.has_solar ? 'text-emerald-700' : 'text-slate-400']">Energía Solar</p>
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- Right Body: Location & Weather & BioProfile -->
                    <div class="lg:col-span-5 space-y-6">
                        <section class="bg-slate-50/50 p-6 rounded-[40px] border border-slate-100 space-y-6">
                            <div>
                                <h3 class="text-[10px] font-black text-emerald-600 uppercase tracking-widest mb-4 flex items-center gap-2">
                                    <MapPin :size="14" /> Ubicación Geográfica
                                </h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Provincia</label>
                                        <select v-model="form.province_id" class="w-full px-4 py-3 bg-white border border-slate-100 rounded-2xl text-sm font-bold font-sans">
                                            <option value="" disabled>Seleccione provincia</option>
                                            <option v-for="province in provinces" :key="province.id" :value="province.id">{{ province.name }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1.5 ml-1">Localidad</label>
                                        <select v-model="form.locality_id" :disabled="!form.province_id" class="w-full px-4 py-3 bg-white border border-slate-100 rounded-2xl text-sm font-bold font-sans disabled:opacity-40">
                                            <option v-for="locality in filteredLocalities" :key="locality.id" :value="locality.id">{{ locality.name }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Weather API Monitor (Real Time) -->
                            <div :class="['p-5 text-white rounded-[32px] shadow-lg relative overflow-hidden group transition-all', currentWeather?.is_fallback ? 'bg-slate-700 shadow-slate-900/20' : 'bg-emerald-600 shadow-emerald-900/20']">
                                <div class="absolute right-0 top-0 opacity-10 translate-x-4 -translate-y-4 group-hover:scale-110 transition-transform">
                                    <Globe :size="120" />
                                </div>
                                <div class="relative z-10 flex items-center justify-between">
                                    <div v-if="currentWeather?.success" class="flex flex-col">
                                        <div class="flex items-center gap-1.5 mb-1">
                                            <span class="text-xs font-black uppercase tracking-widest opacity-60">
                                                {{ currentWeather.is_fallback ? 'Modo Estimado' : 'Tiempo Real' }}
                                            </span>
                                            <div v-if="currentWeather.is_fallback" class="px-1.5 py-0.5 bg-amber-500 rounded text-[7px] font-black animate-pulse">OFFLINE</div>
                                        </div>
                                        <span class="text-3xl font-black tracking-tighter">{{ currentWeather.temp }}°c</span>
                                        <span class="text-[9px] font-bold opacity-80 flex items-center gap-1 mt-1">
                                            <component :is="weatherIcon" :size="10" /> {{ weatherDesc }}
                                        </span>
                                    </div>
                                    <div v-else class="flex flex-col">
                                        <span class="text-xs font-black uppercase tracking-widest opacity-60 italic">Sincronizando...</span>
                                        <span class="text-[9px] font-bold opacity-40">Conectando con la red</span>
                                    </div>
                                    <div class="flex flex-col items-end text-right">
                                        <div :class="['w-8 h-8 rounded-lg flex items-center justify-center mb-1', currentWeather?.is_fallback ? 'bg-white/10' : 'bg-white/20']"><Wind :size="14" /></div>
                                        <span class="text-[10px] font-black">{{ currentWeather?.windspeed || '--' }} km/h</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Bioclimatic Profile (Characteristic Climate) -->
                            <div v-if="climateProfile" class="p-6 bg-white rounded-[32px] border border-slate-100 shadow-sm space-y-4 relative overflow-hidden group">
                                <div v-if="climateProfile.is_fallback" class="absolute top-0 right-0 p-2 opacity-5 scale-150 rotate-12 group-hover:scale-125 transition-transform pointer-events-none">
                                    <AlertCircle :size="80" />
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="flex flex-col">
                                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Perfil Bioclimático Característico</h4>
                                        <span v-if="climateProfile.is_fallback" class="text-[8px] font-black text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded-full inline-block mt-0.5 border border-amber-100 uppercase tracking-tighter">Normales Regionales</span>
                                    </div>
                                    <div :class="['px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-tighter border shadow-sm', climateZoneColor]">
                                        Zona {{ climateProfile.climate_zone }}
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-1">
                                        <p class="text-[9px] font-bold text-slate-400 uppercase">Temp. Media Anual</p>
                                        <div class="flex items-center gap-1.5">
                                            <Thermometer :size="14" class="text-emerald-600" />
                                            <span class="text-sm font-black text-slate-800">{{ climateProfile.avg_temperature }}°c</span>
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="text-[9px] font-bold text-slate-400 uppercase">Severidad Invernal</p>
                                        <div class="flex items-center gap-1.5">
                                            <CloudSun :size="14" class="text-blue-500" />
                                            <span class="text-sm font-black text-slate-800">HDD {{ Math.round(climateProfile.hdd || 0) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-2 border-t border-slate-50">
                                    <div class="flex items-center gap-2">
                                        <Sun :size="12" class="text-amber-500" />
                                        <p class="text-[9px] font-bold text-slate-500 leading-tight">
                                            Recurso Solar: <span class="text-slate-800">{{ climateProfile.avg_radiation }} kWh/m²</span> anuales promedio.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </form>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
