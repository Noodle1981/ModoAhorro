<form method="POST" action="{{ route($config['route_prefix'] . '.rooms.equipment.store', [$room->entity_id, $room->id]) }}">
    @csrf
    
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
            required 
            helper="Verificá el consumo en la etiqueta del equipo"
        />
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Quantity --}}
        <x-input 
            name="cantidad" 
            label="Cantidad" 
            type="number"
            placeholder="1"
            :value="old('cantidad', 1)"
            required 
        />
        
        {{-- Daily Hours --}}
        <x-input 
            name="avg_daily_use_hours" 
            label="Horas de uso diario" 
            type="number"
            step="0.1"
            placeholder="Ej: 6"
            :value="old('avg_daily_use_hours')"
            helper="Horas estimadas que se usa por día"
        />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Frequency --}}
        <div class="space-y-1.5">
            <label for="usage_frequency" class="block text-sm font-medium text-gray-700">
                Frecuencia de Uso
            </label>
            <select name="usage_frequency" id="usage_frequency"
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="diario" {{ old('usage_frequency') == 'diario' ? 'selected' : '' }}>Todos los días (Diario)</option>
                <option value="semanal" {{ old('usage_frequency') == 'semanal' ? 'selected' : '' }}>Algunas veces por semana</option>
                <option value="quincenal" {{ old('usage_frequency') == 'quincenal' ? 'selected' : '' }}>Cada tanto (Quincenal)</option>
                <option value="mensual" {{ old('usage_frequency') == 'mensual' ? 'selected' : '' }}>Raramente (Mensual)</option>
                <option value="puntual" {{ old('usage_frequency') == 'puntual' ? 'selected' : '' }}>Uso muy puntual</option>
            </select>
        </div>
    </div>
    
    {{-- Submit --}}
    <div class="flex justify-end pt-4 border-t border-gray-200">
        <x-button variant="primary" type="submit">
            <i class="bi bi-check-lg mr-2"></i> Guardar Equipo
        </x-button>
    </div>
</form>

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
