@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Header con Resumen de Factura --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-sliders text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Motor Integral de Ajuste</h1>
                    <p class="text-gray-500 text-sm">
                        Factura #{{ $invoice->id }} | Periodo: 
                        <span class="font-semibold text-gray-700">{{ \Carbon\Carbon::parse($invoice->start_date)->format('d/m/Y') }}</span> a 
                        <span class="font-semibold text-gray-700">{{ \Carbon\Carbon::parse($invoice->end_date)->format('d/m/Y') }}</span>
                    </p>
                </div>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-2 gap-4">
                <div class="px-4 py-2 bg-indigo-50 rounded-xl border border-indigo-100">
                    <p class="text-xs text-indigo-600 font-bold uppercase tracking-wider">Total Factura</p>
                    <p class="text-xl font-black text-indigo-900">{{ number_format($invoice->total_energy_consumed_kwh, 1) }} <span class="text-sm font-normal">kWh</span></p>
                </div>
                <div class="px-4 py-2 bg-emerald-50 rounded-xl border border-emerald-100">
                    <x-button variant="secondary" size="sm" href="{{ route($config['route_prefix'] . '.usage_adjustments', $entity->id) }}">
                        <i class="bi bi-arrow-left"></i> Volver
                    </x-button>
                </div>
            </div>
        </div>

        <form action="{{ route($config['route_prefix'] . '.usage_adjustments.update', [$entity->id, $invoice->id]) }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {{-- Columna de Ajustes (Tiers) --}}
                <div class="lg:col-span-8 space-y-8">
                    @foreach($equipmentTiers as $tierKey => $tier)
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                            <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white shadow-sm bg-{{ $tier['color'] }}-500">
                                        <i class="bi {{ $tier['icon'] }} text-lg"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900">{{ $tier['label'] }}</h3>
                                        <p class="text-xs text-gray-500">{{ $tier['desc'] }}</p>
                                    </div>
                                </div>
                                <x-badge variant="{{ $tier['color'] }}">{{ $tier['items']->count() }} equipos</x-badge>
                            </div>

                            <div class="divide-y divide-gray-100">
                                @php
                                    $groupedByRoom = $tier['items']->groupBy(fn($item) => $item->room->name ?? 'Zonas Generales')->sortKeys();
                                @endphp

                                @if($groupedByRoom->isEmpty())
                                    <div class="p-8 text-center text-gray-400 text-sm">
                                        No hay equipos en esta categoría.
                                    </div>
                                @else
                                    @foreach($groupedByRoom as $roomName => $items)
                                        <div class="px-6 py-1.5 bg-gray-50 border-y border-gray-100 flex items-center gap-2">
                                            <i class="bi bi-geo-alt text-gray-400 text-xs"></i>
                                            <h4 class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">{{ $roomName }}</h4>
                                        </div>

                                        @foreach($items as $equipment)
                                            @php 
                                                $usage = $usages[$equipment->id] ?? null; 
                                                $prefix = "usages[{$equipment->id}]";
                                                $currentFrequency = $usage ? $usage->usage_frequency : 'diario';
                                                if ($currentFrequency == 'diariamente') $currentFrequency = 'diario';
                                                $currentHours = $usage ? $usage->avg_daily_use_hours : '';
                                            @endphp
                                            <div class="p-6 hover:bg-gray-50/50 transition-colors" 
                                                x-data="{ 
                                                    frequency: '{{ $currentFrequency }}', 
                                                    hours: '{{ $currentHours }}' 
                                                }">
                                                <div class="flex flex-col md:flex-row gap-6">
                                                    {{-- Info Equipo --}}
                                                    <div class="md:w-1/3">
                                                        <h4 class="font-semibold text-gray-900 mb-1">{{ $equipment->name }}</h4>
                                                        
                                                        <div class="flex items-center gap-2 mt-2">
                                                            <span class="text-xs font-mono bg-gray-100 px-2 py-0.5 rounded text-gray-600">{{ $equipment->nominal_power_w }}W</span>
                                                            @if($equipment->is_validated)
                                                                <span class="text-[10px] bg-green-50 text-green-600 px-1.5 py-0.5 rounded border border-green-100 flex items-center gap-1" title="Potencia Real Validada">
                                                                    <i class="bi bi-check-circle-fill"></i> Real
                                                                </span>
                                                            @else
                                                                <span class="text-[10px] bg-yellow-50 text-yellow-600 px-1.5 py-0.5 rounded border border-yellow-100 flex items-center gap-1" title="Potencia Promedio Sugerida">
                                                                    <i class="bi bi-exclamation-circle"></i> Sugerido
                                                                </span>
                                                            @endif
                                                            @if($equipment->is_standby)
                                                                <span class="text-[10px] bg-red-50 text-red-600 px-1.5 py-0.5 rounded border border-red-100 flex items-center gap-1">
                                                                    <i class="bi bi-lightning-charge"></i> Vampiro
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    {{-- Inputs de Ajuste --}}
                                                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Horas/Día</label>
                                                            <input type="number" step="0.1" min="0" max="24" 
                                                                name="{{ $prefix }}[avg_daily_use_hours]" 
                                                                x-model="hours"
                                                                class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                                placeholder="Ej: 4.5">
                                                        </div>
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Periodicidad</label>
                                                            <select name="{{ $prefix }}[usage_frequency]" 
                                                                x-model="frequency"
                                                                @change="if(frequency === 'nunca') hours = 0"
                                                                class="w-full rounded-lg border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                                                <option value="diario">Diariamente</option>
                                                                <option value="casi_frecuentemente">Casi frecuentemente</option>
                                                                <option value="frecuentemente">Frecuentemente</option>
                                                                <option value="ocasionalmente">Ocasionalmente</option>
                                                                <option value="raramente">Raramente</option>
                                                                <option value="nunca">Nunca</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Sidebar de Acciones --}}
                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-8">
                        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="bi bi-check2-circle text-emerald-500"></i>
                            Finalizar Ajuste
                        </h3>
                        
                        <p class="text-sm text-gray-600 mb-6">
                            Al guardar, el motor recalculará automáticamente la distribución de energía según estos nuevos parámetros.
                        </p>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notas de calibración</label>
                            <textarea name="notes" rows="3" class="w-full rounded-xl border-gray-200 text-sm" placeholder="Ej: Se usó más el aire por visitas..."></textarea>
                        </div>

                        <div class="space-y-3">
                            <label class="flex items-center gap-3 p-3 bg-amber-50 rounded-xl border border-amber-100 cursor-pointer hover:bg-amber-100 transition-colors">
                                <input type="checkbox" name="lock_invoice" value="1" class="rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                                <span class="text-xs text-amber-900 font-medium italic">
                                    Cerrar periodo (No permitir más ajustes)
                                </span>
                            </label>

                            <x-button variant="primary" type="submit" class="w-full py-4 shadow-xl">
                                <i class="bi bi-cloud-upload mr-2"></i> Guardar y Procesar
                            </x-button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

