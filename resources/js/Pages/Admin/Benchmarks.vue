<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { 
    TrendingUp, 
    Plus, 
    Star, 
    Zap, 
    ArrowUpRight,
    Award
} from 'lucide-vue-next';

const props = defineProps({
    benchmarks: Array,
    categories: Array
});
</script>

<template>
    <Head title="Benchmarks de Eficiencia" />

    <MainLayout>
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
                <div>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tighter mb-2">Benchmarks de Referencia</h1>
                    <p class="text-slate-500 font-medium">Equipos "Standard Gold" utilizados para comparativas y recomendaciones.</p>
                </div>
                <button class="bg-slate-900 text-white px-8 py-4 rounded-2xl font-black uppercase tracking-widest text-xs flex items-center gap-3 hover:bg-emerald-600 transition-all shadow-xl shadow-slate-200">
                    <Plus :size="20" /> Agregar Benchmark
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="benchmark in benchmarks" :key="benchmark.id" class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-2xl transition-all overflow-hidden group">
                    <!-- Card Header -->
                    <div class="p-8 pb-4">
                        <div class="flex justify-between items-start mb-6">
                            <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-emerald-600 group-hover:text-white transition-all">
                                <Star :size="24" />
                            </div>
                            <span class="px-4 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest rounded-full">
                                {{ benchmark.energy_label }}
                            </span>
                        </div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight mb-1">{{ benchmark.name }}</h3>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">{{ benchmark.category?.name }}</p>
                    </div>

                    <!-- Metrics -->
                    <div class="px-8 py-6 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Potencia Ideal</p>
                            <p class="text-xl font-black text-slate-900">{{ benchmark.watts }}W</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Ratio Efic.</p>
                            <p class="text-xl font-black text-emerald-600">{{ benchmark.efficiency_ratio || 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="p-8">
                        <p class="text-slate-500 text-sm leading-relaxed mb-8 line-clamp-3 italic">
                            "{{ benchmark.recommendation_text || 'Sin descripción de recomendación.' }}"
                        </p>
                        <button class="w-full py-4 bg-white border-2 border-slate-100 rounded-2xl font-black text-xs uppercase tracking-widest text-slate-600 hover:border-emerald-600 hover:text-emerald-600 transition-all flex items-center justify-center gap-2">
                            Ver Detalles <ArrowUpRight :size="16" />
                        </button>
                    </div>
                </div>

                <!-- Empty State / Add New -->
                <button class="bg-slate-50 border-4 border-dashed border-slate-200 rounded-[2.5rem] p-12 flex flex-col items-center justify-center gap-6 group hover:bg-emerald-50 hover:border-emerald-200 transition-all">
                    <div class="w-20 h-20 bg-white rounded-3xl flex items-center justify-center text-slate-300 group-hover:text-emerald-600 shadow-sm transition-all">
                        <Plus :size="40" />
                    </div>
                    <p class="font-black text-slate-400 group-hover:text-emerald-600 uppercase tracking-widest text-sm">Nuevo Benchmark</p>
                </button>
            </div>
            
            <!-- Pro Tip -->
            <div class="mt-16 bg-white p-12 rounded-[3.5rem] border border-slate-100 shadow-sm flex flex-col md:flex-row items-center gap-12">
                <div class="w-40 h-40 bg-emerald-600 rounded-[3rem] flex items-center justify-center text-white shadow-2xl shadow-emerald-200 shrink-0">
                    <Award :size="64" />
                </div>
                <div>
                    <h3 class="text-3xl font-black text-slate-900 mb-4 tracking-tighter">Venta Basada en Valor</h3>
                    <p class="text-slate-500 text-lg leading-relaxed max-w-3xl">
                        Los benchmarks permiten que ModoAhorro deje de ser solo una app de gastos para ser una herramienta de decisión. Al comparar el equipo ineficiente del usuario contra estos modelos de referencia, generamos un **"Gap de Eficiencia"** que justifica financieramente el recambio tecnológico.
                    </p>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
