<script setup>
import { computed } from 'vue';
import { 
    Layers, 
    Gauge, 
    Clock, 
    Activity 
} from 'lucide-vue-next';

const props = defineProps({
    pavilion: {
        type: Object,
        required: true,
    },
});

const baselineKwh = computed(() => Number(props.pavilion.baseline?.baseline_kwh || 0));
const actualKwh = computed(() => Number(props.pavilion.latest_reading?.kwh || props.pavilion.deviation?.actual_kwh || 0));
const deltaPct = computed(() => Number(props.pavilion.deviation?.delta_pct || 0));
const verdict = computed(() => props.pavilion.deviation?.verdict || 'OK');

// Porcentaje relativo para escala visual (máx 100% sobre el más grande)
const maxKwh = computed(() => Math.max(baselineKwh.value, actualKwh.value, 1));
const baselineBarWidth = computed(() => Math.min(100, Math.round((baselineKwh.value / maxKwh.value) * 100)));
const actualBarWidth = computed(() => Math.min(100, Math.round((actualKwh.value / maxKwh.value) * 100)));

const categories = computed(() => {
    const cats = props.pavilion.baseline?.by_category || {};
    return Object.entries(cats).map(([name, kwh]) => ({
        name,
        kwh: Number(kwh),
        percentage: baselineKwh.value > 0 ? Math.round((Number(kwh) / baselineKwh.value) * 100) : 0,
    })).sort((a, b) => b.kwh - a.kwh);
});
</script>

<template>
    <div class="rounded-xl border border-slate-800 bg-slate-900/90 p-5 shadow-lg space-y-6">
        <!-- Header con Título y Fuente de Suministro -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-4">
            <div>
                <h4 class="text-base font-semibold text-slate-100 flex items-center gap-2">
                    <Gauge class="w-5 h-5 text-emerald-500" />
                    Balance de Consumo: Línea Base vs Lectura Real
                </h4>
                <p class="text-xs text-slate-400 mt-0.5">
                    Fuente de Suministro: <span class="text-emerald-300 font-medium">{{ pavilion.supply_source }}</span>
                </p>
            </div>

            <!-- Chips de Información Operativa -->
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-mono bg-slate-950 text-slate-300 border border-slate-800">
                    <Activity class="w-3.5 h-3.5 text-emerald-400" />
                    Pico: {{ pavilion.latest_reading?.demand_kw_peak || 0 }} kW
                </span>
                <span v-if="pavilion.latest_reading?.generator_hours > 0" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-mono bg-slate-950 text-slate-300 border border-slate-800">
                    <Clock class="w-3.5 h-3.5 text-blue-400" />
                    Gen: {{ pavilion.latest_reading?.generator_hours }} hs
                </span>
            </div>
        </div>

        <!-- Comparador Gráfico de Barras Horizontales -->
        <div class="space-y-4">
            <!-- Barra 1: Línea Base (Consumo Responsable Estimado) -->
            <div>
                <div class="flex justify-between items-center text-xs mb-1.5 font-medium">
                    <span class="text-slate-300 flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span>
                        Línea Base Responsable (Norma ISO 50001 / IRAM)
                    </span>
                    <span class="font-mono text-slate-200 font-bold">
                        {{ baselineKwh.toLocaleString('es-AR') }} kWh
                    </span>
                </div>
                <div class="w-full h-4 rounded-full bg-slate-950 overflow-hidden border border-slate-800 p-0.5">
                    <div 
                        class="h-full rounded-full bg-blue-600 transition-all duration-500"
                        :style="{ width: `${baselineBarWidth}%` }"
                    ></div>
                </div>
            </div>

            <!-- Barra 2: Lectura Real de Tablero -->
            <div>
                <div class="flex justify-between items-center text-xs mb-1.5 font-medium">
                    <span class="text-slate-300 flex items-center gap-1.5">
                        <span 
                            :class="[
                                'w-2.5 h-2.5 rounded-full inline-block',
                                verdict === 'CRITICAL' ? 'bg-rose-500' : verdict === 'WARN' ? 'bg-amber-500' : 'bg-emerald-500'
                            ]"
                        ></span>
                        Lectura de Tablero Eléctrico (Período Quincena)
                    </span>
                    <div class="flex items-center gap-2">
                        <span 
                            :class="[
                                'font-mono text-xs px-2 py-0.5 rounded font-bold',
                                verdict === 'CRITICAL' ? 'bg-rose-950 text-rose-300 border border-rose-800' : verdict === 'WARN' ? 'bg-amber-950 text-amber-300 border border-amber-800' : 'bg-emerald-950 text-emerald-300 border border-emerald-800'
                            ]"
                        >
                            {{ deltaPct > 0 ? `+${deltaPct}%` : `${deltaPct}%` }}
                        </span>
                        <span class="font-mono text-slate-100 font-bold">
                            {{ actualKwh.toLocaleString('es-AR') }} kWh
                        </span>
                    </div>
                </div>
                <div class="w-full h-4 rounded-full bg-slate-950 overflow-hidden border border-slate-800 p-0.5">
                    <div 
                        :class="[
                            'h-full rounded-full transition-all duration-500',
                            verdict === 'CRITICAL' ? 'bg-rose-600' : verdict === 'WARN' ? 'bg-amber-500' : 'bg-emerald-500'
                        ]"
                        :style="{ width: `${actualBarWidth}%` }"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Desglose por Categorías de Proceso -->
        <div class="border-t border-slate-800 pt-4">
            <h5 class="text-xs font-semibold text-slate-300 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <Layers class="w-3.5 h-3.5 text-slate-400" />
                Desglose Térmico y Eléctrico por Categoría de Equipo
            </h5>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2.5">
                <div 
                    v-for="cat in categories" 
                    :key="cat.name"
                    class="p-2.5 rounded-lg bg-slate-950/70 border border-slate-800/80 flex flex-col justify-between"
                >
                    <span class="text-xs text-slate-400 truncate font-medium">{{ cat.name }}</span>
                    <div class="flex items-baseline justify-between mt-1">
                        <span class="font-mono text-sm font-bold text-slate-200">
                            {{ cat.kwh.toLocaleString('es-AR') }} <span class="text-[10px] font-normal text-slate-400">kWh</span>
                        </span>
                        <span class="text-xs font-mono text-amber-400 font-medium">
                            {{ cat.percentage }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
