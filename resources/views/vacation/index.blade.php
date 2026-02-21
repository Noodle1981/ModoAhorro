@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-card class="border-0 shadow-xl overflow-hidden">
            <div class="p-8 text-center">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-blue-100 rounded-full mb-6">
                    <i class="bi bi-airplane-engines text-5xl text-blue-600"></i>
                </div>
                
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Modo Vacaciones</h2>
                
                <p class="text-gray-600 text-lg mb-8 max-w-xl mx-auto">
                    El Asistente de Salida te ayudará a preparar tu casa para ahorrar energía y mantenerla segura mientras no estás.
                </p>

                <form action="{{ route('vacation.calculate', $entity->id) }}" method="POST" class="max-w-sm mx-auto">
                    @csrf
                    
                    <div class="mb-8">
                        <label for="days" class="block text-xl font-medium text-gray-700 mb-4">
                            ¿Por cuántos días te vas?
                        </label>
                        <div class="relative">
                            <input 
                                type="number" 
                                class="block w-full text-center text-4xl font-bold text-blue-600 border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500 p-4" 
                                id="days" 
                                name="days" 
                                placeholder="0" 
                                required 
                                min="1" 
                                autofocus
                            >
                            <span class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 font-medium">días</span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <x-button type="submit" variant="primary" size="lg" class="w-full justify-center text-lg py-4">
                            <i class="bi bi-magic mr-2"></i> Generar Plan de Ahorro
                        </x-button>
                        
                        <a href="{{ route('entities.index') }}" class="block w-full text-center text-gray-500 hover:text-gray-700 font-medium transition-colors">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
            
            <div class="bg-gray-50 px-8 py-4 border-t border-gray-100 flex items-center justify-center gap-2 text-sm text-gray-500">
                <i class="bi bi-info-circle"></i>
                <span>Ahorra hasta un 30% en tu factura durante tu ausencia.</span>
            </div>
        </x-card>
    </div>
</div>
@endsection
