<form method="POST" action="{{ route($config['route_prefix'] . '.rooms.equipment.store', [$room->entity_id, $room->id]) }}">
    @csrf
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Category --}}
        <div class="space-y-1.5">
            <label for="category_id" class="block text-sm font-medium text-gray-700">
                Categoría <span class="text-red-500">*</span>
            </label>
            <select name="category_id" id="category_id" required
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <option value="">Seleccione una categoría...</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Name --}}
        <div class="space-y-1.5">
            <label for="name" class="block text-sm font-medium text-gray-700">
                Nombre del Equipo <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" id="name" required
                value="{{ old('name') }}"
                placeholder="Ej: Aire Acondicionado Split"
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
        </div>
    </div>
    
    {{-- Suggestions (Dynamic) --}}
    <div id="suggestions-container" class="hidden mb-6 p-4 bg-purple-50 border border-purple-100 rounded-xl">
        <p class="text-sm font-semibold text-purple-800 mb-3">
            <i class="bi bi-lightbulb-fill"></i> Valores sugeridos para esta categoría:
        </p>
        <div id="suggestions-list" class="flex flex-wrap gap-2">
            {{-- Injected via JS --}}
        </div>
        <p class="text-xs text-purple-600 mt-3">Haz clic en una sugerencia para auto-completar nombre y potencia.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Power --}}
        <div class="space-y-1.5">
            <label for="nominal_power_w" class="block text-sm font-medium text-gray-700">
                Potencia Nominal (W) <span class="text-red-500">*</span>
            </label>
            <input type="number" name="nominal_power_w" id="nominal_power_w" required
                value="{{ old('nominal_power_w') }}"
                placeholder="Ej: 1500"
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <p class="text-xs text-gray-500">Verificá el consumo en la etiqueta del equipo</p>
        </div>

        {{-- Quantity --}}
        <div class="space-y-1.5">
            <label for="cantidad" class="block text-sm font-medium text-gray-700">
                Cantidad <span class="text-red-500">*</span>
            </label>
            <input type="number" name="cantidad" id="cantidad" required
                value="{{ old('cantidad', 1) }}"
                placeholder="1"
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Daily Hours --}}
        <div class="space-y-1.5">
            <label for="avg_daily_use_hours" class="block text-sm font-medium text-gray-700">
                Horas de uso diario
            </label>
            <input type="number" name="avg_daily_use_hours" id="avg_daily_use_hours"
                step="0.1"
                value="{{ old('avg_daily_use_hours') }}"
                placeholder="Ej: 6"
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <p class="text-xs text-gray-500">Horas estimadas que se usa por día</p>
        </div>
        
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
    
    {{-- Hidden Type ID --}}
    <input type="hidden" name="type_id" id="type_id" value="">

    {{-- Submit --}}
    <div class="flex justify-end pt-4 border-t border-gray-200">
        <x-button variant="primary" type="submit">
            <i class="bi bi-check-lg mr-2"></i> Guardar Equipo
        </x-button>
    </div>
</form>

<script>
    // Function to initialize the form logic
    function initEquipmentForm() {
        // Datos de los tipos (pasados desde PHP a JS)
        // Ensure that $types is available in view
        const equipmentTypes = @json($types ?? []);
        
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
