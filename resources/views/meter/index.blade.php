@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="bi bi-speedometer2 text-blue-600"></i> Medidor del Hogar
            </h1>
            <x-button variant="secondary" href="{{ route('entities.show', $entity->id) }}">
                <i class="bi bi-arrow-left mr-2"></i> Volver al hogar
            </x-button>
        </div>

        @if($meter)
            <x-card>
                <div class="flex items-center gap-4 mb-6 border-b border-gray-100 pb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <i class="bi bi-hdd-network text-2xl text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Datos del Medidor</h3>
                        <p class="text-sm text-gray-500">Información técnica asociada</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <span class="text-xs text-gray-500 uppercase font-bold tracking-wider">Número de Serie</span>
                        <p class="text-lg font-medium text-gray-900 mt-1">{{ $meter->serial_number }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <span class="text-xs text-gray-500 uppercase font-bold tracking-wider">Empresa Proveedora</span>
                        <p class="text-lg font-medium text-gray-900 mt-1">{{ $meter->company->name ?? 'N/A' }}</p>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <span class="text-xs text-gray-500 uppercase font-bold tracking-wider">Fecha de Instalación</span>
                        <p class="text-lg font-medium text-gray-900 mt-1">{{ $meter->installed_at ? \Carbon\Carbon::parse($meter->installed_at)->format('d/m/Y') : 'No registrada' }}</p>
                    </div>
                </div>

                <div class="flex justify-end">
                    <x-button href="{{ route('entities.meter.edit', [$entity->id, $meter->id]) }}">
                        <i class="bi bi-pencil-square mr-2"></i> Editar Medidor
                    </x-button>
                </div>
            </x-card>
        @else
            <x-card class="text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="bi bi-slash-circle text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No hay medidor registrado</h3>
                <p class="text-gray-500 max-w-sm mx-auto mb-6">Registra el medidor para comenzar a realizar seguimiento del consumo eléctrico.</p>
                
                <x-button variant="success" href="{{ route('entities.meter.create', $entity->id) }}">
                    <i class="bi bi-plus-lg mr-2"></i> Registrar Medidor
                </x-button>
            </x-card>
        @endif
    </div>
</div>
@endsection
