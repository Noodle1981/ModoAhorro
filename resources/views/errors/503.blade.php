<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>503 - Servicio no Disponible | {{ config('app.name', 'Modo Ahorro') }}</title>
    
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
                <div class="inline-flex items-center justify-center w-32 h-32 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full mb-6">
                    <i class="bi bi-tools text-6xl text-blue-600"></i>
                </div>
            </div>

            <!-- Error Code -->
            <h1 class="text-8xl font-bold text-blue-600 mb-4">503</h1>
            
            <!-- Error Message -->
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Servicio en Mantenimiento</h2>
            <p class="text-lg text-gray-600 mb-8 max-w-md mx-auto">
                Estamos realizando tareas de mantenimiento para mejorar tu experiencia. Volveremos pronto.
            </p>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <button onclick="window.location.reload()" 
                       class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-semibold px-6 py-3 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                    <i class="bi bi-arrow-clockwise"></i>
                    Reintentar
                </button>
            </div>

            <!-- Maintenance Info -->
            <div class="mt-12 p-6 bg-white rounded-xl shadow-sm border border-gray-200">
                <h3 class="font-semibold text-gray-900 mb-2">¿Qué está pasando?</h3>
                <p class="text-sm text-gray-600">
                    Nuestro equipo está trabajando para mejorar el servicio. El mantenimiento debería finalizar pronto.
                </p>
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
