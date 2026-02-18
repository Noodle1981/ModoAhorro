@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-emerald-500 to-teal-600 w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-graph-up-arrow text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Benchmarks de Eficiencia</h1>
                    <p class="text-gray-500">Base de datos para recomendaciones de reemplazo</p>
                </div>
            </div>
            <x-button variant="primary" href="{{ route('efficiency-benchmarks.create') }}">
                <i class="bi bi-plus-lg mr-2"></i> Nuevo Benchmark
            </x-button>
        </div>

        @if(session('success'))
            <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-3xl font-bold text-emerald-600">{{ $benchmarks->count() }}</p>
                <p class="text-sm text-gray-500">Benchmarks activos</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-3xl font-bold text-blue-600">
                    {{ $benchmarks->count() ? number_format($benchmarks->avg('efficiency_gain_factor') * 100, 0) . '%' : '-' }}
                </p>
                <p class="text-sm text-gray-500">Ahorro promedio</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                <p class="text-3xl font-bold text-gray-700">{{ \App\Models\EquipmentType::count() }}</p>
                <p class="text-sm text-gray-500">Tipos de equipo</p>
            </div>
        </div>

        {{-- Table --}}
        <x-card>
            @if($benchmarks->isEmpty())
                <div class="text-center py-12">
                    <i class="bi bi-database-x text-5xl text-gray-300"></i>
                    <p class="text-gray-500 mt-4">No hay benchmarks cargados aún.</p>
                    <p class="text-sm text-gray-400 mt-1">Ejecutá <code class="bg-gray-100 px-1 rounded">php artisan db:seed --class=EfficiencyBenchmarkSeeder</code> para cargar los datos iniciales.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Equipo</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Categoría</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Ahorro Est.</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Precio Ref. (ARS)</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Búsqueda MeLi</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($benchmarks as $benchmark)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900 text-sm">{{ $benchmark->equipmentType->name ?? 'N/A' }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs text-gray-500">{{ $benchmark->equipmentType->category->name ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $gain = $benchmark->efficiency_gain_factor * 100;
                                            $color = $gain >= 60 ? 'emerald' : ($gain >= 30 ? 'amber' : 'blue');
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-{{ $color }}-100 text-{{ $color }}-700">
                                            {{ number_format($gain, 0) }}% ahorro
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-medium text-gray-700">${{ number_format($benchmark->average_market_price, 0, ',', '.') }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-xs text-gray-600 font-mono bg-gray-100 px-2 py-1 rounded">{{ $benchmark->meli_search_term }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-3">
                                            <a href="{{ route('efficiency-benchmarks.edit', $benchmark) }}"
                                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('efficiency-benchmarks.destroy', $benchmark) }}" method="POST"
                                                  onsubmit="return confirm('¿Eliminar este benchmark?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 text-sm">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>

        {{-- Help note --}}
        <div class="mt-4 bg-blue-50 border border-blue-100 rounded-xl p-4 text-sm text-blue-700">
            <i class="bi bi-info-circle mr-2"></i>
            <strong>¿Cómo funciona?</strong> Cada benchmark define cuánto puede ahorrar un usuario al reemplazar un equipo por una alternativa más eficiente. El sistema compara automáticamente los equipos del usuario con estos datos para generar recomendaciones personalizadas.
        </div>
    </div>
</div>
@endsection
