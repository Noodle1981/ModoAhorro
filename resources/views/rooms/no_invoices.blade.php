@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <x-card class="text-center py-16">
            <div class="w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="bi bi-receipt text-4xl text-amber-600"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Sin facturas cargadas</h2>
            <p class="text-gray-500 mb-6 max-w-md mx-auto">
                Para ver el consumo de tus habitaciones, primero necesitas cargar las facturas de esta entidad.
            </p>
            <x-button variant="primary" href="{{ route($config['route_prefix'] . '.invoices', $entity->id ?? 1) }}">
                <i class="bi bi-plus-circle mr-2"></i> Cargar Facturas
            </x-button>
        </x-card>
        
    </div>
</div>
@endsection
