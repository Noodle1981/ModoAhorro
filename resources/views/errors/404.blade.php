<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Página no encontrada | {{ config('app.name', 'Modo Ahorro') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-emerald-50 via-white to-blue-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="container mx-auto px-4 py-4">
            <a href="/" class="flex items-center gap-2">
                <img src="{{ asset('logo.png') }}" alt="Modo Ahorro Logo" class="h-10">
            </a>
        </div>
    </nav>

    <!-- Error Content -->
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="max-w-2xl w-full text-center">
            <!-- Error Icon -->
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-32 h-32 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-full mb-6">
                    <i class="bi bi-exclamation-triangle text-6xl text-emerald-600"></i>
                </div>
            </div>

            <!-- Error Code -->
            <h1 class="text-8xl font-bold text-emerald-600 mb-4">404</h1>
            
            <!-- Error Message -->
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Página no encontrada</h2>
            <p class="text-lg text-gray-600 mb-8 max-w-md mx-auto">
                Lo sentimos, la página que estás buscando no existe o ha sido movida.
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                @auth
                    <a href="{{ route('dashboard') }}" 
                       class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-semibold px-6 py-3 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                        <i class="bi bi-speedometer2"></i>
                        Ir al Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-semibold px-6 py-3 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Iniciar Sesión
                    </a>
                @endauth
                
                <a href="/" 
                   class="inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-gray-700 font-semibold px-6 py-3 rounded-xl border-2 border-gray-200 hover:border-emerald-300 transition-all duration-200">
                    <i class="bi bi-house"></i>
                    Volver al Inicio
                </a>
            </div>

            <!-- Helpful Links -->
            <div class="mt-12 pt-8 border-t border-gray-200">
                <p class="text-sm text-gray-500 mb-4">¿Necesitás ayuda? Intentá con estos enlaces:</p>
                <div class="flex flex-wrap gap-4 justify-center text-sm">
                    @auth
                        <a href="{{ route('entities.home.index') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">
                            <i class="bi bi-house-door mr-1"></i> Mis Hogares
                        </a>
                        <a href="{{ route('equipment.index') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">
                            <i class="bi bi-plug mr-1"></i> Equipos
                        </a>
                        <a href="{{ route('consumption.panel') }}" class="text-emerald-600 hover:text-emerald-700 font-medium">
                            <i class="bi bi-bar-chart mr-1"></i> Consumo
                        </a>
                    @else
                        <a href="/#features" class="text-emerald-600 hover:text-emerald-700 font-medium">
                            <i class="bi bi-info-circle mr-1"></i> Características
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-6 bg-white border-t border-gray-200 mt-auto">
        <div class="container mx-auto text-center">
            <p class="text-gray-600 text-sm">&copy; {{ date('Y') }} Modo Ahorro. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>
</html>
