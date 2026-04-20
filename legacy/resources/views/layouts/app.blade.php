<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'ModoAhorro') }}</title>
    <meta name="description" content="Gestión inteligente de ahorro energético para hogares y empresas.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine focus plugin (required for some UI components) -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>

    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-50/50 text-gray-900 selection:bg-indigo-100 selection:text-indigo-700">
    
    <div class="min-h-screen flex flex-col lg:flex-row">
        
        {{-- Sidebar (Desktop) --}}
        @unless(request()->routeIs('dashboard'))
            <aside class="hidden lg:block border-r border-gray-100 dark:border-gray-800">
                @include('layouts.sidebar')
            </aside>
        @endunless

        {{-- Mobile Header --}}
        @if(request()->routeIs('dashboard'))
            <header class="lg:hidden sticky top-0 z-50 bg-white border-b border-gray-100 p-4 flex justify-center">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-linear-to-br from-indigo-500 to-indigo-700 rounded-lg flex items-center justify-center text-white shadow-sm">
                        <i class="bi bi-lightning-charge-fill text-sm"></i>
                    </div>
                    <span class="text-sm font-black text-gray-900 tracking-tighter uppercase">Modo<span class="text-indigo-600">Ahorro</span></span>
                </a>
            </header>
        @else
            <header class="lg:hidden sticky top-0 z-50">
                @include('layouts.navbar')
            </header>
        @endif

        {{-- Main Layout --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            {{-- Top Bar (Desktop) --}}
            <header class="hidden lg:block sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-8 h-16 flex items-center justify-between">
                    <div class="flex items-center gap-8">
                        @if(request()->routeIs('dashboard'))
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                                <div class="w-10 h-10 bg-linear-to-br from-indigo-500 to-indigo-700 rounded-xl flex items-center justify-center text-white shadow-lg group-hover:scale-105 transition-transform">
                                    <i class="bi bi-lightning-charge-fill text-lg"></i>
                                </div>
                                <span class="text-lg font-black text-gray-900 tracking-tighter uppercase leading-none block">Modo<span class="text-indigo-600">Ahorro</span></span>
                            </a>
                        @else
                            <div class="flex items-center gap-4 text-gray-400">
                                <i class="bi bi-search text-lg"></i>
                                <span class="text-sm font-medium">Pulsa '/' para buscar...</span>
                            </div>
                        @endif
                    </div>
                    <div class="flex items-center gap-6">
                        @include('layouts.user-dropdown')
                    </div>
                </div>
            </header>

            <main class="flex-1 relative overflow-y-auto focus:outline-none">
                {{-- Flash Messages --}}
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                    @if(session('success'))
                        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-4 py-3 rounded-2xl relative shadow-sm mb-4" role="alert">
                            <span class="block sm:inline font-bold">{{ session('success') }}</span>
                        </div>
                    @endif
                </div>

                <div class="pb-12 text-gray-900">
                    @isset($slot)
                        {{ $slot }}
                    @else
                        @yield('content')
                    @endisset
                </div>
                
                @include('layouts.footer')
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
