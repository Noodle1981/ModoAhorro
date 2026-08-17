<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        <!-- Primary Meta Tags -->
        <title>ModoAhorro — Software de Análisis, Diagnóstico y Eficiencia Energética</title>
        <meta name="title" content="ModoAhorro — Software de Análisis, Diagnóstico y Eficiencia Energética">
        <meta name="description" content="Descubrí exactamente qué equipo consumió cada peso de tu factura de luz. Plataforma de inteligencia y diagnóstico energético para hogares, oficinas y comercios sin hardware.">
        <meta name="keywords" content="eficiencia energética, ahorro de energía, calcular consumo eléctrico, factura de luz argentina, consumo standby, proyectos solares fotovoltaicos, auditoria energética hogar comercio">
        <meta name="author" content="ModoAhorro">
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
        <meta name="theme-color" content="#059669">
        <link rel="canonical" href="{{ url()->current() }}">

        <!-- Open Graph / Facebook / WhatsApp -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:site_name" content="ModoAhorro">
        <meta property="og:title" content="ModoAhorro — Inteligencia y Diagnóstico Energético">
        <meta property="og:description" content="Descubrí qué artefactos consumen cada peso de tu factura de luz. Diagnóstico preciso sin instalar sensores costosos.">
        <meta property="og:image" content="{{ asset('images/landing/logo.png') }}">
        <meta property="og:locale" content="es_AR">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ url()->current() }}">
        <meta name="twitter:title" content="ModoAhorro — Inteligencia y Diagnóstico Energético">
        <meta name="twitter:description" content="Descubrí qué artefactos consumen cada peso de tu factura de luz. Diagnóstico preciso sin sensores.">
        <meta name="twitter:image" content="{{ asset('images/landing/logo.png') }}">

        <!-- Favicons -->
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <link rel="icon" type="image/png" href="/favicon.png">
        <link rel="apple-touch-icon" href="/favicon.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Structured Data (JSON-LD) -->
        <script type="application/ld+json">
        {
            "@@context": "https://schema.org",
            "@@graph": [
                {
                    "@@type": "SoftwareApplication",
                    "name": "ModoAhorro",
                    "applicationCategory": "BusinessApplication, UtilitiesApplication",
                    "operatingSystem": "Web Browser",
                    "description": "Plataforma de software para análisis, conciliación de facturas eléctricas y diagnóstico de eficiencia energética.",
                    "offers": {
                        "@@type": "Offer",
                        "price": "0",
                        "priceCurrency": "ARS"
                    }
                },
                {
                    "@@type": "Organization",
                    "name": "ModoAhorro",
                    "url": "{{ url('/') }}",
                    "logo": "{{ asset('images/landing/logo.png') }}"
                }
            ]
        }
        </script>

        <!-- Vite CSS -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css'])
        @else
            <style>
                body { font-family: 'Inter', sans-serif; }
            </style>
        @endif
    </head>
    <body class="bg-slate-50 text-slate-900 font-sans antialiased selection:bg-emerald-500 selection:text-white min-h-screen flex flex-col">

        <!-- 1. NAVIGATION (Sticky Header) -->
        <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-100 transition-all">
            <div class="max-w-6xl mx-auto px-6 h-20 flex items-center justify-between">
                <!-- Logo Brand -->
                <a href="/" class="flex items-center gap-3 group">
                    <img src="/images/landing/logo.png" alt="ModoAhorro Logo" class="h-10 w-auto object-contain transition-transform group-hover:scale-105" />
                    <span class="text-xl font-extrabold tracking-tight text-slate-900">
                        Modo<span class="text-emerald-600">Ahorro</span>
                    </span>
                </a>

                <!-- Navigation Links -->
                <nav class="flex items-center gap-4">
                    <a href="#como-funciona" class="hidden md:inline-block text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors px-3 py-2">
                        Cómo funciona
                    </a>
                    <a href="#entidades" class="hidden md:inline-block text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors px-3 py-2">
                        Entidades
                    </a>
                    <a href="#carrusel-metricas" class="hidden md:inline-block text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors px-3 py-2">
                        Análisis
                    </a>
                    <a href="#capturas-sistema" class="hidden md:inline-block text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors px-3 py-2">
                        Panel
                    </a>
                    <a href="#recomendaciones" class="hidden md:inline-block text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors px-3 py-2">
                        Recomendaciones
                    </a>
                    <a href="#rubros" class="hidden md:inline-block text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors px-3 py-2">
                        Rubros
                    </a>

                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm px-5 py-2.5 rounded-xl shadow-md shadow-emerald-600/20 transition-all hover:-translate-y-0.5">
                                Ir al panel →
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-bold text-slate-700 hover:text-emerald-600 transition-colors px-4 py-2">
                                Iniciar sesión
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ Route::has('register') ? route('register') : route('login') }}" class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm px-5 py-2.5 rounded-xl shadow-md shadow-emerald-600/20 transition-all hover:-translate-y-0.5">
                                    Registrarme gratis
                                </a>
                            @endif
                        @endauth
                    @endif
                </nav>
            </div>
        </header>

        <main class="flex-1">

            <!-- 2. HERO SECTION (High Impact Split Layout) -->
            <section class="bg-white py-16 lg:py-24 border-b border-slate-100 overflow-hidden relative">
                <div class="max-w-6xl mx-auto px-6">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                        
                        <!-- Left Column: Copy & CTAs -->
                        <div class="lg:col-span-7 text-left">
                            <span class="text-xs font-black uppercase tracking-widest text-emerald-600 block mb-4">
                                Control Total de Consumo
                            </span>

                            <h1 class="text-4xl sm:text-5xl lg:text-[3.25rem] font-black text-slate-900 tracking-tight leading-[1.12]">
                                Dejá de adivinar por qué vino alta tu factura. <br class="hidden sm:inline" />
                                <span class="text-emerald-600">Sabé qué equipo consumió cada peso.</span>
                            </h1>

                            <p class="mt-6 text-base sm:text-lg text-slate-600 font-medium leading-relaxed max-w-xl">
                                ModoAhorro concilia tus facturas reales de luz con tus artefactos físicos, clima zonal y horarios operativos. Sin suposiciones ni sensores costosos.
                            </p>

                            <!-- CTAs -->
                            <div class="mt-8 flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                                @if (Route::has('register'))
                                    <a href="{{ Route::has('register') ? route('register') : route('login') }}" class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-base px-8 py-4 rounded-2xl shadow-lg shadow-emerald-600/25 transition-all hover:-translate-y-0.5 text-center">
                                        Registrarme gratis →
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-base px-8 py-4 rounded-2xl shadow-lg shadow-emerald-600/25 transition-all hover:-translate-y-0.5 text-center">
                                        Ingresar a la plataforma →
                                    </a>
                                @endif
                                <a href="#como-funciona" class="inline-flex items-center justify-center border border-slate-200 hover:border-slate-300 bg-white text-slate-700 font-bold text-base px-7 py-4 rounded-2xl shadow-xs transition-all hover:bg-slate-50 text-center">
                                    Ver cómo funciona
                                </a>
                            </div>

                            <!-- Trust Micro-Badges -->
                            <div class="mt-8 pt-6 border-t border-slate-100 flex flex-wrap items-center gap-y-2 gap-x-6 text-xs font-semibold text-slate-500">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                    Sin instalar hardware
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                    Hogares, Oficinas y Comercios
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                    Diagnóstico en minutos
                                </span>
                            </div>
                        </div>

                        <!-- Right Column: Layered Floating Preview Mockup -->
                        <div class="lg:col-span-5 relative mt-4 lg:mt-0">
                            
                            <!-- Ambient Glow Effect -->
                            <div class="absolute -inset-4 bg-gradient-to-tr from-emerald-500/15 to-teal-500/10 rounded-3xl blur-2xl -z-10 transform -rotate-1"></div>

                            <!-- Main Window Card -->
                            <div class="bg-slate-900 rounded-3xl p-3 sm:p-4 shadow-2xl border border-slate-800 transform lg:rotate-1 hover:rotate-0 transition-transform duration-500">
                                
                                <!-- Window Header Bar -->
                                <div class="flex items-center justify-between pb-3 px-2 border-b border-slate-800 mb-3">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-2.5 h-2.5 rounded-full bg-rose-500/80"></div>
                                        <div class="w-2.5 h-2.5 rounded-full bg-amber-500/80"></div>
                                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500/80"></div>
                                    </div>
                                    <span class="text-[11px] font-bold text-slate-400">ModoAhorro · Análisis en Vivo</span>
                                    <span class="w-8"></span>
                                </div>

                                <!-- Window Screenshot Image -->
                                <div class="rounded-xl overflow-hidden bg-slate-950 border border-slate-800 shadow-inner">
                                    <img src="/images/screenshots/impacto_equipo_1.png" alt="Desglose por equipo en vivo" class="w-full h-auto object-cover" />
                                </div>
                            </div>

                            <!-- Floating Badge 1 (Bottom Left) -->
                            <div class="absolute -bottom-6 -left-4 sm:-left-6 bg-white/95 backdrop-blur-md p-3.5 sm:p-4 rounded-2xl border border-slate-200/80 shadow-xl flex items-center gap-3.5 transform -rotate-2 hover:rotate-0 transition-transform">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-400 leading-none uppercase tracking-wider">Ahorro detectado</p>
                                    <p class="text-base font-black text-slate-900 mt-1">-$24.500 <span class="text-xs font-bold text-emerald-600">/ mes</span></p>
                                </div>
                            </div>

                            <!-- Floating Badge 2 (Top Right) -->
                            <div class="hidden sm:flex absolute -top-5 -right-4 bg-white/95 backdrop-blur-md py-2.5 px-4 rounded-2xl border border-slate-200/80 shadow-lg items-center gap-2.5 transform rotate-2 hover:rotate-0 transition-transform">
                                <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                <span class="text-xs font-bold text-slate-700">Calibración 98.4%</span>
                            </div>

                        </div>

                    </div>
                </div>
            </section>


            <!-- 3. CÓMO FUNCIONA (Directamente debajo del Hero) -->
            <section id="como-funciona" class="py-20 lg:py-28 bg-slate-50 border-b border-slate-100">
                <div class="max-w-6xl mx-auto px-6">
                    
                    <div class="text-center max-w-2xl mx-auto mb-16">
                        <span class="text-xs font-black uppercase tracking-widest text-emerald-600 block mb-3">
                            Metodología de Análisis
                        </span>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                            Así funciona el motor de análisis
                        </h2>
                        <p class="mt-4 text-slate-600 font-medium text-base sm:text-lg">
                            En 4 pasos estructurados obtenés la radiografía de consumo de tus instalaciones.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 relative">
                        
                        <!-- Paso 1 -->
                        <div class="bg-white rounded-3xl p-6 border border-slate-200/70 shadow-sm relative hover:shadow-md transition-all">
                            <span class="text-5xl font-black text-emerald-200/80 block mb-4">01</span>
                            <h3 class="text-lg font-bold text-slate-900">Cargás tu factura</h3>
                            <p class="mt-2 text-sm text-slate-600 font-medium leading-relaxed">
                                Subís los datos clave de tu factura de luz o gas. Sin integraciones bancarias ni accesos complejos.
                            </p>
                        </div>

                        <!-- Paso 2 -->
                        <div class="bg-white rounded-3xl p-6 border border-slate-200/70 shadow-sm relative hover:shadow-md transition-all">
                            <span class="text-5xl font-black text-emerald-200/80 block mb-4">02</span>
                            <h3 class="text-lg font-bold text-slate-900">Registrás tus equipos</h3>
                            <p class="mt-2 text-sm text-slate-600 font-medium leading-relaxed">
                                Indicás los artefactos principales: heladeras, aires, iluminación. El catálogo ya incluye potencias estándar.
                            </p>
                        </div>

                        <!-- Paso 3 -->
                        <div class="bg-white rounded-3xl p-6 border border-slate-200/70 shadow-sm relative hover:shadow-md transition-all">
                            <span class="text-5xl font-black text-emerald-200/80 block mb-4">03</span>
                            <h3 class="text-lg font-bold text-slate-900">El motor analiza</h3>
                            <p class="mt-2 text-sm text-slate-600 font-medium leading-relaxed">
                                El sistema distribuye el consumo real de la factura usando datos climáticos de tu zona y horarios operativos.
                            </p>
                        </div>

                        <!-- Paso 4 -->
                        <div class="bg-white rounded-3xl p-6 border border-slate-200/70 shadow-sm relative hover:shadow-md transition-all">
                            <span class="text-5xl font-black text-emerald-200/80 block mb-4">04</span>
                            <h3 class="text-lg font-bold text-slate-900">Ves dónde está tu dinero</h3>
                            <p class="mt-2 text-sm text-slate-600 font-medium leading-relaxed">
                                Recibís un desglose puntual de qué consume cada equipo y recomendaciones concretas para optimizar.
                            </p>
                        </div>

                    </div>

                </div>
            </section>


            <!-- 4. ENTITY TYPES ("Un sistema. Tres mundos.") -->
            <section id="entidades" class="py-20 lg:py-28 bg-white border-b border-slate-100">
                <div class="max-w-6xl mx-auto px-6">
                    
                    <div class="text-center max-w-2xl mx-auto mb-16">
                        <span class="text-xs font-black uppercase tracking-widest text-emerald-600 block mb-3">
                            Modelos de Consumo
                        </span>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                            Un sistema. Tres mundos.
                        </h2>
                        <p class="mt-4 text-slate-600 font-medium text-base sm:text-lg">
                            Ya seas propietario, empleado o comerciante, el sistema entiende tu contexto y ajusta sus métricas.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        
                        <!-- Card 1: Hogar -->
                        <div class="bg-slate-50 rounded-3xl p-8 border border-slate-200/70 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative flex flex-col justify-between border-t-4 border-t-emerald-500">
                            <div>
                                <div class="w-16 h-16 rounded-2xl bg-white border border-emerald-100 shadow-xs flex items-center justify-center mb-6">
                                    <img src="/images/entities/logo_hogar.png" alt="Eficiencia energética en el Hogar" class="w-12 h-12 object-contain" loading="lazy" decoding="async" />
                                </div>
                                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Residencial</span>
                                <h3 class="text-2xl font-black text-slate-900 mt-1">Tu hogar</h3>
                                <p class="mt-3 text-slate-600 text-sm font-medium leading-relaxed">
                                    Analizamos tu consumo doméstico equipo por equipo: heladeras, aires acondicionados, calefones. Sabés exactamente qué consume cada uno y dónde ahorrar.
                                </p>
                            </div>
                            <div class="mt-8 pt-6 border-t border-slate-200/60">
                                <a href="{{ Route::has('register') ? route('register') : route('login') }}" class="inline-flex items-center text-sm font-bold text-emerald-600 hover:text-emerald-800 transition-colors">
                                    Empezar en mi hogar →
                                </a>
                            </div>
                        </div>

                        <!-- Card 2: Oficina -->
                        <div class="bg-slate-50 rounded-3xl p-8 border border-slate-200/70 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative flex flex-col justify-between border-t-4 border-t-blue-500">
                            <div>
                                <div class="w-16 h-16 rounded-2xl bg-white border border-blue-100 shadow-xs flex items-center justify-center mb-6">
                                    <img src="/images/entities/logo_oficina.png" alt="Control de consumo en Oficinas" class="w-12 h-12 object-contain" loading="lazy" decoding="async" />
                                </div>
                                <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Corporativo</span>
                                <h3 class="text-2xl font-black text-slate-900 mt-1">Tu oficina</h3>
                                <p class="mt-3 text-slate-600 text-sm font-medium leading-relaxed">
                                    Control de consumo por área, por turno laboral y por infraestructura clave. Diseñado para equipos de trabajo que necesitan números reales.
                                </p>
                            </div>
                            <div class="mt-8 pt-6 border-t border-slate-200/60">
                                <a href="{{ Route::has('register') ? route('register') : route('login') }}" class="inline-flex items-center text-sm font-bold text-blue-600 hover:text-blue-800 transition-colors">
                                    Empezar en mi oficina →
                                </a>
                            </div>
                        </div>

                        <!-- Card 3: Comercio -->
                        <div class="bg-slate-50 rounded-3xl p-8 border border-slate-200/70 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative flex flex-col justify-between border-t-4 border-t-purple-500">
                            <div>
                                <div class="w-16 h-16 rounded-2xl bg-white border border-purple-100 shadow-xs flex items-center justify-center mb-6">
                                    <img src="/images/entities/logo_comercio.png" alt="Gestión de energía en Comercios" class="w-12 h-12 object-contain" loading="lazy" decoding="async" />
                                </div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-purple-600 uppercase tracking-wider">Comercial</span>
                                    <span class="text-[10px] font-extrabold uppercase px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-700 tracking-wider">Beta Privada</span>
                                </div>
                                <h3 class="text-2xl font-black text-slate-900 mt-1">Tu comercio</h3>
                                <p class="mt-3 text-slate-600 text-sm font-medium leading-relaxed">
                                    El sistema entiende tu rubro. Diseñamos perfiles a medida según tu actividad comercial (gastronomía, cámaras de frío, servicios).
                                </p>
                            </div>
                            <div class="mt-8 pt-6 border-t border-slate-200/60">
                                <a href="https://wa.me/5492644533704?text=Hola!%20Me%20interesa%20solicitar%20un%20perfil%20a%20medida%20para%20mi%20comercio%20en%20ModoAhorro" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-sm font-bold text-purple-600 hover:text-purple-800 transition-colors">
                                    Consultar por mi rubro →
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </section>


            <!-- 5. MOSTRARIO DE MÉTRICAS (GRID DE ANÁLISIS) -->
            <section id="carrusel-metricas" class="py-20 lg:py-28 bg-slate-50 border-b border-slate-100">
                <div class="max-w-6xl mx-auto px-6">
                    
                    <div class="text-center max-w-3xl mx-auto mb-16">
                        <span class="text-xs font-black uppercase tracking-widest text-emerald-600 block mb-3">
                            Desglose Granular
                        </span>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                            Visualizá tus datos energéticos como nunca antes
                        </h2>
                        <p class="mt-4 text-slate-600 font-medium text-base sm:text-lg">
                            El motor convierte facturas complejas en gráficos claros y tomables para la acción.
                        </p>
                    </div>

                    <!-- Grilla de Métricas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        
                        <!-- Card 1 -->
                        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                            <div class="bg-slate-50 rounded-2xl overflow-hidden border border-slate-100 p-2 shadow-xs">
                                <img src="/images/carousel/eficiencia_motor.png" alt="Gráfico de eficiencia y calibración del motor energético" class="w-full h-auto rounded-xl object-cover" loading="lazy" decoding="async" />
                            </div>
                            <div class="mt-4 px-2">
                                <h3 class="text-base font-extrabold text-slate-900">Eficiencia y Calibración del Motor</h3>
                                <p class="text-xs font-medium text-slate-500 mt-1">Evaluación continua de precisión y calibración entre teórico y facturado.</p>
                            </div>
                        </div>

                        <!-- Card 2 -->
                        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                            <div class="bg-slate-50 rounded-2xl overflow-hidden border border-slate-100 p-2 shadow-xs">
                                <img src="/images/carousel/composicion_tanques.png" alt="Gráfico de composición por tanques de consumo energético" class="w-full h-auto rounded-xl object-cover" loading="lazy" decoding="async" />
                            </div>
                            <div class="mt-4 px-2">
                                <h3 class="text-base font-extrabold text-slate-900">Composición por Tanques de Consumo</h3>
                                <p class="text-xs font-medium text-slate-500 mt-1">Clasificación en Certeza, Base Inmutable, Clima y Elasticidad.</p>
                            </div>
                        </div>

                        <!-- Card 3 -->
                        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                            <div class="bg-slate-50 rounded-2xl overflow-hidden border border-slate-100 p-2 shadow-xs">
                                <img src="/images/carousel/consumo_vs_temperatura.png" alt="Correlación entre consumo de energía eléctrica y temperatura climática" class="w-full h-auto rounded-xl object-cover" loading="lazy" decoding="async" />
                            </div>
                            <div class="mt-4 px-2">
                                <h3 class="text-base font-extrabold text-slate-900">Correlación Consumo vs. Clima</h3>
                                <p class="text-xs font-medium text-slate-500 mt-1">Impacto de días grado y variaciones térmicas de la zona.</p>
                            </div>
                        </div>

                        <!-- Card 4 -->
                        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                            <div class="bg-slate-50 rounded-2xl overflow-hidden border border-slate-100 p-2 shadow-xs">
                                <img src="/images/carousel/desglose_tanque.png" alt="Desglose detallado por artefacto y tanque energético" class="w-full h-auto rounded-xl object-cover" loading="lazy" decoding="async" />
                            </div>
                            <div class="mt-4 px-2">
                                <h3 class="text-base font-extrabold text-slate-900">Desglose Detallado por Tanque</h3>
                                <p class="text-xs font-medium text-slate-500 mt-1">Desglose granular de cada artefacto y categoría asignada.</p>
                            </div>
                        </div>

                        <!-- Card 5 -->
                        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                            <div class="bg-slate-50 rounded-2xl overflow-hidden border border-slate-100 p-2 shadow-xs">
                                <img src="/images/carousel/evolucion_costos.png" alt="Evolución temporal de costos y proyección de ahorro en facturas" class="w-full h-auto rounded-xl object-cover" loading="lazy" decoding="async" />
                            </div>
                            <div class="mt-4 px-2">
                                <h3 class="text-base font-extrabold text-slate-900">Evolución Temporal de Costos</h3>
                                <p class="text-xs font-medium text-slate-500 mt-1">Seguimiento de tarifas, gasto estimado y proyección de ahorro.</p>
                            </div>
                        </div>

                        <!-- Card 6 -->
                        <div class="bg-white border border-slate-200/80 rounded-3xl p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                            <div class="bg-slate-50 rounded-2xl overflow-hidden border border-slate-100 p-2 shadow-xs">
                                <img src="/images/carousel/brecha_eficiencia.png" alt="Diferencial y brecha de eficiencia energética" class="w-full h-auto rounded-xl object-cover" loading="lazy" decoding="async" />
                            </div>
                            <div class="mt-4 px-2">
                                <h3 class="text-base font-extrabold text-slate-900">Brecha de Eficiencia Energética</h3>
                                <p class="text-xs font-medium text-slate-500 mt-1">Diferencial entre consumo óptimo proyectado y consumo real registrado.</p>
                            </div>
                        </div>

                    </div>

                </div>
            </section>


            <!-- 6. CAPTURAS REALES DEL SISTEMA (GRAN CARRUSEL) -->
            <section id="capturas-sistema" class="py-20 lg:py-28 bg-white border-b border-slate-100 overflow-hidden">
                <div class="max-w-6xl mx-auto px-6">
                    
                    <div class="text-center max-w-3xl mx-auto mb-16">
                        <span class="text-xs font-black uppercase tracking-widest text-emerald-600 block mb-3">
                            Experiencia de Usuario
                        </span>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                            Un panel diseñado para la claridad
                        </h2>
                        <p class="mt-4 text-slate-600 font-medium text-base sm:text-lg">
                            Sin sobrecarga de información. Deslizá por las vistas principales de la plataforma en alta resolución.
                        </p>
                    </div>

                    <!-- Gran Carrusel Container -->
                    <div class="relative max-w-5xl mx-auto">
                        
                        <!-- Track de scroll horizontal del Gran Carrusel -->
                        <div id="screenshots-track" class="flex gap-8 overflow-x-auto snap-x snap-mandatory scroll-smooth pb-8 pt-2 no-scrollbar scrollbar-none" style="scrollbar-width: none; -ms-overflow-style: none;">
                            
                            <!-- Slide 1: Impacto 1 -->
                            <div class="snap-center shrink-0 w-full bg-slate-900 rounded-3xl p-4 sm:p-6 shadow-2xl border border-slate-800 flex flex-col">
                                <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-2">
                                    <div>
                                        <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Módulo de Análisis</span>
                                        <h3 class="text-xl sm:text-2xl font-black text-white mt-0.5">Impacto y Desglose por Equipo</h3>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-300 bg-slate-800 px-3 py-1 rounded-lg self-start sm:self-auto border border-slate-700">Vista en vivo</span>
                                </div>
                                <div class="rounded-2xl overflow-hidden bg-slate-950 border border-slate-800 shadow-inner">
                                    <img src="/images/screenshots/impacto_equipo_1.png" alt="Captura del panel de ModoAhorro: Desglose de consumo por equipo y artefacto" class="w-full h-auto object-cover" loading="lazy" decoding="async" />
                                </div>
                            </div>

                            <!-- Slide 2: Impacto 2 -->
                            <div class="snap-center shrink-0 w-full bg-slate-900 rounded-3xl p-4 sm:p-6 shadow-2xl border border-slate-800 flex flex-col">
                                <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-2">
                                    <div>
                                        <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Métricas Detalladas</span>
                                        <h3 class="text-xl sm:text-2xl font-black text-white mt-0.5">Curva de Desempeño y Calibración</h3>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-300 bg-slate-800 px-3 py-1 rounded-lg self-start sm:self-auto border border-slate-700">Análisis granular</span>
                                </div>
                                <div class="rounded-2xl overflow-hidden bg-slate-950 border border-slate-800 shadow-inner">
                                    <img src="/images/screenshots/impacto_equipo_2.png" alt="Captura de curva de calibración energética y consumo atípico" class="w-full h-auto object-cover" loading="lazy" decoding="async" />
                                </div>
                            </div>

                            <!-- Slide 3: Infraestructura -->
                            <div class="snap-center shrink-0 w-full bg-slate-900 rounded-3xl p-4 sm:p-6 shadow-2xl border border-slate-800 flex flex-col">
                                <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-2">
                                    <div>
                                        <span class="text-xs font-bold text-blue-400 uppercase tracking-wider">Gestión Física</span>
                                        <h3 class="text-xl sm:text-2xl font-black text-white mt-0.5">Gestión de Ambientes e Infraestructura</h3>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-300 bg-slate-800 px-3 py-1 rounded-lg self-start sm:self-auto border border-slate-700">Carga rápida</span>
                                </div>
                                <div class="rounded-2xl overflow-hidden bg-slate-950 border border-slate-800 shadow-inner">
                                    <img src="/images/screenshots/infraestructura.png" alt="Captura de gestión de ambientes e infraestructura eléctrica" class="w-full h-auto object-cover" loading="lazy" decoding="async" />
                                </div>
                            </div>

                            <!-- Slide 4: Selector de Entidades -->
                            <div class="snap-center shrink-0 w-full bg-slate-900 rounded-3xl p-4 sm:p-6 shadow-2xl border border-slate-800 flex flex-col">
                                <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-2">
                                    <div>
                                        <span class="text-xs font-bold text-purple-400 uppercase tracking-wider">Multi-Entidad</span>
                                        <h3 class="text-xl sm:text-2xl font-black text-white mt-0.5">Selector y Conmutación de Entidades</h3>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-300 bg-slate-800 px-3 py-1 rounded-lg self-start sm:self-auto border border-slate-700">Multi-entidad</span>
                                </div>
                                <div class="rounded-2xl overflow-hidden bg-slate-950 border border-slate-800 shadow-inner">
                                    <img src="/images/screenshots/selector_entidades.png" alt="Captura del selector y administrador multi-entidad de ModoAhorro" class="w-full h-auto object-cover" loading="lazy" decoding="async" />
                                </div>
                            </div>

                        </div>

                        <!-- Botones de Navegación del Gran Carrusel -->
                        <div class="flex items-center justify-between mt-6 px-2">
                            <button id="screenshots-prev" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-700 font-bold text-sm transition-all shadow-xs cursor-pointer">
                                ← Anterior
                            </button>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest hidden sm:inline">Deslizá para explorar vistas</span>
                            <button id="screenshots-next" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-700 font-bold text-sm transition-all shadow-xs cursor-pointer">
                                Siguiente →
                            </button>
                        </div>

                    </div>

                </div>
            </section>


            <!-- 7. RECOMENDACIONES INTELIGENTES -->
            <section id="recomendaciones" class="py-20 lg:py-28 bg-slate-50 border-b border-slate-100">
                <div class="max-w-6xl mx-auto px-6">
                    
                    <div class="text-center max-w-3xl mx-auto mb-16">
                        <span class="text-xs font-black uppercase tracking-widest text-emerald-600 block mb-3">
                            Módulos de Ahorro y Eficiencia
                        </span>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                            De los datos a la acción: Ahorro real y cuantificado
                        </h2>
                        <p class="mt-4 text-slate-600 font-medium text-base sm:text-lg">
                            El sistema no da consejos genéricos. Modela proyecciones financieras, retorno de inversión y medidas de optimización específicas para tu caso.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        
                        <!-- Card 1: Solar -->
                        <div class="bg-white rounded-3xl p-8 border border-slate-200/70 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                            <div>
                                <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-100 text-amber-600 flex items-center justify-center mb-6">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
                                </div>
                                <span class="text-xs font-bold text-amber-600 uppercase tracking-wider">Generación Limpia</span>
                                <h3 class="text-xl font-bold text-slate-900 mt-1">Proyecto Solar Fotovoltaico</h3>
                                <p class="mt-3 text-sm text-slate-600 font-medium leading-relaxed">
                                    Dimensionamiento de paneles según tu curva de consumo real, cálculo de amortización y porcentaje de cobertura energética.
                                </p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-slate-100 text-xs font-bold text-slate-400">
                                Retorno de inversión estimado en años
                            </div>
                        </div>

                        <!-- Card 2: Reemplazos -->
                        <div class="bg-white rounded-3xl p-8 border border-slate-200/70 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                            <div>
                                <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center mb-6">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>
                                </div>
                                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Eficiencia de Equipos</span>
                                <h3 class="text-xl font-bold text-slate-900 mt-1">Reemplazos Eficientes</h3>
                                <p class="mt-3 text-sm text-slate-600 font-medium leading-relaxed">
                                    Comparativa de ahorro al renovar artefactos antiguos por tecnología Inverter o etiquetas Clase A+++ con cálculo exacto de ROI.
                                </p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-slate-100 text-xs font-bold text-slate-400">
                                Análisis de costo-beneficio directo
                            </div>
                        </div>

                        <!-- Card 3: Standby -->
                        <div class="bg-white rounded-3xl p-8 border border-slate-200/70 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                            <div>
                                <div class="w-14 h-14 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center mb-6">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                </div>
                                <span class="text-xs font-bold text-rose-600 uppercase tracking-wider">Consumo Standby</span>
                                <h3 class="text-xl font-bold text-slate-900 mt-1">Consumo Fantasma (Standby)</h3>
                                <p class="mt-3 text-sm text-slate-600 font-medium leading-relaxed">
                                    Detección de pérdidas parásitas en artefactos enchufados en reposo y su impacto económico acumulado a fin de mes.
                                </p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-slate-100 text-xs font-bold text-slate-400">
                                Ahorro inmediato sin inversión
                            </div>
                        </div>

                        <!-- Card 4: Grid Optimization -->
                        <div class="bg-white rounded-3xl p-8 border border-slate-200/70 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                            <div>
                                <div class="w-14 h-14 rounded-2xl bg-blue-50 border border-blue-100 text-blue-600 flex items-center justify-center mb-6">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </div>
                                <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Gestión Tarifaria</span>
                                <h3 class="text-xl font-bold text-slate-900 mt-1">Optimización de Horarios</h3>
                                <p class="mt-3 text-sm text-slate-600 font-medium leading-relaxed">
                                    Estrategias para desplazar el funcionamiento de equipos de gran potencia hacia bandas horarias con tarifas más económicas.
                                </p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-slate-100 text-xs font-bold text-slate-400">
                                Aprovechamiento de tarifas pico y valle
                            </div>
                        </div>

                        <!-- Card 5: Thermal Comfort -->
                        <div class="bg-white rounded-3xl p-8 border border-slate-200/70 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                            <div>
                                <div class="w-14 h-14 rounded-2xl bg-teal-50 border border-teal-100 text-teal-600 flex items-center justify-center mb-6">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z"/></svg>
                                </div>
                                <span class="text-xs font-bold text-teal-600 uppercase tracking-wider">Confort y Aislación</span>
                                <h3 class="text-xl font-bold text-slate-900 mt-1">Salud y Desempeño Térmico</h3>
                                <p class="mt-3 text-sm text-slate-600 font-medium leading-relaxed">
                                    Evaluación del aislamiento de tus ambientes, inercia térmica y medidas pasivas para reducir el uso de climatización.
                                </p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-slate-100 text-xs font-bold text-slate-400">
                                Diagnóstico de pérdidas de calor y frío
                            </div>
                        </div>

                        <!-- Card 6: Maintenance & Vacations -->
                        <div class="bg-white rounded-3xl p-8 border border-slate-200/70 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                            <div>
                                <div class="w-14 h-14 rounded-2xl bg-purple-50 border border-purple-100 text-purple-600 flex items-center justify-center mb-6">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                                </div>
                                <span class="text-xs font-bold text-purple-600 uppercase tracking-wider">Mantenimiento & Ausencias</span>
                                <h3 class="text-xl font-bold text-slate-900 mt-1">Mantenimiento y Vacaciones</h3>
                                <p class="mt-3 text-sm text-slate-600 font-medium leading-relaxed">
                                    Seguimiento de limpieza de filtros, service periódico y planes de consumo mínimo durante periodos de cierre o vacaciones.
                                </p>
                            </div>
                            <div class="mt-6 pt-4 border-t border-slate-100 text-xs font-bold text-slate-400">
                                Prevención de sobreconsumos por desgaste
                            </div>
                        </div>

                    </div>

                </div>
            </section>


            <!-- 8. MÓDULOS POR RUBRO -->
            <section id="rubros" class="py-20 lg:py-28 bg-white border-b border-slate-100">
                <div class="max-w-6xl mx-auto px-6">
                    
                    <div class="text-center max-w-3xl mx-auto mb-16">
                        <span class="text-xs font-black uppercase tracking-widest text-purple-600 block mb-3">
                            Adaptabilidad Comercial
                        </span>
                        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                            El sistema entiende tu negocio
                        </h2>
                        <p class="mt-4 text-slate-600 font-medium text-base sm:text-lg">
                            No todos los comercios consumen igual. Con el motor de ModoAhorro podemos diseñar perfiles específicos por rubro.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                        
                        <!-- Rubro 1 -->
                        <div class="bg-slate-50 rounded-3xl p-8 border border-slate-200/80 shadow-xs flex items-start gap-5">
                            <div class="w-14 h-14 rounded-2xl bg-white border border-amber-200/80 text-amber-600 flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M18 2v6a3 3 0 0 1-3 3 3 3 0 0 1-3-3V2"/><path d="M15 11v11"/><path d="M5 2v4a3 3 0 0 0 3 3 3 3 0 0 0 3-3V2"/><path d="M8 9v13"/></svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">Gastronomía</h3>
                                <p class="mt-2 text-sm text-slate-600 font-medium leading-relaxed">
                                    Prioriza refrigeración comercial, campanas de extracción y altas cargas de consumo en horarios de servicio.
                                </p>
                            </div>
                        </div>

                        <!-- Rubro 2 -->
                        <div class="bg-slate-50 rounded-3xl p-8 border border-slate-200/80 shadow-xs flex items-start gap-5">
                            <div class="w-14 h-14 rounded-2xl bg-white border border-blue-200/80 text-blue-600 flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">Retail & Comercio General</h3>
                                <p class="mt-2 text-sm text-slate-600 font-medium leading-relaxed">
                                    Ajusta el análisis según el flujo de clientes, horarios comerciales continuos y densidad de iluminación.
                                </p>
                            </div>
                        </div>

                        <!-- Rubro 3 -->
                        <div class="bg-slate-50 rounded-3xl p-8 border border-slate-200/80 shadow-xs flex items-start gap-5">
                            <div class="w-14 h-14 rounded-2xl bg-white border border-indigo-200/80 text-indigo-600 flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="9" y1="22" x2="9" y2="22.01"/><line x1="15" y1="22" x2="15" y2="22.01"/><line x1="8" y1="6" x2="8" y2="6.01"/><line x1="16" y1="6" x2="16" y2="6.01"/><line x1="8" y1="10" x2="8" y2="10.01"/><line x1="16" y1="10" x2="16" y2="10.01"/><line x1="8" y1="14" x2="8" y2="14.01"/><line x1="16" y1="14" x2="16" y2="14.01"/><line x1="8" y1="18" x2="8" y2="18.01"/><line x1="16" y1="18" x2="16" y2="18.01"/></svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">Oficinas & Servicios</h3>
                                <p class="mt-2 text-sm text-slate-600 font-medium leading-relaxed">
                                    Evalúa la carga de climatización, servidores, puestos de trabajo y standby fuera de horas de oficina.
                                </p>
                            </div>
                        </div>

                        <!-- Rubro 4 -->
                        <div class="bg-slate-50 rounded-3xl p-8 border border-slate-200/80 shadow-xs flex items-start gap-5">
                            <div class="w-14 h-14 rounded-2xl bg-white border border-purple-200/80 text-purple-600 flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="2" x2="12" y2="22"/><line x1="2" y1="12" x2="22" y2="12"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/><line x1="19.07" y1="4.93" x2="4.93" y2="19.07"/></svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">Heladerías y Frío Comercial</h3>
                                <p class="mt-2 text-sm text-slate-600 font-medium leading-relaxed">
                                    Contempla vitrinas de frío permanente y alta sensibilidad a picos estacionales de temperatura exterior.
                                </p>
                            </div>
                        </div>

                    </div>

                    <!-- Nota al pie -->
                    <div class="mt-12 text-center">
                        <div class="inline-flex items-center gap-2 bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold px-5 py-2.5 rounded-full shadow-xs">
                            <span class="text-emerald-600 font-extrabold">Configuración a medida:</span>
                            <span>El motor de tanques se adapta a las particularidades de tu rubro comercial.</span>
                        </div>
                    </div>

                </div>
            </section>


            <!-- 9. PROGRAMA PILOTO / ACCESO ANTICIPADO -->
            <section class="py-20 lg:py-28 bg-slate-50">
                <div class="max-w-4xl mx-auto px-6">
                    <div class="bg-white rounded-3xl p-8 sm:p-14 border border-slate-200 shadow-sm text-center relative overflow-hidden">
                        
                        <span class="text-xs font-black uppercase tracking-widest text-emerald-600 block mb-4">
                            Acceso Anticipado
                        </span>

                        <h2 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                            Probá la plataforma en tus espacios
                        </h2>

                        <p class="mt-6 text-slate-600 text-base sm:text-lg font-medium leading-relaxed max-w-2xl mx-auto">
                            El sistema está operativo y en validación continua. Al sumarte al programa de adopción temprana, contás con acceso a las herramientas de análisis y soporte prioritario.
                        </p>

                        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-base px-8 py-4 rounded-2xl shadow-lg shadow-emerald-600/25 transition-all hover:-translate-y-0.5">
                                Ingresar a la plataforma →
                            </a>
                            <a href="https://wa.me/5492644533704?text=Hola!%20Me%20gustaria%20solicitar%20un%20acceso%20beta%20a%20ModoAhorro" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center border border-slate-200 hover:border-slate-300 bg-white text-slate-700 font-bold text-base px-7 py-4 rounded-2xl shadow-xs transition-all hover:bg-slate-50">
                                Solicitar invitación
                            </a>
                        </div>

                    </div>
                </div>
            </section>

        </main>


        <!-- 10. FOOTER -->
        <footer class="bg-white border-t border-slate-100 py-12">
            <div class="max-w-6xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-6">
                
                <div class="flex items-center gap-3">
                    <img src="/images/landing/logo.png" alt="ModoAhorro Logo" class="h-8 w-auto object-contain" />
                    <span class="text-base font-extrabold tracking-tight text-slate-900">
                        Modo<span class="text-emerald-600">Ahorro</span>
                    </span>
                </div>

                <div class="text-xs font-semibold text-slate-500 text-center sm:text-right">
                    <span>ModoAhorro · Plataforma de Eficiencia Energética</span>
                    <span class="block text-slate-400 mt-1">Argentina</span>
                </div>

            </div>
        </footer>

        <!-- Script liviano para el Carrusel de Capturas -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const track = document.getElementById('screenshots-track');
                const prevBtn = document.getElementById('screenshots-prev');
                const nextBtn = document.getElementById('screenshots-next');

                if (track && prevBtn && nextBtn) {
                    prevBtn.addEventListener('click', function () {
                        track.scrollBy({ left: -track.clientWidth, behavior: 'smooth' });
                    });
                    nextBtn.addEventListener('click', function () {
                        track.scrollBy({ left: track.clientWidth, behavior: 'smooth' });
                    });
                }
            });
        </script>
    </body>
</html>
