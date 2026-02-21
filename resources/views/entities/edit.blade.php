@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <i class="bi bi-pencil-square text-amber-600"></i> Editar entidad hogar
        </h2>
    </div>

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <form method="POST" action="{{ route('entities.update', $entity->id) }}">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre del hogar</label>
                        <input type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" id="name" name="name" value="{{ $entity->name }}" required>
                    </div>

                    <div class="mb-4">
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Entidad</label>
                        <input type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" id="type" name="type" value="{{ $entity->type }}" required>
                    </div>

                    <div class="mb-4">
                        <label for="address_street" class="block text-sm font-medium text-gray-700 mb-1">Calle</label>
                        <input type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" id="address_street" name="address_street" value="{{ $entity->address_street }}" required>
                    </div>

                    <div class="mb-4">
                        <label for="address_postal_code" class="block text-sm font-medium text-gray-700 mb-1">Código postal</label>
                        <input type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" id="address_postal_code" name="address_postal_code" value="{{ $entity->address_postal_code }}" required>
                    </div>

                    <div class="mb-4 md:col-span-2">
                        <label for="locality_id" class="block text-sm font-medium text-gray-700 mb-1">Localidad</label>
                        <select class="block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" id="locality_id" name="locality_id" required>
                            <option value="">Selecciona una localidad</option>
                            @foreach($localities as $locality)
                                <option value="{{ $locality->id }}" @if($entity->locality_id == $locality->id) selected @endif>{{ $locality->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4 md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea class="block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" id="description" name="description" rows="3">{{ $entity->description }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label for="square_meters" class="block text-sm font-medium text-gray-700 mb-1">Metros cuadrados</label>
                        <input type="number" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" id="square_meters" name="square_meters" value="{{ $entity->square_meters }}" min="1" required>
                    </div>

                    <div class="mb-4">
                        <label for="people_count" class="block text-sm font-medium text-gray-700 mb-1">Cantidad de personas</label>
                        <input type="number" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm" id="people_count" name="people_count" value="{{ $entity->people_count }}" min="1" required>
                    </div>
                </div>

                <div class="flex items-center justify-end mt-6">
                    <a href="{{ route('entities.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700 active:bg-amber-900 focus:outline-none focus:border-amber-900 focus:ring ring-amber-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <i class="bi bi-save mr-2"></i> Actualizar entidad
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
