<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    Thermometer, 
    Home, 
    Wind, 
    Sun, 
    ArrowRight,
    CheckCircle2,
    Umbrella
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    config: Object,
});

const form = useForm({
    roof_type: '',
    window_type: '',
    window_frame: '',
    orientation: 'norte_sur',
    sun_exposure: 'medium',
    roof_insulation: false,
    drafts_detected: false,
    south_window: false,
});

const submit = () => {
    form.post(route('gestion.thermal.store', props.entity.id));
};
</script>

<template>
    <MainLayout>
        <Head title="Diagnóstico Térmico" />

        <div class="max-w-4xl mx-auto">
            <!-- Header Section -->
            <div class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-energy-warning/10 text-energy-warning rounded-full text-[10px] font-black uppercase tracking-widest border border-energy-warning/20">
                        <Thermometer :size="14" />
                        Módulo de Eficiencia Térmica
                    </div>
                    <h1 class="text-5xl font-black text-slate-900 tracking-tighter leading-none">
                        Salud <span class="text-energy-warning">Térmica</span>
                    </h1>
                    <p class="text-lg text-slate-500 font-medium max-w-xl">
                        Analice la envolvente de <span class="text-slate-900 font-bold decoration-energy-warning/30 decoration-4 underline-offset-4 underline">{{ entity.name }}</span> para optimizar su confort.
                    </p>
                </div>
                
                <div class="hidden md:flex items-center gap-4 text-slate-300">
                    <div class="flex flex-col items-end">
                        <span class="text-[10px] font-black uppercase tracking-tighter">Entidad</span>
                        <span class="text-sm font-bold text-slate-400">{{ entity.name }}</span>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400">
                        <Home :size="20" />
                    </div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-10">
                <!-- Section 1: Techo -->
                <section class="bg-white rounded-[40px] border border-slate-100 shadow-xl shadow-slate-200/50 p-8 sm:p-12 relative overflow-hidden group">
                    <div class="absolute -right-12 -top-12 w-48 h-48 bg-slate-50 rounded-full blur-3xl group-hover:bg-energy-warning/5 transition-colors"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-10">
                            <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center font-black text-xl shadow-lg">1</div>
                            <div>
                                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Cubierta y Techo</h2>
                                <p class="text-sm text-slate-400 font-medium">El mayor punto de ganancia térmica en verano.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Tipo de Estructura</label>
                                <select v-model="form.roof_type" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-energy-warning/20 transition-all appearance-none cursor-pointer">
                                    <option value="">Selecciona material...</option>
                                    <option value="sheet_metal">Chapa Metálica</option>
                                    <option value="concrete_slab">Losa de Hormigón</option>
                                    <option value="tile">Tejas</option>
                                </select>
                            </div>

                            <div class="flex flex-col justify-end">
                                <label :class="['flex items-center gap-4 p-4 rounded-2xl border transition-all cursor-pointer group/opt', form.roof_insulation ? 'bg-energy-success/5 border-energy-success/30' : 'bg-slate-50 border-transparent']">
                                    <input type="checkbox" v-model="form.roof_insulation" class="hidden" />
                                    <div :class="['w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all', form.roof_insulation ? 'bg-energy-success border-energy-success text-white' : 'bg-white border-slate-200']">
                                        <CheckCircle2 v-if="form.roof_insulation" :size="16" stroke-width="3" />
                                    </div>
                                    <span class="text-sm font-bold text-slate-700">Tiene aislante térmico instalado</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section 2: Ventanas -->
                <section class="bg-white rounded-[40px] border border-slate-100 shadow-xl shadow-slate-200/50 p-8 sm:p-12 relative overflow-hidden group">
                    <div class="absolute -left-12 -bottom-12 w-48 h-48 bg-slate-50 rounded-full blur-3xl group-hover:bg-energy-consumption/5 transition-colors"></div>

                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-10">
                            <div class="w-12 h-12 rounded-2xl bg-energy-consumption text-white flex items-center justify-center font-black text-xl shadow-lg">2</div>
                            <div>
                                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Aberturas y Vidrios</h2>
                                <p class="text-sm text-slate-400 font-medium">Principal causa de infiltraciones de aire.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                            <div class="space-y-2">
                                <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Tecnología de Vidriado</label>
                                <select v-model="form.window_type" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-energy-consumption/20 transition-all appearance-none cursor-pointer">
                                    <option value="single_glass">Vidrio Simple (3-4mm)</option>
                                    <option value="dvh">Doble Vidrio Hermético (DVH)</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Material del Marco</label>
                                <select v-model="form.window_frame" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-energy-consumption/20 transition-all appearance-none cursor-pointer">
                                    <option value="aluminum">Aluminio Estándar</option>
                                    <option value="aluminum_rct">Aluminio con RPT</option>
                                    <option value="pvc">PVC</option>
                                    <option value="wood">Madera</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label :class="['flex items-center gap-4 p-4 rounded-2xl border transition-all cursor-pointer', form.drafts_detected ? 'bg-energy-critical/5 border-energy-critical/30' : 'bg-slate-50 border-transparent']">
                                <input type="checkbox" v-model="form.drafts_detected" class="hidden" />
                                <div :class="['w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all', form.drafts_detected ? 'bg-energy-critical border-energy-critical text-white' : 'bg-white border-slate-200']">
                                    <Wind v-if="form.drafts_detected" :size="16" stroke-width="3" />
                                </div>
                                <span class="text-sm font-bold text-slate-700">Se detectan chifletes de aire</span>
                            </label>

                            <label :class="['flex items-center gap-4 p-4 rounded-2xl border transition-all cursor-pointer', form.south_window ? 'bg-blue-50 border-blue-200' : 'bg-slate-50 border-transparent']">
                                <input type="checkbox" v-model="form.south_window" class="hidden" />
                                <div :class="['w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all', form.south_window ? 'bg-blue-600 border-blue-600 text-white' : 'bg-white border-slate-200']">
                                    <Umbrella v-if="form.south_window" :size="16" stroke-width="3" />
                                </div>
                                <span class="text-sm font-bold text-slate-700">Gran superficie vidriada al Sur</span>
                            </label>
                        </div>
                    </div>
                </section>

                <!-- Submit -->
                <div class="flex flex-col items-center gap-6 pt-6 pb-20">
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full max-w-md bg-slate-900 text-white py-6 px-12 rounded-[32px] font-black text-lg uppercase tracking-widest shadow-2xl shadow-slate-300 hover:bg-energy-consumption transition-all hover:-translate-y-1 active:scale-95 disabled:opacity-50 disabled:translate-y-0"
                    >
                        <span class="flex items-center justify-center gap-3">
                            {{ form.processing ? 'Procesando Motor...' : 'Calcular Eficiencia' }}
                            <ArrowRight v-if="!form.processing" :size="24" stroke-width="3" />
                        </span>
                    </button>
                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Powered by ModoAhorro Engine v3.1</p>
                </div>
            </form>
        </div>
    </MainLayout>
</template>
