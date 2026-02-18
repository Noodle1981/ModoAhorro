@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-emerald-500 to-teal-600 w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-arrow-repeat text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Catálogo de Reemplazos</h1>
                    <p class="text-gray-500">Oportunidades de ahorro para {{ $entity->name }}</p>
                </div>
            </div>
            <div class="flex gap-3 mt-4 md:mt-0">
                <x-button variant="secondary" href="{{ route('efficiency-benchmarks.index') }}">
                    <i class="bi bi-gear mr-2"></i> Benchmarks
                </x-button>
                <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.show', $entity->id) }}">
                    <i class="bi bi-arrow-left mr-2"></i> Volver
                </x-button>
            </div>
        </div>

        @if(count($opportunities) > 0)
            {{-- Opportunities Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach($opportunities as $op)
                    @php
                        $colorMap = [
                            'success' => 'emerald',
                            'warning' => 'amber',
                            'danger' => 'red',
                            'info' => 'blue',
                        ];
                        $color = $colorMap[$op['verdict']['color']] ?? 'gray';
                    @endphp
                    <div class="bg-white rounded-2xl shadow-sm border-l-4 border-l-{{ $color }}-500 overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="p-6">
                            {{-- Header --}}
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wide text-{{ $color }}-600">
                                        {{ $op['verdict']['label'] }}
                                    </span>
                                    <h3 class="text-lg font-bold text-gray-900 mt-1">{{ $op['equipment_name'] }}</h3>
                                    <p class="text-sm text-gray-500">
                                        Consumo: <strong>{{ $op['current_consumption_kwh'] }} kWh/mes</strong>
                                        @if($op['is_estimated'] ?? false)
                                            <span class="ml-1 text-xs bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded">estimado</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
                                    <i class="bi bi-plug text-2xl text-gray-400"></i>
                                </div>
                            </div>

                            {{-- Stats --}}
                            <div class="grid grid-cols-2 gap-4 py-4 border-y border-gray-100">
                                <div class="text-center">
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Ahorro Mensual</p>
                                    <p class="text-xl font-bold text-emerald-600">
                                        ${{ number_format($op['monthly_savings_amount'], 0, ',', '.') }}
                                    </p>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Recupero</p>
                                    <p class="text-xl font-bold text-gray-900">{{ $op['payback_months'] }} meses</p>
                                </div>
                            </div>

                            {{-- Suggestion --}}
                            <div class="mt-4 p-3 bg-amber-50 rounded-xl">
                                <p class="text-sm text-gray-700">
                                    <i class="bi bi-lightbulb text-amber-500 mr-1"></i>
                                    <strong>{{ $op['replacement_suggestion'] }}</strong>
                                </p>
                            </div>

                            {{-- Actions --}}
                            <div class="flex gap-2 mt-4">
                                <x-button variant="secondary" size="sm" href="{{ route('replacements.refine', $op['equipment_id']) }}" class="flex-1">
                                    <i class="bi bi-pencil mr-1"></i> Editar
                                </x-button>
                                <x-button variant="primary" size="sm" href="{{ $op['affiliate_link'] ?? '#' }}" target="_blank" class="flex-1">
                                    <i class="bi bi-cart mr-1"></i> Comprar
                                </x-button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <x-card class="text-center py-16 mb-8">
                <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="bi bi-check-circle text-4xl text-emerald-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">¡Todo Optimizado!</h3>
                <p class="text-gray-500 max-w-md mx-auto">
                    No encontramos equipos con alternativas más eficientes disponibles.
                    Puede que los equipos ya sean eficientes, o que les falte potencia/horas de uso cargadas.
                </p>
                <a href="{{ route('efficiency-benchmarks.index') }}" class="inline-flex items-center gap-2 mt-4 text-sm text-blue-600 hover:underline">
                    <i class="bi bi-gear"></i> Ver benchmarks disponibles
                </a>
            </x-card>
        @endif

        {{-- Analyzable Equipment Table --}}
        @if(isset($analyzableEquipments) && count($analyzableEquipments) > 0)
            <x-card :padding="false">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="bi bi-cpu text-blue-500"></i>
                        Mis Equipos Analizados
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">Agrega más detalles (Año, Etiqueta) para obtener mejores recomendaciones</p>
                </div>
                
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4">Equipo</th>
                            <th class="px-6 py-4">Categoría</th>
                            <th class="px-6 py-4">Detalles</th>
                            <th class="px-6 py-4">Acción</th>
                        </tr>
                    </x-slot:head>
                    
                    @foreach($analyzableEquipments as $eq)
                        <tr>
                            <form action="{{ route('replacements.update_refinement', $eq->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $eq->name }}</td>
                                <td class="px-6 py-4">
                                    <x-badge variant="secondary">{{ $eq->category->name ?? '-' }}</x-badge>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <input type="number" name="acquisition_year" 
                                            class="w-24 rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                                            placeholder="Año" value="{{ $eq->acquisition_year }}">
                                        
                                        <select name="energy_label" 
                                            class="rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Etiqueta...</option>
                                            @foreach(['A+++', 'A++', 'A+', 'A', 'B', 'C', 'D', 'E'] as $label)
                                                <option value="{{ $label }}" {{ $eq->energy_label == $label ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        
                                        <label class="flex items-center gap-1 cursor-pointer">
                                            <input type="checkbox" name="is_inverter" value="1" 
                                                {{ $eq->is_inverter ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-blue-500 focus:ring-blue-500">
                                            <span class="text-sm text-gray-600">Inverter</span>
                                        </label>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <x-button variant="primary" size="sm" type="submit">
                                        <i class="bi bi-check-lg mr-1"></i> Guardar
                                    </x-button>
                                </td>
                            </form>
                        </tr>
                    @endforeach
                </x-table>
            </x-card>
        @endif
    </div>
</div>
@endsection
