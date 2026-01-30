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
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#">
                <img src="{{ asset('logo.png') }}" alt="Modo Ahorro Logo" style="height: 100px;" class="d-inline-block align-text-top me-2">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav gap-2">
                    @if (Route::has('login'))
                        @auth
                            <li class="nav-item">
                                <a href="{{ url('/dashboard') }}" class="btn btn-outline-primary">Dashboard</a>
                            </li>
                        @else
                            <li class="nav-item">
                                <a href="{{ route('login') }}" class="btn btn-outline-primary">Iniciar Sesión</a>
                            </li>
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a href="{{ route('register') }}" class="btn btn-primary">Registrarse</a>
                                </li>
                            @endif
                        @endauth
                    @endif
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="py-5 bg-white border-bottom">
        <div class="container py-5">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-3">Toma el control de tu consumo energético</h1>
                    <p class="lead text-muted mb-4">
                        Gestiona tus facturas, monitorea el consumo de tus electrodomésticos y optimiza tus gastos con Modo Ahorro.
                    </p>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-4 me-md-2">Comenzar Gratis</a>
                        @endif
                        <a href="#features" class="btn btn-outline-secondary btn-lg px-4">Saber más</a>
                    </div>
                </div>
                <div class="col-lg-6 mt-4 mt-lg-0 text-center">
                    <img src="https://placehold.co/600x400/e9ecef/495057?text=Dashboard+Preview" alt="App Preview" class="img-fluid rounded shadow-lg">
                </div>
            </div>
        </div>
    </header>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container py-5">
            <div class="text-center mb-5">
                <h2 class="fw-bold">¿Por qué usar Modo Ahorro?</h2>
                <p class="text-muted">Herramientas diseñadas para ayudarte a ahorrar dinero y energía.</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm text-center p-4">
                        <div class="text-primary mb-3">
                            <i class="bi bi-bar-chart-fill fs-1"></i>
                        </div>
                        <h5 class="card-title fw-bold">Monitoreo de Consumo</h5>
                        <p class="card-text text-muted">Visualiza gráficos detallados de tu consumo eléctrico y detecta patrones de uso.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm text-center p-4">
                        <div class="text-success mb-3">
                            <i class="bi bi-cash-coin fs-1"></i>
                        </div>
                        <h5 class="card-title fw-bold">Gestión de Facturas</h5>
                        <p class="card-text text-muted">Registra tus facturas y compáralas mes a mes para evitar sorpresas.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm text-center p-4">
                        <div class="text-info mb-3">
                            <i class="bi bi-house-gear-fill fs-1"></i>
                        </div>
                        <h5 class="card-title fw-bold">Control de Equipos</h5>
                        <p class="card-text text-muted">Administra tus electrodomésticos y conoce cuánto consume cada uno.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 bg-dark text-white">
        <div class="container text-center">
            <p class="mb-0">&copy; {{ date('Y') }} Modo Ahorro. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>
</html>
