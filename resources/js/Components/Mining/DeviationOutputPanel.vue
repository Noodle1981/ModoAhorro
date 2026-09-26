<script setup>
import { ref } from 'vue';
import { 
    GraduationCap, 
    AlertTriangle, 
    Leaf, 
    Wrench, 
    CheckSquare, 
    FileText, 
    DollarSign, 
    Flame, 
    Clock, 
    ArrowRight,
    ShieldAlert
} from 'lucide-vue-next';

const props = defineProps({
    outputs: {
        type: Object,
        required: true,
    },
    pavilionName: {
        type: String,
        default: 'Pabellón Minero',
    },
});

const activeTab = ref('training');
</script>

<template>
    <div class="rounded-xl border border-slate-800 bg-slate-900/90 shadow-xl overflow-hidden">
        <!-- Navegación de Pestañas de las 4 Salidas de Valor -->
        <div class="flex items-center border-b border-slate-800 bg-slate-950/80 px-4 pt-2 gap-2 overflow-x-auto">
            <!-- Pestaña 1: Capacitación (Siempre disponible) -->
            <button 
                @click="activeTab = 'training'"
                :class="[
                    'flex items-center gap-2 py-3 px-3.5 text-xs font-semibold rounded-t-lg transition-colors border-b-2 whitespace-nowrap',
                    activeTab === 'training'
                        ? 'border-blue-500 text-blue-400 bg-slate-900'
                        : 'border-transparent text-slate-400 hover:text-slate-200 hover:bg-slate-900/50'
                ]"
            >
                <GraduationCap class="w-4 h-4" />
                1. Capacitación Operativa
            </button>

            <!-- Pestaña 2: Penalización (Condicional) -->
            <button 
                v-if="outputs.penalty"
                @click="activeTab = 'penalty'"
                :class="[
                    'flex items-center gap-2 py-3 px-3.5 text-xs font-semibold rounded-t-lg transition-colors border-b-2 whitespace-nowrap',
                    activeTab === 'penalty'
                        ? 'border-rose-500 text-rose-400 bg-slate-900'
                        : 'border-transparent text-rose-300/80 hover:text-rose-200 hover:bg-slate-900/50'
                ]"
            >
                <AlertTriangle class="w-4 h-4 text-rose-500" />
                2. Acta de Penalización
                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
            </button>

            <!-- Pestaña 3: Onda Verde (Condicional) -->
            <button 
                v-if="outputs.green_wave"
                @click="activeTab = 'green_wave'"
                :class="[
                    'flex items-center gap-2 py-3 px-3.5 text-xs font-semibold rounded-t-lg transition-colors border-b-2 whitespace-nowrap',
                    activeTab === 'green_wave'
                        ? 'border-emerald-500 text-emerald-400 bg-slate-900'
                        : 'border-transparent text-emerald-300/80 hover:text-emerald-200 hover:bg-slate-900/50'
                ]"
            >
                <Leaf class="w-4 h-4 text-emerald-400" />
                3. Distintivo Onda Verde
                <span class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-950 text-emerald-300 border border-emerald-800">Cumple</span>
            </button>

            <!-- Pestaña 4: Reemplazo (Condicional) -->
            <button 
                v-if="outputs.replacement"
                @click="activeTab = 'replacement'"
                :class="[
                    'flex items-center gap-2 py-3 px-3.5 text-xs font-semibold rounded-t-lg transition-colors border-b-2 whitespace-nowrap',
                    activeTab === 'replacement'
                        ? 'border-amber-500 text-amber-400 bg-slate-900'
                        : 'border-transparent text-amber-300/80 hover:text-amber-200 hover:bg-slate-900/50'
                ]"
            >
                <Wrench class="w-4 h-4 text-amber-400" />
                4. Plan de Reemplazo & ROI Diésel
            </button>
        </div>

        <!-- Contenido de las Pestañas -->
        <div class="p-6">
            <!-- 📚 1. Capacitación y Pautas Operativas -->
            <div v-if="activeTab === 'training'" class="space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h4 class="text-base font-semibold text-slate-100 flex items-center gap-2">
                            <GraduationCap class="w-5 h-5 text-blue-400" />
                            {{ outputs.training.title }}
                        </h4>
                        <p class="text-xs text-slate-400 mt-1">
                            {{ outputs.training.summary }} — Orientado a operarios de {{ pavilionName }}.
                        </p>
                    </div>

                    <div class="p-3 rounded-lg bg-slate-950 border border-slate-800 text-right shrink-0">
                        <p class="text-[11px] text-slate-400">Potencial Recuperable sin Inversión</p>
                        <p class="text-sm font-bold font-mono text-emerald-400">
                            {{ Number(outputs.training.recoverable_kwh).toLocaleString('es-AR') }} kWh / mes
                        </p>
                        <p class="text-xs font-mono text-amber-300 mt-0.5">
                            ≈ {{ Number(outputs.training.recoverable_diesel_liters).toLocaleString('es-AR') }} L diésel
                        </p>
                    </div>
                </div>

                <!-- Checklist de Hábitos -->
                <div class="mt-4 space-y-2">
                    <p class="text-xs font-semibold text-slate-300 uppercase tracking-wider">
                        Pautas Obligatorias en Módulo Habitacional:
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                        <div 
                            v-for="(habit, idx) in outputs.training.habits_checklist" 
                            :key="idx"
                            class="p-3 rounded-lg bg-slate-950/70 border border-slate-800 flex items-start gap-2.5 text-xs text-slate-200"
                        >
                            <CheckSquare class="w-4 h-4 text-blue-400 shrink-0 mt-0.5" />
                            <span>{{ habit }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ⚠️ 2. Acta de Penalización Operativa -->
            <div v-else-if="activeTab === 'penalty' && outputs.penalty" class="space-y-5">
                <div class="p-4 rounded-xl bg-rose-950/50 border border-rose-800/80 flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2 rounded-lg bg-rose-900/80 text-rose-200 border border-rose-700 shrink-0">
                            <ShieldAlert class="w-6 h-6" />
                        </div>
                        <div>
                            <span class="inline-block text-[11px] font-mono px-2 py-0.5 rounded bg-rose-900 text-rose-200 font-semibold mb-1">
                                {{ outputs.penalty.code }}
                            </span>
                            <h4 class="text-base font-bold text-rose-100">
                                Notificación de Sobreconsumo Crítico Reincidente
                            </h4>
                            <p class="text-xs text-rose-300 mt-1">
                                {{ outputs.penalty.notice_message }}
                            </p>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <p class="text-[11px] text-rose-300 font-medium">Severidad</p>
                        <span class="inline-block px-2.5 py-1 rounded bg-rose-900/90 text-rose-100 text-xs font-bold border border-rose-700">
                            {{ outputs.penalty.severity }}
                        </span>
                    </div>
                </div>

                <!-- Detalle del Impacto Económico Reincidente -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div class="p-3 rounded-lg bg-slate-950 border border-slate-800">
                        <span class="text-slate-400">Períodos Fuera de Norma</span>
                        <p class="font-mono text-base font-bold text-rose-400 mt-1">
                            {{ outputs.penalty.consecutive_periods }} ciclos quincenales
                        </p>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-950 border border-slate-800">
                        <span class="text-slate-400">Diésel Desperdiciado Acumulado</span>
                        <p class="font-mono text-base font-bold text-amber-400 mt-1">
                            {{ Number(outputs.penalty.accumulated_diesel_liters).toLocaleString('es-AR') }} Litros
                        </p>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-950 border border-slate-800">
                        <span class="text-slate-400">Cargo al Centro de Costos</span>
                        <p class="font-mono text-base font-bold text-rose-300 mt-1">
                            ${{ Number(outputs.penalty.accumulated_usd_impact).toLocaleString('es-AR') }} USD
                        </p>
                    </div>
                </div>

                <div class="p-3 rounded-lg bg-slate-950/80 border border-slate-800 text-xs text-slate-300">
                    <span class="font-semibold text-amber-400">Acción Requerida:</span> {{ outputs.penalty.required_action }}
                </div>
            </div>

            <!-- 🌿 3. Distintivo Onda Verde -->
            <div v-else-if="activeTab === 'green_wave' && outputs.green_wave" class="space-y-4">
                <div class="p-5 rounded-xl bg-emerald-950/50 border border-emerald-700/80 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="p-3 rounded-xl bg-emerald-900/80 text-emerald-200 border border-emerald-700 shadow-md">
                            <Leaf class="w-8 h-8" />
                        </div>
                        <div>
                            <span class="inline-block text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-900 text-emerald-200 font-bold mb-1">
                                CERTIFICACIÓN OPERATIVA AMBIENTAL
                            </span>
                            <h4 class="text-lg font-bold text-emerald-100">
                                {{ outputs.green_wave.badge }}
                            </h4>
                            <p class="text-xs text-emerald-300 mt-0.5">
                                {{ outputs.green_wave.recognition }}
                            </p>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <p class="text-[11px] text-emerald-300">Emisiones Evitadas</p>
                        <p class="text-base font-mono font-bold text-emerald-200">
                            -{{ outputs.green_wave.co2_avoided_kg }} kg CO₂
                        </p>
                    </div>
                </div>
            </div>

            <!-- 🔧 4. Propuesta de Reemplazo y ROI Diésel -->
            <div v-else-if="activeTab === 'replacement' && outputs.replacement" class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-3">
                    <div>
                        <h4 class="text-base font-semibold text-slate-100 flex items-center gap-2">
                            <Wrench class="w-5 h-5 text-amber-400" />
                            {{ outputs.replacement.title }}
                        </h4>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ outputs.replacement.primary_solution }}
                        </p>
                    </div>

                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-950 text-amber-300 border border-amber-800 self-start sm:self-center">
                        -{{ outputs.replacement.estimated_savings_pct }}% Consumo Estructural
                    </span>
                </div>

                <!-- Métricas de Retorno de Inversión (ROI) -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-3 rounded-lg bg-slate-950 border border-slate-800">
                        <span class="text-slate-400">Ahorro Diésel Proyectado</span>
                        <p class="font-mono text-base font-bold text-amber-400 mt-1">
                            {{ Number(outputs.replacement.diesel_monthly_saved_liters).toLocaleString('es-AR') }} L/mes
                        </p>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-950 border border-slate-800">
                        <span class="text-slate-400">Ahorro Financiero</span>
                        <p class="font-mono text-base font-bold text-emerald-400 mt-1">
                            ${{ Number(outputs.replacement.diesel_monthly_saved_usd).toLocaleString('es-AR') }} USD/mes
                        </p>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-950 border border-slate-800">
                        <span class="text-slate-400">CAPEX Estimado</span>
                        <p class="font-mono text-base font-bold text-slate-200 mt-1">
                            ${{ Number(outputs.replacement.estimated_capex_usd).toLocaleString('es-AR') }} USD
                        </p>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-950 border border-slate-800">
                        <span class="text-slate-400">Plazo de Repago (Payback)</span>
                        <p class="font-mono text-base font-bold text-blue-400 mt-1">
                            {{ outputs.replacement.payback_months }} meses
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
