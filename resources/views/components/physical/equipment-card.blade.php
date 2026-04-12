@props(['equipment', 'selected' => false])

<div {{ $attributes->merge(['class' => 'bg-white rounded-3xl border border-gray-100 p-6 shadow-xs hover:shadow-md transition-all group relative overflow-hidden']) }}>
    {{-- Decorative Background --}}
    <div class="absolute -right-12 -top-12 w-24 h-24 bg-gray-50 rotate-45 group-hover:bg-blue-50 transition-colors"></div>
    <i class="bi bi-lightning-charge absolute top-3 right-3 text-gray-200 group-hover:text-amber-400 transition-colors"></i>

    {{-- Equipment Header --}}
    <div class="flex items-start gap-4 mb-6 relative z-10">
        <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 border border-blue-100 shrink-0 group-hover:scale-110 transition-transform">
            <i class="bi {{ $equipment->type?->is_climatization ? 'bi-thermometer-half' : 'bi-app-indicator' }} text-xl"></i>
        </div>
        <div class="min-w-0">
            <h4 class="font-black text-gray-900 leading-tight truncate uppercase tracking-tighter">{{ $equipment->name }}</h4>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">{{ $equipment->type?->name ?? 'Genérico' }}</p>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 gap-4 mb-6 relative z-10">
        <div class="bg-gray-50/50 p-3 rounded-2xl">
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Potencia</p>
            <p class="font-black text-gray-700">{{ number_format($equipment->nominal_power_w) }} <span class="text-[10px] font-normal text-gray-400 uppercase">W</span></p>
        </div>
        <div class="bg-gray-50/50 p-3 rounded-2xl">
            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Uso Diario</p>
            <p class="font-black text-gray-700">{{ $equipment->avg_daily_use_hours }} <span class="text-[10px] font-normal text-gray-400 uppercase">Hs</span></p>
        </div>
    </div>

    {{-- Footer Actions --}}
    <div class="flex items-center justify-between relative z-10">
        <div class="flex gap-2">
            @if($equipment->is_standby)
                <span class="w-2 h-2 rounded-full bg-amber-400 shadow-xs shadow-amber-200" title="Standby Activo"></span>
            @endif
            @if($equipment->type?->is_shiftable)
                <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-xs shadow-emerald-200" title="Desplazable"></span>
            @endif
        </div>
        <div class="flex gap-1">
            <button wire:click="openEquipmentModal({{ $equipment->id }})" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all">
                <i class="bi bi-pencil-square"></i>
            </button>
            <button onclick="confirm('¿Dar de baja este equipo?') || event.stopImmediatePropagation()" wire:click="deleteEquipment({{ $equipment->id }})" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition-all">
                <i class="bi bi-trash3"></i>
            </button>
        </div>
    </div>
</div>
