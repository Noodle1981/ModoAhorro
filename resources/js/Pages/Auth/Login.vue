<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Identificación" />

    <div class="min-h-screen bg-linear-to-br from-indigo-50 via-white to-orange-50 flex items-center justify-center p-6">
        <div class="max-w-md w-full">
            <!-- Logo / Título -->
            <div class="text-center mb-8">
                <Link href="/" class="inline-block transition-transform hover:scale-105">
                    <img src="/images/landing/logo.png" alt="ModoAhorro Logo" class="w-20 h-20 mx-auto mb-3 object-contain drop-shadow-md" />
                </Link>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Bienvenido</h1>
                <p class="text-slate-500 font-medium text-sm mt-1">Ingresá a tu cuenta de <span class="text-emerald-600 font-bold">ModoAhorro</span></p>
            </div>

            <div v-if="status" class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-2xl text-sm font-bold border border-emerald-100">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="bg-white p-8 rounded-3xl shadow-2xl shadow-gray-200/50 border border-gray-100 space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Correo Electrónico</label>
                    <input 
                        v-model="form.email"
                        type="email" 
                        required
                        class="block w-full rounded-2xl border-gray-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4 bg-gray-50 font-medium"
                        placeholder="tu@correo.com"
                        autocomplete="username"
                    />
                    <div v-if="form.errors.email" class="text-rose-600 text-xs font-bold mt-2 ml-1">
                        {{ form.errors.email }}
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-bold text-gray-700">Contraseña</label>
                        <a href="#" class="text-xs font-bold text-emerald-600 hover:text-emerald-800 transition-colors uppercase tracking-widest">¿La olvidaste?</a>
                    </div>
                    <input 
                        v-model="form.password"
                        type="password" 
                        required
                        class="block w-full rounded-2xl border-gray-100 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-4 bg-gray-50 font-medium"
                        placeholder="••••••••"
                        autocomplete="current-password"
                    />
                    <div v-if="form.errors.password" class="text-rose-600 text-xs font-bold mt-2 ml-1">
                        {{ form.errors.password }}
                    </div>
                </div>

                <div class="flex items-center">
                    <input v-model="form.remember" type="checkbox" class="h-5 w-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500" />
                    <span class="ml-3 text-sm font-bold text-gray-600 uppercase tracking-wide">Recordarme</span>
                </div>

                <button 
                    type="submit" 
                    :disabled="form.processing"
                    class="w-full bg-emerald-600 text-white py-4 px-6 rounded-2xl font-black text-lg shadow-xl shadow-emerald-200 hover:bg-emerald-700 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:translate-y-0 cursor-pointer"
                >
                    {{ form.processing ? 'Verificando...' : 'Entrar' }}
                </button>
            </form>

            <p class="text-center mt-8 text-slate-500 font-medium text-xs">
                ¿No tienes cuenta? <span class="text-emerald-600 font-bold">Registro por invitación (fase beta)</span>
            </p>
        </div>
    </div>
</template>
