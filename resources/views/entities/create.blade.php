@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <i class="bi bi-house-door text-emerald-600"></i> Crear entidad hogar
        </h2>
    </div>

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <form method="POST" action="{{ route('entities.store') }}">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre del hogar</label>
                        <input type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" id="name" name="name" required>
                    </div>

                    <div class="mb-4">
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Entidad</label>
                         <select class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" id="type" name="type" required>
                            <option value="Casa">Casa</option>
                            <option value="Departamento">Departamento</option>
                            <option value="Oficina">Oficina</option>
                            <option value="Local Comercial">Local Comercial</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="address_street" class="block text-sm font-medium text-gray-700 mb-1">Calle y Altura</label>
                        <input type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" id="address_street" name="address_street" required>
                    </div>

                    <div class="mb-4">
                        <label for="address_postal_code" class="block text-sm font-medium text-gray-700 mb-1">Código postal</label>
                        <input type="text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" id="address_postal_code" name="address_postal_code" required>
                    </div>

                    <div class="mb-4 md:col-span-2">
                        <label for="locality_id" class="block text-sm font-medium text-gray-700 mb-1">Localidad</label>
                        @if($localities->isEmpty())
                            <div class="bg-red-50 border-l-4 border-red-400 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-exclamation-triangle text-red-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-red-700">
                                            No hay localidades disponibles. Por favor, contacta al administrador o ejecuta los seeders.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @else
                            <select class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" id="locality_id" name="locality_id" required>
                                <option value="">Selecciona una localidad</option>
                                @foreach($localities as $locality)
                                    <option value="{{ $locality->id }}">
                                        {{ $locality->name }} ({{ $locality->province->name ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div class="mb-4 md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descripción (Opcional)</label>
                        <textarea class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="square_meters" class="block text-sm font-medium text-gray-700 mb-1">Metros cuadrados</label>
                        <input type="number" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" id="square_meters" name="square_meters" min="1" required>
                    </div>

                    <div class="mb-4">
                        <label for="people_count" class="block text-sm font-medium text-gray-700 mb-1">Cantidad de personas</label>
                        <input type="number" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" id="people_count" name="people_count" min="1" required>
                    </div>
                </div>

                <div class="flex items-center justify-end mt-6">
                    <a href="{{ route('entities.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                        Cancelar
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:border-emerald-900 focus:ring ring-emerald-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <i class="bi bi-save mr-2"></i> Guardar entidad
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
