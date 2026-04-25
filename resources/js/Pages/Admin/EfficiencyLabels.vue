<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    Zap, 
    ArrowRight,
    Sliders,
    Info,
    RefreshCw
} from 'lucide-vue-next';

const props = defineProps({
    coefficients: Array,
    categories: Array
});

// Agrupar coeficientes por categoría
const grouped = props.categories.map(cat => ({
    ...cat,
    labels: props.coefficients.filter(c => c.category_id === cat.id)
})).filter(cat => cat.labels.length > 0);
</script>

<template>
    <Head title="Matriz de Eficiencia" />

    <MainLayout>
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-12">
                <div>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tighter mb-2">Matriz de Eficiencia</h1>
                    <p class="text-slate-500 font-medium">Define los multiplicadores de consumo para las etiquetas energéticas.</p>
                </div>
                <button class="bg-emerald-600 text-white px-6 py-3 rounded-2xl font-black uppercase tracking-widest text-xs flex items-center gap-2 hover:bg-slate-900 transition-all shadow-lg shadow-emerald-200">
                    <RefreshCw :size="18" /> Actualizar Matriz
                </button>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                <div v-for="cat in grouped" :key="cat.id" class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400">
                            <Sliders :size="24" />
                        </div>
                        <h2 class="text-2xl font-black text-slate-900 tracking-tighter">{{ cat.name }}</h2>
                    </div>

                    <div class="space-y-4">
                        <div v-for="coeff in cat.labels" :key="coeff.id" class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-transparent hover:border-emerald-600/20 hover:bg-white transition-all group">
                            <div class="flex items-center gap-4">
                                <div :class="[
                                    'w-14 h-8 rounded-lg flex items-center justify-center text-xs font-black text-white shadow-sm',
                                    coeff.label.startsWith('A') ? 'bg-emerald-500' : 
                                    coeff.label === 'B' ? 'bg-lime-500' :
                                    coeff.label === 'C' ? 'bg-amber-500' : 'bg-rose-500'
                                ]">
                                    {{ coeff.label }}
                                </div>
                                <span class="text-sm font-bold text-slate-600">Multiplicador</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="text-xl font-black text-slate-900">{{ coeff.coefficient }}x</span>
                                <div class="w-8 h-8 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400 group-hover:text-emerald-600 transition-colors opacity-0 group-hover:opacity-100">
                                    <ArrowRight :size="14" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROI Note -->
            <div class="mt-12 p-8 bg-emerald-950 rounded-[3rem] text-emerald-100 flex gap-8 items-center overflow-hidden relative">
                <div class="w-16 h-16 bg-emerald-800 rounded-2xl flex items-center justify-center text-emerald-400 shadow-inner shrink-0 relative z-10">
                    <Info :size="32" />
                </div>
                <div class="relative z-10">
                    <h4 class="text-xl font-black text-white mb-2 tracking-tight">Impacto en el Ahorro</h4>
                    <p class="text-emerald-300/80 leading-relaxed max-w-4xl font-medium">
                        Estos coeficientes son la base para calcular el ROI. Si un equipo pasa de Clase D (1.6x) a Clase A (1.0x), el sistema reportará un ahorro potencial del 37.5%. Asegúrate de sincronizar estos valores con las normativas locales (IRAM/Etiquetado Europeo).
                    </p>
                </div>
                <Zap :size="200" class="absolute -right-20 -top-20 text-emerald-900 opacity-20 rotate-12" />
            </div>
        </div>
    </MainLayout>
</template>
