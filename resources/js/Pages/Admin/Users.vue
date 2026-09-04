<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { 
    Users, 
    UserPlus, 
    ShieldCheck, 
    UserCheck, 
    Search, 
    Edit2, 
    Trash2, 
    X, 
    AlertTriangle,
    Building,
    Calendar,
    Mail
} from 'lucide-vue-next';
import { shallowRef, computed } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    users: Array,
});

const page = usePage();
const currentUserId = computed(() => page.props.auth?.user?.id);

const searchQuery = shallowRef('');
const roleFilter = shallowRef('all');

const filteredUsers = computed(() => {
    return props.users.filter(u => {
        const query = searchQuery.value.toLowerCase();
        const matchesSearch = u.name.toLowerCase().includes(query) ||
                              u.email.toLowerCase().includes(query);
        
        const matchesRole = roleFilter.value === 'all' ||
                            (roleFilter.value === 'admin' && u.is_super_admin) ||
                            (roleFilter.value === 'user' && !u.is_super_admin);

        return matchesSearch && matchesRole;
    });
});

const totalAdmins = computed(() => props.users.filter(u => u.is_super_admin).length);
const totalStandard = computed(() => props.users.filter(u => !u.is_super_admin).length);

// Modales de Creación / Edición
const isModalOpen = shallowRef(false);
const isEditing = shallowRef(false);
const currentEditUser = shallowRef(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    is_super_admin: false,
});

const openCreateModal = () => {
    isEditing.value = false;
    currentEditUser.value = null;
    form.reset();
    form.clearErrors();
    form.is_super_admin = false;
    isModalOpen.value = true;
};

const openEditModal = (user) => {
    isEditing.value = true;
    currentEditUser.value = user;
    form.clearErrors();
    form.name = user.name;
    form.email = user.email;
    form.password = ''; // Vacío para no cambiarlo si no se desea
    form.is_super_admin = Boolean(user.is_super_admin);
    isModalOpen.value = true;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('sistema.users.update', currentEditUser.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
            }
        });
    } else {
        form.post(route('sistema.users.store'), {
            preserveScroll: true,
            onSuccess: () => {
                isModalOpen.value = false;
            }
        });
    }
};

// Toggle Admin Role
const toggleAdminRole = (user) => {
    if (user.id === currentUserId.value) return;
    router.patch(route('sistema.users.toggle-admin', user.id), {}, {
        preserveScroll: true,
    });
};

// Modal de Borrado
const isDeleteModalOpen = shallowRef(false);
const userToDelete = shallowRef(null);

const promptDelete = (user) => {
    if (user.id === currentUserId.value) return;
    userToDelete.value = user;
    isDeleteModalOpen.value = true;
};

const confirmDelete = () => {
    if (!userToDelete.value) return;
    router.delete(route('sistema.users.destroy', userToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            userToDelete.value = null;
        }
    });
};
</script>

<template>
    <Head title="Gestión de Usuarios · Administrador" />

    <MainLayout>
        <div class="max-w-7xl mx-auto space-y-8">
            
            <!-- Page Header Ribbon -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 sm:p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                <div>
                    <span class="text-xs font-black uppercase tracking-widest text-emerald-600 block mb-1">
                        Control de Accesos & Seguridad
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Gestión de Usuarios
                    </h1>
                    <p class="text-slate-500 font-medium text-sm mt-1">
                        Administrá las cuentas de clientes, asigná roles de Super Administrador y gestioná contraseñas.
                    </p>
                </div>

                <button 
                    @click="openCreateModal"
                    class="inline-flex items-center justify-center gap-2 bg-[#009966] hover:bg-[#008055] text-white font-extrabold text-xs px-6 py-4 rounded-2xl shadow-lg shadow-emerald-900/10 hover:shadow-emerald-900/20 transition-all hover:-translate-y-0.5 cursor-pointer uppercase tracking-wider shrink-0"
                >
                    <UserPlus :size="18" />
                    <span>Nuevo Usuario</span>
                </button>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-xs flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <Users :size="24" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Total Usuarios</p>
                        <p class="text-2xl font-black text-slate-900">{{ users.length }}</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-xs flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <ShieldCheck :size="24" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Super Admins</p>
                        <p class="text-2xl font-black text-slate-900">{{ totalAdmins }}</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-xs flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <UserCheck :size="24" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Usuarios Estándar</p>
                        <p class="text-2xl font-black text-slate-900">{{ totalStandard }}</p>
                    </div>
                </div>
            </div>

            <!-- Toolbar & Filtros -->
            <div class="bg-white p-4 rounded-3xl border border-slate-100 shadow-sm flex flex-col lg:flex-row items-center gap-3">
                <div class="relative w-full lg:flex-1">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" :size="18" />
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Buscar por nombre o correo electrónico..." 
                        class="w-full pl-12 pr-6 py-3 bg-slate-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-medium text-sm text-slate-800"
                    />
                </div>

                <select 
                    v-model="roleFilter"
                    class="px-4 py-3 bg-slate-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-bold text-slate-700 text-xs min-w-[200px]"
                >
                    <option value="all">Todos los Roles</option>
                    <option value="admin">Solo Super Administradores</option>
                    <option value="user">Solo Usuarios Estándar</option>
                </select>
            </div>

            <!-- Table of Users -->
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/50 text-[10px] font-black uppercase tracking-wider text-slate-400">
                                <th class="py-4 px-6 sm:px-8">Usuario</th>
                                <th class="py-4 px-6">Rol / Permisos</th>
                                <th class="py-4 px-6 text-center">Entidades / Hogares</th>
                                <th class="py-4 px-6">Fecha Alta</th>
                                <th class="py-4 px-6 sm:px-8 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-700">
                            <tr 
                                v-for="user in filteredUsers" 
                                :key="user.id" 
                                class="hover:bg-slate-50/80 transition-colors group"
                            >
                                <!-- Nombre & Email -->
                                <td class="py-4 px-6 sm:px-8">
                                    <div class="flex items-center gap-3.5">
                                        <div 
                                            :class="[
                                                'w-10 h-10 rounded-2xl flex items-center justify-center font-black text-sm uppercase shadow-xs shrink-0',
                                                user.is_super_admin ? 'bg-purple-100 text-purple-700 font-extrabold' : 'bg-slate-100 text-slate-700'
                                            ]"
                                        >
                                            {{ user.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-black text-slate-900 group-hover:text-emerald-600 transition-colors">{{ user.name }}</span>
                                                <span v-if="user.id === currentUserId" class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[9px] font-black uppercase tracking-wider">
                                                    Tú
                                                </span>
                                            </div>
                                            <p class="text-xs text-slate-400 font-medium flex items-center gap-1.5 mt-0.5">
                                                <Mail :size="12" />
                                                {{ user.email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Rol / Permiso -->
                                <td class="py-4 px-6">
                                    <button 
                                        @click="toggleAdminRole(user)"
                                        :disabled="user.id === currentUserId"
                                        :title="user.id === currentUserId ? 'No puedes quitarte a ti mismo el rol' : 'Clic para alternar rol'"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black transition-all cursor-pointer disabled:cursor-default"
                                        :class="user.is_super_admin ? 'bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-100' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200'"
                                    >
                                        <ShieldCheck v-if="user.is_super_admin" :size="13" />
                                        <UserCheck v-else :size="13" />
                                        <span>{{ user.is_super_admin ? 'Super Admin' : 'Usuario Estándar' }}</span>
                                    </button>
                                </td>

                                <!-- Entidades Vinculadas -->
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-50 text-slate-700 rounded-xl font-black text-xs">
                                        <Building :size="13" class="text-slate-400" />
                                        {{ user.entities_count || 0 }}
                                    </span>
                                </td>

                                <!-- Fecha de Registro -->
                                <td class="py-4 px-6 text-xs text-slate-400 font-medium">
                                    <span class="flex items-center gap-1.5">
                                        <Calendar :size="13" />
                                        {{ new Date(user.created_at).toLocaleDateString('es-AR', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                                    </span>
                                </td>

                                <!-- Acciones -->
                                <td class="py-4 px-6 sm:px-8 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button 
                                            @click="openEditModal(user)"
                                            title="Editar usuario o contraseña"
                                            class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all cursor-pointer"
                                        >
                                            <Edit2 :size="16" />
                                        </button>

                                        <button 
                                            v-if="user.id !== currentUserId"
                                            @click="promptDelete(user)"
                                            title="Eliminar usuario"
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

                <!-- Empty state -->
                <div v-if="filteredUsers.length === 0" class="p-12 text-center text-slate-400">
                    <p class="font-bold text-sm">No se encontraron usuarios coincidentes con tu búsqueda.</p>
                </div>
            </div>

        </div>

        <!-- MODAL DE CREACIÓN / EDICIÓN -->
        <Modal :show="isModalOpen" max-width="lg" @close="isModalOpen = false">
            <div class="relative w-full overflow-hidden">
                
                <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 block">
                            {{ isEditing ? 'Modificar Cuenta' : 'Nueva Cuenta' }}
                        </span>
                        <h3 class="text-xl font-black text-slate-900">
                            {{ isEditing ? form.name : 'Registrar Nuevo Usuario' }}
                        </h3>
                    </div>
                    <button @click="isModalOpen = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-100">
                        <X :size="20" />
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-8 space-y-4">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Nombre Completo *</label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            required 
                            placeholder="Ej: Juan Pérez" 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">Correo Electrónico *</label>
                        <input 
                            v-model="form.email" 
                            type="email" 
                            required 
                            placeholder="usuario@ejemplo.com" 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-2">
                            {{ isEditing ? 'Nueva Contraseña (dejar en blanco para conservar)' : 'Contraseña *' }}
                        </label>
                        <input 
                            v-model="form.password" 
                            type="password" 
                            :required="!isEditing"
                            minlength="6"
                            placeholder="Mínimo 6 caracteres" 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-900 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-xs font-bold text-rose-500">{{ form.errors.password }}</p>
                    </div>

                    <!-- Super Admin Checkbox -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-black text-slate-900 uppercase tracking-wider">¿Rol Super Administrador?</p>
                            <p class="text-[11px] font-medium text-slate-500 mt-0.5">Acceso total al catálogo, algoritmos y gestión de usuarios.</p>
                        </div>
                        <input 
                            type="checkbox" 
                            v-model="form.is_super_admin" 
                            class="w-5 h-5 rounded-lg text-emerald-600 focus:ring-emerald-500 border-slate-300 cursor-pointer"
                        />
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <button 
                            type="button" 
                            @click="isModalOpen = false" 
                            class="px-5 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 rounded-xl cursor-pointer"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            :disabled="form.processing"
                            class="bg-[#009966] hover:bg-[#008055] text-white font-black text-xs uppercase tracking-wider px-6 py-3 rounded-2xl shadow-md cursor-pointer disabled:opacity-50"
                        >
                            {{ isEditing ? 'Guardar Cambios' : 'Crear Usuario' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- MODAL DE BORRADO -->
        <Modal :show="isDeleteModalOpen" max-width="md" @close="isDeleteModalOpen = false">
            <div class="p-8 space-y-6">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <AlertTriangle :size="28" />
                </div>
                <div>
                    <h3 class="text-xl font-black text-slate-900">¿Eliminar usuario?</h3>
                    <p class="text-sm font-medium text-slate-600 mt-2 leading-relaxed">
                        ¿Estás seguro de que deseas eliminar la cuenta de <strong>"{{ userToDelete?.name }}"</strong> ({{ userToDelete?.email }})? Esta acción no se puede deshacer.
                    </p>
                </div>
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button @click="isDeleteModalOpen = false" class="px-5 py-2.5 text-xs font-bold text-slate-500 hover:text-slate-800 rounded-xl cursor-pointer">
                        Cancelar
                    </button>
                    <button @click="confirmDelete" class="px-5 py-2.5 text-xs font-black text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-md cursor-pointer">
                        Eliminar Cuenta
                    </button>
                </div>
            </div>
        </Modal>

    </MainLayout>
</template>
