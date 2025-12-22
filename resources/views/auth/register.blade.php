@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-emerald-50 flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <img src="{{ asset('logo.png') }}" alt="Modo Ahorro" class="h-12 mx-auto mb-4">
            <h1 class="text-2xl font-bold text-gray-900">Creá tu cuenta</h1>
            <p class="text-gray-500">Comenzá a optimizar tu consumo energético</p>
        </div>

        {{-- Register Card --}}
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                <h2 class="text-white font-semibold text-lg flex items-center gap-2">
                    <i class="bi bi-person-plus-fill"></i>
                    Crear Cuenta
                </h2>
            </div>

            {{-- Form --}}
            <div class="p-6">
                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    {{-- Name --}}
                    <div class="mb-5">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre Completo
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="bi bi-person text-gray-400"></i>
                            </div>
                            <input type="text" name="name" id="name" 
                                class="block w-full pl-10 pr-4 py-3 rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-300 @enderror"
                                placeholder="Ej: Juan Pérez"
                                value="{{ old('name') }}"
                                required autofocus>
                        </div>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-5">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Correo Electrónico
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="bi bi-envelope text-gray-400"></i>
                            </div>
                            <input type="email" name="email" id="email" 
                                class="block w-full pl-10 pr-4 py-3 rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-300 @enderror"
                                placeholder="nombre@ejemplo.com"
                                value="{{ old('email') }}"
                                required>
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-5">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Contraseña
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="bi bi-lock text-gray-400"></i>
                            </div>
                            <input type="password" name="password" id="password" 
                                class="block w-full pl-10 pr-4 py-3 rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('password') border-red-300 @enderror"
                                placeholder="Mínimo 8 caracteres"
                                required>
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirmar Contraseña
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="bi bi-lock-fill text-gray-400"></i>
                            </div>
                            <input type="password" name="password_confirmation" id="password_confirmation" 
                                class="block w-full pl-10 pr-4 py-3 rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Repetí tu contraseña"
                                required>
                        </div>
                    </div>

                    {{-- Terms --}}
                    <div class="mb-6">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="terms" required
                                class="mt-0.5 rounded border-gray-300 text-blue-500 focus:ring-blue-500">
                            <span class="text-sm text-gray-600">
                                Acepto los <a href="#" class="text-blue-600 hover:underline">Términos de Uso</a> 
                                y la <a href="#" class="text-blue-600 hover:underline">Política de Privacidad</a>
                            </span>
                        </label>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-3 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2">
                        <i class="bi bi-person-plus"></i>
                        Crear Cuenta
                    </button>
                </form>
            </div>
        </div>

        {{-- Login Link --}}
        <p class="text-center mt-6 text-gray-600">
            ¿Ya tenés cuenta? 
            <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-medium">
                Iniciá sesión
            </a>
        </p>

        {{-- Features --}}
        <div class="mt-8 grid grid-cols-3 gap-4 text-center">
            <div class="p-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <i class="bi bi-lightning-charge text-emerald-600"></i>
                </div>
                <p class="text-xs text-gray-500">Analizá consumo</p>
            </div>
            <div class="p-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <i class="bi bi-graph-up text-blue-600"></i>
                </div>
                <p class="text-xs text-gray-500">Visualizá datos</p>
            </div>
            <div class="p-3">
                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                    <i class="bi bi-piggy-bank text-amber-600"></i>
                </div>
                <p class="text-xs text-gray-500">Ahorrá dinero</p>
            </div>
        </div>
    </div>
</div>
@endsection
