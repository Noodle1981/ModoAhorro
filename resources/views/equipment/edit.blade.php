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
                                    data-power="{{ $type->default_power_watts }}"
                                    {{ $equipment->type_id == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Power --}}
                    <div class="space-y-1.5">
                        <div class="flex justify-between items-end mb-1">
                            <label for="nominal_power_w" class="block text-sm font-medium text-gray-700">
                                Potencia Nominal (W) <span class="text-red-500">*</span>
                            </label>
                            
                            {{-- Validation Toggles --}}
                            <div class="flex gap-3 text-xs">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="radio" name="power_mode" value="suggested" class="form-radio text-purple-600 focus:ring-purple-500 h-3 w-3" {{ !$equipment->is_validated ? 'checked' : '' }}>
                                    <span class="ml-1.5 text-gray-600">Sugerido</span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="radio" name="power_mode" value="real" class="form-radio text-emerald-600 focus:ring-emerald-500 h-3 w-3" {{ $equipment->is_validated ? 'checked' : '' }}>
                                    <span class="ml-1.5 text-gray-600">Real</span>
                                </label>
                            </div>
                        </div>
                        
                        <input type="number" name="nominal_power_w" id="nominal_power_w" required
                            value="{{ old('nominal_power_w', $equipment->nominal_power_w) }}"
                            placeholder="Ej: 1500"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors {{ !$equipment->is_validated ? 'bg-gray-50 text-gray-500' : '' }}">
                        <p class="text-xs text-gray-500" id="power-help-text">Valor promedio para este tipo de equipo.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                   {{-- Quantity removed implies we don't edit quantity here, usually one by one or it's a field I missed? 
                      Edit view doesn't have quantity in original file provided? 
                      Looking at file content... it does NOT have quantity. It edits a single equipment instance.
                      It HAS usage fields logic at lines 88-115. I will replace that block with empty. --}}
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
                
                {{-- Hidden Fields --}}
                <input type="hidden" name="is_validated" id="is_validated" value="{{ $equipment->is_validated ? '1' : '0' }}">

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
    // Initialize logic
    function initEditForm() {
        // Filtrar categorias (existing logic)
        filtrarEquiposPorCategoria();
        
        const powerInput = document.getElementById('nominal_power_w');
        const isValidatedInput = document.getElementById('is_validated');
        const radioSuggested = document.querySelector('input[name="power_mode"][value="suggested"]');
        const radioReal = document.querySelector('input[name="power_mode"][value="real"]');
        const powerHelpText = document.getElementById('power-help-text');
        const typeSelect = document.getElementById('type_id');

        let currentTypeDefaultPower = null;
        
        // Get default from selected type
        function updateDefaultPower() {
            const selectedOption = typeSelect.options[typeSelect.selectedIndex];
            if (selectedOption && selectedOption.value) {
                currentTypeDefaultPower = selectedOption.getAttribute('data-power');
            }
        }
        
        typeSelect.addEventListener('change', updateDefaultPower);
        updateDefaultPower(); // Init

        // Helper to set mode
        function setPowerMode(mode) {
            if (mode === 'suggested') {
                radioSuggested.checked = true;
                isValidatedInput.value = '0';
                powerInput.classList.add('bg-gray-50', 'text-gray-500');
                powerHelpText.textContent = "Valor promedio para este tipo de equipo.";
                
                // Restore default if available
                if (currentTypeDefaultPower) {
                    powerInput.value = currentTypeDefaultPower;
                }
            } else {
                radioReal.checked = true;
                isValidatedInput.value = '1';
                powerInput.classList.remove('bg-gray-50', 'text-gray-500');
                powerHelpText.textContent = "Ingresa el valor exacto de la etiqueta.";
            }
        }

        // Listeners
        radioSuggested.addEventListener('change', () => setPowerMode('suggested'));
        radioReal.addEventListener('change', () => setPowerMode('real'));

        // Auto-switch to Real
        powerInput.addEventListener('input', function() {
            if (radioSuggested.checked && this.value != currentTypeDefaultPower) {
                setPowerMode('real');
            }
        });
    }

    function filtrarEquiposPorCategoria() {
        var categoriaId = document.getElementById('category_id').value;
        var equipoSelect = document.getElementById('type_id');
        for (var i = 0; i < equipoSelect.options.length; i++) {
            var option = equipoSelect.options[i];
            if (!option.value) continue;
            option.style.display = !categoriaId || option.getAttribute('data-category') === categoriaId ? '' : 'none';
        }
        // Don't reset value on edit unless invalid? No, keep it.
    }
    
    document.addEventListener('DOMContentLoaded', initEditForm);
</script>
@endsection
