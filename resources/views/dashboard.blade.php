@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                    <span class="bg-gradient-to-r from-emerald-500 to-emerald-600 w-10 h-10 rounded-xl flex items-center justify-center text-white shadow-lg">
                        <i class="bi bi-speedometer2"></i>
                    </span>
                    Dashboard
                </h1>
                <p class="text-gray-500 mt-1">¡Bienvenido, <span class="font-medium text-gray-700">{{ $user->name }}</span>!</p>
            </div>
            <div class="mt-4 md:mt-0">
                @php
                    $planColors = [
                        'Enterprise' => 'bg-gradient-to-r from-amber-400 to-amber-500',
                        'Profesional' => 'bg-gradient-to-r from-emerald-400 to-emerald-500',
                        'Gratuito' => 'bg-gray-100 text-gray-600',
                    ];
                    $planColor = $planColors[$plan->name ?? 'Gratuito'] ?? 'bg-gray-100 text-gray-600';
                    $isLight = ($plan->name ?? '') === 'Gratuito';
                @endphp
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full {{ $planColor }} {{ !$isLight ? 'text-white' : '' }} font-medium shadow-sm">
                    <i class="bi bi-star-fill"></i>
                    Plan {{ $plan->name ?? 'Gratuito' }}
                </span>
            </div>
        </div>

        {{-- Stats Overview --}}
        <section class="mb-10">
            <livewire:dashboard.stats-overview />
        </section>

        {{-- My Entities --}}
        <section class="mb-10">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                    <i class="bi bi-collection text-emerald-500"></i>
                    Mis Entidades
                </h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($entitiesByType as $type => $data)
                    <livewire:dashboard.entity-type-card 
                        :type="$type" 
                        :config="$data['config']" 
                        :allowed="$data['allowed']"
                        :key="$type"
                    />
                @endforeach
            </div>
        </section>

        {{-- Quick Access --}}
        <section>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                    <i class="bi bi-lightning-charge text-amber-500"></i>
                    Acceso Rápido
                </h2>
            </div>
            
            <livewire:dashboard.quick-access-panel />
        </section>

    </div>
</div>
@endsection
