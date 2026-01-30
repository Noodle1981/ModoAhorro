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

        {{-- Type --}}
        <div class="space-y-1.5">
            <label for="type_id" class="block text-sm font-medium text-gray-700">
                Tipo de Equipo <span class="text-red-500">*</span>
            </label>
            <select name="type_id" id="type_id" required disabled
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 bg-gray-50">
                <option value="">Primero seleccione categoría...</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}" 
                        data-category="{{ $type->category_id }}"
                        data-power="{{ $type->default_power_watts }}"
                        data-name="{{ $type->name }}">
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Name --}}
    <div class="mb-6 space-y-1.5">
        <label for="name" class="block text-sm font-medium text-gray-700">
            Nombre del Equipo <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" id="name" required
            value="{{ old('name') }}"
            placeholder="Ej: Aire Acondicionado Split"
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
    </div>
    
    {{-- Suggestions (Dynamic) --}}
    <div id="suggestions-container" class="hidden mb-6 p-4 bg-purple-50 border border-purple-100 rounded-xl">
        <p class="text-sm font-semibold text-purple-800 mb-3">
            <i class="bi bi-lightbulb-fill"></i> Valores sugeridos para esta categoría:
        </p>
        <div id="suggestions-list" class="flex flex-wrap gap-2">
            {{-- Injected via JS --}}
        </div>
        <p class="text-xs text-purple-600 mt-3">Haz clic en una sugerencia para auto-completar.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Power --}}
        <div class="space-y-1.5">
            <div class="flex justify-between items-end mb-1">
                <label for="nominal_power_w" class="block text-sm font-medium text-gray-700">
                    Potencia Nominal (W) <span class="text-red-500">*</span>
                </label>
                
                {{-- Validation Toggles --}}
                <div class="flex gap-3 text-xs">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" name="power_mode" value="suggested" class="form-radio text-purple-600 focus:ring-purple-500 h-3 w-3" checked>
                        <span class="ml-1.5 text-gray-600">Sugerido</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" name="power_mode" value="real" class="form-radio text-emerald-600 focus:ring-emerald-500 h-3 w-3">
                        <span class="ml-1.5 text-gray-600">Real</span>
                    </label>
                </div>
            </div>
            
            <input type="number" name="nominal_power_w" id="nominal_power_w" required
                value="{{ old('nominal_power_w') }}"
                placeholder="Ej: 1500"
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors bg-gray-50">
            <p class="text-xs text-gray-500" id="power-help-text">Valor promedio para este tipo de equipo.</p>
        </div>

        {{-- Quantity --}}
        <div class="space-y-1.5">
            <label for="cantidad" class="block text-sm font-medium text-gray-700">
                Cantidad <span class="text-red-500">*</span>
            </label>
            <input type="number" name="cantidad" id="cantidad" required min="1"
                value="{{ old('cantidad', 1) }}"
                placeholder="1"
                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Daily Hours --}}

        
        {{-- Frequency --}}

    </div>
    
    {{-- Hidden Validation Flag --}}
    <input type="hidden" name="is_validated" id="is_validated" value="0">

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
        const equipmentTypes = @json($types ?? []);
        
        const categorySelect = document.getElementById('category_id');
        const typeSelect = document.getElementById('type_id');
        
        const suggestionsContainer = document.getElementById('suggestions-container');
        const suggestionsList = document.getElementById('suggestions-list');
        
        const nameInput = document.getElementById('name');
        const powerInput = document.getElementById('nominal_power_w');
        const isValidatedInput = document.getElementById('is_validated');
        
        const radioSuggested = document.querySelector('input[name="power_mode"][value="suggested"]');
        const radioReal = document.querySelector('input[name="power_mode"][value="real"]');
        const powerHelpText = document.getElementById('power-help-text');

        let currentTypeDefaultPower = null;

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

        // Listeners for Radios
        radioSuggested.addEventListener('change', () => setPowerMode('suggested'));
        radioReal.addEventListener('change', () => setPowerMode('real'));

        // Auto-switch to Real if user types different value
        powerInput.addEventListener('input', function() {
            if (radioSuggested.checked && this.value != currentTypeDefaultPower) {
                setPowerMode('real');
            }
        });

        if (!categorySelect || !typeSelect) return; 

        // -- LOGICA CATEGORIA -> TIPO --
        const newCategorySelect = categorySelect.cloneNode(true);
        categorySelect.parentNode.replaceChild(newCategorySelect, categorySelect);
        
        newCategorySelect.addEventListener('change', function() {
            const catId = this.value;
            suggestionsList.innerHTML = '';
            
            // Filtrar Tipos select
            let hasOptions = false;
            typeSelect.value = "";
            
            for (let i = 0; i < typeSelect.options.length; i++) {
                const opt = typeSelect.options[i];
                if (!opt.value) continue; // Skip placeholder
                
                if (catId && opt.getAttribute('data-category') == catId) {
                    opt.style.display = '';
                    hasOptions = true;
                } else {
                    opt.style.display = 'none';
                }
            }

            if (catId) {
                typeSelect.disabled = !hasOptions;
                typeSelect.classList.remove('bg-gray-50');
            } else {
                typeSelect.disabled = true;
                typeSelect.classList.add('bg-gray-50');
            }

            // Suggestions Buttons (Quick Pick)
            if (catId && hasOptions) {
                // Filtrar array original para botones
                const filtered = equipmentTypes.filter(t => t.category_id == catId);
                suggestionsContainer.classList.remove('hidden');
                
                filtered.forEach(type => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = "px-3 py-1 bg-white border border-purple-200 rounded-full text-xs font-medium text-purple-700 hover:bg-purple-100 transition shadow-sm";
                    btn.innerHTML = `${type.name} <span class="text-gray-400 ml-1">(${type.default_power_watts}W)</span>`;
                    
                    btn.onclick = () => {
                        // Seleccionar en el dropdown
                        typeSelect.value = type.id;
                        typeSelect.dispatchEvent(new Event('change')); // Trigger autofill
                    };
                    suggestionsList.appendChild(btn);
                });
            } else {
                suggestionsContainer.classList.add('hidden');
            }
        });

        // -- LOGICA TIPO -> AUTOFILL --
        typeSelect.addEventListener('change', function() {
            const selectedOpt = this.options[this.selectedIndex];
            if (!selectedOpt || !selectedOpt.value) return;

            const defaultPower = selectedOpt.getAttribute('data-power');
            const defaultName = selectedOpt.getAttribute('data-name');
            
            currentTypeDefaultPower = defaultPower;

            // Auto-fill Name (solo si esta vacio o parece generico, para no sobreescribir custom)
            // Simplificacion: Sobreescribimos para feedback inmediato, usuario puede editar despues
            nameInput.value = defaultName;
            
            // Set Power & Suggested Mode
            setPowerMode('suggested');
            
            // Highlight
             [nameInput, powerInput].forEach(el => {
                el.classList.add('bg-yellow-50');
                setTimeout(() => el.classList.remove('bg-yellow-50'), 1000);
            });
        });
        
        // Init state
        if (newCategorySelect.value) {
            newCategorySelect.dispatchEvent(new Event('change'));
        }
    }

    // Run immediately
    initEquipmentForm();

    document.addEventListener('livewire:navigated', initEquipmentForm);
</script>
