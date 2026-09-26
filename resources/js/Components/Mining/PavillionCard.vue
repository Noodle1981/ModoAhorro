<script setup>
import { computed } from 'vue';
import { 
    Building2, 
    Users, 
    Zap, 
    Flame, 
    AlertTriangle, 
    CheckCircle2, 
    AlertCircle, 
    ArrowUpRight,
    Leaf
} from 'lucide-vue-next';

const props = defineProps({
    pavilion: {
        type: Object,
        required: true,
    },
    isSelected: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['select']);

const verdictConfig = computed(() => {
    const verdict = props.pavilion.deviation?.verdict || 'OK';
    switch (verdict) {
        case 'CRITICAL':
            return {
                bg: 'bg-rose-950/40 border-rose-600/60 text-rose-400',
                badgeBg: 'bg-rose-900/80 text-rose-200 border-rose-700',
                badgeText: 'Desvío Crítico',
                icon: AlertCircle,
                deltaColor: 'text-rose-400',
            };
        case 'WARN':
            return {
                bg: 'bg-amber-950/40 border-amber-600/60 text-amber-400',
                badgeBg: 'bg-amber-900/80 text-amber-200 border-amber-700',
                badgeText: 'Alerta Operativa',
                icon: AlertTriangle,
                deltaColor: 'text-amber-400',
            };
        default:
            return {
                bg: 'bg-emerald-950/40 border-emerald-600/60 text-emerald-400',
                badgeBg: 'bg-emerald-900/80 text-emerald-200 border-emerald-700',
                badgeText: 'Onda Verde',
                icon: Leaf,
                deltaColor: 'text-emerald-400',
            };
    }
});

const deltaPct = computed(() => props.pavilion.deviation?.delta_pct || 0);
const deltaKwh = computed(() => props.pavilion.deviation?.delta_kwh || 0);
const litersWasted = computed(() => props.pavilion.deviation?.liters_wasted || 0);
</script>

<template>
    <div 
        @click="$emit('select', pavilion)"
        :class="[
            'relative cursor-pointer rounded-xl border p-4 transition-all duration-200 select-none shadow-md',
            isSelected 
                ? 'ring-2 ring-amber-500 bg-slate-800 border-amber-500/80' 
                : 'bg-slate-900/90 border-slate-800 hover:border-slate-700 hover:bg-slate-850'
        ]"
    >
        <!-- Header con Nombre y Badge de Veredicto -->
        <div class="flex items-start justify-between gap-2 mb-3">
            <div class="flex items-center gap-2">
                <div class="p-2 rounded-lg bg-slate-800 text-amber-400 border border-slate-700">
                    <Building2 class="w-5 h-5" />
                </div>
                <div>
                    <h3 class="font-semibold text-slate-100 text-sm leading-tight line-clamp-1">
                        {{ pavilion.name }}
                    </h3>
                    <p class="text-xs text-slate-400 flex items-center gap-2 mt-0.5">
                        <span class="inline-flex items-center gap-1">
                            <Users class="w-3.5 h-3.5 text-slate-400" />
                            {{ pavilion.occupancy }} personas
                        </span>
                        <span>•</span>
                        <span class="text-amber-300 font-mono text-xs">Turno {{ pavilion.shift_type }}</span>
                    </p>
                </div>
            </div>

            <!-- Badge Status -->
            <span :class="['inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium border', verdictConfig.badgeBg]">
                <component :is="verdictConfig.icon" class="w-3.5 h-3.5" />
                {{ verdictConfig.badgeText }}
            </span>
        </div>

        <!-- Métricas Principales (Línea Base vs Real) -->
        <div class="grid grid-cols-2 gap-2 my-3 p-2.5 rounded-lg bg-slate-950/60 border border-slate-800/80 text-xs">
            <div>
                <p class="text-slate-400">Línea Base</p>
                <p class="font-mono font-bold text-slate-200 text-sm">
                    {{ Number(pavilion.baseline?.baseline_kwh || 0).toLocaleString('es-AR') }} <span class="text-xs font-normal text-slate-400">kWh</span>
                </p>
            </div>
            <div>
                <p class="text-slate-400">Lectura Tablero</p>
                <p :class="['font-mono font-bold text-sm', verdictConfig.deltaColor]">
                    {{ Number(pavilion.latest_reading?.kwh || pavilion.deviation?.actual_kwh || 0).toLocaleString('es-AR') }} <span class="text-xs font-normal text-slate-400">kWh</span>
                </p>
            </div>
        </div>

        <!-- Indicador de Desvío / Diésel Desperdiciado -->
        <div class="flex items-center justify-between pt-1 border-t border-slate-800 text-xs">
            <div class="flex items-center gap-1.5 font-medium">
                <span :class="verdictConfig.deltaColor">
                    {{ deltaPct > 0 ? `+${deltaPct}%` : `${deltaPct}%` }}
                </span>
                <span class="text-slate-500 font-normal">desvío</span>
            </div>

            <div v-if="litersWasted > 0" class="flex items-center gap-1 text-rose-400 text-xs font-mono font-semibold">
                <Flame class="w-3.5 h-3.5 text-amber-500" />
                <span>{{ Number(litersWasted).toLocaleString('es-AR') }} L diésel</span>
            </div>
            <div v-else class="flex items-center gap-1 text-emerald-400 text-xs font-mono">
                <CheckCircle2 class="w-3.5 h-3.5" />
                <span>Cumple norma</span>
            </div>
        </div>
    </div>
</template>
