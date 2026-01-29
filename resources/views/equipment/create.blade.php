@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-3xl mx-auto px-4">
        
        <div class="flex items-center gap-4 mb-8">
            <div class="bg-purple-600 w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                <i class="bi bi-lightning-charge-fill text-xl"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Nuevo Equipo</h1>
                <p class="text-gray-500 text-sm">Configura la potencia y el uso</p>
            </div>
        </div>

        <x-card>
            <form method="POST" action="{{ route('equipment.store') }}" id="equipmentForm">
                @csrf
                <input type="hidden" name="room_id" value="{{ $roomId }}">

                {{-- 1. SELECCIÓN DE CATEGORÍA --}}
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">1. Elige la Categoría</label>
                    <select name="category_id" id="category_id" required
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Seleccione una categoría...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. EL "CARTEL" DE SUGERENCIAS (Dinámico) --}}
                <div id="suggestions-container" class="hidden mb-6 p-4 bg-purple-50 border border-purple-100 rounded-xl">
                    <p class="text-sm font-semibold text-purple-800 mb-3">
                        <i class="bi bi-lightbulb-fill"></i> Valores sugeridos para esta categoría:
                    </p>
                    <div id="suggestions-list" class="flex flex-wrap gap-2">
                        {{-- Aquí se inyectan los botoncitos por JS --}}
                    </div>
                    <p class="text-xs text-purple-600 mt-3">Haz clic en una sugerencia para auto-completar nombre y potencia.</p>
                </div>

                {{-- 3. DATOS DEL EQUIPO --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Equipo</label>
                        <input type="text" name="name" id="name" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-purple-500"
                            placeholder="Ej: Aire Comedor">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Potencia Nominal (W)</label>
                        <input type="number" name="nominal_power_w" id="nominal_power_w" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-purple-500"
                            placeholder="0">
                    </div>
                </div>

                {{-- 4. USO Y FRECUENCIA --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Horas de Uso Diario</label>
                        <input type="number" step="0.1" name="avg_daily_use_hours" id="avg_daily_use_hours" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-purple-500"
                            placeholder="Ej: 4.5">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Frecuencia de Uso</label>
                        <select name="periodicidad" id="periodicidad" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:ring-purple-500">
                            <option value="diariamente">Diariamente</option>
                            <option value="casi_frecuentemente">Casi frecuentemente</option>
                            <option value="frecuentemente">Frecuentemente</option>
                            <option value="ocasionalmente">Ocasionalmente</option>
                            <option value="raramente">Raramente</option>
                            <option value="nunca">Nunca</option>
                        </select>
                    </div>
                </div>

                {{-- Campo oculto para el ID del tipo (necesario para el motor) --}}
                <input type="hidden" name="type_id" id="type_id" value="">

                <div class="flex justify-end gap-3 pt-6 border-t">
                    <x-button variant="secondary" href="{{ route('equipment.index') }}">Cancelar</x-button>
                    <x-button variant="primary" type="submit" class="bg-purple-600 hover:bg-purple-700">
                        Guardar Equipo
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</div>

<script>
    // Function to initialize the form logic
    function initEquipmentForm() {
        // Datos de los tipos (pasados desde PHP a JS)
        const equipmentTypes = @json($types);
        
        const categorySelect = document.getElementById('category_id');
        const suggestionsContainer = document.getElementById('suggestions-container');
        const suggestionsList = document.getElementById('suggestions-list');
        
        const nameInput = document.getElementById('name');
        const powerInput = document.getElementById('nominal_power_w');
        const hoursInput = document.getElementById('avg_daily_use_hours');
        const typeIdInput = document.getElementById('type_id');

        if (!categorySelect) return; // Guard clause

        // Remove existing listeners to prevent duplication if re-initialized
        const newCategorySelect = categorySelect.cloneNode(true);
        categorySelect.parentNode.replaceChild(newCategorySelect, categorySelect);
        
        newCategorySelect.addEventListener('change', function() {
            const catId = this.value;
            suggestionsList.innerHTML = '';
            
            if (!catId) {
                suggestionsContainer.classList.add('hidden');
                return;
            }

            // Filtrar tipos por la categoría seleccionada
            const filtered = equipmentTypes.filter(t => t.category_id == catId);

            if (filtered.length > 0) {
                suggestionsContainer.classList.remove('hidden');
                filtered.forEach(type => {
                    // Crear el "botoncito" sugerencia
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = "px-3 py-1 bg-white border border-purple-200 rounded-full text-xs font-medium text-purple-700 hover:bg-purple-100 transition shadow-sm";
                    btn.innerHTML = `${type.name} <span class="text-gray-400 ml-1">(${type.default_power_watts}W)</span>`;
                    
                    // Al hacer clic, autocompletar todo
                    btn.onclick = () => {
                        nameInput.value = type.name;
                        powerInput.value = type.default_power_watts;
                        hoursInput.value = type.default_avg_daily_use_hours;
                        typeIdInput.value = type.id;
                        
                        // Efecto visual de resaltado
                        [nameInput, powerInput, hoursInput].forEach(el => {
                            el.classList.add('bg-yellow-50');
                            setTimeout(() => el.classList.remove('bg-yellow-50'), 1000);
                        });
                    };
                    
                    suggestionsList.appendChild(btn);
                });
            } else {
                suggestionsContainer.classList.add('hidden');
            }
        });
        
        // Trigger change if value exists (edit mode or back button)
        if (newCategorySelect.value) {
            newCategorySelect.dispatchEvent(new Event('change'));
        }
    }

    // Run immediately
    initEquipmentForm();

    // Re-run on Livewire navigation (if applicable)
    document.addEventListener('livewire:navigated', initEquipmentForm);
</script>
@endsection