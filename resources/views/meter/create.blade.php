@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="bi bi-speedometer2 text-blue-600"></i> Registrar Medidor
            </h1>
            <p class="text-sm text-gray-500 mt-1">Ingresa los datos del medidor de tu hogar</p>
        </div>

        <x-card>
            <form method="POST" action="{{ route('entities.meter.store', $entity->id) }}">
                @csrf
                
                <div class="grid grid-cols-1 gap-6 mb-6">
                    <div>
                        <label for="serial_number" class="block text-sm font-medium text-gray-700 mb-1">Número de Serie</label>
                        <input type="text" 
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" 
                               id="serial_number" 
                               name="serial_number" 
                               required 
                               placeholder="Ej: 123456789">
                    </div>

                    <div>
                        <label for="company_id" class="block text-sm font-medium text-gray-700 mb-1">Empresa Proveedora</label>
                        <select class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" 
                                id="company_id" 
                                name="company_id" 
                                required>
                            <option value="">Seleccione empresa</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <x-button variant="secondary" href="{{ route('entities.meter.index', $entity->id) }}">
                        Cancelar
                    </x-button>
                    <x-button type="submit" variant="success">
                        <i class="bi bi-save mr-2"></i> Guardar Medidor
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</div>
@endsection
