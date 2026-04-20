@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
            <i class="bi bi-house-door text-emerald-600"></i> Mis entidades
        </h2>
        <a href="{{ route('entities.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:border-emerald-900 focus:ring ring-emerald-300 disabled:opacity-25 transition ease-in-out duration-150">
            <i class="bi bi-plus-circle mr-2"></i> Nueva entidad
        </a>
    </div>

    <!-- Tabla de Entidades -->
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-8">
        <div class="p-6 bg-white border-b border-gray-200">
            @if($entities->isEmpty())
                <p class="text-gray-500 text-center py-4">No hay entidades registradas aún.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Localidad</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metros²</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Personas</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($entities as $entity)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $entity->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $entity->type }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $entity->locality->name ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $entity->square_meters }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $entity->people_count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex gap-2">
                                        <a href="{{ route('entities.show', $entity->id) }}" class="text-blue-600 hover:text-blue-900" title="Ver"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('entities.edit', $entity->id) }}" class="text-amber-600 hover:text-amber-900" title="Editar"><i class="bi bi-pencil-square"></i></a>
                                        <a href="{{ route('rooms.index', $entity->id) }}" class="text-gray-600 hover:text-gray-900" title="Habitaciones"><i class="bi bi-door-open"></i></a>
                                        <a href="{{ route('entities.invoices.index', $entity->id) }}" class="text-emerald-600 hover:text-emerald-900" title="Facturas"><i class="bi bi-receipt"></i></a>
                                        <form action="{{ route('entities.destroy', $entity->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 ml-2" onclick="return confirm('¿Seguro que deseas eliminar esta entidad?')" title="Eliminar"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Centro de Consumo -->
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-8">
        <div class="bg-white px-6 py-4 border-b border-gray-200">
            <h4 class="text-lg font-semibold text-gray-800 flex items-center gap-2"><i class="bi bi-lightning-charge text-yellow-500"></i> Centro de Consumo</h4>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Facturas -->
                <div class="bg-white border border-cyan-200 rounded-lg shadow-sm hover:shadow-md transition-shadow h-full flex flex-col">
                    <div class="p-5 flex-1">
                        <h5 class="text-lg font-medium text-cyan-800 mb-2"><i class="bi bi-receipt"></i> Facturas y Ajustes</h5>
                        <p class="text-gray-600 mb-4 text-sm">Revisa el estado de tus facturas y realiza el <b>ajuste de uso</b> para obtener cálculos precisos.</p>
                    </div>
                    <div class="px-5 py-3 bg-cyan-50 border-t border-cyan-100 rounded-b-lg">
                        @if(Route::has('usage_adjustments.index') && Auth::check())
                            <a href="{{ route('usage_adjustments.index') }}" class="text-cyan-600 hover:text-cyan-800 font-medium text-sm flex items-center">
                                Ir a ajustes de uso <i class="bi bi-arrow-right ml-1"></i>
                            </a>
                        @else
                            <span class="text-gray-400 text-sm cursor-not-allowed">Ajustes no disponibles</span>
                        @endif
                    </div>
                </div>

                <!-- Consumo -->
                <div class="bg-white border border-emerald-200 rounded-lg shadow-sm hover:shadow-md transition-shadow h-full flex flex-col">
                    <div class="p-5 flex-1">
                        <h5 class="text-lg font-medium text-emerald-800 mb-2"><i class="bi bi-bar-chart-line"></i> Consumo energético</h5>
                        <p class="text-gray-600 mb-4 text-sm">Visualiza el consumo estimado y real de tus entidades, compara periodos y optimiza tu gestión.</p>
                    </div>
                    <div class="px-5 py-3 bg-emerald-50 border-t border-emerald-100 rounded-b-lg">
                        <a href="{{ route('consumption.panel') }}" class="text-emerald-600 hover:text-emerald-800 font-medium text-sm flex items-center">
                            Panel de consumo <i class="bi bi-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Centro de Recomendaciones -->
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-8">
        <div class="bg-white px-6 py-4 border-b border-gray-200">
            <h4 class="text-lg font-semibold text-gray-800 flex items-center gap-2"><i class="bi bi-lightbulb text-amber-400"></i> Centro de Recomendaciones</h4>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Paneles Solares -->
                <div class="bg-white border border-amber-200 rounded-lg shadow-sm hover:shadow-md transition-shadow h-full flex flex-col">
                    <div class="p-5 flex-1">
                        <h5 class="text-lg font-medium text-amber-800 mb-2"><i class="bi bi-sun"></i> Paneles Solares</h5>
                        <p class="text-gray-600 mb-4 text-sm">Calcula el potencial de energía solar para tu propiedad y solicita un presupuesto personalizado.</p>
                    </div>
                    <div class="px-5 py-3 bg-amber-50 border-t border-amber-100 rounded-b-lg">
                        <a href="{{ route('entities.budget', $entity->id ?? 0) }}" class="text-amber-600 hover:text-amber-800 font-medium text-sm flex items-center">
                            <i class="bi bi-calculator mr-1"></i> Pedir presupuesto
                        </a>
                    </div>
                </div>

                <!-- Calefones Solares -->
                <div class="bg-white border border-red-200 rounded-lg shadow-sm hover:shadow-md transition-shadow h-full flex flex-col">
                    <div class="p-5 flex-1">
                        <h5 class="text-lg font-medium text-red-800 mb-2"><i class="bi bi-droplet-half"></i> Calefones Solares</h5>
                        <p class="text-gray-600 mb-4 text-sm">Ahorra gas o electricidad calentando agua con energía solar.</p>
                    </div>
                    <div class="px-5 py-3 bg-red-50 border-t border-red-100 rounded-b-lg">
                        @if($entities->isNotEmpty())
                            <a href="{{ route('entities.solar_water_heater', $entities->first()->id) }}" class="text-red-600 hover:text-red-800 font-medium text-sm flex items-center">
                                Pedir presupuesto <i class="bi bi-arrow-right ml-1"></i>
                            </a>
                        @else
                            <span class="text-gray-400 text-sm cursor-not-allowed">Crear entidad primero</span>
                        @endif
                    </div>
                </div>

                <!-- Reemplazos -->
                <div class="bg-white border border-blue-200 rounded-lg shadow-sm hover:shadow-md transition-shadow h-full flex flex-col">
                    <div class="p-5 flex-1">
                        <h5 class="text-lg font-medium text-blue-800 mb-2"><i class="bi bi-arrow-repeat"></i> Reemplazos</h5>
                        <p class="text-gray-600 mb-4 text-sm">Descubre qué equipos conviene renovar por eficiencia energética.</p>
                    </div>
                    <div class="px-5 py-3 bg-blue-50 border-t border-blue-100 rounded-b-lg">
                        @if($entities->isNotEmpty())
                            <a href="{{ route('replacements.index', $entities->first()->id) }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm flex items-center">
                                Ver recomendaciones <i class="bi bi-arrow-right ml-1"></i>
                            </a>
                        @else
                            <span class="text-gray-400 text-sm cursor-not-allowed">Crear entidad primero</span>
                        @endif
                    </div>
                </div>

                <!-- Consumo Fantasma -->
                <div class="bg-white border border-gray-300 rounded-lg shadow-sm hover:shadow-md transition-shadow h-full flex flex-col">
                    <div class="p-5 flex-1">
                        <h5 class="text-lg font-medium text-gray-800 mb-2"><i class="bi bi-power"></i> Consumo Fantasma</h5>
                        <p class="text-gray-600 mb-4 text-sm">Detecta y reduce el consumo de equipos en modo espera (Stand By).</p>
                    </div>
                    <div class="px-5 py-3 bg-gray-100 border-t border-gray-200 rounded-b-lg">
                        @if($entities->isNotEmpty())
                            <a href="{{ route('entities.standby_analysis', $entities->first()->id) }}" class="text-gray-600 hover:text-gray-800 font-medium text-sm flex items-center">
                                Analizar Stand By <i class="bi bi-arrow-right ml-1"></i>
                            </a>
                        @else
                            <span class="text-gray-400 text-sm cursor-not-allowed">Crear entidad primero</span>
                        @endif
                    </div>
                </div>

                <!-- Mantenimiento -->
                <div class="bg-white border border-cyan-200 rounded-lg shadow-sm hover:shadow-md transition-shadow h-full flex flex-col">
                    <div class="p-5 flex-1">
                        <h5 class="text-lg font-medium text-cyan-800 mb-2"><i class="bi bi-tools"></i> Mantenimiento</h5>
                        <p class="text-gray-600 mb-4 text-sm">Gestiona el mantenimiento de tus aires, lavarropas y heladeras.</p>
                    </div>
                    <div class="px-5 py-3 bg-cyan-50 border-t border-cyan-100 rounded-b-lg">
                        @if($entities->isNotEmpty())
                            <a href="{{ route('maintenance.index', $entities->first()->id) }}" class="text-cyan-600 hover:text-cyan-800 font-medium text-sm flex items-center">
                                Ver mantenimientos <i class="bi bi-arrow-right ml-1"></i>
                            </a>
                        @else
                            <span class="text-gray-400 text-sm cursor-not-allowed">Crear entidad primero</span>
                        @endif
                    </div>
                </div>

                <!-- Vacaciones -->
                <div class="bg-white border border-emerald-200 rounded-lg shadow-sm hover:shadow-md transition-shadow h-full flex flex-col">
                    <div class="p-5 flex-1">
                        <h5 class="text-lg font-medium text-emerald-800 mb-2"><i class="bi bi-airplane"></i> Vacaciones</h5>
                        <p class="text-gray-600 mb-4 text-sm">Recomendaciones para ahorrar energía cuando no estás en casa.</p>
                    </div>
                    <div class="px-5 py-3 bg-emerald-50 border-t border-emerald-100 rounded-b-lg">
                        @if($entities->isNotEmpty())
                            <a href="{{ route('vacation.index', $entities->first()->id) }}" class="text-emerald-600 hover:text-emerald-800 font-medium text-sm flex items-center">
                                Modo Vacaciones <i class="bi bi-arrow-right ml-1"></i>
                            </a>
                        @else
                            <span class="text-gray-400 text-sm cursor-not-allowed">Crear entidad primero</span>
                        @endif
                    </div>
                </div>

                <!-- Optimización Horaria -->
                <div class="bg-white border border-indigo-200 rounded-lg shadow-sm hover:shadow-md transition-shadow h-full flex flex-col">
                    <div class="p-5 flex-1">
                        <h5 class="text-lg font-medium text-indigo-800 mb-2"><i class="bi bi-clock"></i> Optimización Horaria</h5>
                        <p class="text-gray-600 mb-4 text-sm">Aprovecha las tarifas reducidas usando tus equipos en horarios óptimos.</p>
                    </div>
                    <div class="px-5 py-3 bg-indigo-50 border-t border-indigo-100 rounded-b-lg">
                        @if($entities->isNotEmpty())
                            <a href="{{ route('grid.optimization', $entities->first()->id) }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm flex items-center">
                                Analizar Horarios <i class="bi bi-arrow-right ml-1"></i>
                            </a>
                        @else
                            <span class="text-gray-400 text-sm cursor-not-allowed">Crear entidad primero</span>
                        @endif
                    </div>
                </div>

                <!-- Medidor Inteligente -->
                <div class="bg-white border border-blue-200 rounded-lg shadow-sm hover:shadow-md transition-shadow h-full flex flex-col">
                    <div class="p-5 flex-1">
                        <h5 class="text-lg font-medium text-blue-800 mb-2"><i class="bi bi-speedometer2"></i> Medidor Inteligente</h5>
                        <p class="text-gray-600 mb-4 text-sm">Conoce los beneficios de la medición inteligente y solicítalo.</p>
                    </div>
                    <div class="px-5 py-3 bg-blue-50 border-t border-blue-100 rounded-b-lg">
                        @if($entities->isNotEmpty())
                            <a href="{{ route('smart_meter.demo', $entities->first()->id) }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm flex items-center">
                                Ver Demo en Vivo <i class="bi bi-arrow-right ml-1"></i>
                            </a>
                        @else
                            <span class="text-gray-400 text-sm cursor-not-allowed">Crear entidad primero</span>
                        @endif
                    </div>
                </div>

                <!-- Salud Térmica -->
                <div class="bg-white border border-red-200 rounded-lg shadow-sm hover:shadow-md transition-shadow h-full flex flex-col">
                    <div class="p-5 flex-1">
                        <h5 class="text-lg font-medium text-red-800 mb-2"><i class="bi bi-thermometer-half"></i> Salud Térmica</h5>
                        <p class="text-gray-600 mb-4 text-sm">Diagnostica la aislación de tu hogar y recibe recomendaciones para evitar fugas de energía.</p>
                    </div>
                    <div class="px-5 py-3 bg-red-50 border-t border-red-100 rounded-b-lg">
                        @if($entities->isNotEmpty())
                            <a href="{{ route('thermal.index', $entities->first()->id) }}" class="text-red-600 hover:text-red-800 font-medium text-sm flex items-center">
                                Diagnóstico Térmico <i class="bi bi-arrow-right ml-1"></i>
                            </a>
                        @else
                            <span class="text-gray-400 text-sm cursor-not-allowed">Crear entidad primero</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
