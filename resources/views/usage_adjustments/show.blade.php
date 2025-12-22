@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-blue-500 to-indigo-600 w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-eye text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detalle del Ajuste</h1>
                    <p class="text-gray-500 text-sm">Factura #{{ $invoice->id }}</p>
                </div>
            </div>
            <div class="flex gap-3">
                <x-button variant="secondary" href="{{ route('usage_adjustments.index') }}">
                    <i class="bi bi-arrow-left mr-2"></i> Volver
                </x-button>
                <x-button variant="primary" href="{{ route('usage_adjustments.edit', $invoice->id) }}">
                    <i class="bi bi-pencil mr-2"></i> Editar
                </x-button>
            </div>
        </div>

        {{-- Summary Card --}}
        <x-card class="mb-6 border-l-4 border-l-blue-500">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Factura #{{ $invoice->id }}</h3>
                    <p class="text-gray-600">
                        <strong>Período:</strong> 
                        {{ \Carbon\Carbon::parse($invoice->start_date)->format('d/m/Y') }} - 
                        {{ \Carbon\Carbon::parse($invoice->end_date)->format('d/m/Y') }}
                        <x-badge variant="secondary" size="xs" class="ml-2">
                            {{ \Carbon\Carbon::parse($invoice->start_date)->diffInDays(\Carbon\Carbon::parse($invoice->end_date)) }} días
                        </x-badge>
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500 uppercase tracking-wide">Consumo Calculado</p>
                    <p class="text-3xl font-bold text-blue-600">{{ number_format($totalCalculatedConsumption, 0) }} kWh</p>
                </div>
            </div>
        </x-card>

        {{-- Rooms & Usage --}}
        @forelse($groupedUsages as $roomName => $usages)
            <x-card :padding="false" class="mb-6">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="bi bi-door-open text-blue-500"></i>
                        {{ $roomName }}
                    </h3>
                </div>
                
                <x-table>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4">Equipo</th>
                            <th class="px-6 py-4">Potencia</th>
                            <th class="px-6 py-4">Frecuencia</th>
                            <th class="px-6 py-4">Detalle de Uso</th>
                        </tr>
                    </x-slot:head>
                    
                    @foreach($usages as $usage)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <span class="font-medium text-gray-900">{{ $usage->equipment->name }}</span>
                            </td>
                            <td class="px-6 py-4 font-mono text-gray-600">
                                {{ $usage->equipment->nominal_power_w ?? '-' }} W
                            </td>
                            <td class="px-6 py-4">
                                <x-badge variant="secondary">{{ ucfirst($usage->usage_frequency ?? 'diario') }}</x-badge>
                            </td>
                            <td class="px-6 py-4">
                                @if(in_array($usage->usage_frequency, ['diario', 'semanal']) || empty($usage->usage_frequency))
                                    <div class="text-sm">
                                        <span class="font-medium">{{ $usage->avg_daily_use_hours }} h/día</span>
                                        <span class="text-gray-500">× {{ $usage->use_days_in_period }} días</span>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <i class="bi bi-calendar-week mr-1"></i>
                                        {{ $usage->use_days_of_week ?? 'Todos los días' }}
                                    </p>
                                @else
                                    <div class="text-sm">
                                        <span class="font-medium">{{ $usage->usage_count }} usos</span>
                                        <span class="text-gray-500">× {{ $usage->avg_use_duration }} h</span>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            </x-card>
        @empty
            <x-card class="text-center py-12">
                <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-exclamation-triangle text-3xl text-amber-500"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Sin Ajustes</h3>
                <p class="text-gray-500">No hay ajustes de uso registrados para esta factura.</p>
            </x-Card>
        @endforelse
    </div>
</div>
@endsection
