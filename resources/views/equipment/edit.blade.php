@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-amber-500 to-amber-600 w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-pencil text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Editar Equipo</h1>
                    <p class="text-gray-500 text-sm">{{ $equipment->name }}</p>
                </div>
            </div>
            <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.rooms.equipment', [$room->entity_id, $room->id]) }}">
                <i class="bi bi-arrow-left mr-2"></i> Volver
            </x-button>
        </div>

        {{-- Form --}}
        <x-card>
            <form method="POST" action="{{ route($config['route_prefix'] . '.rooms.equipment.update', [$room->entity_id, $room->id, $equipment->id]) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    {{-- Name --}}
                    <x-input 
                        name="name" 
                        label="Nombre del Equipo" 
                        placeholder="Ej: Aire Acondicionado Split"
                        :value="old('name', $equipment->name)"
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
                                <option value="{{ $category->id }}" {{ $equipment->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
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
                                <option value="{{ $type->id }}" 
                                    data-category="{{ $type->category_id }}"
                                    {{ $equipment->type_id == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Power --}}
                    <x-input 
                        name="nominal_power_w" 
                        label="Potencia Nominal (W)" 
                        type="number"
                        placeholder="Ej: 1500"
                        :value="old('nominal_power_w', $equipment->nominal_power_w)"
                        required
                        helper="Verificá el consumo en la etiqueta del equipo"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    {{-- Daily Hours --}}
                    <x-input 
                        name="avg_daily_use_hours" 
                        label="Horas de uso diario" 
                        type="number"
                        step="0.1"
                        placeholder="Ej: 6"
                        :value="old('avg_daily_use_hours', $equipment->avg_daily_use_hours)"
                        helper="Horas estimadas que se usa por día"
                    />
                    
                    {{-- Frequency --}}
                    <div class="space-y-1.5">
                        <label for="usage_frequency" class="block text-sm font-medium text-gray-700">
                            Frecuencia de Uso
                        </label>
                        <select name="usage_frequency" id="usage_frequency"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            @php $currentFreq = old('usage_frequency', $equipment->usage_frequency); @endphp
                            <option value="diario" {{ $currentFreq == 'diario' ? 'selected' : '' }}>Todos los días (Diario)</option>
                            <option value="semanal" {{ $currentFreq == 'semanal' ? 'selected' : '' }}>Algunas veces por semana</option>
                            <option value="quincenal" {{ $currentFreq == 'quincenal' ? 'selected' : '' }}>Cada tanto (Quincenal)</option>
                            <option value="mensual" {{ $currentFreq == 'mensual' ? 'selected' : '' }}>Raramente (Mensual)</option>
                            <option value="puntual" {{ $currentFreq == 'puntual' ? 'selected' : '' }}>Uso muy puntual</option>
                        </select>
                    </div>
                </div>
                {{-- Status --}}
                <div class="mb-6 p-4 bg-gray-50 rounded-xl">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" 
                            {{ $equipment->is_active ? 'checked' : '' }}
                            class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500 w-5 h-5">
                        <div>
                            <span class="font-medium text-gray-900">Equipo Activo</span>
                            <p class="text-sm text-gray-500">Desmarcar si el equipo fue dado de baja o reemplazado</p>
                        </div>
                    </label>
                </div>

                {{-- Submit --}}
                <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                    <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.rooms.equipment', [$room->entity_id, $room->id]) }}">
                        Cancelar
                    </x-button>
                    <x-button variant="warning" type="submit">
                        <i class="bi bi-check-lg mr-2"></i> Guardar Cambios
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
