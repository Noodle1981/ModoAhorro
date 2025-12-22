@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-blue-50 flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <img src="{{ asset('logo.png') }}" alt="Modo Ahorro" class="h-12 mx-auto mb-4">
            <h1 class="text-2xl font-bold text-gray-900">Bienvenido de vuelta</h1>
            <p class="text-gray-500">Ingresá a tu cuenta para continuar</p>
        </div>

        {{-- Login Card --}}
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 px-6 py-4">
                <h2 class="text-white font-semibold text-lg flex items-center gap-2">
                    <i class="bi bi-person-circle"></i>
                    Iniciar Sesión
                </h2>
            </div>

            {{-- Form --}}
            <div class="p-6">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

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
                                class="block w-full pl-10 pr-4 py-3 rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('email') border-red-300 @enderror"
                                placeholder="nombre@ejemplo.com"
                                value="{{ old('email') }}"
                                required autofocus>
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Contraseña
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="bi bi-lock text-gray-400"></i>
                            </div>
                            <input type="password" name="password" id="password" 
                                class="block w-full pl-10 pr-4 py-3 rounded-xl border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('password') border-red-300 @enderror"
                                placeholder="••••••••"
                                required>
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" 
                                class="rounded border-gray-300 text-emerald-500 focus:ring-emerald-500">
                            <span class="text-sm text-gray-600">Recordarme</span>
                        </label>
                        <a href="#" class="text-sm text-emerald-600 hover:text-emerald-700">¿Olvidaste tu contraseña?</a>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" 
                        class="w-full bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-semibold py-3 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Entrar
                    </button>
                </form>
            </div>
        </div>

        {{-- Register Link --}}
        <p class="text-center mt-6 text-gray-600">
            ¿No tenés cuenta? 
            <a href="{{ route('register') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">
                Registrate aquí
            </a>
        </p>
    </div>
</div>
@endsection
