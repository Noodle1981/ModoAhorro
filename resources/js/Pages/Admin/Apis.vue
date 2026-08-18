<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { 
    KeyRound, 
    ShoppingBag, 
    Zap, 
    CloudSun, 
    CheckCircle2, 
    Save, 
    RefreshCw
} from 'lucide-vue-next';

defineProps({
    integrations: Object,
});

const form = useForm({
    meli_app_id: 'MLA-MODOAHORRO-2026',
    meli_secret: '••••••••••••••••••••••••••••••••',
    meli_affiliate_id: 'AFF-AR-MODOAHORRO',
    weather_provider: 'open-meteo',
    cammesa_sync_frequency: 'weekly',
});

const submit = () => {
    form.post(route('sistema.apis.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="APIs & Integraciones · Administrador" />

    <MainLayout>
        <div class="max-w-7xl mx-auto space-y-8">
            
            <!-- Page Header Ribbon -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 sm:p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
                <div>
                    <span class="text-xs font-black uppercase tracking-widest text-emerald-600 block mb-1">
                        Conectividad Externa & Fuentes de Datos
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        APIs & Integraciones
                    </h1>
                    <p class="text-slate-500 font-medium text-sm mt-1">
                        Gestioná las llaves de acceso, conectores de precios de Mercado Libre, tarifas mayoristas de energía y servidores meteorológicos.
                    </p>
                </div>

                <button 
                    @click="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center gap-2 bg-[#009966] hover:bg-[#008055] text-white font-extrabold text-xs px-6 py-4 rounded-2xl shadow-lg shadow-emerald-900/10 hover:shadow-emerald-900/20 transition-all hover:-translate-y-0.5 cursor-pointer uppercase tracking-wider shrink-0 disabled:opacity-50"
                >
                    <Save :size="18" />
                    <span>Guardar Configuración</span>
                </button>
            </div>

            <!-- Integrations Status Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Mercado Libre API -->
                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xs flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-14 h-14 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center">
                                <ShoppingBag :size="28" />
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full font-black text-[10px] uppercase tracking-wider">
                                <CheckCircle2 :size="12" /> Conectado
                            </span>
                        </div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight mb-2">Mercado Libre Marketplace</h3>
                        <p class="text-slate-500 text-xs font-medium leading-relaxed mb-6">
                            Permite buscar precios de reemplazos eficientes, verificar stock en tiempo real y generar enlaces monetizables de afiliados.
                        </p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 space-y-3">
                        <div>
                            <label class="text-[10px] font-black uppercase text-slate-400">ID de Afiliado</label>
                            <input v-model="form.meli_affiliate_id" type="text" class="w-full mt-1 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800" />
                        </div>
                    </div>
                </div>

                <!-- CAMMESA / ENRE API -->
                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xs flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-14 h-14 bg-sky-50 text-sky-600 rounded-2xl flex items-center justify-center">
                                <Zap :size="28" />
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-sky-50 text-sky-700 rounded-full font-black text-[10px] uppercase tracking-wider">
                                <RefreshCw :size="12" /> Semanal
                            </span>
                        </div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight mb-2">CAMMESA / ENRE Tarifas</h3>
                        <p class="text-slate-500 text-xs font-medium leading-relaxed mb-6">
                            Actualización automática de los cuadros tarifarios mayoristas PEST (Precio Estacional) y subsidios N1, N2, N3.
                        </p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 space-y-3">
                        <div>
                            <label class="text-[10px] font-black uppercase text-slate-400">Frecuencia de Sincronización</label>
                            <select v-model="form.cammesa_sync_frequency" class="w-full mt-1 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800">
                                <option value="daily">Diaria</option>
                                <option value="weekly">Semanal (Recomendado)</option>
                                <option value="monthly">Mensual</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Open-Meteo API -->
                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xs flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center">
                                <CloudSun :size="28" />
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full font-black text-[10px] uppercase tracking-wider">
                                <CheckCircle2 :size="12" /> Activo
                            </span>
                        </div>
                        <h3 class="text-xl font-black text-slate-900 tracking-tight mb-2">Open-Meteo Datos Clima</h3>
                        <p class="text-slate-500 text-xs font-medium leading-relaxed mb-6">
                            Cálculo de Grados-Día de Calefacción (HDD) y Refrigeración (CDD) por geolocalización de cada hogar.
                        </p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 space-y-3">
                        <div>
                            <label class="text-[10px] font-black uppercase text-slate-400">Servicio Meteorológico</label>
                            <input value="Open-Meteo Free Tier (Unlimited)" disabled type="text" class="w-full mt-1 px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-xs font-bold text-slate-500" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- API Keys & Webhooks Card -->
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-xs space-y-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <KeyRound :size="20" />
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900">Credenciales del Sistema</h3>
                        <p class="text-xs text-slate-400 font-medium">Claves para consumo programático y webhooks de ModoAhorro.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1.5">
                        <span class="text-[10px] font-black uppercase text-slate-400">API Key Pública</span>
                        <p class="font-mono text-xs font-bold text-slate-800 break-all">pk_live_modoahorro_8f3910c29a884ef1</p>
                    </div>
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1.5">
                        <span class="text-[10px] font-black uppercase text-slate-400">Webhook Secret</span>
                        <p class="font-mono text-xs font-bold text-slate-800 break-all">whsec_modoahorro_901cfa9130da4411</p>
                    </div>
                </div>
            </div>

        </div>
    </MainLayout>
</template>
