@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-purple-500 to-indigo-600 w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-sliders text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Ajustes de Uso</h1>
                    <p class="text-gray-500 text-sm">Calibra el uso de equipos por factura</p>
                </div>
            </div>
            <x-button variant="secondary" href="{{ route('dashboard') }}">
                <i class="bi bi-house-door mr-2"></i> Panel de Entidades
            </x-button>
        </div>

        {{-- Invoices Table --}}
        <x-card :padding="false">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Facturas Registradas</h3>
            </div>
            
            <x-table hover>
                <x-slot:head>
                    <tr>
                        <th class="px-6 py-4">Factura</th>
                        <th class="px-6 py-4">Período</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4 text-center">Acciones</th>
                    </tr>
                </x-slot:head>
                
                @foreach($invoices as $invoice)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="font-mono font-semibold text-gray-900">#{{ $invoice->id }}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ \Carbon\Carbon::parse($invoice->start_date)->format('d/m/Y') }} - 
                            {{ \Carbon\Carbon::parse($invoice->end_date)->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4">
                            @if($invoice->usageAdjustment && $invoice->usageAdjustment->adjusted)
                                <x-badge variant="success" dot>Ajustado</x-badge>
                            @else
                                <x-badge variant="warning" dot>Pendiente</x-badge>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <x-button variant="primary" size="sm" href="{{ route('usage_adjustments.edit', $invoice->id) }}">
                                    <i class="bi bi-pencil mr-1"></i> Editar
                                </x-button>
                                <x-button variant="ghost" size="sm" href="{{ route('usage_adjustments.show', $invoice->id) }}">
                                    <i class="bi bi-eye mr-1"></i> Ver
                                </x-button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>
    </div>
</div>
@endsection
