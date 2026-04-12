@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    {{-- Breadcrumbs / Back button --}}
    <div class="mb-8">
        <a href="{{ route('efficiency-benchmarks.index') }}" class="inline-flex items-center text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 hover:text-indigo-600 transition-colors gap-2">
            <i class="bi bi-arrow-left text-base"></i>
            Volver al Listado
        </a>
    </div>

    {{-- Header --}}
    <div class="mb-12">
        <div class="flex items-center gap-3 text-indigo-600 mb-2">
            <i class="bi bi-pencil-square text-lg"></i>
            <span class="text-[10px] font-black uppercase tracking-widest">Edición de Parámetros</span>
        </div>
        <h1 class="text-4xl font-black text-gray-900 tracking-tighter uppercase">Editar <span class="text-indigo-600">Benchmark</span></h1>
        <p class="text-gray-500 mt-2 font-medium text-sm">Actualizando parámetros técnicos para: <strong>{{ $efficiencyBenchmark->equipmentType->name }}</strong>.</p>
    </div>

    {{-- Form Card --}}
    <x-card class="border-none shadow-2xl shadow-indigo-100/50 p-8">
        <form action="{{ route('efficiency-benchmarks.update', $efficiencyBenchmark) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                {{-- Equipment Type Selection --}}
                <div class="md:col-span-2">
                    <x-select 
                        name="equipment_type_id" 
                        label="Tipo de Equipo" 
                        :options="$types->mapWithKeys(fn($t) => [$t->id => $t->name . ' (' . $t->category->name . ')'])->toArray()" 
                        :selected="$efficiencyBenchmark->equipment_type_id"
                        required 
                        placeholder="Seleccioná un tipo de equipo"
                    />
                </div>

                {{-- Efficiency Gain Factor --}}
                <div>
                    <x-input 
                        name="efficiency_gain_factor" 
                        label="Factor de Ahorro (0 a 1)" 
                        type="number" 
                        step="0.01" 
                        min="0" 
                        max="1" 
                        value="{{ $efficiencyBenchmark->efficiency_gain_factor }}"
                        placeholder="Ej: 0.35 para 35% de ahorro" 
                        required 
                        helper="Indica el porcentaje de ahorro potencial al reemplazar un equipo antiguo."
                    />
                </div>

                {{-- Market Price --}}
                <div>
                    <x-input 
                        name="average_market_price" 
                        label="Precio de Mercado Sugerido" 
                        type="number" 
                        min="0" 
                        value="{{ $efficiencyBenchmark->average_market_price }}"
                        placeholder="Ej: 850000" 
                        required 
                        helper="Precio promedio estimado para el cálculo de ROI."
                    />
                </div>

                {{-- ML Search Term --}}
                <div>
                    <x-input 
                        name="meli_search_term" 
                        label="Término de Búsqueda MercadoLibre" 
                        value="{{ $efficiencyBenchmark->meli_search_term }}"
                        placeholder="Ej: Aire Acondicionado Split Inverter 3000" 
                        required 
                        helper="Palabras clave usadas para buscar ofertas en tiempo real."
                    />
                </div>

                {{-- Affiliate Link --}}
                <div>
                    <x-input 
                        name="affiliate_link" 
                        label="Link de Afiliado (Opcional)" 
                        type="url" 
                        value="{{ $efficiencyBenchmark->affiliate_link }}"
                        placeholder="https://articulo.mercadolibre.com..." 
                        helper="Enlace directo a una oferta recomendada."
                    />
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-50">
                <x-button href="{{ route('efficiency-benchmarks.index') }}" variant="ghost" class="text-gray-400">
                    Cancelar
                </x-button>
                <x-button type="submit" variant="primary" size="lg" class="px-10 shadow-lg shadow-indigo-100">
                    Guardar Cambios
                </x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
