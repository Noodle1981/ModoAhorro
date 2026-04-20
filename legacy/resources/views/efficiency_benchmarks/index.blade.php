@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
        <div>
            <div class="flex items-center gap-2 text-indigo-600 mb-2">
                <i class="bi bi-sliders text-lg"></i>
                <span class="text-[10px] font-black uppercase tracking-widest">Administración de IA</span>
            </div>
            <h1 class="text-4xl font-black text-gray-900 tracking-tighter uppercase">Benchmarks de <span class="text-indigo-600">Eficiencia</span></h1>
            <p class="text-gray-500 mt-2 font-medium">Configuración de parámetros para el motor de recomendaciones de reemplazo.</p>
        </div>
        <div class="shrink-0">
            <x-button href="{{ route('efficiency-benchmarks.create') }}" variant="primary" size="lg" class="shadow-xl shadow-indigo-100">
                <i class="bi bi-plus-lg mr-2"></i> Nuevo Benchmark
            </x-button>
        </div>
    </div>

    {{-- Stats Overview --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <x-stat-card 
            title="Total Benchmarks" 
            :value="$benchmarks->count()" 
            icon="bi-box-seam" 
            color="indigo" 
        />
        <x-stat-card 
            title="Promedio de Ahorro" 
            :value="number_format($benchmarks->avg('efficiency_gain_factor') * 100, 1) . '%'" 
            icon="bi-graph-up-arrow" 
            color="emerald" 
        />
        <x-stat-card 
            title="Precio Mercado Avg" 
            :value="'$' . number_format($benchmarks->avg('average_market_price'), 0, ',', '.')" 
            icon="bi-currency-dollar" 
            color="blue" 
        />
    </div>

    {{-- Benchmarks Table --}}
    <x-card class="overflow-hidden border-none shadow-xl shadow-gray-100">
        <x-table striped hover>
            <x-slot:head>
                <tr class="text-[10px] font-black uppercase tracking-widest text-gray-400">
                    <th class="px-6 py-4">Tipo de Equipo</th>
                    <th class="px-6 py-4">Factor de Ganancia</th>
                    <th class="px-6 py-4">Precio Sugerido</th>
                    <th class="px-6 py-4">Búsqueda ML</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </x-slot:head>

            @foreach($benchmarks as $benchmark)
                <tr class="group transition-colors hover:bg-indigo-50/30">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-white group-hover:text-indigo-600 transition-all border border-transparent group-hover:border-indigo-100">
                                <i class="{{ $benchmark->equipmentType->category->icon ?? 'bi-device-ssd' }} text-lg"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ $benchmark->equipmentType->name }}</p>
                                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest">{{ $benchmark->equipmentType->category->name }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            @php $gainPercent = $benchmark->efficiency_gain_factor * 100; @endphp
                            <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden max-w-[80px]">
                                <div class="bg-indigo-500 h-full" @style(['width' => $gainPercent . '%'])></div>
                            </div>
                            <span class="text-xs font-black text-gray-700">{{ number_format($benchmark->efficiency_gain_factor * 100, 0) }}%</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-black text-gray-900">${{ number_format($benchmark->average_market_price, 0, ',', '.') }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <x-badge variant="ghost" class="text-[10px] font-black uppercase tracking-tight">
                            {{ $benchmark->meli_search_term }}
                        </x-badge>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <x-button href="{{ route('efficiency-benchmarks.edit', $benchmark) }}" variant="ghost" size="sm" class="text-gray-400 hover:text-indigo-600">
                                <i class="bi bi-pencil-square"></i>
                            </x-button>
                            
                            <form action="{{ route('efficiency-benchmarks.destroy', $benchmark) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este benchmark?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-500 transition-all">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach

            @if($benchmarks->isEmpty())
                <tr>
                    <td colspan="5" class="px-6 py-20 text-center">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-dashed border-gray-200 text-gray-300">
                            <i class="bi bi-database-exclamation text-3xl"></i>
                        </div>
                        <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">No hay benchmarks registrados</p>
                        <p class="text-[10px] text-gray-400 mt-1 uppercase font-black">Empezá creando uno nuevo para activar las recomendaciones</p>
                    </td>
                </tr>
            @endif
        </x-table>
    </x-card>
</div>
@endsection
