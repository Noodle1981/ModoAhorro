@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="flex items-center gap-4 mb-8">
            <x-button variant="secondary" href="{{ route('efficiency-benchmarks.index') }}">
                <i class="bi bi-arrow-left mr-2"></i> Volver
            </x-button>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Nuevo Benchmark</h1>
                <p class="text-gray-500 text-sm">Define la alternativa eficiente para un tipo de equipo</p>
            </div>
        </div>

        <x-card>
            <form action="{{ route('efficiency-benchmarks.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Tipo de equipo --}}
                <div>
                    <label for="equipment_type_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Tipo de Equipo <span class="text-red-500">*</span>
                    </label>
                    <select name="equipment_type_id" id="equipment_type_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('equipment_type_id') border-red-400 @enderror">
                        <option value="">Seleccionar tipo...</option>
                        @foreach($types->groupBy(fn($t) => $t->category->name ?? 'Sin categoría') as $category => $categoryTypes)
                            <optgroup label="{{ $category }}">
                                @foreach($categoryTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('equipment_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('equipment_type_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Búsqueda MeLi --}}
                <div>
                    <label for="meli_search_term" class="block text-sm font-medium text-gray-700 mb-1">
                        Término de Búsqueda (Mercado Libre) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="meli_search_term" id="meli_search_term" required
                        value="{{ old('meli_search_term') }}"
                        placeholder="Ej: Aire Acondicionado Inverter 3500 frigorias"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('meli_search_term') border-red-400 @enderror">
                    <p class="text-xs text-gray-400 mt-1">Este texto se usará para buscar el producto recomendado.</p>
                    @error('meli_search_term')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Factor de ahorro + Precio --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="efficiency_gain_factor" class="block text-sm font-medium text-gray-700 mb-1">
                            Factor de Ahorro <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01" min="0" max="1" name="efficiency_gain_factor" id="efficiency_gain_factor" required
                                value="{{ old('efficiency_gain_factor') }}"
                                placeholder="0.40"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('efficiency_gain_factor') border-red-400 @enderror">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">0.40 = 40% de ahorro estimado</p>
                        @error('efficiency_gain_factor')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="average_market_price" class="block text-sm font-medium text-gray-700 mb-1">
                            Precio Referencia (ARS) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="1" min="0" name="average_market_price" id="average_market_price" required
                            value="{{ old('average_market_price') }}"
                            placeholder="380000"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('average_market_price') border-red-400 @enderror">
                        <p class="text-xs text-gray-400 mt-1">Precio promedio del equipo nuevo</p>
                        @error('average_market_price')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Link afiliado --}}
                <div>
                    <label for="affiliate_link" class="block text-sm font-medium text-gray-700 mb-1">
                        Link de Afiliado <span class="text-gray-400 font-normal">(Opcional)</span>
                    </label>
                    <input type="url" name="affiliate_link" id="affiliate_link"
                        value="{{ old('affiliate_link') }}"
                        placeholder="https://mercadolibre.com.ar/..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                {{-- Actions --}}
                <div class="flex gap-3 pt-2">
                    <x-button type="submit" variant="primary">
                        <i class="bi bi-check-lg mr-2"></i> Guardar Benchmark
                    </x-button>
                    <x-button variant="secondary" href="{{ route('efficiency-benchmarks.index') }}">
                        Cancelar
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</div>
@endsection
