@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-amber-500 to-amber-600 w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-pencil text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Editar {{ $config['label'] }}</h1>
                    <p class="text-gray-500 text-sm">{{ $entity->name }}</p>
                </div>
            </div>
            <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.show', $entity->id) }}">
                <i class="bi bi-arrow-left mr-2"></i> Volver
            </x-button>
        </div>

        {{-- Form --}}
        <x-card>
            <form action="{{ route($config['route_prefix'] . '.update', $entity->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Basic Info --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <x-input 
                        name="name" 
                        label="Nombre" 
                        placeholder="Ej: Mi Casa Principal"
                        :value="old('name', $entity->name)"
                        required 
                    />

                    {{-- Province --}}
                    <div class="space-y-1.5">
                        <label for="province_id" class="block text-sm font-medium text-gray-700">
                            Provincia <span class="text-red-500">*</span>
                        </label>
                        @php
                            $currentProvinceId = $entity->locality->province_id ?? (old('province_id'));
                        @endphp
                        <select id="province_id" required
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Seleccionar provincia...</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province->id }}" {{ $currentProvinceId == $province->id ? 'selected' : '' }}>
                                    {{ $province->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Locality --}}
                    <div class="space-y-1.5">
                        <label for="locality_id" class="block text-sm font-medium text-gray-700">
                            Localidad <span class="text-red-500">*</span>
                        </label>
                        <select name="locality_id" id="locality_id" required
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Seleccionar localidad...</option>
                            @foreach($localities as $locality)
                                <option value="{{ $locality->id }}" {{ old('locality_id', $entity->locality_id) == $locality->id ? 'selected' : '' }}>
                                    {{ $locality->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('locality_id')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <script>
                    document.getElementById('province_id').addEventListener('change', function() {
                        const provinceId = this.value;
                        const localitySelect = document.getElementById('locality_id');
                        
                        localitySelect.innerHTML = '<option value="">Cargando localidades...</option>';
                        
                        if (!provinceId) {
                            localitySelect.innerHTML = '<option value="">Seleccionar localidad...</option>';
                            return;
                        }

                        fetch(`/api/provinces/${provinceId}/localities`)
                            .then(response => response.json())
                            .then(data => {
                                localitySelect.innerHTML = '<option value="">Seleccionar localidad...</option>';
                                data.forEach(locality => {
                                    const option = document.createElement('option');
                                    option.value = locality.id;
                                    option.textContent = `${locality.name}${locality.postal_code ? ' (' + locality.postal_code + ')' : ''}`;
                                    localitySelect.appendChild(option);
                                });
                            })
                            .catch(error => {
                                console.error('Error fetching localities:', error);
                                localitySelect.innerHTML = '<option value="">Error al cargar localidades</option>';
                            });
                    });
                </script>

                {{-- Address --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="md:col-span-2">
                        <x-input 
                            name="address_street" 
                            label="Dirección" 
                            placeholder="Ej: Av. Corrientes 1234"
                            :value="old('address_street', $entity->address_street)"
                            required 
                        />
                    </div>
                    <x-input 
                        name="address_postal_code" 
                        label="Código Postal" 
                        placeholder="Ej: 1414"
                        :value="old('address_postal_code', $entity->address_postal_code)"
                        required 
                    />
                </div>

                {{-- Size & People --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <x-input 
                        name="square_meters" 
                        label="Metros²" 
                        type="number"
                        placeholder="Ej: 80"
                        :value="old('square_meters', $entity->square_meters)"
                        required 
                    />
                    <x-input 
                        name="people_count" 
                        :label="$config['people_label']" 
                        type="number"
                        placeholder="Ej: 4"
                        :value="old('people_count', $entity->people_count)"
                        required 
                    />
                </div>

                {{-- Business Hours (conditional) --}}
                @if($config['has_business_hours'])
                    @php
                        $currentDays = $entity->operating_days 
                            ? (is_string($entity->operating_days) ? json_decode($entity->operating_days, true) : $entity->operating_days)
                            : ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
                    @endphp
                    
                    <div class="border-t border-gray-200 pt-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2 mb-4">
                            <i class="bi bi-clock text-blue-500"></i>
                            Horario de Operación
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-4">
                            <x-input 
                                name="opens_at" 
                                label="Abre" 
                                type="time"
                                :value="old('opens_at', $entity->opens_at ? \Carbon\Carbon::parse($entity->opens_at)->format('H:i') : '09:00')"
                            />
                            <x-input 
                                name="closes_at" 
                                label="Cierra" 
                                type="time"
                                :value="old('closes_at', $entity->closes_at ? \Carbon\Carbon::parse($entity->closes_at)->format('H:i') : '18:00')"
                            />
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Días de operación</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'] as $day)
                                        <label class="inline-flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg cursor-pointer hover:bg-gray-100 transition-colors">
                                            <input type="checkbox" name="operating_days[]" value="{{ $day }}"
                                                {{ in_array($day, old('operating_days', $currentDays)) ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                                            <span class="text-sm text-gray-700">{{ ucfirst(substr($day, 0, 3)) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Description --}}
                <div class="mb-6">
                    <x-textarea 
                        name="description" 
                        label="Descripción (opcional)" 
                        placeholder="Alguna nota o descripción adicional..."
                        :value="old('description', $entity->description)"
                        rows="3"
                    />
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
                    <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.show', $entity->id) }}">
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
@endsection
