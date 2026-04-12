@props(['room', 'selected' => false])

<div class="relative group">
    <button {{ $attributes->merge(['class' => 'w-full flex items-center gap-3 p-4 rounded-2xl transition-all text-left group ' . ($selected ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'bg-gray-50 hover:bg-white border border-transparent hover:border-gray-100 text-gray-700')]) }}>
        <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $selected ? 'bg-white/20' : 'bg-white border border-gray-100' }}">
            @if($room->name == 'Portátiles') <i class="bi bi-laptop"></i>
            @elseif($room->name == 'Temporales') <i class="bi bi-clock-history"></i>
            @else <i class="bi bi-house-door"></i>
            @endif
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-black text-sm truncate uppercase tracking-tighter">{{ $room->name }}</p>
            <p class="text-[10px] opacity-60 font-medium">{{ $room->equipment_count ?? $room->equipment()->where('is_active', true)->count() }} equipos</p>
        </div>
    </button>
    
    {{-- Room Actions (only for non-system) --}}
    @if(!$room->isSystemRoom())
        <div class="absolute right-2 top-1/2 -translate-y-1/2 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button wire:click="openRoomModal({{ $room->id }})" class="p-1.5 {{ $selected ? 'text-white' : 'text-gray-400 hover:text-blue-600' }}">
                <i class="bi bi-pencil-square"></i>
            </button>
            <button onclick="confirm('¿Eliminar habitación y sus equipos?') || event.stopImmediatePropagation()" wire:click="deleteRoom({{ $room->id }})" class="p-1.5 {{ $selected ? 'text-white' : 'text-gray-400 hover:text-red-500' }}">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    @endif
</div>
