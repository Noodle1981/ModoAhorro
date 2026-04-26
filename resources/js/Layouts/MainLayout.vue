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
    Thermometer,
    DollarSign
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth);
const currentEntity = computed(() => auth.value.current_entity);
const entities = computed(() => auth.value.entities);

const isSidebarOpen = ref(true);
const isEntityMenuOpen = ref(false);
const activeCategory = ref(auth.value?.user?.is_super_admin ? 'Sistema' : 'Gestión Física');

// Sincronizar categoría activa con la URL actual
import { watchEffect } from 'vue';
watchEffect(() => {
    const url = page.url;
    if (url.startsWith('/sistema')) activeCategory.value = 'Sistema';
    else if (url.startsWith('/analisis')) activeCategory.value = 'Análisis';
    else if (url.startsWith('/recomendaciones')) activeCategory.value = 'Recomendaciones';
    else activeCategory.value = auth.value?.user?.is_super_admin ? 'Sistema' : 'Gestión Física';
});

const navigation = computed(() => [
    {
        name: 'Gestión Física',
        icon: Building,
        color: 'text-energy-consumption',
        bgColor: 'bg-emerald-600',
        hidden: auth.value?.user?.is_super_admin,
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
        hidden: auth.value?.user?.is_super_admin,
        items: [
            { name: 'Ajuste de Ciclos', icon: Sliders, href: route('analisis.usage') },
            { name: 'Consumo Real', icon: BarChart3, href: route('analisis.consumption') },
            { name: 'Impacto por Equipo', icon: DollarSign, href: route('analisis.equipment-cost') },
            { name: 'Evolución Temporal', icon: Activity, href: route('analisis.time') },
        ]
    },
    {
        name: 'Recomendaciones',
        icon: Sun,
        color: 'text-energy-solar',
        bgColor: 'bg-amber-500',
        hidden: auth.value?.user?.is_super_admin,
        items: [
            { name: 'Proyecto Solar', icon: Sun, href: route('recomendaciones.solar') },
            { name: 'Reemplazos Eficientes', icon: RefreshCw, href: route('recomendaciones.replacements') },
            { name: 'Análisis de Ciclos', icon: RefreshCw, href: '#' }, // Futuro
            { name: 'Modelos Deterministas', icon: Heart, href: '#' }, // Futuro
            { name: 'Consumo Fantasma', icon: Ghost, href: route('recomendaciones.standby') },
            { name: 'Optimización Horarios', icon: Clock, href: route('analisis.grid-optimization') },
        ]
    },
    {
        name: 'Sistema',
        icon: Settings,
        color: 'text-slate-400',
        bgColor: 'bg-slate-400',
        hidden: !auth.value?.user?.is_super_admin,
        items: [
            { name: 'Dashboard Admin', icon: Settings, href: route('sistema.admin') },
            { name: 'Catálogo Maestro', icon: Briefcase, href: route('sistema.catalogue') },
            { name: 'Matriz Eficiencia', icon: Sliders, href: route('sistema.efficiency') },
            { name: 'Benchmarks', icon: TrendingUp, href: route('sistema.benchmarks') },
        ]
    }
]);

const activeItems = computed(() => {
    const categories = navigation.value.filter(n => !n.hidden);
    return categories.find(n => n.name === activeCategory.value)?.items || [];
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
    <div class="min-h-screen bg-energy-surface flex overflow-hidden relative">
        <!-- Backdrop for mobile sidebar -->
        <div 
            v-if="isSidebarOpen" 
            @click="isSidebarOpen = false" 
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[80] lg:hidden animate-in fade-in duration-300"
        ></div>

        <!-- Level 1: Slim Sidebar (Central Icons) -->
        <aside class="hidden lg:flex w-20 bg-slate-900 flex flex-col items-center py-6 z-[90] border-r border-white/5 shrink-0">
            <!-- Brand Logo -->
            <Link :href="route('dashboard')" class="w-12 h-12 bg-emerald-600 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-emerald-900/50 mb-10 hover:scale-105 transition-transform">
                <Zap :size="24" stroke-width="3" />
            </Link>

            <!-- Main Nav Icons -->
            <nav class="flex-1 flex flex-col gap-4 w-full items-center">
                <Link :href="route('dashboard')" class="p-3 rounded-2xl text-slate-400 hover:bg-white/5 transition-all group relative">
                    <Home :size="24" />
                    <span class="absolute left-full ml-4 px-2 py-1 bg-slate-800 text-white text-[10px] rounded opacity-0 group-hover:opacity-100 whitespace-nowrap pointer-events-none transition-opacity z-50">Inicio</span>
                </Link>

                <div class="w-10 h-[1px] bg-white/10 my-2"></div>

                <button 
                    v-for="cat in navigation.filter(n => !n.hidden)" 
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
            class="fixed inset-y-0 left-0 lg:relative w-72 bg-white border-r border-slate-200 z-[100] flex flex-col transition-all duration-300 transform shadow-2xl lg:shadow-none shrink-0"
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
                            'flex items-center justify-between p-2.5 rounded-2xl group transition-all',
                            isActive(item.href) ? 'bg-emerald-50/50' : 'hover:bg-slate-50'
                        ]"
                    >
                        <div class="flex items-center gap-3">
                            <div :class="[
                                'p-2 rounded-xl transition-all duration-300',
                                isActive(item.href) ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-400 group-hover:text-emerald-600 group-hover:bg-white group-hover:shadow-sm'
                            ]">
                                <component :is="item.icon" :size="18" />
                            </div>
                            <span :class="['text-sm font-bold', isActive(item.href) ? 'text-emerald-600' : 'text-slate-600 group-hover:text-slate-900']">{{ item.name }}</span>
                        </div>
                        <ChevronRight :size="14" :class="['transition-all', isActive(item.href) ? 'text-emerald-600' : 'text-slate-300 group-hover:text-slate-500 opacity-0 group-hover:opacity-100']" />
                    </Link>
                </nav>

                <!-- Bottom: Collapse Button -->
                <div class="p-4 border-t border-slate-100">
                    <button 
                        @click="isSidebarOpen = false"
                        class="w-full flex items-center justify-center gap-2 p-3 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all font-bold text-xs uppercase tracking-widest"
                    >
                        <ChevronRight :size="16" class="rotate-180" /> Plegar Menú
                    </button>
                </div>
            </div>
        </aside>

        <!-- Sidebar Toggle (When collapsed - Only Desktop) -->
        <button 
            v-if="!isSidebarOpen"
            @click="isSidebarOpen = true"
            class="hidden lg:flex fixed left-20 top-1/2 -translate-y-1/2 w-8 h-12 bg-white border border-slate-200 border-l-0 rounded-r-xl items-center justify-center text-slate-400 hover:text-emerald-600 shadow-sm z-40 transition-all"
        >
            <ChevronRight :size="16" />
        </button>

        <!-- Main Content -->
        <main class="flex-1 min-w-0 flex flex-col h-screen overflow-hidden">
            <!-- Header (Mobile Toggle) -->
            <header class="lg:hidden p-4 bg-white border-b border-slate-200 flex items-center justify-between z-[40] shrink-0">
                <button @click="isSidebarOpen = !isSidebarOpen" class="p-2 text-slate-600 hover:bg-slate-50 rounded-xl transition-colors">
                    <Menu v-if="!isSidebarOpen" :size="24" />
                    <X v-else :size="24" />
                </button>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center text-white">
                        <Zap :size="16" stroke-width="3" />
                    </div>
                    <span class="font-black text-slate-900 tracking-tighter">ModoAhorro</span>
                </div>
                <div class="w-10 h-10 flex items-center justify-center">
                    <User :size="20" class="text-slate-400" />
                </div>
            </header>

            <!-- Scrollable Page Content -->
            <div class="flex-1 overflow-y-auto p-4 md:p-8 lg:p-12 relative">
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
