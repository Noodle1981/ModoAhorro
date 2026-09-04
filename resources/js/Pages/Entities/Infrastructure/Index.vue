<script setup>
import { shallowRef, computed, watch, onUnmounted } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmDeleteModal from '@/Components/ConfirmDeleteModal.vue';
import { 
    LayoutGrid, 
    Plus, 
    Monitor, 
    Zap, 
    Trash2, 
    Pencil, 
    Building2, 
    AirVent, 
    Refrigerator, 
    Tv, 
    Lightbulb, 
    Microwave, 
    Waves, 
    ShieldCheck, 
    Bath, 
    Sparkles, 
    Settings
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    rooms: Array,
    categories: Array,
    types: Array,
    flash: Object
});

const themeColors = computed(() => {
    const type = props.entity?.type;
    if (type === 'comercio') {
        return {
            text: 'text-purple-600',
            bg: 'bg-purple-600',
            bgLight: 'bg-purple-600/10',
            borderLight: 'border-purple-600/20',
            hoverBg: 'hover:bg-purple-600',
            hoverText: 'hover:text-purple-600',
            focusRing: 'focus:ring-purple-600/10',
            focusRingForm: 'focus:ring-purple-600/20',
            focusRingInput: 'focus:ring-purple-600',
            borderBottom: 'border-purple-600',
            groupHoverText: 'group-hover:text-purple-600',
            
            // Room card specific (Comercio = Deep Purple theme)
            roomBg: 'bg-purple-950 shadow-purple-900/20',
            roomTextLight: 'text-purple-300',
            roomTextMuted: 'text-purple-200/60',
            roomBtn: 'text-purple-950 hover:bg-purple-600 hover:text-white shadow-purple-950/20'
        };
    }
    if (type === 'oficina') {
        return {
            text: 'text-blue-600',
            bg: 'bg-blue-600',
            bgLight: 'bg-blue-600/10',
            borderLight: 'border-blue-600/20',
            hoverBg: 'hover:bg-blue-600',
            hoverText: 'hover:text-blue-600',
            focusRing: 'focus:ring-blue-600/10',
            focusRingForm: 'focus:ring-blue-600/20',
            focusRingInput: 'focus:ring-blue-600',
            borderBottom: 'border-blue-600',
            groupHoverText: 'group-hover:text-blue-600',
            
            // Room card specific (Oficina = Deep Blue theme)
            roomBg: 'bg-blue-950 shadow-blue-900/20',
            roomTextLight: 'text-blue-300',
            roomTextMuted: 'text-blue-200/60',
            roomBtn: 'text-blue-950 hover:bg-blue-600 hover:text-white shadow-blue-950/20'
        };
    }
    // Default / hogar (Emerald theme)
    return {
        text: 'text-emerald-600',
        bg: 'bg-emerald-600',
        bgLight: 'bg-emerald-600/10',
        borderLight: 'border-emerald-600/20',
        hoverBg: 'hover:bg-emerald-600',
        hoverText: 'hover:text-emerald-600',
        focusRing: 'focus:ring-emerald-600/10',
        focusRingForm: 'focus:ring-emerald-600/20',
        focusRingInput: 'focus:ring-emerald-600',
        borderBottom: 'border-emerald-600',
        groupHoverText: 'group-hover:text-emerald-600',
        
        // Room card specific (Hogar = Deep Emerald theme)
        roomBg: 'bg-emerald-950 shadow-emerald-900/20',
        roomTextLight: 'text-emerald-300',
        roomTextMuted: 'text-emerald-200/60',
        roomBtn: 'text-emerald-950 hover:bg-emerald-600 hover:text-white shadow-emerald-950/20'
    };
});

const selectedRoomId = shallowRef(props.rooms.length > 0 ? props.rooms[0].id : null);
const selectedRoom = computed(() => props.rooms.find(r => r.id === selectedRoomId.value));

// Modals State
const showRoomModal = shallowRef(false);
const editingRoom = shallowRef(null);
const showEquipmentModal = shallowRef(false);
const editingEquipment = shallowRef(null);

// Forms
const roomForm = useForm({
    id: null,
    entity_id: props.entity.id,
    name: '',
    description: '',
});

const eqForm = useForm({
    id: null,
    room_id: '',
    category_id: '',
    type_id: '',
    model_id: null,
    name: '',
    nominal_power_w: '',
    avg_daily_use_hours: '',
    has_defined_pattern: false,
    brand: '',
    model: '',
    serial_number: '',
    energy_label: '',
    is_standby: null,
    is_inverter: false,
    cantidad: 1,
    is_active: true
});

// Category -> Type filtering
const filteredTypes = computed(() => {
    if (!eqForm.category_id) return [];
    return props.types.filter(t => t.category_id === eqForm.category_id);
});

// Autocompletado de Modelos Oficiales Verificados
const modelSuggestions = shallowRef([]);
const isSearchingModels = shallowRef(false);
const showModelDropdown = shallowRef(false);
let searchDebounceTimer = null;
let searchAbortController = null;

const onBrandOrModelInput = () => {
    clearTimeout(searchDebounceTimer);
    if (searchAbortController) {
        searchAbortController.abort();
    }

    searchDebounceTimer = setTimeout(async () => {
        const query = (eqForm.brand + ' ' + eqForm.model).trim();
        if (query.length < 2) {
            modelSuggestions.value = [];
            showModelDropdown.value = false;
            return;
        }
        searchAbortController = new AbortController();
        try {
            isSearchingModels.value = true;
            const res = await fetch(`/sistema/api/modelos-autocompletar?q=${encodeURIComponent(query)}`, {
                signal: searchAbortController.signal,
            });
            if (res.ok) {
                const data = await res.json();
                modelSuggestions.value = data;
                showModelDropdown.value = data.length > 0;
            }
        } catch (e) {
            if (e.name !== 'AbortError') {
                console.error('Error buscando modelos oficiales:', e);
            }
        } finally {
            isSearchingModels.value = false;
        }
    }, 250);
};

onUnmounted(() => {
    clearTimeout(searchDebounceTimer);
    if (searchAbortController) {
        searchAbortController.abort();
    }
});

const selectModelSuggestion = (m) => {
    eqForm.brand = m.brand;
    eqForm.model = m.model;
    eqForm.model_id = m.id;
    if (m.category_id) eqForm.category_id = m.category_id;
    if (m.type_id) eqForm.type_id = m.type_id;
    if (m.nominal_power_w) eqForm.nominal_power_w = m.nominal_power_w;
    if (m.is_inverter !== undefined) eqForm.is_inverter = m.is_inverter;
    if (m.energy_label) eqForm.energy_label = m.energy_label;
    if (!eqForm.name || eqForm.name.trim() === '') {
        eqForm.name = `${m.brand} ${m.model}`;
    }
    showModelDropdown.value = false;
};

// Auto-fill defaults when type changes
watch(() => eqForm.type_id, (newTypeId) => {
    if (!newTypeId || editingEquipment.value) return;
    const type = props.types.find(t => t.id === newTypeId);
    if (type) {
        eqForm.nominal_power_w = type.default_power_watts;
        // Si es climatización, solemos dejarlo en 0 para que el usuario defina o se use el clima
        if (type.is_climatization) {
            eqForm.avg_daily_use_hours = '';
        } else {
            eqForm.avg_daily_use_hours = ''; // O un valor por defecto si quisiéramos
        }
        
        // Sugerir Inverter si el nombre lo indica (aunque los estamos borrando, por si acaso)
        if (type.name.toLowerCase().includes('inverter')) {
            eqForm.is_inverter = true;
        }
    }
});

// Room Actions
const openRoomCreate = () => {
    editingRoom.value = null;
    // Limpieza manual agresiva para evitar persistencia de estados
    roomForm.id = null;
    roomForm.name = '';
    roomForm.description = '';
    roomForm.clearErrors();
    showRoomModal.value = true;
};

const openRoomEdit = (room) => {
    editingRoom.value = room;
    roomForm.clearErrors();
    roomForm.id = room.id;
    roomForm.entity_id = room.entity_id;
    roomForm.name = room.name;
    roomForm.description = room.description;
    showRoomModal.value = true;
};

const submitRoom = () => {
    if (editingRoom.value) {
        roomForm.put(route('gestion.rooms.update', editingRoom.value.id), {
            onSuccess: () => {
                showRoomModal.value = false;
            }
        });
    } else {
        roomForm.post(route('gestion.rooms.store'), {
            onSuccess: () => {
                showRoomModal.value = false;
                // Auto-seleccionar el ambiente más reciente (el último creado)
                if (props.rooms.length > 0) {
                    const lastRoom = props.rooms[props.rooms.length - 1];
                    selectedRoomId.value = lastRoom.id;
                }
            }
        });
    }
};

const roomToDelete = shallowRef(null);
const isDeletingRoom = shallowRef(false);

const deleteRoom = (room) => {
    roomToDelete.value = room;
};

const confirmDeleteRoom = () => {
    if (!roomToDelete.value) return;
    isDeletingRoom.value = true;
    router.delete(route('gestion.rooms.destroy', roomToDelete.value.id), {
        onSuccess: () => {
            roomToDelete.value = null;
        },
        onFinish: () => {
            isDeletingRoom.value = false;
        },
    });
};

// Equipment Actions
const openEqCreate = () => {
    editingEquipment.value = null;
    eqForm.id = null;
    eqForm.category_id = '';
    eqForm.type_id = '';
    eqForm.name = '';
    eqForm.nominal_power_w = '';
    eqForm.avg_daily_use_hours = '';
    eqForm.has_defined_pattern = false;
    eqForm.brand = '';
    eqForm.model = '';
    eqForm.serial_number = '';
    eqForm.energy_label = '';
    eqForm.is_standby = false;
    eqForm.is_inverter = false;
    eqForm.cantidad = 1;
    eqForm.is_active = true;
    eqForm.room_id = selectedRoomId.value;
    eqForm.clearErrors();
    showEquipmentModal.value = true;
};

const openEqEdit = (eq) => {
    editingEquipment.value = eq;
    eqForm.id = eq.id;
    eqForm.room_id = eq.room_id;
    eqForm.category_id = eq.category_id;
    eqForm.type_id = eq.type_id;
    eqForm.name = eq.name;
    eqForm.nominal_power_w = eq.nominal_power_w;
    eqForm.avg_daily_use_hours = eq.avg_daily_use_hours;
    eqForm.has_defined_pattern = !!eq.has_defined_pattern;
    eqForm.brand = eq.brand || '';
    eqForm.model = eq.model || '';
    eqForm.serial_number = eq.serial_number || '';
    eqForm.energy_label = eq.energy_label || '';
    eqForm.is_standby = !!eq.is_standby;
    eqForm.is_inverter = !!eq.is_inverter;
    eqForm.is_active = !!eq.is_active;
    showEquipmentModal.value = true;
};

const submitEq = () => {
    if (editingEquipment.value) {
        eqForm.put(route('gestion.equipment.update', editingEquipment.value.id), {
            onSuccess: () => showEquipmentModal.value = false,
        });
    } else {
        eqForm.post(route('gestion.equipment.store'), {
            onSuccess: () => showEquipmentModal.value = false,
        });
    }
};

const equipmentToDelete = shallowRef(null);
const isDeletingEquipment = shallowRef(false);

const deleteEq = (eq) => {
    equipmentToDelete.value = eq;
};

const confirmDeleteEquipment = () => {
    if (!equipmentToDelete.value) return;
    isDeletingEquipment.value = true;
    router.delete(route('gestion.equipment.destroy', equipmentToDelete.value.id), {
        onSuccess: () => {
            equipmentToDelete.value = null;
        },
        onFinish: () => {
            isDeletingEquipment.value = false;
        },
    });
};

const getCategoryIcon = (catName) => {
    const name = catName.toLowerCase();
    if (name.includes('climatización')) return AirVent;
    if (name.includes('refrigeración')) return Refrigerator;
    if (name.includes('iluminación')) return Lightbulb;
    if (name.includes('cocina')) return Microwave;
    if (name.includes('lavado')) return Waves;
    if (name.includes('entretenimiento')) return Tv;
    if (name.includes('oficina') || name.includes('informática')) return Monitor;
    if (name.includes('seguridad') || name.includes('redes')) return ShieldCheck;
    if (name.includes('agua caliente')) return Bath;
    if (name.includes('cuidado personal')) return Sparkles;
    if (name.includes('mantenimiento') || name.includes('bombas')) return Settings;
    return Zap;
};
</script>

<template>
    <MainLayout>
        <Head title="Infraestructura y Equipos" />

        <div class="h-full flex flex-col md:flex-row gap-4 max-w-7xl mx-auto w-full min-h-0 overflow-hidden">
            <!-- Left Pane: Rooms (Master List) -->
            <aside class="w-full md:w-72 lg:w-80 shrink-0 flex flex-col bg-white rounded-3xl border border-slate-100 shadow-xl overflow-hidden min-h-0 max-h-[38vh] md:max-h-full">
                <!-- Rooms Header (Fixed) -->
                <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-2 shrink-0 bg-slate-50/50">
                    <div class="flex items-center gap-2">
                        <Building2 :size="16" class="text-slate-400" />
                        <h3 class="text-xs font-black text-slate-900 uppercase tracking-wider">Ambientes</h3>
                        <span class="px-2 py-0.5 bg-slate-200/80 rounded-full text-[9px] font-black text-slate-600 leading-none">
                            {{ rooms.length }}
                        </span>
                    </div>
                    <button 
                        @click="openRoomCreate" 
                        class="h-7 px-2.5 rounded-lg flex items-center gap-1 font-black text-[9px] uppercase tracking-wider transition-all hover:text-white cursor-pointer shadow-xs" 
                        :class="[themeColors.bgLight, themeColors.text, themeColors.hoverBg]"
                        title="Crear nuevo ambiente"
                    >
                        <Plus :size="13" stroke-width="3" />
                        <span>Nuevo</span>
                    </button>
                </div>

                <!-- Rooms Scrollable List -->
                <div class="flex-1 min-h-0 overflow-y-auto p-2 space-y-1.5 custom-scrollbar">
                    <div v-if="rooms.length === 0" class="p-6 text-center text-slate-400 text-xs">
                        <p>No hay ambientes registrados.</p>
                        <button @click="openRoomCreate" class="mt-2 text-[10px] font-black uppercase text-slate-900 underline">Crear primero</button>
                    </div>

                    <button 
                        v-for="room in rooms" 
                        :key="room.id"
                        @click="selectedRoomId = room.id"
                        :class="[
                            'w-full flex items-center justify-between p-3 rounded-2xl transition-all group text-left cursor-pointer',
                            selectedRoomId === room.id ? 'bg-slate-900 text-white shadow-md' : 'hover:bg-slate-50 text-slate-700'
                        ]"
                    >
                        <div class="flex items-center gap-2.5 min-w-0 pr-2">
                            <div :class="['w-2 h-2 rounded-full shrink-0', selectedRoomId === room.id ? themeColors.bg : 'bg-slate-300']"></div>
                            <span class="text-xs font-black truncate leading-tight">{{ room.name }}</span>
                        </div>
                        <span :class="['text-[9px] font-black px-2 py-0.5 rounded-lg shrink-0', selectedRoomId === room.id ? 'bg-slate-800 text-slate-300' : 'bg-slate-100 text-slate-400']">
                            {{ room.equipment_count }} {{ room.equipment_count === 1 ? 'eq.' : 'eqs.' }}
                        </span>
                    </button>
                </div>

                <!-- Active Room Summary Footer (Fixed at Bottom of Left Pane) -->
                <div v-if="selectedRoom" :class="[themeColors.roomBg, 'p-4 text-white shrink-0 relative overflow-hidden border-t border-white/10']">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[8px] font-black uppercase tracking-widest opacity-60" :class="themeColors.roomTextLight">Ambiente Activo</span>
                        <div class="flex items-center gap-1">
                            <button @click="openRoomEdit(selectedRoom)" class="w-6 h-6 flex items-center justify-center rounded-md bg-white/10 text-white/60 hover:text-white hover:bg-white/20 transition-all cursor-pointer" title="Editar ambiente">
                                <Pencil :size="11" />
                            </button>
                            <button @click="deleteRoom(selectedRoom)" class="w-6 h-6 flex items-center justify-center rounded-md bg-white/10 text-white/40 hover:text-rose-300 hover:bg-rose-500/20 transition-all cursor-pointer" title="Eliminar ambiente">
                                <Trash2 :size="11" />
                            </button>
                        </div>
                    </div>
                    <h4 class="text-xs font-black truncate">{{ selectedRoom.name }}</h4>
                    <p class="text-[9px] font-medium opacity-70 truncate mt-0.5" :title="selectedRoom.description">
                        {{ selectedRoom.description || 'Sin descripción adicional.' }}
                    </p>
                </div>
            </aside>

            <!-- Right Pane: Equipment Details (Scrollable Main View) -->
            <main class="flex-1 min-w-0 flex flex-col bg-white rounded-3xl border border-slate-100 shadow-xl overflow-hidden min-h-0">
                <div v-if="selectedRoom" class="flex-1 min-h-0 flex flex-col">
                    <!-- Top Action Toolbar (Fixed) -->
                    <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between gap-3 shrink-0 bg-slate-50/40">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-white border border-slate-100 shadow-xs flex items-center justify-center shrink-0" :class="themeColors.text">
                                <LayoutGrid :size="16" />
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h2 class="text-base font-black text-slate-900 tracking-tight truncate">{{ selectedRoom.name }}</h2>
                                    <span class="px-2 py-0.5 bg-slate-200/80 rounded-md text-[8px] font-black text-slate-600 uppercase tracking-wider shrink-0">
                                        {{ selectedRoom.equipment_count }} {{ selectedRoom.equipment_count === 1 ? 'Equipo' : 'Equipos' }}
                                    </span>
                                </div>
                                <p class="text-[10px] text-slate-400 font-medium truncate">Inventario eléctrico asignado a este ambiente</p>
                            </div>
                        </div>

                        <button 
                            @click="openEqCreate" 
                            class="bg-slate-900 text-white px-4 py-2 rounded-xl font-black text-[10px] uppercase tracking-wider transition-all hover:scale-105 active:scale-95 shadow-sm shrink-0 flex items-center gap-1.5 cursor-pointer" 
                            :class="themeColors.hoverBg"
                        >
                            <Plus :size="13" stroke-width="3" />
                            <span>Añadir Equipo</span>
                        </button>
                    </div>

                    <!-- Equipment Scrollable Grid -->
                    <div class="flex-1 min-h-0 overflow-y-auto p-4 sm:p-5 custom-scrollbar">
                        <div v-if="selectedRoom.equipment && selectedRoom.equipment.length > 0" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3.5">
                            <div 
                                v-for="eq in selectedRoom.equipment" 
                                :key="eq.id"
                                class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 space-y-3.5 group hover:shadow-md hover:border-slate-200 transition-all flex flex-col justify-between"
                            >
                                <div class="space-y-2.5">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 shrink-0 border border-slate-100 transition-colors" :class="themeColors.groupHoverText">
                                                <component :is="getCategoryIcon(eq.category?.name || '')" :size="20" />
                                            </div>
                                            <div class="min-w-0">
                                                <h5 class="text-xs font-black text-slate-900 leading-tight truncate">{{ eq.name }}</h5>
                                                <p v-if="eq.brand || eq.model" class="text-[8px] font-bold uppercase tracking-wider truncate mt-0.5" :class="themeColors.text">
                                                    {{ eq.brand }} {{ eq.model }}
                                                </p>
                                                <p class="text-[8px] font-black text-slate-300 uppercase tracking-widest truncate">{{ eq.type?.name }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1 shrink-0">
                                            <div v-if="eq.is_inverter" class="p-1 bg-emerald-50 text-emerald-500 rounded-md border border-emerald-100" title="Tecnología Inverter">
                                                <Sparkles :size="12" />
                                            </div>
                                            <div v-if="eq.is_standby" class="p-1 bg-amber-50 text-amber-500 rounded-md border border-amber-100" title="Consumo Vampiro (Standby)">
                                                <Zap :size="12" />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <div class="bg-slate-50/70 p-2 rounded-xl border border-slate-100/60">
                                            <p class="text-[7px] font-black text-slate-400 uppercase">Potencia</p>
                                            <p class="text-xs font-black text-slate-800">{{ eq.nominal_power_w }}<span class="text-[8px] font-bold ml-0.5 text-slate-400">W</span></p>
                                        </div>
                                        <div class="bg-slate-50/70 p-2 rounded-xl border border-slate-100/60">
                                            <p class="text-[7px] font-black text-slate-400 uppercase">Eficiencia</p>
                                            <p class="text-xs font-black text-slate-800">
                                                <span v-if="eq.energy_label" class="text-emerald-600">{{ eq.energy_label }}</span>
                                                <span v-else class="text-slate-300 font-medium italic text-[10px]">N/A</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 pt-2 border-t border-slate-50">
                                    <button @click="openEqEdit(eq)" class="flex-1 bg-slate-50 hover:bg-slate-100 text-slate-500 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-wider transition-all cursor-pointer" :class="themeColors.hoverText">
                                        Editar
                                    </button>
                                    <button @click="deleteEq(eq)" class="w-8 bg-slate-50 hover:bg-rose-50 text-slate-300 hover:text-rose-500 py-1.5 rounded-lg transition-all cursor-pointer" title="Eliminar equipo">
                                        <Trash2 :size="12" class="mx-auto" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Empty Equipment State -->
                        <div v-else class="h-full min-h-[220px] flex flex-col items-center justify-center p-8 text-center bg-slate-50/40 rounded-2xl border-2 border-dashed border-slate-100">
                            <div class="max-w-xs mx-auto space-y-3">
                                <Zap :size="32" class="text-slate-300 mx-auto" />
                                <div>
                                    <h4 class="text-sm font-black text-slate-700">Sin equipos registrados</h4>
                                    <p class="text-[11px] text-slate-400 font-medium mt-0.5">Añade los artefactos eléctricos de este ambiente para calcular su consumo.</p>
                                </div>
                                <button @click="openEqCreate" class="inline-flex items-center gap-1 px-4 py-2 bg-slate-900 text-white rounded-xl text-[9px] font-black uppercase tracking-wider transition-all shadow-sm cursor-pointer" :class="themeColors.hoverBg">
                                    <Plus :size="12" stroke-width="3" />
                                    <span>Añadir Primer Equipo</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- No Room Selected -->
                <div v-else class="flex-1 flex flex-col items-center justify-center p-12 text-center space-y-4">
                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-300">
                        <Monitor :size="32" />
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-base font-black text-slate-900">Selecciona un ambiente</h3>
                        <p class="text-xs text-slate-400 max-w-xs">Elige un espacio del panel lateral para administrar su inventario de equipos.</p>
                    </div>
                </div>
            </main>
        </div>

        <!-- Room Modal -->
        <Modal :show="showRoomModal" max-width="md" @close="showRoomModal = false">
            <div class="p-8 md:p-12 space-y-6 md:space-y-8">
                <div class="space-y-2">
                    <h2 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tighter">{{ editingRoom ? 'Configurar Ambiente' : 'Nuevo Ambiente' }}</h2>
                    <p class="text-sm text-slate-400 font-medium">Cree un espacio funcional para organizar sus equipos.</p>
                </div>
                <form @submit.prevent="submitRoom" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nombre del Espacio</label>
                        <input v-model="roomForm.name" type="text" placeholder="Ej: Living, Cocina, Oficina..." class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-black text-slate-900 transition-all focus:ring-2" :class="themeColors.focusRingForm" />
                        <p v-if="roomForm.errors.name" class="text-[9px] text-rose-500 font-bold">{{ roomForm.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Descripción Breve</label>
                        <textarea v-model="roomForm.description" rows="3" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-medium text-slate-700 transition-all focus:ring-2" :class="themeColors.focusRingForm"></textarea>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 pt-4">
                        <button type="submit" class="w-full sm:flex-1 bg-slate-900 text-white py-4 md:py-5 rounded-[18px] md:rounded-[24px] font-black text-xs uppercase tracking-widest transition-all shadow-xl shadow-slate-200 cursor-pointer" :class="themeColors.hoverBg">
                            Confirmar
                        </button>
                        <button type="button" @click="showRoomModal = false" class="w-full sm:w-auto px-8 py-4 md:py-5 text-slate-400 font-black text-xs uppercase tracking-widest order-last sm:order-none cursor-pointer">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Equipment Modal -->
        <Modal :show="showEquipmentModal" max-width="2xl" @close="showEquipmentModal = false">
            <div class="px-6 md:px-8 pt-6 md:pt-8 pb-4 border-b border-slate-50">
                <div class="inline-flex items-center gap-2 px-2 py-0.5 bg-slate-900 text-white rounded-full text-[8px] font-black uppercase tracking-widest mb-2">
                    {{ selectedRoom?.name }}
                </div>
                <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tighter">{{ editingEquipment ? 'Especificaciones Técnicas' : 'Nuevo Activo Eléctrico' }}</h2>
                </div>

                <form @submit.prevent="submitEq" class="px-6 md:px-8 py-4 md:py-6 space-y-4 max-h-[85vh] overflow-y-auto custom-scrollbar">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-5">
                        <!-- Left Column: Category & Name -->
                        <div class="space-y-4">
                            <div class="space-y-1.5">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Categoría</label>
                                <select v-model="eqForm.category_id" class="w-full bg-slate-50 border-none rounded-xl p-3 text-sm font-bold text-slate-900 transition-all appearance-none focus:ring-2" :class="themeColors.focusRingForm">
                                    <option value="">Seleccionar...</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Tipo de Equipo</label>
                                <select v-model="eqForm.type_id" class="w-full bg-slate-50 border-none rounded-xl p-3 text-sm font-bold text-slate-900 transition-all appearance-none disabled:opacity-50 focus:ring-2" :class="themeColors.focusRingForm" :disabled="!eqForm.category_id">
                                    <option value="">Seleccionar tipo...</option>
                                    <option v-for="type in filteredTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Right Column: Identity -->
                        <div class="space-y-4">
                            <div class="space-y-1.5">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Nombre / Alias</label>
                                <input v-model="eqForm.name" type="text" placeholder="Ej: Aire Living, Heladera Cocina..." class="w-full bg-slate-50 border-none rounded-xl p-3 text-sm font-black text-slate-900 transition-all focus:ring-2" :class="themeColors.focusRingForm" />
                            </div>
                            <div v-if="!editingEquipment" class="space-y-1.5">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Cantidad</label>
                                <input v-model="eqForm.cantidad" type="number" class="w-full bg-slate-50 border-none rounded-xl p-3 text-sm font-black text-slate-900 transition-all focus:ring-2" :class="themeColors.focusRingForm" />
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Asset Details -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-5">
                        <div class="space-y-4 relative">
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Marca</label>
                                    <span v-if="eqForm.model_id" class="text-[9px] font-black text-emerald-600 uppercase tracking-wider flex items-center gap-1">
                                        <Sparkles :size="10" /> Modelo Oficial
                                    </span>
                                </div>
                                <input 
                                    v-model="eqForm.brand" 
                                    @input="onBrandOrModelInput"
                                    type="text" 
                                    placeholder="Ej: Samsung, Philips, LG..." 
                                    class="w-full bg-slate-50 border-none rounded-xl p-3 text-sm font-bold text-slate-900 transition-all focus:ring-2" 
                                    :class="themeColors.focusRingForm" 
                                />
                            </div>
                            <div class="space-y-1.5 relative">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Modelo / N° Serie</label>
                                <div class="flex gap-2">
                                    <input 
                                        v-model="eqForm.model" 
                                        @input="onBrandOrModelInput"
                                        type="text" 
                                        placeholder="Ej: RT38, Inverter..." 
                                        class="flex-1 bg-slate-50 border-none rounded-xl p-3 text-sm font-bold text-slate-900 transition-all focus:ring-2" 
                                        :class="themeColors.focusRingForm" 
                                    />
                                    <input 
                                        v-model="eqForm.serial_number" 
                                        type="text" 
                                        placeholder="S/N" 
                                        class="w-1/3 bg-slate-50 border-none rounded-xl p-3 text-sm font-bold text-slate-900 transition-all focus:ring-2" 
                                        :class="themeColors.focusRingForm" 
                                    />
                                </div>

                                <!-- Floating Autocomplete Suggestions -->
                                <div 
                                    v-if="showModelDropdown && modelSuggestions.length > 0"
                                    class="absolute left-0 right-0 top-full mt-2 z-50 bg-white rounded-2xl shadow-xl border border-slate-100 p-2 space-y-1 max-h-56 overflow-y-auto"
                                >
                                    <div class="px-3 py-1.5 text-[9px] font-black uppercase tracking-wider text-slate-400 border-b border-slate-50 flex items-center justify-between">
                                        <span>Modelos Oficiales Homologados</span>
                                        <button type="button" @click="showModelDropdown = false" class="text-slate-400 hover:text-slate-600">✕</button>
                                    </div>
                                    <button 
                                        type="button"
                                        v-for="sug in modelSuggestions" 
                                        :key="sug.id"
                                        @click="selectModelSuggestion(sug)"
                                        class="w-full text-left p-2.5 rounded-xl hover:bg-slate-50 transition-colors flex items-center justify-between group cursor-pointer"
                                    >
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-xs font-black text-slate-900 group-hover:text-emerald-600">{{ sug.brand }} {{ sug.model }}</span>
                                                <span v-if="sug.is_inverter" class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[8px] font-black uppercase">Inverter</span>
                                            </div>
                                            <p class="text-[10px] font-bold text-slate-400">
                                                {{ sug.type?.name || sug.category?.name }} · {{ sug.nominal_power_w }}W
                                                <span v-if="sug.energy_label"> · Etiqueta {{ sug.energy_label }}</span>
                                            </p>
                                        </div>
                                        <span class="text-[10px] font-black text-emerald-600 opacity-0 group-hover:opacity-100 transition-opacity uppercase tracking-wider">
                                            Auto-completar →
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div class="space-y-1.5">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest ml-1">Eficiencia Energética</label>
                                <select v-model="eqForm.energy_label" class="w-full bg-slate-50 border-none rounded-xl p-3 text-sm font-bold text-slate-900 transition-all appearance-none focus:ring-2" :class="themeColors.focusRingForm" translate="no">
                                    <option value="">Seleccionar...</option>
                                    <option value="A+++">A+++</option>
                                    <option value="A++">A++</option>
                                    <option value="A+">A+</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                    <option value="E">E</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Tech Panel -->
                    <div class="p-4 md:p-6 bg-slate-900 rounded-[24px] md:rounded-[28px] text-white space-y-4">
                        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
                            <h4 class="text-xs font-black uppercase tracking-widest flex items-center gap-2">
                                <Zap :size="16" />
                                Parámetros de Consumo
                            </h4>
                            <div class="flex flex-wrap items-center gap-4 sm:gap-6">
                                <!-- Los campos de patrón y horas se movieron a la fase de ajuste -->
                                <div class="hidden">
                                    <input type="checkbox" v-model="eqForm.has_defined_pattern" />
                                </div>
                                
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <div class="text-right">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 group-hover:text-energy-success">¿Inverter?</p>
                                        <p class="text-[8px] font-medium text-slate-600">Eficiencia Pro</p>
                                    </div>
                                    <input type="checkbox" v-model="eqForm.is_inverter" class="hidden" />
                                    <div :class="['w-10 h-5 rounded-full relative transition-colors', eqForm.is_inverter ? 'bg-energy-success' : 'bg-slate-700']">
                                        <div :class="['absolute top-1 w-3 h-3 bg-white rounded-full transition-all', eqForm.is_inverter ? 'left-6' : 'left-1']"></div>
                                    </div>
                                </label>

                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <div class="text-right">
                                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 group-hover:text-amber-400">¿Vampiro?</p>
                                        <p class="text-[8px] font-medium text-slate-600">Standby activo</p>
                                    </div>
                                    <input type="checkbox" v-model="eqForm.is_standby" class="hidden" />
                                    <div :class="['w-10 h-5 rounded-full relative transition-colors', eqForm.is_standby ? 'bg-amber-400' : 'bg-slate-700']">
                                        <div :class="['absolute top-1 w-3 h-3 bg-white rounded-full transition-all', eqForm.is_standby ? 'left-6' : 'left-1']"></div>
                                    </div>
                                </label>
                            </div>
                        </div>

                            <div class="space-y-2 col-span-full">
                                <label class="text-[8px] font-black text-slate-500 uppercase tracking-widest text-center block">Potencia Nominal (W)</label>
                                <div class="relative">
                                    <input v-model="eqForm.nominal_power_w" type="number" class="w-full bg-slate-800 border-none rounded-xl p-4 text-center text-xl font-black text-white focus:ring-1" :class="themeColors.focusRingInput" />
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-600">W</span>
                                </div>
                            </div>
                    </div>
                </form>

                <div class="px-6 md:px-8 py-4 bg-slate-50 flex flex-col sm:flex-row gap-3">
                    <button @click="submitEq" :disabled="eqForm.processing" class="w-full sm:flex-1 bg-slate-900 text-white py-3.5 md:py-4 rounded-[16px] md:rounded-[20px] font-black text-xs uppercase tracking-widest shadow-xl shadow-slate-200 transition-all cursor-pointer" :class="themeColors.hoverBg">
                        {{ editingEquipment ? 'Guardar Cambios' : 'Confirmar Registro' }}
                    </button>
                    <button @click="showEquipmentModal = false" class="w-full sm:w-auto px-6 py-3.5 md:py-4 text-slate-400 font-black text-xs uppercase tracking-widest order-last sm:order-none cursor-pointer">
                        Cancelar
                    </button>
                </div>
        </Modal>
        <!-- Delete Confirmation Modals -->
        <ConfirmDeleteModal
            :show="!!roomToDelete"
            title="¿Eliminar ambiente?"
            :message="`¿Estás seguro de eliminar el ambiente '${roomToDelete?.name}'? Se perderán todos sus equipos asociados.`"
            :processing="isDeletingRoom"
            @close="roomToDelete = null"
            @confirm="confirmDeleteRoom"
        />

        <ConfirmDeleteModal
            :show="!!equipmentToDelete"
            title="¿Eliminar equipo?"
            :message="`¿Eliminar '${equipmentToDelete?.name}' del inventario?`"
            :processing="isDeletingEquipment"
            @close="equipmentToDelete = null"
            @confirm="confirmDeleteEquipment"
        />
    </MainLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f8fafc;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
