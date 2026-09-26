<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import PavillionCard from '@/Components/Mining/PavillionCard.vue';
import BaselineComparisonChart from '@/Components/Mining/BaselineComparisonChart.vue';
import DeviationOutputPanel from '@/Components/Mining/DeviationOutputPanel.vue';
import { 
    Building2, 
    Flame, 
    Zap, 
    AlertCircle, 
    Leaf, 
    DollarSign, 
    Activity, 
    Mountain,
    ShieldAlert,
    Gauge,
    Layers,
    ArrowUpRight
} from 'lucide-vue-next';

const props = defineProps({
    kpis: {
        type: Object,
        required: true,
    },
    pavilions: {
        type: Array,
        required: true,
    },
});

const selectedPavilion = ref(props.pavilions[0] || null);

const selectPavilion = (pavilion) => {
    selectedPavilion.value = pavilion;
};
</script>

<template>
    <MainLayout>
        <Head title="Control y Auditoría de Campamento Minero" />

        <div class="space-y-6 pb-8">
            <!-- ── 1. BANNER OPERATIVO DE ALTA MONTAÑA ─────────────────────── -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-slate-850 to-slate-900 border border-slate-800 p-6 shadow-2xl">
                <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none text-slate-100">
                    <Mountain class="w-80 h-80" />
                </div>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-mono font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                <Mountain class="w-3.5 h-3.5" />
                                {{ kpis.altitude_msnm }} msnm • Puna Sanjuanina
                            </span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-800 text-slate-300 border border-slate-700">
                                Régimen 24/7
                            </span>
                        </div>
                        <h1 class="text-2xl font-black text-slate-100 tracking-tight flex items-center gap-3">
                            {{ kpis.camp_name }}
                        </h1>
                        <p class="text-xs text-slate-400 max-w-2xl mt-1">
                            Monitoreo de Línea Base de Consumo Responsable por módulo habitacional, auditoría de lecturas de tableros eléctricos y detección automatizada de derroche de diésel en cordillera.
                        </p>
                    </div>

                    <div class="flex items-center gap-3 self-start md:self-center">
                        <Link 
                            :href="route('gestion.invoices')"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition shadow-lg shadow-amber-500/20"
                        >
                            <Zap class="w-4 h-4" />
                            Cargar Lectura de Tablero
                        </Link>
                    </div>
                </div>
            </div>

            <!-- ── 2. KPIS EJECUTIVOS DE CAMPAMENTO (4 Tarjetas) ───────────── -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- KPI 1: Desvío Crítico -->
                <div class="rounded-xl border border-rose-900/60 bg-slate-900/90 p-4 shadow-lg flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-400">Pabellones en Desvío Crítico</p>
                        <p class="text-2xl font-black font-mono text-rose-400 mt-1">
                            {{ kpis.critical_count }} <span class="text-xs text-slate-400 font-normal">/ {{ kpis.total_pavilions }}</span>
                        </p>
                        <p class="text-[11px] text-rose-300/80 mt-1">Sobreconsumo > 20%</p>
                    </div>
                    <div class="p-3 rounded-xl bg-rose-950/80 text-rose-400 border border-rose-800/80">
                        <AlertCircle class="w-6 h-6" />
                    </div>
                </div>

                <!-- KPI 2: Diésel Desperdiciado -->
                <div class="rounded-xl border border-amber-900/60 bg-slate-900/90 p-4 shadow-lg flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-400">Diésel Desperdiciado Estimado</p>
                        <p class="text-2xl font-black font-mono text-amber-400 mt-1">
                            {{ Number(kpis.diesel_wasted_month_liters).toLocaleString('es-AR') }} <span class="text-xs text-slate-400 font-normal">L / mes</span>
                        </p>
                        <p class="text-[11px] text-amber-300/80 mt-1">≈ {{ kpis.diesel_wasted_today_liters }} L hoy</p>
                    </div>
                    <div class="p-3 rounded-xl bg-amber-950/80 text-amber-400 border border-amber-800/80">
                        <Flame class="w-6 h-6" />
                    </div>
                </div>

                <!-- KPI 3: Pabellones Onda Verde -->
                <div class="rounded-xl border border-emerald-900/60 bg-slate-900/90 p-4 shadow-lg flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-400">Pabellones Onda Verde</p>
                        <p class="text-2xl font-black font-mono text-emerald-400 mt-1">
                            {{ kpis.green_wave_count }} <span class="text-xs text-slate-400 font-normal">cumplidores</span>
                        </p>
                        <p class="text-[11px] text-emerald-300/80 mt-1">Certificación sustentable</p>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-950/80 text-emerald-400 border border-emerald-800/80">
                        <Leaf class="w-6 h-6" />
                    </div>
                </div>

                <!-- KPI 4: Ahorro Potencial Mensual -->
                <div class="rounded-xl border border-blue-900/60 bg-slate-900/90 p-4 shadow-lg flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-slate-400">Costo Recuperable</p>
                        <p class="text-2xl font-black font-mono text-blue-400 mt-1">
                            ${{ Number(kpis.monthly_usd_savings_potential).toLocaleString('es-AR') }} <span class="text-xs text-slate-400 font-normal">USD</span>
                        </p>
                        <p class="text-[11px] text-blue-300/80 mt-1">Ahorro mensual si se corrige</p>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-950/80 text-blue-400 border border-blue-800/80">
                        <DollarSign class="w-6 h-6" />
                    </div>
                </div>
            </div>

            <!-- ── 3. LISTADO SELECTOR DE PABELLONES Y MÓDULOS ─────────────── -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                        <Layers class="w-4 h-4 text-amber-500" />
                        Pabellones y Módulos de Campamento (Selecciona uno para auditar)
                    </h2>
                    <span class="text-xs text-slate-400 font-mono">
                        {{ pavilions.length }} módulos activos
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <PavillionCard 
                        v-for="p in pavilions" 
                        :key="p.id"
                        :pavilion="p"
                        :is-selected="selectedPavilion?.id === p.id"
                        @select="selectPavilion"
                    />
                </div>
            </div>

            <!-- ── 4. DETALLE EN PROFUNDIDAD DEL PABELLÓN SELECCIONADO ──────── -->
            <div v-if="selectedPavilion" class="space-y-6 pt-2">
                <div class="border-t border-slate-800 pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <span class="p-2 rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                <Gauge class="w-5 h-5" />
                            </span>
                            <div>
                                <h3 class="text-lg font-bold text-slate-100">
                                    Auditoría Detallada: {{ selectedPavilion.name }}
                                </h3>
                                <p class="text-xs text-slate-400">
                                    Capacidad: {{ selectedPavilion.occupancy }} personas • Turno {{ selectedPavilion.shift_type }} • Módulo {{ selectedPavilion.module_type }}
                                </p>
                            </div>
                        </div>

                        <span class="text-xs font-mono px-3 py-1 rounded bg-slate-900 border border-slate-800 text-slate-300">
                            ID: #{{ selectedPavilion.id }}
                        </span>
                    </div>

                    <!-- Gráfico Comparativo Línea Base vs Real -->
                    <BaselineComparisonChart :pavilion="selectedPavilion" />
                </div>

                <!-- Panel de las 4 Salidas de Valor -->
                <div class="space-y-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                        <ShieldAlert class="w-4 h-4 text-amber-500" />
                        Plan de Acción: Las 4 Salidas de Valor
                    </h3>

                    <DeviationOutputPanel 
                        :outputs="selectedPavilion.outputs" 
                        :pavilion-name="selectedPavilion.name"
                    />
                </div>
            </div>
        </div>
    </MainLayout>
</template>
