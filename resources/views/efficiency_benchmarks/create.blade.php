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
            <i class="bi bi-plus-circle-dotted text-lg"></i>
            <span class="text-[10px] font-black uppercase tracking-widest">Configuración Técnica</span>
        </div>
        <h1 class="text-4xl font-black text-gray-900 tracking-tighter uppercase">Nuevo <span class="text-indigo-600">Benchmark</span></h1>
        <p class="text-gray-500 mt-2 font-medium">Definí los parámetros de eficiencia y precio para un tipo de equipo.</p>
    </div>

    {{-- Form Card --}}
    <x-card class="border-none shadow-2xl shadow-indigo-100/50 p-8">
        <form action="{{ route('efficiency-benchmarks.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                {{-- Equipment Type Selection --}}
                <div class="md:col-span-2">
                    <x-select 
                        name="equipment_type_id" 
                        label="Tipo de Equipo" 
                        :options="$types->mapWithKeys(fn($t) => [$t->id => $t->name . ' (' . $t->category->name . ')'])->toArray()" 
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
                        placeholder="Ej: 0.35 para 35% de ahorro" 
                        required 
                        helper="Indica el porcentaje de ahorro potencial al reemplazar un equipo antiguo por uno moderno de este tipo."
                    />
                </div>

                {{-- Market Price --}}
                <div>
                    <x-input 
                        name="average_market_price" 
                        label="Precio de Mercado Sugerido" 
                        type="number" 
                        min="0" 
                        placeholder="Ej: 850000" 
                        required 
                        helper="Precio promedio estimado para el cálculo de ROI en pesos argentinos."
                    />
                </div>

                {{-- ML Search Term --}}
                <div>
                    <x-input 
                        name="meli_search_term" 
                        label="Término de Búsqueda MercadoLibre" 
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
                    Crear Benchmark
                </x-button>
            </div>
        </form>
    </x-card>

    {{-- Help card --}}
    <div class="mt-12 bg-indigo-50/50 rounded-3xl p-6 border border-indigo-100 border-dashed">
        <div class="flex gap-4">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shrink-0">
                <i class="bi bi-info-circle"></i>
            </div>
            <div>
                <h5 class="text-xs font-black text-indigo-900 uppercase tracking-widest mb-1">Guía Rápida</h5>
                <p class="text-[11px] text-indigo-600 leading-relaxed font-medium">
                    Los benchmarks alimentan el **Módulo de Reemplazos**. El motor compara el consumo teórico de equipos obsoletos contra estos parámetros para mostrarle al usuario cuánto dinero ahorraría mensualmente y en cuánto tiempo recuperaría la inversión del nuevo equipo.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
