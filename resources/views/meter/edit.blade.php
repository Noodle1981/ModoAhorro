@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="bi bi-pencil-square text-blue-600"></i> Editar Medidor
            </h1>
            <p class="text-sm text-gray-500 mt-1">Actualiza los datos del medidor de tu hogar</p>
        </div>

        <x-card>
            <form method="POST" action="{{ route('entities.meter.update', [$entity->id, $meter->id]) }}">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 gap-6 mb-6">
                    <div>
                        <label for="serial_number" class="block text-sm font-medium text-gray-700 mb-1">Número de Serie</label>
                        <input type="text" 
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" 
                               id="serial_number" 
                               name="serial_number" 
                               value="{{ $meter->serial_number }}" 
                               required>
                    </div>

                    <div>
                        <label for="company_id" class="block text-sm font-medium text-gray-700 mb-1">Empresa Proveedora</label>
                        <select class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" 
                                id="company_id" 
                                name="company_id" 
                                required>
                            <option value="">Seleccione empresa</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" @selected($meter->company_id == $company->id)>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <x-button variant="secondary" href="{{ route('entities.meter.index', $entity->id) }}">
                        Cancelar
                    </x-button>
                    <x-button type="submit" variant="primary">
                        <i class="bi bi-check-lg mr-2"></i> Actualizar
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</div>
@endsection
