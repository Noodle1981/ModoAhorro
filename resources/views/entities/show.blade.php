@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <i class="bi bi-house-door text-indigo-600"></i> Detalles de la entidad hogar
        </h2>
    </div>

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <dl class="divide-y divide-gray-200">
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $entity->name }}</dd>
                </div>
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Calle</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $entity->address_street }}</dd>
                </div>
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Código postal</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $entity->address_postal_code }}</dd>
                </div>
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Localidad</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $entity->locality->name ?? '-' }}</dd>
                </div>
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Metros cuadrados</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $entity->square_meters }}</dd>
                </div>
                <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">Cantidad de personas</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $entity->people_count }}</dd>
                </div>
                
                @if(isset($climateProfile) && !empty($climateProfile))
                    <div class="py-4 bg-gray-50 rounded-lg mt-4 px-4">
                        <dt class="text-sm font-medium text-gray-700 mb-3 flex items-center gap-2">
                            <i class="bi bi-sun text-amber-500"></i> Perfil Solar (Promedio)
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-center border-r border-gray-200">
                                    <h5 class="text-lg font-bold text-amber-500">{{ $climateProfile['avg_radiation'] }}</h5>
                                    <small class="text-gray-500">MJ/m²</small>
                                </div>
                                <div class="text-center border-r border-gray-200">
                                    <h5 class="text-lg font-bold text-amber-500">{{ $climateProfile['avg_sunshine_duration'] }}</h5>
                                    <small class="text-gray-500">Horas Sol</small>
                                </div>
                                <div class="text-center">
                                    <h5 class="text-lg font-bold text-gray-600">{{ $climateProfile['avg_cloud_cover'] }}%</h5>
                                    <small class="text-gray-500">Nubosidad</small>
                                </div>
                            </div>
                        </dd>
                    </div>
                @endif
            </dl>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('entities.edit', $entity->id) }}" class="inline-flex items-center px-4 py-2 bg-amber-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-600 focus:outline-none focus:border-amber-700 focus:ring ring-amber-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <i class="bi bi-pencil-square mr-2"></i> Editar
                </a>
                <a href="{{ route('rooms.index', $entity->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <i class="bi bi-door-open mr-2"></i> Habitaciones
                </a>
                <a href="{{ route('equipment.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:border-gray-700 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <i class="bi bi-laptop mr-2"></i> Equipos
                </a>
                <a href="{{ route('entities.invoices.index', $entity->id) }}" class="inline-flex items-center px-4 py-2 bg-cyan-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-cyan-700 focus:outline-none focus:border-cyan-700 focus:ring ring-cyan-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <i class="bi bi-receipt mr-2"></i> Facturas
                </a>
                <a href="{{ route('meter.create', ['entity_id' => $entity->id]) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:outline-none focus:border-emerald-700 focus:ring ring-emerald-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <i class="bi bi-lightning mr-2"></i> Contrato/Medidor
                </a>
                
                {{-- Botón de Reemplazos Inteligentes --}}
                <a href="{{ route('replacements.index', $entity->id) }}" class="inline-flex items-center px-4 py-2 bg-white border border-emerald-500 rounded-md font-semibold text-xs text-emerald-600 uppercase tracking-widest hover:bg-emerald-50 focus:outline-none focus:ring ring-emerald-300 disabled:opacity-25 transition ease-in-out duration-150 relative">
                    <i class="bi bi-arrow-repeat mr-2"></i> Reemplazos
                    @if(isset($replacementsCount) && $replacementsCount > 0)
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">
                            {{ $replacementsCount }}
                        </span>
                    @endif
                </a>
                
                {{-- Botón de Salud Térmica --}}
                <a href="{{ route('thermal.index', $entity->id) }}" class="inline-flex items-center px-4 py-2 bg-white border border-blue-500 rounded-md font-semibold text-xs text-blue-600 uppercase tracking-widest hover:bg-blue-50 focus:outline-none focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <i class="bi bi-thermometer-half mr-2"></i> Salud Térmica
                </a>

                <a href="{{ route('entities.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <i class="bi bi-arrow-left mr-2"></i> Volver
                </a>
            </div>

            {{-- Mensaje si no hay facturas --}}
            @if(empty($entity->invoices) || $entity->invoices->isEmpty())
                <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="bi bi-exclamation-triangle text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Para gestionar facturas primero debes ingresar los datos de tu medidor.
                            </p>
                            <div class="mt-2">
                                <a href="{{ route('meter.create', ['entity_id' => $entity->id]) }}" class="text-sm font-medium text-yellow-700 hover:text-yellow-600 underline">
                                    Ingresar datos del medidor
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
