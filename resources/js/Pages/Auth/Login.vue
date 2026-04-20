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
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-2xl text-white shadow-xl mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight">Bienvenido</h1>
                <p class="text-gray-500 font-medium mt-2">Ingresa a tu cuenta de ModoAhorro</p>
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
                        class="block w-full rounded-2xl border-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-4 bg-gray-50 font-medium"
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
                        <a href="#" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors uppercase tracking-widest">¿La olvidaste?</a>
                    </div>
                    <input 
                        v-model="form.password"
                        type="password" 
                        required
                        class="block w-full rounded-2xl border-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-4 bg-gray-50 font-medium"
                        placeholder="••••••••"
                        autocomplete="current-password"
                    />
                    <div v-if="form.errors.password" class="text-rose-600 text-xs font-bold mt-2 ml-1">
                        {{ form.errors.password }}
                    </div>
                </div>

                <div class="flex items-center">
                    <input v-model="form.remember" type="checkbox" class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    <span class="ml-3 text-sm font-bold text-gray-600 uppercase tracking-wide">Recordarme</span>
                </div>

                <button 
                    type="submit" 
                    :disabled="form.processing"
                    class="w-full bg-indigo-600 text-white py-4 px-6 rounded-2xl font-black text-lg shadow-xl shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all disabled:opacity-50 disabled:translate-y-0"
                >
                    {{ form.processing ? 'Verificando...' : 'Entrar' }}
                </button>
            </form>

            <p class="text-center mt-10 text-gray-400 font-bold uppercase tracking-widest text-xs">
                ¿No tienes cuenta? <a href="#" class="text-indigo-600 hover:text-orange-500 transition-colors">Empezar gratis</a>
            </p>
        </div>
    </div>
</template>
