<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { 
    LayoutGrid, 
    Plus, 
    Monitor, 
    Lamp, 
    Wind, 
    Zap, 
    Trash2, 
    Pencil, 
    X,
    CheckCircle2,
    Search,
    ChevronLeft,
    Building2,
    AirVent,
    Refrigerator,
    Tv,
    Lightbulb,
    Microwave,
    Waves
} from 'lucide-vue-next';

const props = defineProps({
    entity: Object,
    rooms: Array,
    categories: Array,
    types: Array,
    flash: Object
});

const selectedRoomId = ref(props.rooms.length > 0 ? props.rooms[0].id : null);
const selectedRoom = computed(() => props.rooms.find(r => r.id === selectedRoomId.value));
const equipmentList = ref([]);

// Fetch equipment for selected room
watch(selectedRoomId, async (newId) => {
    if (!newId) return;
    // In a real Inertia app, we might use manual data fetching or reload
    // For this implementation, we'll assume rooms.equipment is loaded or we handle it via router.reload
    // Let's use router.reload with partial data if possible, or just expect it in the props
}, { immediate: true });

// Modals State
const showRoomModal = ref(false);
const editingRoom = ref(null);
const showEquipmentModal = ref(false);
const editingEquipment = ref(null);

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
    name: '',
    nominal_power_w: '',
    avg_daily_use_hours: '',
    is_standby: false,
    cantidad: 1,
    is_active: true
});

// Category -> Type filtering
const filteredTypes = computed(() => {
    if (!eqForm.category_id) return [];
    return props.types.filter(t => t.category_id === eqForm.category_id);
});

// Room Actions
const openRoomCreate = () => {
    editingRoom.value = null;
    roomForm.reset();
    roomForm.entity_id = props.entity.id;
    showRoomModal.value = true;
};

const openRoomEdit = (room) => {
    editingRoom.value = room;
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
            }
        });
    }
};

const deleteRoom = (room) => {
    if (confirm(`¿Estás seguro de eliminar el ambiente "${room.name}"? Se perderán todos sus equipos asociados.`)) {
        router.delete(route('gestion.rooms.destroy', room.id));
    }
};

// Equipment Actions
const openEqCreate = () => {
    editingEquipment.value = null;
    eqForm.reset();
    eqForm.room_id = selectedRoomId.value;
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
    eqForm.is_standby = !!eq.is_standby;
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

const deleteEq = (eq) => {
    if (confirm(`¿Eliminar ${eq.name} del inventario?`)) {
        router.delete(route('gestion.equipment.destroy', eq.id));
    }
};

const getCategoryIcon = (catName) => {
    const name = catName.toLowerCase();
    if (name.includes('climatización')) return AirVent;
    if (name.includes('refrigeración')) return Refrigerator;
    if (name.includes('iluminación')) return Lightbulb;
    if (name.includes('cocina')) return Microwave;
    if (name.includes('lavado')) return Waves;
    if (name.includes('entretenimiento')) return Tv;
    return Zap;
};
</script>

<template>
    <MainLayout>
        <Head title="Infraestructura y Equipos" />

        <div class="max-w-7xl mx-auto space-y-10">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-energy-solar/10 text-energy-solar rounded-full text-[10px] font-black uppercase tracking-widest border border-energy-solar/20">
                        <Building2 :size="14" />
                        Mapa de Activos
                    </div>
                    <h1 class="text-5xl font-black text-slate-900 tracking-tighter leading-none">
                        Infraestructura <span class="text-energy-solar">y Equipos</span>
                    </h1>
                    <p class="text-lg text-slate-500 font-medium">Gestione ambientes y el inventario eléctrico de {{ entity.name }}.</p>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-8 items-start">
                
                <!-- Sidebar: Rooms -->
                <aside class="w-full lg:w-80 shrink-0 space-y-4">
                    <div class="bg-white rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden">
                        <div class="p-6 border-b border-slate-50 flex items-center justify-between">
                            <h3 class="text-[10px] font-black text-slate-300 uppercase tracking-widest">Ambientes</h3>
                            <button @click="openRoomCreate" class="w-8 h-8 rounded-lg bg-energy-solar/10 text-energy-solar flex items-center justify-center hover:bg-energy-solar hover:text-white transition-all">
                                <Plus :size="16" />
                            </button>
                        </div>
                        <div class="p-2 space-y-1 max-h-[500px] overflow-y-auto">
                            <button 
                                v-for="room in rooms" 
                                :key="room.id"
                                @click="selectedRoomId = room.id"
                                :class="[
                                    'w-full flex items-center justify-between p-4 rounded-2xl transition-all group',
                                    selectedRoomId === room.id ? 'bg-slate-900 text-white shadow-lg' : 'hover:bg-slate-50 text-slate-600'
                                ]"
                            >
                                <div class="flex items-center gap-3">
                                    <div :class="['w-2 h-2 rounded-full', selectedRoomId === room.id ? 'bg-energy-solar' : 'bg-slate-200']"></div>
                                    <span class="text-sm font-bold">{{ room.name }}</span>
                                </div>
                                <span :class="['text-[10px] font-black px-2 py-0.5 rounded-md', selectedRoomId === room.id ? 'bg-slate-800 text-slate-400' : 'bg-slate-100 text-slate-400']">
                                    {{ room.equipment_count }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Room Info Card -->
                    <div v-if="selectedRoom" class="bg-indigo-900 rounded-[32px] p-8 text-white space-y-4 relative overflow-hidden">
                        <div class="absolute -right-4 -bottom-4 opacity-10">
                            <Building2 :size="120" />
                        </div>
                        <div class="relative z-10 space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-lg font-black tracking-tight">{{ selectedRoom.name }}</h4>
                                <button @click="openRoomEdit(selectedRoom)" class="text-white/40 hover:text-white transition-colors">
                                    <Pencil :size="14" />
                                </button>
                            </div>
                            <p class="text-xs text-indigo-200/70 font-medium leading-relaxed">{{ selectedRoom.description || 'Sin descripción adicional.' }}</p>
                            <button @click="deleteRoom(selectedRoom)" class="text-[9px] font-black uppercase tracking-widest text-rose-400/60 hover:text-rose-400 transition-colors">
                                Eliminar Ambiente
                            </button>
                        </div>
                    </div>
                </aside>

                <!-- Main Grid: Equipment -->
                <main class="flex-1 space-y-6">
                    <div v-if="selectedRoom" class="space-y-8">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl bg-white border border-slate-100 shadow-md flex items-center justify-center text-energy-solar">
                                    <LayoutGrid :size="24" />
                                </div>
                                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Equipos en {{ selectedRoom.name }}</h2>
                            </div>
                            <button @click="openEqCreate" class="bg-slate-900 text-white px-6 py-3 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-energy-solar transition-all shadow-xl shadow-slate-200">
                                <Plus :size="14" class="inline mr-2" stroke-width="3" /> Añadir Equipo
                            </button>
                        </div>

                        <!-- Equipment Grid -->
                        <div v-if="selectedRoom.equipment && selectedRoom.equipment.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                            <div 
                                v-for="eq in selectedRoom.equipment" 
                                :key="eq.id"
                                class="bg-white rounded-[32px] border border-slate-100 shadow-xl shadow-slate-200/30 p-8 space-y-6 group hover:shadow-2xl transition-all"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="w-14 h-14 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 group-hover:text-energy-solar transition-colors border border-slate-100">
                                            <component :is="getCategoryIcon(eq.category?.name || '')" :size="28" />
                                        </div>
                                        <div>
                                            <h5 class="font-black text-slate-900 leading-tight">{{ eq.name }}</h5>
                                            <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">{{ eq.type?.name }}</p>
                                        </div>
                                    </div>
                                    <div v-if="eq.is_standby" class="p-1.5 bg-amber-50 text-amber-500 rounded-lg" title="Consumo Vampiro (Standby)">
                                        <Zap :size="14" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-slate-50/50 p-3 rounded-xl">
                                        <p class="text-[8px] font-black text-slate-300 uppercase mb-1">Potencia</p>
                                        <p class="text-sm font-black text-slate-700">{{ eq.nominal_power_w }}<span class="text-[9px] ml-0.5">W</span></p>
                                    </div>
                                    <div class="bg-slate-50/50 p-3 rounded-xl">
                                        <p class="text-[8px] font-black text-slate-300 uppercase mb-1">Uso Diario</p>
                                        <p class="text-sm font-black text-slate-700">{{ eq.avg_daily_use_hours }}<span class="text-[9px] ml-0.5">HS</span></p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 pt-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="openEqEdit(eq)" class="flex-1 bg-slate-50 text-slate-400 hover:text-energy-solar py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                                        Editar
                                    </button>
                                    <button @click="deleteEq(eq)" class="w-10 bg-slate-50 text-slate-300 hover:text-rose-500 py-2 rounded-xl transition-all">
                                        <Trash2 :size="14" class="mx-auto" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Empty Equipment -->
                        <div v-else class="bg-slate-50/50 rounded-[40px] p-20 text-center border-2 border-dashed border-slate-100">
                            <div class="max-w-xs mx-auto space-y-4">
                                <Zap :size="40" class="text-slate-100 mx-auto" />
                                <h4 class="text-xl font-black text-slate-300">Habitación vacía</h4>
                                <p class="text-sm text-slate-400 font-medium">Añada los equipos eléctricos de este ambiente para ver su impacto energético.</p>
                                <button @click="openEqCreate" class="text-xs font-black text-energy-solar uppercase tracking-widest border-b-2 border-energy-solar pb-1">
                                    Añadir Primer Equipo
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="flex flex-col items-center justify-center py-40 text-center space-y-6">
                        <div class="w-24 h-24 bg-white rounded-[32px] shadow-2xl flex items-center justify-center text-slate-100 rotate-12">
                            <Monitor :size="48" />
                        </div>
                        <div class="space-y-2">
                            <h3 class="text-2xl font-black text-slate-900 tracking-tight">Selecciona un ambiente</h3>
                            <p class="text-slate-400 font-medium">Elige una habitación de la izquierda para gestionar su inventario.</p>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <!-- Room Modal -->
        <div v-if="showRoomModal" class="fixed inset-0 z-50 flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" @click="showRoomModal = false"></div>
            <div class="relative bg-white w-full max-w-md rounded-[48px] shadow-2xl p-12 space-y-8 animate-in zoom-in duration-300">
                <div class="space-y-2">
                    <h2 class="text-3xl font-black text-slate-900 tracking-tighter">{{ editingRoom ? 'Configurar Ambiente' : 'Nuevo Ambiente' }}</h2>
                    <p class="text-sm text-slate-400 font-medium">Cree un espacio funcional para organizar sus equipos.</p>
                </div>
                <form @submit.prevent="submitRoom" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nombre del Espacio</label>
                        <input v-model="roomForm.name" type="text" placeholder="Ej: Living, Cocina, Oficina..." class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-black text-slate-900 focus:ring-2 focus:ring-energy-solar/20 transition-all" />
                        <p v-if="roomForm.errors.name" class="text-[9px] text-rose-500 font-bold">{{ roomForm.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Descripción Breve</label>
                        <textarea v-model="roomForm.description" rows="3" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-medium text-slate-700 focus:ring-2 focus:ring-energy-solar/20 transition-all"></textarea>
                    </div>
                    <div class="flex gap-4 pt-4">
                        <button type="submit" class="flex-1 bg-slate-900 text-white py-5 rounded-[24px] font-black text-xs uppercase tracking-widest hover:bg-energy-solar transition-all shadow-xl shadow-slate-200">
                            Confirmar
                        </button>
                        <button type="button" @click="showRoomModal = false" class="px-8 py-5 text-slate-400 font-black text-xs uppercase tracking-widest">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Equipment Modal -->
        <div v-if="showEquipmentModal" class="fixed inset-0 z-50 flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" @click="showEquipmentModal = false"></div>
            <div class="relative bg-white w-full max-w-2xl rounded-[48px] shadow-2xl overflow-hidden animate-in zoom-in duration-300">
                <div class="px-12 pt-12 pb-8 border-b border-slate-50">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-slate-900 text-white rounded-full text-[9px] font-black uppercase tracking-widest mb-4">
                        {{ selectedRoom?.name }}
                    </div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tighter">{{ editingEquipment ? 'Especificaciones Técnicas' : 'Nuevo Activo Eléctrico' }}</h2>
                </div>

                <form @submit.prevent="submitEq" class="px-12 py-10 space-y-8 max-h-[60vh] overflow-y-auto custom-scrollbar">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Category & Type -->
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Categoría</label>
                                <select v-model="eqForm.category_id" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-energy-solar/20 transition-all appearance-none">
                                    <option value="">Seleccionar...</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Tipo de Equipo</label>
                                <select v-model="eqForm.type_id" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-energy-solar/20 transition-all appearance-none disabled:opacity-50" :disabled="!eqForm.category_id">
                                    <option value="">Seleccionar tipo...</option>
                                    <option v-for="type in filteredTypes" :key="type.id" :value="type.id">{{ type.name }}</option>
                                </select>
                            </div>
                        </div>

                        <!-- Name & Quantity -->
                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Nombre / Alias</label>
                                <input v-model="eqForm.name" type="text" placeholder="Ej: Aire Living, Heladera Cocina..." class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-black text-slate-900 focus:ring-2 focus:ring-energy-solar/20 transition-all" />
                            </div>
                            <div v-if="!editingEquipment" class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Cantidad de equipos idénticos</label>
                                <input v-model="eqForm.cantidad" type="number" class="w-full bg-slate-50 border-none rounded-2xl p-4 text-sm font-black text-slate-900 focus:ring-2 focus:ring-energy-solar/20 transition-all" />
                            </div>
                        </div>
                    </div>

                    <!-- Tech Panel -->
                    <div class="p-8 bg-slate-900 rounded-[32px] text-white space-y-8">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black uppercase tracking-widest flex items-center gap-2">
                                <Zap :size="16" />
                                Parámetros de Consumo
                            </h4>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <div class="text-right">
                                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400">¿Vampiro?</p>
                                    <p class="text-[8px] font-medium text-slate-600">Consume apagado</p>
                                </div>
                                <input type="checkbox" v-model="eqForm.is_standby" class="hidden" />
                                <div :class="['w-10 h-5 rounded-full relative transition-colors', eqForm.is_standby ? 'bg-amber-400' : 'bg-slate-700']">
                                    <div :class="['absolute top-1 w-3 h-3 bg-white rounded-full transition-all', eqForm.is_standby ? 'left-6' : 'left-1']"></div>
                                </div>
                            </label>
                        </div>

                        <div class="grid grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[8px] font-black text-slate-500 uppercase tracking-widest text-center block">Potencia Nominal (W)</label>
                                <div class="relative">
                                    <input v-model="eqForm.nominal_power_w" type="number" class="w-full bg-slate-800 border-none rounded-xl p-4 text-center text-xl font-black text-white focus:ring-1 focus:ring-energy-solar" />
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-600">W</span>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[8px] font-black text-slate-500 uppercase tracking-widest text-center block">Uso Diario (Hs)</label>
                                <div class="relative">
                                    <input v-model="eqForm.avg_daily_use_hours" type="number" step="0.5" class="w-full bg-slate-800 border-none rounded-xl p-4 text-center text-xl font-black text-white focus:ring-1 focus:ring-energy-solar" />
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-600">HS</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="px-12 py-8 bg-slate-50 flex gap-4">
                    <button @click="submitEq" :disabled="eqForm.processing" class="flex-1 bg-slate-900 text-white py-5 rounded-[24px] font-black text-xs uppercase tracking-widest shadow-xl shadow-slate-200 hover:bg-energy-solar transition-all">
                        {{ editingEquipment ? 'Guardar Cambios' : 'Confirmar Registro' }}
                    </button>
                    <button @click="showEquipmentModal = false" class="px-8 py-5 text-slate-400 font-black text-xs uppercase tracking-widest">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
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
</style>
