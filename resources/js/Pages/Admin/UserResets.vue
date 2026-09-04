<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { 
    Link as LinkIcon, 
    Copy, 
    Check, 
    Trash2, 
    Search, 
    X, 
    Lock, 
    Mail, 
    Calendar,
    Sparkles
} from 'lucide-vue-next';
import { shallowRef, computed } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    activeTokens: Array,
    users: Array,
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const searchQuery = shallowRef('');
const copiedToken = shallowRef(null);

const filteredTokens = computed(() => {
    return props.activeTokens.filter(t => {
        const query = searchQuery.value.toLowerCase();
        return t.email.toLowerCase().includes(query) ||
               t.user_name.toLowerCase().includes(query);
    });
});

const copyToClipboard = (url, token) => {
    navigator.clipboard.writeText(url);
    copiedToken.value = token;
    setTimeout(() => {
        copiedToken.value = null;
    }, 2500);
};

// Formulario de Generar Enlace
const generateForm = useForm({
    email: props.users.length > 0 ? props.users[0].email : '',
});

const isGenerateModalOpen = shallowRef(false);

const submitGenerate = () => {
    generateForm.post(route('sistema.users.resets.generate'), {
        preserveScroll: true,
        onSuccess: () => {
            isGenerateModalOpen.value = false;
        }
    });
};

// Formulario de Forzar Contraseña
const isForceModalOpen = shallowRef(false);
const forceForm = useForm({
    user_id: props.users.length > 0 ? props.users[0].id : '',
    new_password: '',
});

const openForceModal = (user = null) => {
    forceForm.reset();
    forceForm.clearErrors();
    if (user && user.id) {
        forceForm.user_id = user.id;
    } else if (props.users.length > 0) {
        forceForm.user_id = props.users[0].id;
    }
    isForceModalOpen.value = true;
};

const submitForceReset = () => {
    forceForm.post(route('sistema.users.resets.force'), {
        preserveScroll: true,
        onSuccess: () => {
            isForceModalOpen.value = false;
        }
    });
};

const revokeToken = (email) => {
    router.delete(route('sistema.users.resets.destroy', encodeURIComponent(email)), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Reseteos de Clave · Administrador" />

    <MainLayout>
        <div class="max-w-7xl mx-auto space-y-8">
            
            <!-- Page Header Ribbon -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 sm:p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                <div>
                    <span class="text-xs font-black uppercase tracking-widest text-emerald-600 block mb-1">
                        Seguridad & Recuperación de Cuentas
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Reseteos de Contraseña
                    </h1>
                    <p class="text-slate-500 font-medium text-sm mt-1">
                        Generá enlaces seguros temporales para usuarios o restablecé contraseñas de forma asistida.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button 
                        @click="isGenerateModalOpen = true"
                        class="inline-flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-black text-xs px-5 py-3.5 rounded-2xl transition-all cursor-pointer uppercase tracking-wider"
                    >
                        <LinkIcon :size="15" />
                        <span>Generar Enlace</span>
                    </button>

                    <button 
                        @click="openForceModal()"
                        class="inline-flex items-center gap-2 bg-[#009966] hover:bg-[#008055] text-white font-black text-xs px-6 py-3.5 rounded-2xl shadow-lg shadow-emerald-900/10 hover:shadow-emerald-900/20 transition-all hover:-translate-y-0.5 cursor-pointer uppercase tracking-wider shrink-0"
                    >
                        <Lock :size="16" />
                        <span>Forzar Nueva Clave</span>
                    </button>
                </div>
            </div>

            <!-- Banner con Enlace Generado Reciente -->
            <div v-if="flash?.generated_reset_url" class="p-6 bg-emerald-900 rounded-[2rem] text-white shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4 animate-in fade-in duration-300">
                <div class="space-y-1 overflow-hidden">
                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-300 flex items-center gap-1.5">
                        <Sparkles :size="13" /> Enlace de Reseteo Listo para Enviar
                    </span>
                    <p class="font-mono text-xs text-emerald-100 truncate max-w-2xl bg-emerald-950/60 p-2.5 rounded-xl border border-emerald-700/50 select-all">
                        {{ flash.generated_reset_url }}
                    </p>
                </div>
                <button 
                    @click="copyToClipboard(flash.generated_reset_url, 'generated')"
                    class="px-5 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 rounded-xl font-black text-xs uppercase tracking-wider flex items-center gap-2 cursor-pointer shrink-0 transition-all"
                >
                    <Check v-if="copiedToken === 'generated'" :size="14" />
                    <Copy v-else :size="14" />
                    <span>{{ copiedToken === 'generated' ? '¡Copiado!' : 'Copiar Enlace' }}</span>
                </button>
            </div>

            <!-- Toolbar & Filtros -->
            <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm flex items-center justify-between gap-3">
                <div class="relative w-full max-w-md">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="18" />
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Buscar por usuario o correo..." 
                        class="w-full pl-12 pr-6 py-3 bg-slate-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-medium text-sm text-slate-800"
                    />
                </div>

                <span class="text-xs font-bold text-slate-400">
                    {{ activeTokens.length }} tokens activos
                </span>
            </div>

            <!-- Table of Active Tokens -->
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50 text-[10px] font-black uppercase tracking-wider text-slate-400">
                                <th class="py-4 px-6 sm:px-8">Usuario Destino</th>
                                <th class="py-4 px-6">Enlace Temporal</th>
                                <th class="py-4 px-6">Solicitado</th>
                                <th class="py-4 px-6 sm:px-8 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                            <tr 
                                v-for="token in filteredTokens" 
                                :key="token.email" 
                                class="hover:bg-slate-50/80 transition-colors group"
                            >
                                <td class="py-4 px-6 sm:px-8">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center font-black text-xs">
                                            {{ token.user_name.charAt(0) }}
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-900">{{ token.user_name }}</p>
                                            <p class="text-xs text-slate-400 flex items-center gap-1">
                                                <Mail :size="11" />
                                                {{ token.email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-4 px-6">
                                    <button 
                                        @click="copyToClipboard(token.reset_url, token.token)"
                                        class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 rounded-xl border border-slate-200 hover:border-emerald-200 text-xs font-bold transition-all cursor-pointer"
                                    >
                                        <Check v-if="copiedToken === token.token" :size="13" class="text-emerald-600" />
                                        <Copy v-else :size="13" />
                                        <span>{{ copiedToken === token.token ? '¡Copiado!' : 'Copiar URL' }}</span>
                                    </button>
                                </td>

                                <td class="py-4 px-6 text-xs text-slate-400 font-medium">
                                    <span class="flex items-center gap-1.5">
                                        <Calendar :size="13" />
                                        {{ new Date(token.created_at).toLocaleString('es-AR') }}
                                    </span>
                                </td>

                                <td class="py-4 px-6 sm:px-8 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button 
                                            @click="openForceModal({ id: token.user_id })"
                                            title="Cambiar contraseña directamente"
                                            class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all cursor-pointer"
                                        >
                                            <Lock :size="16" />
                                        </button>
                                        <button 
                                            @click="revokeToken(token.email)"
                                            title="Revocar token de recuperación"
                                            class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all cursor-pointer"
                                        >
                                            <Trash2 :size="16" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="filteredTokens.length === 0" class="p-12 text-center text-slate-400">
                    <p class="font-bold text-sm">No hay tokens de recuperación pendientes en este momento.</p>
                </div>
            </div>

        </div>

        <!-- MODAL GENERAR ENLACE -->
        <Modal :show="isGenerateModalOpen" max-width="md" @close="isGenerateModalOpen = false">
            <div class="relative w-full overflow-hidden">
                <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 block">Recuperación</span>
                        <h3 class="text-xl font-black text-slate-900">Generar Enlace de Reseteo</h3>
                    </div>
                    <button @click="isGenerateModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 cursor-pointer">
                        <X :size="20" />
                    </button>
                </div>

                <form @submit.prevent="submitGenerate" class="p-8 space-y-4">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Seleccionar Usuario *</label>
                        <select 
                            v-model="generateForm.email" 
                            required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                        >
                            <option v-for="u in users" :key="u.id" :value="u.email">
                                {{ u.name }} ({{ u.email }})
                            </option>
                        </select>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <button type="button" @click="isGenerateModalOpen = false" class="px-5 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 rounded-xl cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="generateForm.processing" class="bg-[#009966] hover:bg-[#008055] text-white font-black text-xs uppercase tracking-wider px-6 py-3 rounded-2xl shadow-md cursor-pointer disabled:opacity-50">
                            Generar Enlace
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- MODAL FORZAR NUEVA CONTRASEÑA -->
        <Modal :show="isForceModalOpen" max-width="md" @close="isForceModalOpen = false">
            <div class="relative w-full overflow-hidden">
                <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 block">Acceso Directo</span>
                        <h3 class="text-xl font-black text-slate-900">Forzar Nueva Contraseña</h3>
                    </div>
                    <button @click="isForceModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100 cursor-pointer">
                        <X :size="20" />
                    </button>
                </div>

                <form @submit.prevent="submitForceReset" class="p-8 space-y-4">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Usuario *</label>
                        <select 
                            v-model="forceForm.user_id" 
                            required 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                        >
                            <option v-for="u in users" :key="u.id" :value="u.id">
                                {{ u.name }} ({{ u.email }})
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Nueva Contraseña *</label>
                        <input 
                            v-model="forceForm.new_password" 
                            type="password" 
                            required 
                            minlength="6"
                            placeholder="Mínimo 6 caracteres" 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                        />
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <button type="button" @click="isForceModalOpen = false" class="px-5 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 rounded-xl cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="forceForm.processing" class="bg-[#009966] hover:bg-[#008055] text-white font-black text-xs uppercase tracking-wider px-6 py-3 rounded-2xl shadow-md cursor-pointer disabled:opacity-50">
                            Actualizar Contraseña
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

    </MainLayout>
</template>
