<script setup>
import { Link, Head, usePage } from '@inertiajs/vue3';
import { 
    LayoutGrid, 
    FileText, 
    Monitor, 
    Activity, 
    LogOut, 
    Menu, 
    X,
    ChevronRight,
    User,
    Zap,
    Briefcase,
    Building,
    BarChart3,
    Sliders,
    Clock,
    Sun,
    Thermometer,
    RefreshCw,
    Ghost,
    Wrench,
    Palmtree,
    Heart,
    Cpu,
    Settings,
    TrendingUp,
    ChevronDown
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth);
const currentEntity = computed(() => auth.value.current_entity);
const entities = computed(() => auth.value.entities);

const isSidebarOpen = ref(true);
const isEntityMenuOpen = ref(false);

const navigation = computed(() => [
    {
        name: 'Gestión Física',
        color: 'text-energy-consumption',
        items: [
            { name: 'Desempeño Térmico', icon: Zap, href: currentEntity.value ? route('gestion.thermal.wizard', currentEntity.value.id) : '#' },
            { name: 'Contratos', icon: FileText, href: route('gestion.contracts') },
            { name: 'Facturas', icon: Briefcase, href: route('gestion.invoices') },
            { name: 'Infraestructura', icon: Building, href: route('gestion.infrastructure') },
        ]
    },
    {
        name: 'Análisis y Ahorro',
        color: 'text-energy-success',
        items: [
            { name: 'Consumo Real', icon: BarChart3, href: route('analisis.consumption') },
            { name: 'Ajuste de Uso', icon: Sliders, href: route('analisis.usage') },
            { name: 'Optimización Horarios', icon: Clock, href: route('analisis.grid-optimization') },
        ]
    },
    {
        name: 'Recomendaciones',
        color: 'text-energy-solar',
        items: [
            { name: 'Proyecto Solar', icon: Sun, href: route('recomendaciones.solar-panels') },
            { name: 'Reemplazos', icon: RefreshCw, href: route('recomendaciones.replacements') },
            { name: 'Consumo Fantasma', icon: Ghost, href: route('recomendaciones.standby-analysis') },
            { name: 'Salud Térmica', icon: Heart, href: currentEntity.value ? route('gestion.thermal.result', currentEntity.value.id) : '#' },
            { name: 'Mantenimiento', icon: Wrench, href: route('recomendaciones.maintenance') },
            { name: 'Vacaciones', icon: Palmtree, href: route('recomendaciones.vacation') },
        ]
    },
    {
        name: 'Sistema',
        color: 'text-slate-400',
        items: [
            { name: 'Administración', icon: Settings, href: route('sistema.admin') },
            { name: 'Benchmarks', icon: TrendingUp, href: route('sistema.benchmarks') },
        ]
    }
]);
</script>

<template>
    <div class="min-h-screen bg-energy-surface flex">
        <!-- Sidebar -->
        <aside 
            :class="[
                'fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200 transition-transform duration-300 transform lg:translate-x-0 lg:static lg:inset-0',
                isSidebarOpen ? 'translate-x-0' : '-translate-x-full'
            ]"
        >
            <div class="h-full flex flex-col">
                <!-- Brand -->
                <div class="p-6 flex items-center gap-3">
                    <div class="w-10 h-10 bg-energy-consumption rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-100">
                        <Zap :size="24" stroke-width="3" />
                    </div>
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">
                        Modo<span class="text-energy-consumption">Ahorro</span>
                    </h1>
                </div>

                <!-- Entity Selector (Nomenclatura Santa) -->
                <div class="px-6 mb-8 relative">
                    <button 
                        @click="isEntityMenuOpen = !isEntityMenuOpen"
                        class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-between group hover:border-energy-consumption/30 transition-all"
                    >
                        <div class="flex items-center gap-3 text-left overflow-hidden">
                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-energy-consumption shadow-sm border border-slate-100">
                                <Building :size="16" />
                            </div>
                            <div class="truncate">
                                <p class="text-xs font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Entidad</p>
                                <p class="text-sm font-bold text-slate-900 truncate">{{ currentEntity?.name || 'Seleccionar...' }}</p>
                            </div>
                        </div>
                        <ChevronDown :size="16" :class="['text-slate-400 transition-transform', isEntityMenuOpen ? 'rotate-180' : '']" />
                    </button>

                    <!-- Entity Dropdown -->
                    <div v-if="isEntityMenuOpen" class="absolute left-6 right-6 top-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 max-h-60 overflow-y-auto">
                        <div class="p-2 space-y-1">
                            <Link 
                                v-for="entity in entities" 
                                :key="entity.id"
                                :href="route('entities.activate', entity.id)"
                                class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 transition-colors text-left"
                            >
                                <div class="w-2 h-2 rounded-full" :class="entity.id === currentEntity?.id ? 'bg-energy-consumption' : 'bg-slate-200'"></div>
                                <span :class="['text-sm font-bold', entity.id === currentEntity?.id ? 'text-slate-900' : 'text-slate-500']">{{ entity.name }}</span>
                            </Link>
                            <div class="border-t border-slate-100 my-1"></div>
                            <Link :href="route('dashboard')" class="w-full flex items-center gap-2 p-3 text-xs font-black text-energy-consumption uppercase tracking-widest hover:bg-blue-50 rounded-xl transition-colors">
                                <LayoutGrid :size="14" />
                                Gestión de Entidades
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Navigation Groups -->
                <nav class="flex-1 overflow-y-auto px-6 space-y-8 scrollbar-hide">
                    <div v-for="category in navigation" :key="category.name">
                        <h2 :class="['text-[10px] font-black uppercase tracking-widest mb-4 flex items-center gap-2', category.color]">
                            <span class="w-4 h-[1px] bg-current opacity-30"></span>
                            {{ category.name }}
                        </h2>
                        <div class="space-y-1">
                            <Link 
                                v-for="item in category.items" 
                                :key="item.name"
                                :href="item.href"
                                class="flex items-center justify-between p-3 rounded-xl group transition-all hover:bg-slate-50"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="p-1.5 rounded-lg text-slate-400 group-hover:text-energy-consumption group-hover:bg-white group-hover:shadow-sm transition-all duration-300">
                                        <component :is="item.icon" :size="18" />
                                    </div>
                                    <span class="text-sm font-bold text-slate-600 group-hover:text-slate-900">{{ item.name }}</span>
                                </div>
                                <ChevronRight :size="14" class="text-slate-300 group-hover:text-slate-500 opacity-0 group-hover:opacity-100 transition-all" />
                            </Link>
                        </div>
                    </div>
                </nav>

                <!-- User / Bottom -->
                <div class="p-6 border-t border-slate-100 space-y-4 bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-energy-consumption/10 border border-energy-consumption/20 flex items-center justify-center text-energy-consumption">
                            <User :size="20" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-slate-900 truncate">{{ auth.user?.name }}</p>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Plan Pro</p>
                        </div>
                    </div>
                    
                    <Link 
                        method="post" 
                        as="button" 
                        :href="route('logout')"
                        class="w-full flex items-center gap-3 p-3 text-slate-400 hover:text-energy-critical hover:bg-rose-50 rounded-xl transition-all font-bold text-xs uppercase tracking-widest"
                    >
                        <LogOut :size="18" />
                        Cerrar Sesión
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Main Content (Resto igual...) -->
        <main class="flex-1 min-w-0 flex flex-col h-screen overflow-hidden">
            <!-- Header (Mobile Toggle) -->
            <header class="lg:hidden p-4 bg-white border-b border-slate-200 flex items-center justify-between">
                <button @click="isSidebarOpen = !isSidebarOpen" class="p-2 text-slate-600">
                    <Menu v-if="!isSidebarOpen" :size="24" />
                    <X v-else :size="24" />
                </button>
                <div class="text-lg font-black text-slate-900">ModoAhorro</div>
                <div class="w-10 h-10"></div>
            </header>

            <!-- Scrollable Page Content -->
            <div class="flex-1 overflow-y-auto p-6 lg:p-12 relative">
                <!-- Content Slot -->
                <slot />
                
                <!-- Audit Section (Regla visual.md 4) -->
                <footer class="mt-20 pt-12 border-t border-slate-200 border-dashed">
                    <div class="bg-indigo-50/50 rounded-3xl p-8 border border-indigo-100/50 flex flex-col md:flex-row gap-6 items-start">
                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100">
                            <Activity :size="24" />
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-indigo-900 uppercase tracking-widest mb-2">Motor de Eficiencia v3.2</h4>
                            <p class="text-xs text-indigo-700/70 leading-relaxed max-w-2xl">
                                Los cálculos se generan en tiempo real utilizando el modelo de Gemelo Digital para la entidad: 
                                <span class="font-bold text-indigo-900">{{ currentEntity?.name }}</span>.
                            </p>
                        </div>
                    </div>
                </footer>
            </div>
        </main>
    </div>
</template>

