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
    Umbrella,
    Compass,
    CloudSun
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
                                    <option value="sheet_metal_no_insulation">Chapa Metálica (Sin aislación)</option>
                                    <option value="concrete_slab">Losa de Hormigón</option>
                                    <option value="insulated_panel">Panel Aislado / Sandwich</option>
                                    <option value="tile">Tejas</option>
                                </select>
                            </div>

                            <div class="flex flex-col justify-end">
                                <label :class="['flex items-center gap-4 p-4 rounded-2xl border transition-all cursor-pointer group/opt', form.roof_insulation ? 'bg-emerald-500/10 border-emerald-500/30' : 'bg-slate-50 border-transparent']">
                                    <input type="checkbox" v-model="form.roof_insulation" class="hidden" />
                                    <div :class="['w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all', form.roof_insulation ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white border-slate-200']">
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
                    <div class="absolute -left-12 -bottom-12 w-48 h-48 bg-slate-50 rounded-full blur-3xl group-hover:bg-emerald-600/5 transition-colors"></div>

                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-10">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-xl shadow-lg">2</div>
                            <div>
                                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Aberturas y Vidrios</h2>
                                <p class="text-sm text-slate-400 font-medium">Principal causa de infiltraciones de aire.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                            <div class="space-y-2">
                                <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Tecnología de Vidriado</label>
                                <select v-model="form.window_type" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-emerald-600/20 transition-all appearance-none cursor-pointer">
                                    <option value="single_glass">Vidrio Simple (3-4mm)</option>
                                    <option value="dvh">Doble Vidrio Hermético (DVH)</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Material del Marco</label>
                                <select v-model="form.window_frame" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-emerald-600/20 transition-all appearance-none cursor-pointer">
                                    <option value="aluminum">Aluminio Estándar</option>
                                    <option value="aluminum_rct">Aluminio con RPT</option>
                                    <option value="pvc">PVC</option>
                                    <option value="wood">Madera</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-col justify-end">
                            <label :class="['flex items-center gap-4 p-4 rounded-2xl border transition-all cursor-pointer', form.drafts_detected ? 'bg-energy-critical/5 border-energy-critical/30' : 'bg-slate-50 border-transparent']">
                                <input type="checkbox" v-model="form.drafts_detected" class="hidden" />
                                <div :class="['w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all', form.drafts_detected ? 'bg-energy-critical border-energy-critical text-white' : 'bg-white border-slate-200']">
                                    <Wind v-if="form.drafts_detected" :size="16" stroke-width="3" />
                                </div>
                                <span class="text-sm font-bold text-slate-700">Se detectan chifletes de aire ("Chifletes")</span>
                            </label>
                        </div>
                    </div>
                </section>

                <!-- Section 3: Orientación -->
                <section class="bg-white rounded-[40px] border border-slate-100 shadow-xl shadow-slate-200/50 p-8 sm:p-12 relative overflow-hidden group">
                    <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-slate-50 rounded-full blur-3xl group-hover:bg-energy-warning/5 transition-colors"></div>

                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-10">
                            <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-black text-xl shadow-lg shadow-amber-100">3</div>
                            <div>
                                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Orientación y Viento</h2>
                                <p class="text-sm text-slate-400 font-medium">Optimización del sol de invierno y aire de verano.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                            <div class="space-y-2">
                                <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Eje de Fachada Principal</label>
                                <select v-model="form.orientation" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-amber-500/20 transition-all appearance-none cursor-pointer">
                                    <option value="norte_sur">Norte-Sur (Ideal: Sol Invierno / Sombra Verano)</option>
                                    <option value="este_oeste">Este-Oeste (Sol bajo, mayor calentamiento)</option>
                                    <option value="diagonal">Diagonal / Otra</option>
                                </select>
                            </div>

                            <div class="flex flex-col justify-end">
                                <label :class="['flex items-center gap-4 p-4 rounded-2xl border transition-all cursor-pointer', form.south_window ? 'bg-emerald-500/10 border-emerald-500/30' : 'bg-slate-50 border-transparent']">
                                    <input type="checkbox" v-model="form.south_window" class="hidden" />
                                    <div :class="['w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all', form.south_window ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white border-slate-200']">
                                        <Compass v-if="form.south_window" :size="16" stroke-width="3" />
                                    </div>
                                    <span class="text-sm font-bold text-slate-700">Tengo ventanas al SUR (Ventilación cruzada)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section 4: Sombra y Entorno -->
                <section class="bg-white rounded-[40px] border border-slate-100 shadow-xl shadow-slate-200/50 p-8 sm:p-12 relative overflow-hidden group">
                    <div class="absolute -left-12 -top-12 w-48 h-48 bg-slate-50 rounded-full blur-3xl group-hover:bg-emerald-600/5 transition-colors"></div>

                    <div class="relative z-10">
                        <div class="flex items-center gap-4 mb-10">
                            <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center font-black text-xl shadow-lg">4</div>
                            <div>
                                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Exposición y Sombras</h2>
                                <p class="text-sm text-slate-400 font-medium">Protección natural del inmueble.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <button 
                                type="button"
                                v-for="level in ['high', 'medium', 'low']"
                                :key="level"
                                @click="form.sun_exposure = level"
                                :class="[
                                    'p-6 rounded-[32px] border transition-all text-center flex flex-col items-center gap-3 group/btn',
                                    form.sun_exposure === level ? 'bg-emerald-600 border-emerald-600 shadow-lg shadow-emerald-100' : 'bg-slate-50 border-transparent hover:bg-slate-100'
                                ]"
                            >
                                <div :class="['w-14 h-14 rounded-2xl flex items-center justify-center transition-transform group-hover/btn:scale-110', form.sun_exposure === level ? 'bg-white/20 text-white' : 'bg-white text-emerald-600 shadow-sm border border-slate-100']">
                                    <Sun v-if="level === 'high'" :size="28" />
                                    <CloudSun v-if="level === 'medium'" :size="28" />
                                    <Umbrella v-if="level === 'low'" :size="28" />
                                </div>
                                <div>
                                    <p :class="['font-black text-sm uppercase tracking-widest', form.sun_exposure === level ? 'text-white' : 'text-slate-900']">
                                        {{ level === 'high' ? 'Sol Directo' : level === 'medium' ? 'Sombra Parcial' : 'Mucha Sombra' }}
                                    </p>
                                    <p :class="['text-[10px] font-bold', form.sun_exposure === level ? 'text-white/70' : 'text-slate-400']">
                                        {{ level === 'high' ? 'Exposición Máxima' : level === 'medium' ? 'Equilibrio' : 'Muy Protegido' }}
                                    </p>
                                </div>
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Submit -->
                <div class="flex flex-col items-center gap-6 pt-6 pb-20">
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full max-w-md bg-slate-900 text-white py-6 px-12 rounded-[32px] font-black text-lg uppercase tracking-widest shadow-2xl shadow-slate-300 hover:bg-emerald-600 transition-all hover:-translate-y-1 active:scale-95 disabled:opacity-50 disabled:translate-y-0"
                    >
                        <span class="flex items-center justify-center gap-3">
                            {{ form.processing ? 'Sincronizando Gemelo...' : 'Calcular Eficiencia' }}
                            <ArrowRight v-if="!form.processing" :size="24" stroke-width="3" />
                        </span>
                    </button>
                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Powered by ModoAhorro Engine v3.2</p>
                </div>
            </form>
        </div>
    </MainLayout>
</template>

<style scoped>
/* Scoped styles keeping layout clean and within standard Tailwind 4 flow */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>
