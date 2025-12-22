@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-receipt text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Facturas</h1>
                    <p class="text-gray-500 text-sm flex items-center gap-2">
                        <i class="{{ $config['icon_secondary'] ?? 'bi-house' }}"></i>
                        {{ $entity->name }}
                    </p>
                </div>
            </div>
            <div class="flex gap-3 mt-4 md:mt-0">
                @if($contract)
                    <x-button variant="primary" href="{{ route($config['route_prefix'] . '.invoices.create', $entity->id) }}">
                        <i class="bi bi-plus-circle mr-2"></i> Nueva Factura
                    </x-button>
                @endif
                <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.show', $entity->id) }}">
                    <i class="bi bi-arrow-left mr-2"></i> Volver
                </x-button>
            </div>
        </div>

        @if(!$contract)
            {{-- No Contract Warning --}}
            <x-alert type="warning" title="Sin contrato activo">
                No hay contrato activo registrado para esta entidad. 
                <a href="{{ route($config['route_prefix'] . '.contracts.create', $entity->id) }}" class="underline font-medium">Crear contrato</a> para poder cargar facturas.
            </x-alert>
        @elseif(empty($invoices) || $invoices->isEmpty())
            {{-- Empty State --}}
            <x-card class="text-center py-16">
                <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="bi bi-receipt text-4xl text-emerald-600"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Sin facturas registradas</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">
                    Carga tus facturas de electricidad para comenzar a analizar tu consumo energético.
                </p>
                <x-button variant="primary" href="{{ route($config['route_prefix'] . '.invoices.create', $entity->id) }}">
                    <i class="bi bi-plus-circle mr-2"></i> Cargar primera factura
                </x-button>
            </x-card>
        @else
            {{-- Invoice Table --}}
            <x-card :padding="false">
                <x-table hover>
                    <x-slot:head>
                        <tr>
                            <th class="px-6 py-4">Período</th>
                            <th class="px-6 py-4">Consumo</th>
                            <th class="px-6 py-4">Importe</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </x-slot:head>
                    
                    @foreach($invoices as $invoice)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                        <i class="bi bi-calendar3 text-emerald-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            {{ \Carbon\Carbon::parse($invoice->start_date)->format('d/m/Y') }} - 
                                            {{ \Carbon\Carbon::parse($invoice->end_date)->format('d/m/Y') }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($invoice->start_date)->diffInDays($invoice->end_date) }} días
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-mono text-lg font-semibold text-gray-900">
                                    {{ number_format($invoice->total_energy_consumed_kwh ?? 0, 0) }}
                                </span>
                                <span class="text-gray-500 text-sm">kWh</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-gray-900">
                                    ${{ number_format($invoice->total_amount ?? 0, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($invoice->usage_locked)
                                    <x-badge variant="success" dot>Calibrado</x-badge>
                                @else
                                    <x-badge variant="warning" dot>Pendiente</x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <x-button variant="ghost" size="xs" href="{{ route($config['route_prefix'] . '.invoices.edit', [$entity->id, $invoice->id]) }}" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </x-button>
                                    <form action="{{ route($config['route_prefix'] . '.invoices.destroy', [$entity->id, $invoice->id]) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <x-button variant="ghost" size="xs" type="submit" 
                                            onclick="return confirm('¿Seguro que deseas eliminar esta factura?')"
                                            class="text-red-500 hover:text-red-700 hover:bg-red-50">
                                            <i class="bi bi-trash"></i>
                                        </x-button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            </x-card>
            
            {{-- Summary Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                <x-stat-card 
                    title="Total Facturas" 
                    :value="$invoices->count()" 
                    icon="bi-receipt" 
                    color="emerald"
                />
                <x-stat-card 
                    title="Consumo Total" 
                    :value="number_format($invoices->sum('total_energy_consumed_kwh'), 0) . ' kWh'" 
                    icon="bi-lightning-charge" 
                    color="blue"
                />
                <x-stat-card 
                    title="Gasto Total" 
                    :value="'$' . number_format($invoices->sum('total_amount'), 0, ',', '.')" 
                    icon="bi-currency-dollar" 
                    color="purple"
                />
            </div>
        @endif
    </div>
</div>
@endsection
