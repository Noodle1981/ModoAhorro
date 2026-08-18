<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { 
    Users, 
    CreditCard, 
    DollarSign, 
    Calendar, 
    Edit2, 
    Search, 
    Building, 
    Mail, 
    X, 
    Crown,
    Sparkles,
    CheckCircle2
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    usersList: Array,
    plans: Array,
    stats: Object,
});

const activeTab = ref('users'); // 'users' | 'plans'

const searchQuery = ref('');
const planFilter = ref('all');

const filteredUsers = computed(() => {
    return props.usersList.filter(u => {
        const query = searchQuery.value.toLowerCase();
        const matchesSearch = u.user_name.toLowerCase().includes(query) ||
                              u.user_email.toLowerCase().includes(query) ||
                              u.entities.some(e => e.name.toLowerCase().includes(query));
        
        const matchesPlan = planFilter.value === 'all' || u.plan_id === Number(planFilter.value);

        return matchesSearch && matchesPlan;
    });
});

// Modal de Cambiar Plan de Usuario
const isPlanModalOpen = ref(false);
const currentEditingUser = ref(null);
const planForm = useForm({
    user_id: '',
    plan_id: props.plans.length > 0 ? props.plans[0].id : '',
});

const openPlanModal = (u) => {
    currentEditingUser.value = u;
    planForm.user_id = u.user_id;
    planForm.plan_id = u.plan_id || (props.plans.length > 0 ? props.plans[0].id : '');
    isPlanModalOpen.value = true;
};

const submitPlanChange = () => {
    planForm.post(route('sistema.users.payments.assign'), {
        preserveScroll: true,
        onSuccess: () => {
            isPlanModalOpen.value = false;
        }
    });
};

// Modal de Extender Membresía
const isExtendModalOpen = ref(false);
const extendForm = useForm({
    user_id: '',
    days: 30,
});

const openExtendModal = (u) => {
    currentEditingUser.value = u;
    extendForm.user_id = u.user_id;
    extendForm.days = 30;
    isExtendModalOpen.value = true;
};

const submitExtend = () => {
    extendForm.post(route('sistema.users.payments.extend'), {
        preserveScroll: true,
        onSuccess: () => {
            isExtendModalOpen.value = false;
        }
    });
};

// Modal de Configurar Plan del Sistema
const isEditPlanModalOpen = ref(false);
const currentEditingPlan = ref(null);
const editPlanForm = useForm({
    name: '',
    price: 0,
    max_entities: 1,
    allowed_entity_types: ['hogar'],
    features: '',
});

const openEditPlanModal = (plan) => {
    currentEditingPlan.value = plan;
    editPlanForm.name = plan.name;
    editPlanForm.price = plan.price;
    editPlanForm.max_entities = plan.max_entities;
    editPlanForm.allowed_entity_types = Array.isArray(plan.allowed_entity_types) ? [...plan.allowed_entity_types] : ['hogar'];
    editPlanForm.features = plan.features || '';
    isEditPlanModalOpen.value = true;
};

const toggleEntityType = (type) => {
    const idx = editPlanForm.allowed_entity_types.indexOf(type);
    if (idx > -1) {
        if (editPlanForm.allowed_entity_types.length > 1) {
            editPlanForm.allowed_entity_types.splice(idx, 1);
        }
    } else {
        editPlanForm.allowed_entity_types.push(type);
    }
};

const submitEditPlan = () => {
    if (!currentEditingPlan.value) return;
    editPlanForm.put(route('sistema.users.plans.update', currentEditingPlan.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isEditPlanModalOpen.value = false;
        }
    });
};

const getPlanBadgeClass = (planName) => {
    const name = (planName || '').toLowerCase();
    if (name.includes('enterprise')) {
        return 'bg-amber-50 text-amber-700 border border-amber-200';
    } else if (name.includes('premium')) {
        return 'bg-purple-50 text-purple-700 border border-purple-200';
    }
    return 'bg-slate-100 text-slate-700 border border-slate-200';
};
</script>

<template>
    <Head title="Pagos & Suscripciones · Administrador" />

    <MainLayout>
        <div class="max-w-7xl mx-auto space-y-8">
            
            <!-- Page Header Ribbon -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 sm:p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                <div>
                    <span class="text-xs font-black uppercase tracking-widest text-emerald-600 block mb-1">
                        Control de Facturación & Planes
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Pagos & Suscripciones de Usuarios
                    </h1>
                    <p class="text-slate-500 font-medium text-sm mt-1">
                        Cada usuario posee un único plan contratado que determina cuántas entidades y de qué tipo puede gestionar.
                    </p>
                </div>

                <!-- Tab Switcher Buttons -->
                <div class="flex items-center gap-2 bg-slate-100 p-1.5 rounded-2xl">
                    <button 
                        @click="activeTab = 'users'"
                        :class="[
                            'px-4 py-2.5 rounded-xl font-black text-xs uppercase tracking-wider transition-all cursor-pointer',
                            activeTab === 'users' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-900'
                        ]"
                    >
                        Usuarios & Planes
                    </button>
                    <button 
                        @click="activeTab = 'plans'"
                        :class="[
                            'px-4 py-2.5 rounded-xl font-black text-xs uppercase tracking-wider transition-all cursor-pointer',
                            activeTab === 'plans' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-900'
                        ]"
                    >
                        Configurar Planes
                    </button>
                </div>
            </div>

            <!-- Stats Bar: Breakdown por Plan -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-xs">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Total Usuarios</span>
                        <Users :size="18" class="text-slate-400" />
                    </div>
                    <p class="text-2xl font-black text-slate-900">{{ stats?.total_users || 0 }}</p>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-xs">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Plan Gratuito</span>
                        <CreditCard :size="18" class="text-emerald-500" />
                    </div>
                    <p class="text-2xl font-black text-slate-900">{{ stats?.free_users || 0 }} <span class="text-xs font-bold text-slate-400">users</span></p>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-xs">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-purple-600">Plan Premium</span>
                        <Sparkles :size="18" class="text-purple-500" />
                    </div>
                    <p class="text-2xl font-black text-purple-700">{{ stats?.premium_users || 0 }} <span class="text-xs font-bold text-purple-400">users</span></p>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-xs">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-600">Enterprise</span>
                        <Crown :size="18" class="text-amber-500" />
                    </div>
                    <p class="text-2xl font-black text-amber-700">{{ stats?.enterprise_users || 0 }} <span class="text-xs font-bold text-amber-400">users</span></p>
                </div>

                <div class="bg-white p-5 rounded-3xl border border-slate-100 shadow-xs col-span-2 sm:col-span-1">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-emerald-600">MRR Mensual</span>
                        <DollarSign :size="18" class="text-emerald-600" />
                    </div>
                    <p class="text-2xl font-black text-emerald-700">${{ Number(stats?.monthly_revenue || 0).toLocaleString('es-AR') }}</p>
                </div>
            </div>

            <!-- TAB 1: LISTADO DE USUARIOS & SUS PLANES -->
            <div v-if="activeTab === 'users'" class="space-y-6">
                <!-- Toolbar & Filtros -->
                <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm flex flex-col lg:flex-row items-center gap-3">
                    <div class="relative w-full lg:flex-1">
                        <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="18" />
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Buscar usuario, correo electrónico o entidad..." 
                            class="w-full pl-12 pr-6 py-3 bg-slate-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-medium text-sm text-slate-800"
                        />
                    </div>

                    <select 
                        v-model="planFilter"
                        class="px-4 py-3 bg-slate-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-bold text-slate-700 text-xs min-w-[200px]"
                    >
                        <option value="all">Todos los Planes</option>
                        <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }} (${{ Number(p.price).toLocaleString('es-AR') }})</option>
                    </select>
                </div>

                <!-- Table of Users with Plans -->
                <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/50 text-[10px] font-black uppercase tracking-wider text-slate-400">
                                    <th class="py-4 px-6 sm:px-8">Usuario</th>
                                    <th class="py-4 px-6">Plan Contratado</th>
                                    <th class="py-4 px-6">Capacidad / Entidades</th>
                                    <th class="py-4 px-6">Vencimiento</th>
                                    <th class="py-4 px-6 sm:px-8 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                                <tr 
                                    v-for="u in filteredUsers" 
                                    :key="u.user_id" 
                                    class="hover:bg-slate-50/80 transition-colors group"
                                >
                                    <!-- Usuario -->
                                    <td class="py-4 px-6 sm:px-8">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-2xl bg-slate-100 flex items-center justify-center font-black text-slate-700 text-sm uppercase">
                                                {{ u.user_name.charAt(0) }}
                                            </div>
                                            <div>
                                                <p class="font-black text-slate-900">{{ u.user_name }}</p>
                                                <p class="text-xs text-slate-400 flex items-center gap-1 mt-0.5">
                                                    <Mail :size="11" />
                                                    {{ u.user_email }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Plan Asignado -->
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2">
                                            <span 
                                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-black text-xs"
                                                :class="getPlanBadgeClass(u.plan_name)"
                                            >
                                                <Crown v-if="u.plan_name.toLowerCase().includes('enterprise')" :size="12" />
                                                <Sparkles v-else-if="u.plan_name.toLowerCase().includes('premium')" :size="12" />
                                                <span>{{ u.plan_name }}</span>
                                            </span>
                                            <span class="text-xs font-black text-slate-900">
                                                ${{ Number(u.plan_price).toLocaleString('es-AR') }}/m
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Entidades Utilizadas vs Límite -->
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-black text-xs text-slate-900">
                                                {{ u.entities_count }} / {{ u.max_entities >= 999 ? '∞' : u.max_entities }}
                                            </span>
                                            <span class="text-[10px] text-slate-400 uppercase font-bold">entidades creadas</span>
                                        </div>
                                        <div v-if="u.entities.length > 0" class="flex flex-wrap gap-1">
                                            <span 
                                                v-for="ent in u.entities" 
                                                :key="ent.id"
                                                class="px-2 py-0.5 bg-slate-50 border border-slate-100 rounded-md text-[10px] font-bold text-slate-600 flex items-center gap-1"
                                            >
                                                <Building :size="10" class="text-slate-400" />
                                                {{ ent.name }}
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Vencimiento -->
                                    <td class="py-4 px-6 text-xs font-medium">
                                        <span v-if="u.expires_at" class="flex items-center gap-1.5 text-slate-600 font-bold">
                                            <Calendar :size="13" class="text-slate-400" />
                                            {{ new Date(u.expires_at).toLocaleDateString('es-AR') }}
                                        </span>
                                        <span v-else class="text-emerald-600 font-black text-[11px] uppercase tracking-wider">
                                            Permanente
                                        </span>
                                    </td>

                                    <!-- Acciones -->
                                    <td class="py-4 px-6 sm:px-8 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button 
                                                @click="openPlanModal(u)"
                                                title="Cambiar Plan del Usuario"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-purple-50 text-slate-700 hover:text-purple-700 rounded-xl text-xs font-bold transition-all cursor-pointer"
                                            >
                                                <Edit2 :size="13" />
                                                <span>Plan</span>
                                            </button>
                                            <button 
                                                @click="openExtendModal(u)"
                                                title="Extender Membresía"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 rounded-xl text-xs font-bold transition-all cursor-pointer"
                                            >
                                                <Calendar :size="13" />
                                                <span>Extender</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="filteredUsers.length === 0" class="p-12 text-center text-slate-400">
                        <p class="font-bold text-sm">No se encontraron usuarios coincidentes.</p>
                    </div>
                </div>
            </div>

            <!-- TAB 2: CONFIGURACIÓN DE LOS 3 PLANES DEL SISTEMA -->
            <div v-if="activeTab === 'plans'" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div 
                    v-for="plan in plans" 
                    :key="plan.id"
                    class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xs flex flex-col justify-between hover:shadow-xl transition-all group relative overflow-hidden"
                >
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600">
                                Nivel {{ plan.id }}
                            </span>
                            <button 
                                @click="openEditPlanModal(plan)"
                                class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all cursor-pointer"
                                title="Editar configuración del plan"
                            >
                                <Edit2 :size="16" />
                            </button>
                        </div>

                        <div>
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ plan.name }}</h3>
                            <div class="flex items-baseline gap-1 mt-1">
                                <span class="text-3xl font-black text-slate-900">${{ Number(plan.price).toLocaleString('es-AR') }}</span>
                                <span class="text-xs font-bold text-slate-400">/mes por usuario</span>
                            </div>
                        </div>

                        <p class="text-xs text-slate-500 font-medium leading-relaxed">
                            {{ plan.features || 'Plan estándar del sistema' }}
                        </p>

                        <div class="space-y-2 pt-4 border-t border-slate-100">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-400">Límite Entidades:</span>
                                <span class="text-slate-900 font-black">{{ plan.max_entities >= 999 ? 'Ilimitadas' : `Hasta ${plan.max_entities}` }}</span>
                            </div>

                            <div class="text-xs font-bold space-y-1">
                                <span class="text-slate-400 block mb-1.5">Tipos Permitidos:</span>
                                <div class="flex flex-wrap gap-1.5">
                                    <span 
                                        v-for="t in (plan.allowed_entity_types || ['hogar'])" 
                                        :key="t"
                                        class="px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase tracking-wider inline-flex items-center gap-1"
                                    >
                                        <CheckCircle2 :size="10" /> {{ t }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button 
                        @click="openEditPlanModal(plan)"
                        class="mt-6 w-full py-3 bg-slate-50 group-hover:bg-emerald-50 text-slate-700 group-hover:text-emerald-700 font-black text-xs uppercase tracking-wider rounded-2xl transition-all cursor-pointer"
                    >
                        Configurar Parámetros
                    </button>
                </div>
            </div>

        </div>

        <!-- MODAL CAMBIAR PLAN DE USUARIO -->
        <div v-if="isPlanModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="relative w-full max-w-md bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 block">Suscripción de Usuario</span>
                        <h3 class="text-xl font-black text-slate-900">Cambiar Plan: {{ currentEditingUser?.user_name }}</h3>
                    </div>
                    <button @click="isPlanModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100">
                        <X :size="20" />
                    </button>
                </div>

                <form @submit.prevent="submitPlanChange" class="p-8 space-y-4">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Nuevo Plan para el Usuario *</label>
                        <select 
                            v-model="planForm.plan_id" 
                            required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                        >
                            <option v-for="p in plans" :key="p.id" :value="p.id">
                                {{ p.name }} — ${{ Number(p.price).toLocaleString('es-AR') }}/mes ({{ p.max_entities >= 999 ? 'Ilimitadas' : `Hasta ${p.max_entities} entidades` }})
                            </option>
                        </select>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <button type="button" @click="isPlanModalOpen = false" class="px-5 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 rounded-xl">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="planForm.processing" class="bg-[#009966] hover:bg-[#008055] text-white font-black text-xs uppercase tracking-wider px-6 py-3 rounded-2xl shadow-md cursor-pointer disabled:opacity-50">
                            Asignar Plan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL EXTENDER MEMBRESÍA -->
        <div v-if="isExtendModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="relative w-full max-w-md bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 block">Renovación de Cuenta</span>
                        <h3 class="text-xl font-black text-slate-900">Extender Membresía: {{ currentEditingUser?.user_name }}</h3>
                    </div>
                    <button @click="isExtendModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100">
                        <X :size="20" />
                    </button>
                </div>

                <form @submit.prevent="submitExtend" class="p-8 space-y-4">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Días a Extender *</label>
                        <select 
                            v-model.number="extendForm.days" 
                            required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                        >
                            <option :value="30">+30 Días (1 Mes)</option>
                            <option :value="90">+90 Días (Trimestral)</option>
                            <option :value="180">+180 Días (Semestral)</option>
                            <option :value="365">+365 Días (1 Año)</option>
                        </select>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <button type="button" @click="isExtendModalOpen = false" class="px-5 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 rounded-xl">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="extendForm.processing" class="bg-[#009966] hover:bg-[#008055] text-white font-black text-xs uppercase tracking-wider px-6 py-3 rounded-2xl shadow-md cursor-pointer disabled:opacity-50">
                            Extender Membresía
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL EDITAR CONFIGURACIÓN DE PLAN DEL SISTEMA -->
        <div v-if="isEditPlanModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
            <div class="relative w-full max-w-lg bg-white rounded-[2.5rem] shadow-2xl border border-slate-100 overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 block">Arquitectura SaaS</span>
                        <h3 class="text-xl font-black text-slate-900">Configurar Plan: {{ currentEditingPlan?.name }}</h3>
                    </div>
                    <button @click="isEditPlanModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100">
                        <X :size="20" />
                    </button>
                </div>

                <form @submit.prevent="submitEditPlan" class="p-8 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Nombre del Plan *</label>
                            <input 
                                v-model="editPlanForm.name" 
                                type="text" 
                                required 
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Precio Mensual ($) *</label>
                            <input 
                                v-model.number="editPlanForm.price" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                required 
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Máximo de Entidades Permitidas *</label>
                        <input 
                            v-model.number="editPlanForm.max_entities" 
                            type="number" 
                            min="1" 
                            required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                        />
                        <span class="text-[10px] text-slate-400 font-bold block mt-1">Colocá 999 para ilimitadas.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Tipos de Entidades Habilitadas *</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button 
                                type="button" 
                                v-for="type in ['hogar', 'oficina', 'comercio']" 
                                :key="type"
                                @click="toggleEntityType(type)"
                                :class="[
                                    'py-2.5 px-3 rounded-xl text-xs font-black uppercase tracking-wider border transition-all cursor-pointer flex items-center justify-center gap-1.5',
                                    editPlanForm.allowed_entity_types.includes(type) 
                                        ? 'bg-emerald-50 border-emerald-300 text-emerald-700 shadow-xs' 
                                        : 'bg-slate-50 border-slate-200 text-slate-400'
                                ]"
                            >
                                <CheckCircle2 v-if="editPlanForm.allowed_entity_types.includes(type)" :size="13" />
                                <span>{{ type }}</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Descripción / Features</label>
                        <input 
                            v-model="editPlanForm.features" 
                            type="text" 
                            placeholder="Ej: Hasta 3 entidades (hogar, oficina, comercio)" 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                        />
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <button type="button" @click="isEditPlanModalOpen = false" class="px-5 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 rounded-xl">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="editPlanForm.processing" class="bg-[#009966] hover:bg-[#008055] text-white font-black text-xs uppercase tracking-wider px-6 py-3 rounded-2xl shadow-md cursor-pointer disabled:opacity-50">
                            Guardar Plan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </MainLayout>
</template>
