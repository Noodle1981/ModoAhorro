<div class="min-h-screen bg-gray-50 pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-linear-to-br from-blue-600 to-indigo-800 w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-diagram-3 text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 line-clamp-1">Infraestructura y Equipos</h1>
                    <p class="text-gray-500 text-sm">Gestiona los ambientes y el inventario eléctrico de {{ $entity->name }}</p>
                </div>
            </div>
            <div class="flex gap-3 mt-4 md:mt-0">
                <x-button variant="secondary" href="{{ route($route_prefix . '.show', $entity->id) }}">
                    <i class="bi bi-arrow-left mr-2"></i> Volver
                </x-button>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            
            {{-- Left Sidebar: Rooms --}}
            <div class="w-full lg:w-80 shrink-0">
                <div class="bg-white rounded-3xl shadow-xs border border-gray-100 overflow-hidden flex flex-col h-full sticky top-8">
                    <div class="p-6 border-b border-gray-50 flex items-center justify-between">
                        <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Ambientes</h3>
                        <button wire:click="openRoomModal" class="text-blue-600 hover:text-blue-700 p-1 transition-colors">
                            <i class="bi bi-plus-circle-fill text-xl"></i>
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto max-h-[60vh] lg:max-h-[calc(100vh-300px)] p-3 space-y-2">
                        @foreach($this->rooms as $room)
                            <x-physical.room-sidebar-item 
                                :room="$room" 
                                :selected="$selectedRoomId == $room->id" 
                                wire:click="selectRoom({{ $room->id }})" 
                            />
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Main Content: Equipment --}}
            <div class="flex-1">
                @if($this->selectedRoom)
                    <div wire:key="room-{{ $selectedRoomId }}" wire:transition>
                        
                        {{-- Room Header & Info --}}
                        <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-xs mb-8">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div>
                                    <h2 class="text-3xl font-black text-gray-900">{{ $this->selectedRoom->name }}</h2>
                                    <p class="text-gray-500 mt-1 max-w-2xl">{{ $this->selectedRoom->getSystemDescription() ?? $this->selectedRoom->description ?? 'Sin descripción.' }}</p>
                                </div>
                                <div class="shrink-0">
                                    <x-button variant="primary" wire:click="openEquipmentModal" class="bg-emerald-600 hover:bg-emerald-700 shadow-lg shadow-emerald-100">
                                        <i class="bi bi-plus-lg mr-2"></i> Añadir Equipo
                                    </x-button>
                                </div>
                            </div>
                        </div>

                        {{-- Equipment Grid --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" wire:loading.class="opacity-50 transition-opacity">
                            @forelse($this->equipment as $eq)
                                <x-physical.equipment-card :equipment="$eq" />
                            @empty
                                <div class="col-span-full py-20 text-center bg-gray-50/50 rounded-3xl border-2 border-dashed border-gray-100">
                                    <div class="max-w-xs mx-auto">
                                        <div class="w-16 h-16 bg-white rounded-2xl shadow-xs border border-gray-50 flex items-center justify-center mx-auto mb-4 text-gray-300">
                                            <i class="bi bi-outlet text-3xl"></i>
                                        </div>
                                        <p class="font-black text-gray-400 uppercase tracking-widest text-xs">Habitación vacía</p>
                                        <p class="text-gray-400 text-xs mt-2">Añade los equipos eléctricos de este ambiente para ver su impacto energético.</p>
                                        <x-button variant="secondary" wire:click="openEquipmentModal" class="mt-6">
                                            <i class="bi bi-plus-lg mr-2"></i> Añadir Primer Equipo
                                        </x-button>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-40 text-center">
                        <i class="bi bi-cursor-fill text-6xl text-gray-100 mb-6 -rotate-12"></i>
                        <h3 class="text-2xl font-black text-gray-900">Selecciona un ambiente</h3>
                        <p class="text-gray-400 max-w-sm mt-2">Elige una habitación para gestionar su inventario de equipos.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Room Modal --}}
    @if($showRoomModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-md" wire:transition>
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden" @click.away="$wire.set('showRoomModal', false)">
                <div class="bg-linear-to-r {{ $isEditingRoom ? 'from-blue-600 to-indigo-700' : 'from-emerald-600 to-teal-700' }} px-8 py-6 text-white">
                    <h3 class="text-xl font-bold flex items-center gap-2">
                        <i class="bi {{ $isEditingRoom ? 'bi-pencil-square' : 'bi-plus-circle' }}"></i>
                        {{ $isEditingRoom ? 'Configurar Ambiente' : 'Nuevo Ambiente' }}
                    </h3>
                </div>
                <form wire:submit.prevent="saveRoom" class="p-8 space-y-6">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nombre</label>
                        <input type="text" wire:model="room_name" class="w-full bg-gray-50 border-gray-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-bold text-gray-700">
                        @error('room_name') <span class="text-[10px] text-red-500 mt-1 block font-bold">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Descripción (Opcional)</label>
                        <textarea wire:model="room_description" rows="3" class="w-full bg-gray-50 border-gray-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"></textarea>
                    </div>
                    <div class="flex flex-col md:flex-row gap-3 pt-4">
                        <x-button type="submit" variant="primary" class="flex-1 justify-center py-4 bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-100">
                            Confirmar
                        </x-button>
                        <x-button type="button" variant="secondary" wire:click="$set('showRoomModal', false)" class="justify-center py-4">
                            Cancelar
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Equipment Modal --}}
    @if($showEquipmentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-md" wire:transition>
            <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full overflow-hidden" @click.away="$wire.set('showEquipmentModal', false)">
                <div class="bg-linear-to-r {{ $isEditingEquipment ? 'from-blue-600 to-indigo-700' : 'from-emerald-600 to-teal-700' }} px-8 py-6 text-white flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold flex items-center gap-2 uppercase tracking-tighter">
                            <i class="bi {{ $isEditingEquipment ? 'bi-pencil-square' : 'bi-plus-circle' }}"></i>
                            {{ $isEditingEquipment ? 'Editar Especificaciones' : 'Nuevo Activo Eléctrico' }}
                        </h3>
                        <p class="text-white/70 text-[10px] font-black uppercase tracking-widest mt-1">Lugar: {{ $this->selectedRoom->name }}</p>
                    </div>
                    <button wire:click="$set('showEquipmentModal', false)" class="text-white/80 hover:text-white">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                
                <form wire:submit.prevent="saveEquipment" class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- Basic Category/Type --}}
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Categoría</label>
                                <select wire:model.live="category_id" class="w-full bg-gray-50 border-gray-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-bold text-gray-700">
                                    <option value="">Selecciona...</option>
                                    @foreach($this->categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <span class="text-[10px] text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Tipo de Equipo</label>
                                <select wire:model.live="type_id" class="w-full bg-gray-50 border-gray-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-bold text-gray-700 disabled:opacity-50" {{ !$category_id ? 'disabled' : '' }}>
                                    <option value="">Selecciona tipo...</option>
                                    @foreach($this->types as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('type_id') <span class="text-[10px] text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Name & Quantity --}}
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Nombre Identificador</label>
                                <input type="text" wire:model="eq_name" class="w-full bg-blue-50/30 border-blue-50 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-black text-gray-800" placeholder="Ej: Aire Acondicionado Living">
                                @error('eq_name') <span class="text-[10px] text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                            </div>
                            @if(!$isEditingEquipment)
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Cantidad de equipos idénticos</label>
                                    <input type="number" wire:model="cantidad" class="w-full bg-gray-50 border-gray-100 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                                </div>
                            @endif
                        </div>

                        {{-- Technical Specs --}}
                        <div class="md:col-span-2 grid grid-cols-2 md:grid-cols-3 gap-6 p-6 bg-gray-50 rounded-3xl border border-gray-100">
                            <div>
                                <label class="block text-[9px] font-black text-gray-400 uppercase mb-1">Potencia Nominal (W)</label>
                                <div class="relative">
                                    <input type="number" wire:model="nominal_power_w" class="w-full bg-white border-gray-100 rounded-xl px-4 py-3 text-lg font-black text-gray-900">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 font-bold text-gray-300 text-xs">W</span>
                                </div>
                                @error('nominal_power_w') <span class="text-[10px] text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[9px] font-black text-gray-400 uppercase mb-1">Uso Diario Promedio (Hs)</label>
                                <div class="relative">
                                    <input type="number" step="0.5" wire:model="avg_daily_use_hours" class="w-full bg-white border-gray-100 rounded-xl px-4 py-3 text-lg font-black text-gray-900">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 font-bold text-gray-300 text-xs">HS</span>
                                </div>
                            </div>
                            <div class="flex items-end pb-3">
                                <label class="flex items-center cursor-pointer group">
                                    <div class="relative">
                                        <input type="checkbox" wire:model="is_standby" class="sr-only">
                                        <div class="w-12 h-6 bg-gray-200 rounded-full shadow-inner transition-colors {{ $is_standby ? 'bg-amber-400' : '' }}"></div>
                                        <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform {{ $is_standby ? 'translate-x-6' : '' }}"></div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-[10px] font-black text-gray-800 uppercase leading-none">Vampiro / Standby</p>
                                        <p class="text-[9px] text-gray-400 mt-1">¿Consume apagado?</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col md:flex-row gap-3">
                        <x-button type="submit" variant="primary" class="flex-1 justify-center py-4 bg-emerald-600 hover:bg-emerald-700 shadow-xl shadow-emerald-100">
                            <i class="bi bi-check-circle mr-2 text-lg"></i> Confirmar y Guardar
                        </x-button>
                        <x-button type="button" variant="secondary" wire:click="$set('showEquipmentModal', false)" class="justify-center py-4 border-gray-100">
                            Regresar
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
