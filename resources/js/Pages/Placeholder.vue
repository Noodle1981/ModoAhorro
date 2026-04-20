<script setup>
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    Construction, 
    ArrowLeft,
    Clock
} from 'lucide-vue-next';

defineProps({
    title: String
});
</script>

<template>
    <MainLayout>
        <Head :title="title" />
        
        <div class="h-full flex items-center justify-center min-h-[60vh]">
            <div class="max-w-md w-full text-center space-y-8 p-12 bg-white rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden">
                <!-- Background Decorative -->
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-energy-consumption/5 rounded-full blur-3xl"></div>
                
                <div class="relative z-10 space-y-6">
                    <div class="w-20 h-20 bg-amber-50 text-amber-500 rounded-3xl flex items-center justify-center mx-auto shadow-sm">
                        <Construction :size="40" stroke-width="2" />
                    </div>

                    <div class="space-y-2">
                        <h1 class="text-3xl font-black text-slate-900 tracking-tight">{{ title }}</h1>
                        <p class="text-slate-500 font-medium text-sm leading-relaxed">
                            Esta sección se encuentra actualmente bajo migración del sistema legacy. 
                            Próximamente disponible con el nuevo Motor de Eficiencia v3.
                        </p>
                    </div>

                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-slate-50 text-slate-400 rounded-full text-[10px] font-black uppercase tracking-widest border border-slate-100">
                        <Clock :size="12" />
                        Lanzamiento Estimado: Q2 2026
                    </div>

                    <div class="pt-6">
                        <button @click="window.history.back()" class="flex items-center justify-center gap-2 w-full p-4 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-energy-consumption transition-all transform hover:-translate-y-1">
                            <ArrowLeft :size="16" stroke-width="3" />
                            Regresar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
