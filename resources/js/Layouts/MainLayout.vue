<script setup>
import { Link, Head, usePage } from '@inertiajs/vue3';
import { 
    LayoutGrid, 
    FileText, 
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
    RefreshCw,
    Ghost,
    Wrench,
    Palmtree,
    Heart,
    Settings,
    TrendingUp,
    ChevronDown,
    Home,
    Thermometer
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth);
const currentEntity = computed(() => auth.value.current_entity);
const entities = computed(() => auth.value.entities);

const isSidebarOpen = ref(true);
const isEntityMenuOpen = ref(false);
const activeCategory = ref('Gestión Física');

// Sincronizar categoría activa con la URL actual
import { watchEffect } from 'vue';
watchEffect(() => {
    const url = page.url;
    if (url.startsWith('/analisis')) activeCategory.value = 'Análisis';
    else if (url.startsWith('/recomendaciones')) activeCategory.value = 'Recomendaciones';
    else if (url.startsWith('/sistema')) activeCategory.value = 'Sistema';
    else activeCategory.value = 'Gestión Física';
});

const navigation = computed(() => [
    {
        name: 'Gestión Física',
        icon: Building,
        color: 'text-energy-consumption',
        bgColor: 'bg-emerald-600',
        items: [
            { name: 'Desempeño Térmico', icon: Thermometer, href: currentEntity.value ? route('gestion.thermal.index', currentEntity.value.id) : '#' },
            { name: 'Perfil de Mi Casa', icon: Home, href: route('gestion.entity.edit') },
            { name: 'Contratos', icon: FileText, href: route('gestion.contracts') },
            { name: 'Facturas', icon: Briefcase, href: route('gestion.invoices') },
            { name: 'Unificaciones', icon: RefreshCw, href: route('gestion.unifications') },
            { name: 'Infraestructura', icon: Building, href: route('gestion.infrastructure') },
        ]
    },
    {
        name: 'Análisis',
        icon: BarChart3,
        color: 'text-energy-success',
        bgColor: 'bg-emerald-500',
        items: [
            { name: 'Ajuste de Uso', icon: Sliders, href: route('analisis.usage') },
            { name: 'Consumo Real', icon: BarChart3, href: route('analisis.consumption') },
            { name: 'Evolución Temporal', icon: Activity, href: route('analisis.time') },
        ]
    },
    {
        name: 'Recomendaciones',
        icon: Sun,
        color: 'text-energy-solar',
        bgColor: 'bg-amber-500',
        items: [
            { name: 'Proyecto Solar', icon: Sun, href: route('recomendaciones.solar') },
            { name: 'Reemplazos', icon: RefreshCw, href: route('recomendaciones.replacements') },
            { name: 'Consumo Fantasma', icon: Ghost, href: route('recomendaciones.standby') },
            { name: 'Salud Térmica', icon: Heart, href: route('recomendaciones.thermal-health') },
            { name: 'Mantenimiento', icon: Wrench, href: route('recomendaciones.maintenance') },
            { name: 'Vacaciones', icon: Palmtree, href: route('recomendaciones.vacation') },
            { name: 'Optimización Horarios', icon: Clock, href: route('analisis.grid-optimization') },
        ]
    },
    {
        name: 'Sistema',
        icon: Settings,
        color: 'text-slate-400',
        bgColor: 'bg-slate-400',
        items: [
            { name: 'Administración', icon: Settings, href: route('sistema.admin') },
            { name: 'Benchmarks', icon: TrendingUp, href: route('sistema.benchmarks') },
        ]
    }
]);

const activeItems = computed(() => {
    return navigation.value.find(n => n.name === activeCategory.value)?.items || [];
});

const selectCategory = (name) => {
    activeCategory.value = name;
    isSidebarOpen.value = true;
};

// Función para verificar si un link está activo basado en el path relativo
const isActive = (href) => {
    try {
        const path = new URL(href, window.location.origin).pathname;
        return page.url === path || page.url.startsWith(path + '/');
    } catch (e) {
        return page.url.startsWith(href);
    }
};
</script>

<template>
    <div class="min-h-screen bg-energy-surface flex overflow-hidden">
        <!-- Level 1: Slim Sidebar (Central Icons) -->
        <aside class="w-20 bg-slate-900 flex flex-col items-center py-6 z-[60] border-r border-white/5">
            <!-- Brand Logo -->
            <Link :href="route('dashboard')" class="w-12 h-12 bg-emerald-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-emerald-900/50 mb-10 hover:scale-105 transition-transform">
                <Zap :size="24" stroke-width="3" />
            </Link>

            <!-- Main Nav Icons -->
            <nav class="flex-1 flex flex-col gap-6 w-full items-center">
                <Link :href="route('dashboard')" class="p-3 rounded-2xl text-slate-400 hover:bg-white/5 transition-all group relative">
                    <Home :size="24" />
                    <span class="absolute left-full ml-4 px-2 py-1 bg-slate-800 text-white text-[10px] rounded opacity-0 group-hover:opacity-100 whitespace-nowrap pointer-events-none transition-opacity z-50">Inicio</span>
                </Link>

                <div class="w-10 h-[1px] bg-white/10 my-2"></div>

                <button 
                    v-for="cat in navigation" 
                    :key="cat.name"
                    @click="selectCategory(cat.name)"
                    :class="[
                        'p-3 rounded-2xl transition-all group relative',
                        activeCategory === cat.name ? 'bg-white text-slate-900 shadow-xl' : 'text-slate-400 hover:bg-white/5 hover:text-white'
                    ]"
                >
                    <component :is="cat.icon" :size="24" />
                    <span class="absolute left-full ml-4 px-2 py-1 bg-slate-800 text-white text-[10px] rounded opacity-0 group-hover:opacity-100 whitespace-nowrap pointer-events-none transition-opacity z-50">{{ cat.name }}</span>
                </button>
            </nav>

            <!-- Bottom Icons -->
            <div class="mt-auto flex flex-col gap-6 items-center">
                <button class="p-3 rounded-2xl text-slate-400 hover:text-white hover:bg-white/5 transition-all group relative">
                    <User :size="24" />
                    <span class="absolute left-full ml-4 px-2 py-1 bg-slate-800 text-white text-[10px] rounded opacity-0 group-hover:opacity-100 whitespace-nowrap pointer-events-none transition-opacity z-50">Perfil</span>
                </button>
                <Link method="post" as="button" :href="route('logout')" class="p-3 rounded-2xl text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition-all group relative">
                    <LogOut :size="24" />
                    <span class="absolute left-full ml-4 px-2 py-1 bg-rose-600 text-white text-[10px] rounded opacity-0 group-hover:opacity-100 whitespace-nowrap pointer-events-none transition-opacity z-50">Salir</span>
                </Link>
            </div>
        </aside>

        <!-- Level 2: Expanded Menu Panel -->
        <aside 
            v-if="isSidebarOpen"
            class="w-72 bg-white border-r border-slate-200 z-50 flex flex-col transition-all duration-300 transform"
        >
            <div class="h-full flex flex-col">
                <!-- Header of category -->
                <div class="p-6 border-b border-slate-50">
                    <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Sección Activa</h2>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tighter">{{ activeCategory }}</h1>
                </div>

                <!-- Entity Selector Inside Panel -->
                <div class="p-6 relative">
                    <button 
                        @click="isEntityMenuOpen = !isEntityMenuOpen"
                        class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-between group hover:border-emerald-600/30 transition-all"
                    >
                        <div class="flex items-center gap-3 text-left overflow-hidden">
                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-emerald-600 shadow-sm border border-slate-100">
                                <Building :size="16" />
                            </div>
                            <div class="truncate">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Entidad</p>
                                <p class="text-xs font-bold text-slate-900 truncate">{{ currentEntity?.name || 'Seleccionar...' }}</p>
                            </div>
                        </div>
                        <ChevronDown :size="14" :class="['text-slate-300 transition-transform', isEntityMenuOpen ? 'rotate-180' : '']" />
                    </button>

                    <!-- Entity Dropdown -->
                    <div v-if="isEntityMenuOpen" class="absolute left-6 right-6 top-full mt-2 bg-white border border-slate-200 rounded-2xl shadow-2xl z-[70] max-h-60 overflow-y-auto">
                        <div class="p-2 space-y-1">
                            <Link 
                                v-for="entity in entities" 
                                :key="entity.id"
                                :href="route('entities.activate', entity.id)"
                                class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-slate-50 transition-colors text-left"
                            >
                                <div class="w-2 h-2 rounded-full" :class="entity.id === currentEntity?.id ? 'bg-emerald-600' : 'bg-slate-200'"></div>
                                <span :class="['text-xs font-bold', entity.id === currentEntity?.id ? 'text-slate-900' : 'text-slate-500']">{{ entity.name }}</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Menu Items -->
                <nav class="flex-1 overflow-y-auto px-6 py-4 space-y-1 scrollbar-hide">
                    <Link 
                        v-for="item in activeItems" 
                        :key="item.name"
                        :href="item.href"
                        :class="[
                            'flex items-center justify-between p-4 rounded-2xl group transition-all',
                            isActive(item.href) ? 'bg-emerald-50/50' : 'hover:bg-slate-50'
                        ]"
                    >
                        <div class="flex items-center gap-4">
                            <div :class="[
                                'p-2 rounded-xl transition-all duration-300',
                                isActive(item.href) ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-400 group-hover:text-emerald-600 group-hover:bg-white group-hover:shadow-sm'
                            ]">
                                <component :is="item.icon" :size="20" />
                            </div>
                            <span :class="['text-sm font-bold', isActive(item.href) ? 'text-emerald-600' : 'text-slate-600 group-hover:text-slate-900']">{{ item.name }}</span>
                        </div>
                        <ChevronRight :size="16" :class="['transition-all', isActive(item.href) ? 'text-emerald-600' : 'text-slate-300 group-hover:text-slate-500 opacity-0 group-hover:opacity-100']" />
                    </Link>
                </nav>

                <!-- Support/Context -->
                <div class="p-6 bg-slate-50/50 border-t border-slate-100">
                    <div class="flex items-center gap-3 text-slate-400 mb-2">
                        <Activity :size="14" />
                        <span class="text-[10px] font-black uppercase tracking-widest">Estado de Carga</span>
                    </div>
                    <div class="h-1.5 w-full bg-slate-200 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-600 w-[65%] rounded-full"></div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 min-w-0 flex flex-col h-screen overflow-hidden">
            <!-- Header (Mobile Toggle) -->
            <header class="lg:hidden p-4 bg-white border-b border-slate-200 flex items-center justify-between z-[40]">
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
            </div>
        </main>
    </div>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
