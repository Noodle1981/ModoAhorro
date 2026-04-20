<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    PenTool, 
    CheckCircle2, 
    Calendar, 
    Plus, 
    TrendingDown, 
    Zap, 
    Clock, 
    ChevronRight,
    AlertCircle,
    Settings,
    Activity,
    Wrench
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    tasks: Array
});

const getPriorityColor = (priority) => {
    switch (priority?.toLowerCase()) {
        case 'alta': return 'text-rose-500 bg-rose-50 border-rose-100';
        case 'media': return 'text-amber-500 bg-amber-50 border-amber-100';
        default: return 'text-sky-500 bg-sky-50 border-sky-100';
    }
};
</script>

<template>
    <MainLayout>
        <Head title="Plan de Mantenimiento" />

        <div class="max-w-7xl mx-auto space-y-12">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-slate-900 text-white rounded-full text-[10px] font-black uppercase tracking-widest">
                        <Wrench :size="14" />
                        Prevencón Activa
                    </div>
                    <h1 class="text-5xl font-black text-slate-900 tracking-tighter leading-none">
                        Mantenimiento <span class="text-energy-consumption">Preventivo</span>
                    </h1>
                    <p class="text-lg text-slate-500 font-medium">Tareas técnicas que aseguran que tus equipos operen al 100% de eficiencia.</p>
                </div>

                <div class="bg-indigo-900 px-8 py-6 rounded-[32px] text-white flex items-center gap-6 shadow-xl shadow-indigo-900/20">
                    <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center text-energy-solar shrink-0">
                        <Zap :size="28" />
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-indigo-400 uppercase tracking-widest leading-none mb-2">Ahorro por Mantenimiento</p>
                        <p class="text-2xl font-black leading-none">Hasta 15%</p>
                    </div>
                </div>
            </div>

            <!-- Tasks Table / List -->
            <div class="bg-white rounded-[48px] border border-slate-100 shadow-2xl shadow-slate-200/30 overflow-hidden">
                <div class="p-10 border-b border-slate-50 flex items-center justify-between">
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Tareas Pendientes</h3>
                    <div class="flex items-center gap-4">
                        <span class="text-xs font-bold text-slate-400">Total: {{ tasks.length }} tareas</span>
                    </div>
                </div>

                <div class="p-0">
                    <div v-if="tasks.length > 0" class="divide-y divide-slate-50">
                        <div 
                            v-for="task in tasks" 
                            :key="task.id"
                            class="group p-10 hover:bg-slate-50/80 transition-all flex flex-col md:flex-row items-start md:items-center justify-between gap-8"
                        >
                            <div class="flex items-center gap-8 flex-1">
                                <div class="w-16 h-16 rounded-[24px] bg-slate-50 flex items-center justify-center text-slate-400 group-hover:bg-energy-consumption/10 group-hover:text-energy-consumption transition-all">
                                    <Activity :size="32" />
                                </div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-3">
                                        <h4 class="text-2xl font-black text-slate-900 tracking-tight">{{ task.name }}</h4>
                                        <span :class="['px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border', getPriorityColor(task.priority)]">
                                            {{ task.priority }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-slate-500 font-medium">Equipo: <span class="text-slate-900 font-bold">{{ task.equipment_name }}</span></p>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-10 w-full md:w-auto">
                                <div class="space-y-1">
                                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">Impacto en Eficiencia</p>
                                    <div class="flex items-center gap-2 text-energy-success font-black">
                                        <TrendingDown :size="16" />
                                        <span>+{{ task.efficiency_gain }}%</span>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">Frecuencia Sugerida</p>
                                    <p class="text-xs font-bold text-slate-700">{{ task.frequency }}</p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">Próxima Fecha</p>
                                    <div class="flex items-center gap-2 text-slate-400 font-bold text-xs">
                                        <Calendar :size="14" />
                                        <span>{{ task.due_date }}</span>
                                    </div>
                                </div>
                            </div>

                            <button class="bg-white border border-slate-100 p-4 rounded-2xl text-slate-400 hover:bg-slate-900 hover:text-white transition-all shadow-sm">
                                <CheckCircle2 :size="20" />
                            </button>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="p-32 text-center flex flex-col items-center gap-6">
                        <div class="w-20 h-20 bg-slate-50 rounded-3xl flex items-center justify-center text-slate-200">
                            <PenTool :size="40" />
                        </div>
                        <div class="space-y-2">
                            <h4 class="text-2xl font-black text-slate-900 tracking-tight">Todo bajo control</h4>
                            <p class="text-slate-400 font-medium max-w-sm mx-auto">No hay tareas de mantenimiento críticas pendientes para esta entidad en este momento.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maintenance Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-energy-consumption p-12 rounded-[48px] text-white flex flex-col justify-between space-y-8 relative overflow-hidden group">
                    <Settings :size="100" class="absolute -right-8 -bottom-8 text-white/5 opacity-50 group-hover:rotate-45 transition-transform duration-1000" />
                    <div class="space-y-4">
                        <h4 class="text-3xl font-black tracking-tight">¿Por qué aumenta el consumo?</h4>
                        <p class="text-blue-100 font-medium leading-relaxed">
                            Un filtro de aire acondicionado sucio requiere un **15% más de energía** para enfriar el mismo ambiente. Un termotanque con sarro consume **hasta un 25% más** de gas o electricidad para calentar el agua.
                        </p>
                    </div>
                    <div class="flex items-center gap-2 bg-white/10 w-fit px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest">
                        <Clock :size="14" />
                        Evita el desgaste prematuro
                    </div>
                </div>

                <div class="bg-white border border-slate-100 p-12 rounded-[48px] shadow-2xl shadow-slate-200/20 space-y-8 flex flex-col justify-between relative overflow-hidden group">
                    <div class="absolute -left-12 -top-12 w-40 h-40 bg-energy-solar/5 rounded-full"></div>
                    <div class="space-y-4">
                        <h4 class="text-3xl font-black text-slate-900 tracking-tight">Servicio Técnico</h4>
                        <p class="text-slate-500 font-medium leading-relaxed">
                            Contamos con una red de profesionales calificados para realizar estos mantenimientos por ti, garantizando los niveles de ahorro calculados por el sistema.
                        </p>
                    </div>
                    <button class="w-full bg-slate-900 text-white py-5 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-energy-consumption transition-all shadow-xl shadow-slate-200">
                        Contactar un Experto
                    </button>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
