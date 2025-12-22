@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-plus-lg text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Nueva {{ str_replace('s', '', $config['rooms_label'] ?? 'Habitación') }}</h1>
                    <p class="text-gray-500 text-sm">{{ $entity->name }}</p>
                </div>
            </div>
            <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.rooms', $entity->id) }}">
                <i class="bi bi-arrow-left mr-2"></i> Volver
            </x-button>
        </div>

        {{-- Form --}}
        <x-card>
            <form action="{{ route($config['route_prefix'] . '.rooms.store', $entity->id) }}" method="POST">
                @csrf

                <div class="space-y-6">
                    <x-input 
                        name="name" 
                        label="Nombre" 
                        placeholder="Ej: Dormitorio Principal, Cocina, Sala de Reuniones..."
                        :value="old('name')"
                        required 
                    />

                    <x-textarea 
                        name="description" 
                        label="Descripción (opcional)" 
                        placeholder="Alguna nota o descripción adicional..."
                        :value="old('description')"
                        rows="3"
                    />
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-gray-200">
                    <x-button variant="secondary" href="{{ route($config['route_prefix'] . '.rooms', $entity->id) }}">
                        Cancelar
                    </x-button>
                    <x-button variant="primary" type="submit">
                        <i class="bi bi-check-lg mr-2"></i> Crear
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</div>
@endsection
