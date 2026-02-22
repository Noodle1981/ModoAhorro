<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Modo Ahorro') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="#" class="flex-shrink-0 flex items-center">
                        <img src="{{ asset('logo.png') }}" alt="Modo Ahorro Logo" class="h-10 w-auto mr-2">
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-blue-600 hover:text-blue-800 font-medium px-4 py-2 border border-blue-600 rounded-md transition">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-md transition">Iniciar Sesión</a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="bg-white py-16 sm:py-24 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-2 lg:gap-8 items-center">
                <div class="mb-12 lg:mb-0">
                    <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl mb-6">
                        Toma el control de tu consumo energético
                    </h1>
                    <p class="text-lg text-gray-500 mb-8 max-w-2xl">
                        Gestiona tus facturas, monitorea el consumo de tus electrodomésticos y optimiza tus gastos con Modo Ahorro.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition">
                                Ingresar Demo
                            </a>
                        @endif
                        <a href="#features" class="inline-flex justify-center items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition">
                            Saber más
                        </a>
                    </div>
                </div>
                <div class="relative mx-auto w-full rounded-lg shadow-lg lg:max-w-md text-center bg-gray-100 flex items-center justify-center overflow-hidden" style="min-height: 250px;">
                    <img src="https://placehold.co/600x400/e9ecef/495057?text=Dashboard+Preview" alt="App Preview" class="w-full h-auto object-cover">
                </div>
            </div>
        </div>
    </header>

    <!-- Features Section -->
    <section id="features" class="py-16 sm:py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl">¿Por qué usar Modo Ahorro?</h2>
                <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-500">Herramientas diseñadas para ayudarte a ahorrar dinero y energía.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center transition hover:shadow-md">
                    <div class="text-blue-500 mb-4 inline-flex items-center justify-center h-16 w-16 rounded-full bg-blue-50">
                        <i class="bi bi-bar-chart-fill text-3xl"></i>
                    </div>
                    <h5 class="text-xl font-bold text-gray-900 mb-2">Monitoreo de Consumo</h5>
                    <p class="text-gray-500">Visualiza gráficos detallados de tu consumo eléctrico y detecta patrones de uso.</p>
                </div>
                <!-- Feature 2 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center transition hover:shadow-md">
                    <div class="text-green-500 mb-4 inline-flex items-center justify-center h-16 w-16 rounded-full bg-green-50">
                        <i class="bi bi-cash-coin text-3xl"></i>
                    </div>
                    <h5 class="text-xl font-bold text-gray-900 mb-2">Gestión de Facturas</h5>
                    <p class="text-gray-500">Registra tus facturas y compáralas mes a mes para evitar sorpresas.</p>
                </div>
                <!-- Feature 3 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center transition hover:shadow-md">
                    <div class="text-teal-500 mb-4 inline-flex items-center justify-center h-16 w-16 rounded-full bg-teal-50">
                        <i class="bi bi-house-gear-fill text-3xl"></i>
                    </div>
                    <h5 class="text-xl font-bold text-gray-900 mb-2">Control de Equipos</h5>
                    <p class="text-gray-500">Administra tus electrodomésticos y conoce cuánto consume cada uno.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p class="text-gray-400 text-sm">&copy; {{ date('Y') }} Modo Ahorro. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>
</html>
