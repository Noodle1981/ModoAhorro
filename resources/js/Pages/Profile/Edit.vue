<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    User, 
    Mail, 
    Lock, 
    ShieldCheck, 
    Building2, 
    Calendar, 
    CheckCircle2, 
    KeyRound,
    Save
} from 'lucide-vue-next';

const props = defineProps({
    userData: Object,
    entities: Array,
    status: String,
});

// Formulario de Datos Personales
const profileForm = useForm({
    name: props.userData.name,
    email: props.userData.email,
});

const profileUpdated = ref(false);

const updateProfile = () => {
    profileForm.put(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            profileUpdated.value = true;
            setTimeout(() => {
                profileUpdated.value = false;
            }, 3000);
        },
    });
};

// Formulario de Contraseña
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const passwordUpdated = ref(false);

const updatePassword = () => {
    passwordForm.put(route('profile.password'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            passwordUpdated.value = true;
            setTimeout(() => {
                passwordUpdated.value = false;
            }, 3000);
        },
    });
};
</script>

<template>
    <MainLayout>
        <Head title="Mi Perfil" />

        <div class="max-w-5xl mx-auto space-y-6 pb-16">
            <!-- Header Card with User Overview -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 font-black text-2xl shadow-xs shrink-0">
                        {{ userData.name?.charAt(0)?.toUpperCase() || 'U' }}
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h2 class="text-xl font-black text-slate-900 tracking-tight">{{ userData.name }}</h2>
                            <span 
                                :class="[
                                    'px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider',
                                    userData.is_super_admin 
                                        ? 'bg-purple-100 text-purple-700' 
                                        : 'bg-emerald-100 text-emerald-700'
                                ]"
                            >
                                {{ userData.is_super_admin ? 'Super Admin' : 'Usuario' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 font-medium flex items-center gap-1.5">
                            <Mail :size="12" class="text-slate-400" />
                            <span>{{ userData.email }}</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4 text-xs font-medium text-slate-500 bg-slate-50 p-3 rounded-2xl border border-slate-100">
                    <div class="flex items-center gap-2">
                        <Calendar :size="14" class="text-slate-400" />
                        <span>Miembro desde: <strong>{{ userData.created_at || '—' }}</strong></span>
                    </div>
                    <div class="w-px h-4 bg-slate-200"></div>
                    <div class="flex items-center gap-2">
                        <Building2 :size="14" class="text-slate-400" />
                        <span><strong>{{ userData.entities_count }}</strong> {{ userData.entities_count === 1 ? 'Entidad' : 'Entidades' }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Left Column: Personal Information & Password -->
                <div class="lg:col-span-7 space-y-6">
                    <!-- Form 1: Información Personal -->
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-7 space-y-5">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700">
                                    <User :size="16" />
                                </div>
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Datos Personales</h3>
                            </div>
                        </div>

                        <form @submit.prevent="updateProfile" class="space-y-4">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Nombre Completo</label>
                                <input 
                                    v-model="profileForm.name"
                                    type="text" 
                                    required
                                    class="w-full bg-slate-50 border border-slate-200/80 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none"
                                />
                                <p v-if="profileForm.errors.name" class="text-[10px] text-rose-500 font-bold">{{ profileForm.errors.name }}</p>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Correo Electrónico</label>
                                <input 
                                    v-model="profileForm.email"
                                    type="email" 
                                    required
                                    class="w-full bg-slate-50 border border-slate-200/80 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none"
                                />
                                <p v-if="profileForm.errors.email" class="text-[10px] text-rose-500 font-bold">{{ profileForm.errors.email }}</p>
                            </div>

                            <div class="flex items-center justify-between pt-2">
                                <transition name="fade">
                                    <span v-if="profileUpdated" class="text-xs font-bold text-emerald-600 flex items-center gap-1.5">
                                        <CheckCircle2 :size="14" /> Datos actualizados
                                    </span>
                                </transition>

                                <button 
                                    type="submit"
                                    :disabled="profileForm.processing"
                                    class="ml-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-xs flex items-center gap-2 cursor-pointer disabled:opacity-50"
                                >
                                    <Save :size="14" />
                                    <span>{{ profileForm.processing ? 'Guardando...' : 'Guardar Cambios' }}</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Form 2: Seguridad y Contraseña -->
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 sm:p-7 space-y-5">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700">
                                    <KeyRound :size="16" />
                                </div>
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Seguridad y Contraseña</h3>
                            </div>
                        </div>

                        <form @submit.prevent="updatePassword" class="space-y-4">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Contraseña Actual</label>
                                <input 
                                    v-model="passwordForm.current_password"
                                    type="password" 
                                    required
                                    class="w-full bg-slate-50 border border-slate-200/80 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none"
                                />
                                <p v-if="passwordForm.errors.current_password" class="text-[10px] text-rose-500 font-bold">{{ passwordForm.errors.current_password }}</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Nueva Contraseña</label>
                                    <input 
                                        v-model="passwordForm.password"
                                        type="password" 
                                        required
                                        class="w-full bg-slate-50 border border-slate-200/80 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none"
                                    />
                                    <p v-if="passwordForm.errors.password" class="text-[10px] text-rose-500 font-bold">{{ passwordForm.errors.password }}</p>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Confirmar Contraseña</label>
                                    <input 
                                        v-model="passwordForm.password_confirmation"
                                        type="password" 
                                        required
                                        class="w-full bg-slate-50 border border-slate-200/80 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all outline-none"
                                    />
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-2">
                                <transition name="fade">
                                    <span v-if="passwordUpdated" class="text-xs font-bold text-emerald-600 flex items-center gap-1.5">
                                        <CheckCircle2 :size="14" /> Contraseña actualizada
                                    </span>
                                </transition>

                                <button 
                                    type="submit"
                                    :disabled="passwordForm.processing"
                                    class="ml-auto px-5 py-2.5 bg-slate-900 hover:bg-slate-800 active:scale-95 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-xs flex items-center gap-2 cursor-pointer disabled:opacity-50"
                                >
                                    <Lock :size="14" />
                                    <span>{{ passwordForm.processing ? 'Actualizando...' : 'Actualizar Contraseña' }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Right Column: Entities & Account Info -->
                <div class="lg:col-span-5 space-y-6">
                    <!-- Entidades Asociadas -->
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-md p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700">
                                    <Building2 :size="16" />
                                </div>
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Tus Entidades</h3>
                            </div>
                            <span class="text-[10px] font-black px-2 py-0.5 bg-slate-100 rounded-full text-slate-600">
                                {{ entities.length }}
                            </span>
                        </div>

                        <div class="space-y-2.5">
                            <div 
                                v-for="ent in entities" 
                                :key="ent.id"
                                class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100/80 flex items-center justify-between gap-3 hover:bg-slate-100/60 transition-colors"
                            >
                                <div class="min-w-0">
                                    <h4 class="text-xs font-black text-slate-900 truncate">{{ ent.name }}</h4>
                                    <p class="text-[10px] text-slate-400 font-medium truncate">
                                        {{ ent.city || ent.address || 'Ubicación configurada' }}
                                    </p>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider bg-white border border-slate-200 text-slate-600 shrink-0">
                                    {{ ent.type || 'Hogar' }}
                                </span>
                            </div>

                            <div v-if="entities.length === 0" class="p-6 text-center text-slate-400 text-xs font-bold">
                                No tienes entidades registradas.
                            </div>
                        </div>
                    </div>

                    <!-- Seguridad y Privacidad -->
                    <div class="bg-emerald-50/50 border border-emerald-100/80 rounded-3xl p-5 space-y-2 text-emerald-950">
                        <div class="flex items-center gap-2 text-emerald-800 font-black text-xs uppercase tracking-wider">
                            <ShieldCheck :size="16" />
                            <span>Privacidad y Control</span>
                        </div>
                        <p class="text-[11px] text-emerald-800/80 font-medium leading-relaxed">
                            Tus datos energéticos y simulaciones están vinculados exclusivamente a tu cuenta y protegidos bajo cifrado de sesión.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
