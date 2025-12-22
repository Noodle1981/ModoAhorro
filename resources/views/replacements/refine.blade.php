@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-blue-500 to-indigo-600 w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-magic text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Refinar Datos</h1>
                    <p class="text-gray-500">{{ $equipment->name }}</p>
                </div>
            </div>
            <x-button variant="secondary" href="{{ url()->previous() }}">
                <i class="bi bi-arrow-left mr-2"></i> Volver
            </x-button>
        </div>

        {{-- Form --}}
        <x-card>
            <p class="text-gray-600 mb-6">
                Cuantos más datos nos proporciones, más preciso será el cálculo de ahorro y las recomendaciones de reemplazo.
            </p>

            <form method="POST" action="{{ route('replacements.update_refinement', $equipment->id) }}">
                @csrf
                @method('PUT')

                {{-- Age Section --}}
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2 mb-4">
                        <i class="bi bi-calendar3 text-blue-500"></i>
                        Antigüedad y Eficiencia
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label for="acquisition_year" class="block text-sm font-medium text-gray-700">
                                Año de Compra (Aprox)
                            </label>
                            <input type="number" name="acquisition_year" id="acquisition_year"
                                min="1990" max="{{ date('Y') }}"
                                value="{{ old('acquisition_year', $equipment->acquisition_year) }}"
                                placeholder="Ej: 2015"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="text-xs text-gray-500">Usamos esto para estimar el desgaste y la ineficiencia.</p>
                        </div>
                        
                        <div class="space-y-1.5">
                            <label for="energy_label" class="block text-sm font-medium text-gray-700">
                                Etiqueta Energética
                            </label>
                            <select name="energy_label" id="energy_label"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Desconozco</option>
                                @foreach(['A+++', 'A++', 'A+', 'A', 'B', 'C', 'D', 'E'] as $label)
                                    <option value="{{ $label }}" {{ old('energy_label', $equipment->energy_label) == $label ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500">Suele estar pegada en el frente del equipo.</p>
                        </div>
                    </div>
                </div>

                {{-- Capacity Section --}}
                <div class="mb-8 pb-8 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2 mb-4">
                        <i class="bi bi-speedometer2 text-amber-500"></i>
                        Capacidad del Equipo
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-1.5">
                            <label for="capacity" class="block text-sm font-medium text-gray-700">
                                Capacidad
                            </label>
                            <input type="number" step="0.01" name="capacity" id="capacity"
                                value="{{ old('capacity', $equipment->capacity) }}"
                                placeholder="Ej: 3500"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        
                        <div class="space-y-1.5">
                            <label for="capacity_unit" class="block text-sm font-medium text-gray-700">
                                Unidad
                            </label>
                            <select name="capacity_unit" id="capacity_unit"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Seleccionar...</option>
                                <option value="frigorias" {{ old('capacity_unit', $equipment->capacity_unit) == 'frigorias' ? 'selected' : '' }}>Frigorías (Aire)</option>
                                <option value="litros" {{ old('capacity_unit', $equipment->capacity_unit) == 'litros' ? 'selected' : '' }}>Litros (Termotanque/Heladera)</option>
                                <option value="kg" {{ old('capacity_unit', $equipment->capacity_unit) == 'kg' ? 'selected' : '' }}>Kg (Lavarropas)</option>
                                <option value="btu" {{ old('capacity_unit', $equipment->capacity_unit) == 'btu' ? 'selected' : '' }}>BTU</option>
                                <option value="watts" {{ old('capacity_unit', $equipment->capacity_unit) == 'watts' ? 'selected' : '' }}>Watts</option>
                            </select>
                        </div>
                        
                        <div class="flex items-end pb-1">
                            <label class="flex items-center gap-3 cursor-pointer p-3 bg-gray-50 rounded-xl w-full">
                                <input type="checkbox" name="is_inverter" value="1" 
                                    {{ old('is_inverter', $equipment->is_inverter) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-500 focus:ring-blue-500 w-5 h-5">
                                <div>
                                    <span class="font-medium text-gray-900">Inverter</span>
                                    <p class="text-xs text-gray-500">Tecnología de alta eficiencia</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-3">
                    <x-button variant="secondary" href="{{ url()->previous() }}">
                        Cancelar
                    </x-button>
                    <x-button variant="primary" type="submit">
                        <i class="bi bi-check-lg mr-2"></i> Guardar y Recalcular
                    </x-button>
                </div>
            </form>
        </x-card>

        {{-- Help Info --}}
        <x-alert type="info" class="mt-6">
            <div class="flex items-start gap-3">
                <i class="bi bi-info-circle text-xl"></i>
                <div class="text-sm">
                    <strong>¿Por qué es importante?</strong>
                    <p class="mt-1">Un equipo de 10 años puede consumir hasta un 30% más que cuando era nuevo. Con tus datos reales podemos calcular mejor el ahorro potencial de un reemplazo.</p>
                </div>
            </div>
        </x-alert>
    </div>
</div>
@endsection
