@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-plus-lg text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Agregar Equipo</h1>
                    <p class="text-gray-500 text-sm">Registra un nuevo equipo eléctrico</p>
                </div>
            </div>
            <x-button variant="secondary" href="{{ route('equipment.index') }}">
                <i class="bi bi-arrow-left mr-2"></i> Volver
            </x-button>
        </div>

        {{-- Form --}}
        <x-card>
            <form method="POST" action="{{ route('equipment.store') }}">
                @csrf

                {{-- Room Selection --}}
                @if(isset($roomId))
                    <x-alert type="info" class="mb-6">
                        Agregando equipo a: <strong>{{ $rooms->find($roomId)->name ?? 'Sala' }}</strong>
                    </x-alert>
                    <input type="hidden" name="room_id" value="{{ $roomId }}">
                @else
                    <div class="space-y-1.5 mb-6">
                        <label for="room_id" class="block text-sm font-medium text-gray-700">
                            Habitación <span class="text-red-500">*</span>
                        </label>
                        <select name="room_id" id="room_id" required
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Seleccione una habitación...</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->name }} ({{ $room->entity->name ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    {{-- Name --}}
                    <x-input 
                        name="name" 
                        label="Nombre del Equipo" 
                        placeholder="Ej: Aire Acondicionado Split"
                        :value="old('name')"
                        required 
                    />
                    
                    {{-- Category --}}
                    <div class="space-y-1.5">
                        <label for="category_id" class="block text-sm font-medium text-gray-700">
                            Categoría <span class="text-red-500">*</span>
                        </label>
                        <select name="category_id" id="category_id" required
                            onchange="filtrarEquiposPorCategoria()"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Seleccione una categoría...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    {{-- Type --}}
                    <div class="space-y-1.5">
                        <label for="type_id" class="block text-sm font-medium text-gray-700">
                            Tipo de Equipo <span class="text-red-500">*</span>
                        </label>
                        <select name="type_id" id="type_id" required
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Seleccione un equipo...</option>
                            @foreach($types as $type)
                                <option value="{{ $type->id }}" data-category="{{ $type->category_id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Power --}}
                    <x-input 
                        name="nominal_power_w" 
                        label="Potencia Nominal (W)" 
                        type="number"
                        placeholder="Ej: 1500"
                        :value="old('nominal_power_w')"
                        helper="Verificá el consumo en la etiqueta del equipo"
                    />
                </div>

                {{-- Submit --}}
                <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                    <x-button variant="secondary" href="{{ route('equipment.index') }}">
                        Cancelar
                    </x-button>
                    <x-button variant="primary" type="submit">
                        <i class="bi bi-check-lg mr-2"></i> Guardar Equipo
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</div>

<script>
function filtrarEquiposPorCategoria() {
    var categoriaId = document.getElementById('category_id').value;
    var equipoSelect = document.getElementById('type_id');
    for (var i = 0; i < equipoSelect.options.length; i++) {
        var option = equipoSelect.options[i];
        if (!option.value) continue;
        option.style.display = !categoriaId || option.getAttribute('data-category') === categoriaId ? '' : 'none';
    }
    equipoSelect.value = '';
}
</script>
@endsection
