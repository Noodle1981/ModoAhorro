<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { 
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
    ShoppingBag,
    BarChart3,
    Sliders,
    Clock,
    Sun,
    RefreshCw,
    Ghost,
    Settings,
    ChevronDown,
    Home,
    Thermometer,
    DollarSign,
    ShieldCheck,
    Sparkles,
    LayoutDashboard,
    Users,
    Layers,
    Award,
    KeyRound,
    CreditCard
} from 'lucide-vue-next';
import { ref, computed, watchEffect } from 'vue';

const props = defineProps({
    title: {
        type: String,
        default: null,
    },
    subtitle: {
        type: String,
        default: null,
    },
    badge: {
        type: String,
        default: null,
    },
});

const page = usePage();
const auth = computed(() => page.props.auth);
const currentEntity = computed(() => auth.value.current_entity);
const entities = computed(() => auth.value.entities);

const isSidebarOpen = ref(true);
const isEntityMenuOpen = ref(false);
const activeCategory = ref(auth.value?.user?.is_super_admin ? 'Sistema' : 'Gestión Física');

const entityLogoPath = computed(() => {
    const type = currentEntity.value?.type;
    if (type === 'hogar') return '/images/entities/logo_hogar.png';
    if (type === 'oficina') return '/images/entities/logo_oficina.png';
    if (type === 'comercio') return '/images/entities/logo_comercio.png';
    return null;
});

const currentEntityIcon = computed(() => {
    const type = currentEntity.value?.type;
    if (type === 'hogar') return Home;
    if (type === 'comercio') return ShoppingBag;
    if (type === 'oficina') return Building;
    return Building;
});

const themeColors = computed(() => {
    const type = currentEntity.value?.type;
    if (type === 'comercio') {
        return {
            text: 'text-purple-600',
            bg: 'bg-purple-600',
            bgLight: 'bg-purple-600/10',
            hoverBg: 'hover:bg-purple-600',
            hoverText: 'hover:text-purple-600',
            groupHoverText: 'group-hover:text-purple-600',
            borderHover: 'hover:border-purple-600/30',
            activeMenuBg: 'bg-purple-50/50',
            hoverMenuBg: 'hover:bg-purple-50',
            logoBg: 'bg-purple-600 shadow-purple-900/50',
        };
    }
    if (type === 'oficina') {
        return {
            text: 'text-blue-600',
            bg: 'bg-blue-600',
            bgLight: 'bg-blue-600/10',
            hoverBg: 'hover:bg-blue-600',
            hoverText: 'hover:text-blue-600',
            groupHoverText: 'group-hover:text-blue-600',
            borderHover: 'hover:border-blue-600/30',
            activeMenuBg: 'bg-blue-50/50',
            hoverMenuBg: 'hover:bg-blue-50',
            logoBg: 'bg-blue-600 shadow-blue-900/50',
        };
    }
    // Default / hogar (Emerald theme)
    return {
        text: 'text-emerald-600',
        bg: 'bg-emerald-600',
        bgLight: 'bg-emerald-600/10',
        hoverBg: 'hover:bg-emerald-600',
        hoverText: 'hover:text-emerald-600',
        groupHoverText: 'group-hover:text-emerald-600',
        borderHover: 'hover:border-emerald-600/30',
        activeMenuBg: 'bg-emerald-50/50',
        hoverMenuBg: 'hover:bg-emerald-50',
        logoBg: 'bg-emerald-600 shadow-emerald-900/50',
    };
});

// Sincronizar categoría activa con la URL actual
watchEffect(() => {
    const url = page.url;
    if (auth.value?.user?.is_super_admin) {
        if (url.startsWith('/sistema/usuarios')) activeCategory.value = 'Usuarios';
        else if (url.startsWith('/sistema/apis')) activeCategory.value = 'APIs & Conectores';
        else if (url.startsWith('/sistema/catalogo') || url.startsWith('/sistema/modelos') || url.startsWith('/sistema/eficiencia') || url.startsWith('/sistema/benchmarks')) activeCategory.value = 'Configuración';
        else activeCategory.value = 'Dashboard';
    } else {
        if (url.startsWith('/analisis')) activeCategory.value = 'Análisis';
        else if (url.startsWith('/recomendaciones')) activeCategory.value = 'Recomendaciones';
        else activeCategory.value = 'Gestión Física';
    }
});

const navigation = computed(() => [
    // --- VISTAS DE USUARIOS REGULARES ---
    {
        name: 'Gestión Física',
        icon: Building,
        color: 'text-energy-consumption',
        bgColor: 'bg-emerald-600',
        hidden: auth.value?.user?.is_super_admin,
        items: [
            { name: 'Desempeño Térmico', icon: Thermometer, href: currentEntity.value ? route('gestion.thermal.index', currentEntity.value.id) : '#' },
            { name: 'Perfil de Entidad', icon: Home, href: route('gestion.entity.edit') },
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
            { name: 'Consumo Fantasma', icon: Ghost, href: route('recomendaciones.standby') },
            { name: 'Optimización Horarios', icon: Clock, href: route('recomendaciones.grid-optimization') },
        ]
    },

    // --- SEGMENTOS DE SUPER ADMINISTRADOR ---
    {
        name: 'Dashboard',
        icon: LayoutDashboard,
        color: 'text-emerald-500',
        bgColor: 'bg-emerald-600',
        hidden: !auth.value?.user?.is_super_admin,
        items: [
            { name: 'Panel Principal', icon: LayoutDashboard, href: route('sistema.admin') },
        ]
    },
    {
        name: 'Configuración',
        icon: Settings,
        color: 'text-slate-400',
        bgColor: 'bg-slate-700',
        hidden: !auth.value?.user?.is_super_admin,
        items: [
            { name: 'Catálogo Maestro', icon: Layers, href: route('sistema.catalogue') },
            { name: 'Modelos & Clientes', icon: Sparkles, href: route('sistema.models') },
            { name: 'Matriz Eficiencia', icon: Sliders, href: route('sistema.efficiency') },
            { name: 'Benchmarks & ROI', icon: Award, href: route('sistema.benchmarks') },
        ]
    },
    {
        name: 'Usuarios',
        icon: Users,
        color: 'text-purple-500',
        bgColor: 'bg-purple-600',
        hidden: !auth.value?.user?.is_super_admin,
        items: [
            { name: 'Cuentas & Roles', icon: Users, href: route('sistema.users') },
            { name: 'Reseteos de Clave', icon: KeyRound, href: route('sistema.users.resets') },
            { name: 'Pagos & Suscripciones', icon: CreditCard, href: route('sistema.users.payments') },
        ]
    },
    {
        name: 'APIs & Conectores',
        icon: KeyRound,
        color: 'text-sky-500',
        bgColor: 'bg-sky-600',
        hidden: !auth.value?.user?.is_super_admin,
        items: [
            { name: 'APIs & Integraciones', icon: KeyRound, href: route('sistema.apis') },
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

// Función para verificar si un link está activo basado en el path relativo o rutas hijas
const isActive = (itemOrHref) => {
    const href = typeof itemOrHref === 'string' ? itemOrHref : itemOrHref?.href;
    const name = typeof itemOrHref === 'object' ? itemOrHref?.name : null;

    if (!href || href === '#') return false;

    // Desempeño Térmico cubre todas las rutas hijas /gestion/thermal/* (wizard, result, index)
    if (name === 'Desempeño Térmico' || href.includes('/gestion/thermal')) {
        if (page.url.startsWith('/gestion/thermal')) {
            return true;
        }
    }

    try {
        const path = new URL(href, window.location.origin).pathname;
        if (page.url === path) return true;
        if (path !== '/' && path !== '/inicio' && (page.url.startsWith(path + '/') || page.url.startsWith(path))) {
            return true;
        }
        return false;
    } catch {
        return page.url.startsWith(href);
    }
};

const resolvedTitle = computed(() => {
    if (props.title) return props.title;
    if (page.url === '/inicio' || page.url === '/') {
        return `Resumen de ${currentEntity.value?.name || 'Casa 27'}`;
    }
    for (const cat of navigation.value) {
        for (const item of cat.items) {
            if (isActive(item)) {
                return item.name;
            }
        }
    }
    return currentEntity.value?.name ? `Resumen de ${currentEntity.value.name}` : 'Panel Principal';
});

const resolvedIcon = computed(() => {
    if (page.url === '/inicio' || page.url === '/') {
        return Home;
    }
    for (const cat of navigation.value) {
        for (const item of cat.items) {
            if (isActive(item)) {
                return item.icon;
            }
        }
    }
    return Building;
});

const isHomeView = computed(() => {
    return (page.url === '/inicio' || page.url === '/') && !props.title;
});
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
            <Link v-if="entityLogoPath && !auth?.user?.is_super_admin" :href="route('dashboard')" class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center p-1.5 shadow-lg mb-10 hover:scale-105 transition-transform block">
                <img :src="entityLogoPath" :alt="currentEntity?.type" class="w-full h-full object-contain" />
            </Link>
            <Link v-else :href="auth?.user?.is_super_admin ? route('sistema.admin') : route('dashboard')" class="w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-lg mb-10 hover:scale-105 transition-transform" :class="auth?.user?.is_super_admin ? 'bg-slate-800 shadow-slate-950/50' : themeColors.logoBg">
                <Zap :size="24" stroke-width="3" />
            </Link>

            <!-- Main Nav Icons -->
            <nav class="flex-1 flex flex-col gap-4 w-full items-center">
                <Link :href="auth?.user?.is_super_admin ? route('sistema.admin') : route('dashboard')" class="p-3 rounded-2xl text-slate-400 hover:bg-white/5 transition-all group relative">
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
                <Link 
                    :href="route('profile.edit')" 
                    :class="[
                        'p-3 rounded-2xl transition-all group relative',
                        route().current('profile.*') ? 'bg-white text-slate-900 shadow-xl' : 'text-slate-400 hover:text-white hover:bg-white/5'
                    ]"
                >
                    <User :size="24" />
                    <span class="absolute left-full ml-4 px-2 py-1 bg-slate-800 text-white text-[10px] rounded opacity-0 group-hover:opacity-100 whitespace-nowrap pointer-events-none transition-opacity z-50">Mi Perfil</span>
                </Link>
                <Link 
                    v-if="!auth?.user?.is_super_admin" 
                    :href="route('dashboard')" 
                    class="p-3 rounded-2xl text-slate-400 hover:text-white hover:bg-white/5 transition-all group relative cursor-pointer"
                >
                    <RefreshCw :size="24" />
                    <span class="absolute left-full ml-4 px-2 py-1 bg-slate-800 text-white text-[10px] rounded opacity-0 group-hover:opacity-100 whitespace-nowrap pointer-events-none transition-opacity z-50">Cambiar Entidad</span>
                </Link>
                <Link 
                    :href="route('logout')" 
                    method="post" 
                    as="button" 
                    class="p-3 rounded-2xl text-slate-400 hover:text-rose-400 hover:bg-white/5 transition-all group relative cursor-pointer"
                >
                    <LogOut :size="24" />
                    <span class="absolute left-full ml-4 px-2 py-1 bg-slate-800 text-white text-[10px] rounded opacity-0 group-hover:opacity-100 whitespace-nowrap pointer-events-none transition-opacity z-50">Cerrar Sesión</span>
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

                <!-- Entity Selector Inside Panel (Only for users with entities) -->
                <div v-if="!auth?.user?.is_super_admin" class="p-6 relative">
                    <button 
                        @click="isEntityMenuOpen = !isEntityMenuOpen"
                        class="w-full p-4 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-between group transition-all"
                        :class="themeColors.borderHover"
                    >
                        <div class="flex items-center gap-3 text-left overflow-hidden">
                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center shadow-sm border border-slate-100" :class="themeColors.text">
                                <component :is="currentEntityIcon" :size="16" />
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
                                <div class="w-2 h-2 rounded-full" :class="entity.id === currentEntity?.id ? themeColors.bg : 'bg-slate-200'"></div>
                                <span :class="['text-xs font-bold', entity.id === currentEntity?.id ? 'text-slate-900' : 'text-slate-500']">{{ entity.name }}</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Super Admin Status Banner Inside Panel -->
                <div v-else class="px-6 py-4">
                    <div class="p-4 bg-slate-900 rounded-2xl flex items-center gap-3 text-white shadow-md">
                        <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-emerald-400">
                            <ShieldCheck :size="18" />
                        </div>
                        <div class="truncate">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Rol Global</p>
                            <p class="text-xs font-bold text-white truncate">Super Administrador</p>
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
                            isActive(item) ? themeColors.activeMenuBg : 'hover:bg-slate-50'
                        ]"
                    >
                        <div class="flex items-center gap-3">
                            <div :class="[
                                'p-2 rounded-xl transition-all duration-300',
                                isActive(item) ? ['bg-white shadow-sm', themeColors.text] : ['text-slate-400 group-hover:bg-white group-hover:shadow-sm', themeColors.groupHoverText]
                            ]">
                                <component :is="item.icon" :size="18" />
                            </div>
                            <span :class="['text-sm font-bold', isActive(item) ? themeColors.text : 'text-slate-600 group-hover:text-slate-900']">{{ item.name }}</span>
                        </div>
                        <ChevronRight :size="14" :class="['transition-all', isActive(item) ? themeColors.text : 'text-slate-300 group-hover:text-slate-500 opacity-0 group-hover:opacity-100']" />
                    </Link>
                </nav>

                <!-- Bottom: Collapse Button -->
                <div class="p-4 border-t border-slate-100">
                    <button 
                        @click="isSidebarOpen = false"
                        class="w-full flex items-center justify-center gap-2 p-3 text-slate-400 rounded-xl transition-all font-bold text-xs uppercase tracking-widest"
                        :class="[themeColors.hoverText, themeColors.hoverMenuBg]"
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
            class="hidden lg:flex fixed left-20 top-1/2 -translate-y-1/2 w-8 h-12 bg-white border border-slate-200 border-l-0 rounded-r-xl items-center justify-center text-slate-400 shadow-sm z-40 transition-all"
            :class="themeColors.hoverText"
        >
            <ChevronRight :size="16" />
        </button>

        <!-- Main Content -->
        <main class="flex-1 min-w-0 flex flex-col h-screen overflow-hidden">
            <!-- Navbar Horizontal Estático Superior para Títulos -->
            <header class="bg-white border-b border-slate-200/80 px-4 md:px-8 py-3 flex items-center justify-between z-30 shrink-0 min-h-[60px]">
                <!-- Left: Sidebar Toggle (mobile) + Icono + Título -->
                <div class="flex items-center gap-3 md:gap-3.5 overflow-hidden">
                    <button 
                        @click="isSidebarOpen = !isSidebarOpen" 
                        class="lg:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-xl transition-colors shrink-0"
                    >
                        <Menu v-if="!isSidebarOpen" :size="20" />
                        <X v-else :size="20" />
                    </button>

                    <!-- Icono Normalizado de la Vista -->
                    <div :class="['w-9 h-9 rounded-xl flex items-center justify-center border shadow-xs shrink-0', themeColors.bgLight, themeColors.text, themeColors.borderHover]">
                        <component :is="resolvedIcon" :size="18" stroke-width="2.5" />
                    </div>

                    <!-- Título Normalizado -->
                    <h1 class="text-base md:text-lg font-black text-slate-900 tracking-tight leading-none truncate">
                        <template v-if="isHomeView">
                            Resumen de <span :class="themeColors.text">{{ currentEntity?.name || 'Casa 27' }}</span>
                        </template>
                        <template v-else>
                            {{ resolvedTitle }}
                        </template>
                    </h1>
                </div>
            </header>

            <!-- Scrollable Page Content -->
            <div class="flex-1 overflow-y-auto p-4 md:p-8 lg:p-8 xl:p-12 relative">
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
