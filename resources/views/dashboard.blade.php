@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    {{-- High-Level Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
        <div>
            <div class="flex items-center gap-2 text-emerald-600 mb-2">
                <i class="bi bi-speedometer2 text-lg"></i>
                <span class="text-[10px] font-black uppercase tracking-widest">Resumen Operativo</span>
            </div>
            <h1 class="text-4xl font-black text-gray-900 tracking-tighter uppercase">Panel de <span class="text-indigo-600">Control</span></h1>
            <p class="text-gray-500 mt-2 font-medium">Gestionando eficientemente tus recursos energéticos, <span class="text-gray-900 font-bold">{{ $user->name }}</span>.</p>
        </div>
        <div class="shrink-0">
            @php
                $planColors = [
                    'Enterprise' => 'from-amber-400 to-amber-600 shadow-amber-100',
                    'Profesional' => 'from-emerald-400 to-emerald-600 shadow-emerald-100',
                    'Gratuito' => 'from-gray-100 to-gray-200 text-gray-600 shadow-none',
                ];
                $planColor = $planColors[$plan->name ?? 'Gratuito'] ?? 'from-gray-100 to-gray-200';
                $isLight = ($plan->name ?? '') === 'Gratuito';
            @endphp
            <div class="bg-linear-to-r {{ $planColor }} {{ !$isLight ? 'text-white shadow-xl' : '' }} px-6 py-3 rounded-2xl flex items-center gap-3 border border-white/20">
                <i class="bi bi-patch-check-fill text-xl"></i>
                <div>
                    <p class="text-[9px] font-black uppercase tracking-widest opacity-80 leading-none">Suscripción Actual</p>
                    <p class="text-sm font-black uppercase tracking-tighter mt-0.5">Plan {{ $plan->name ?? 'Gratuito' }}</p>
                </div>
            </div>
        </div>
    </div>


    {{-- My Entities --}}
    <section>
        <div class="flex items-center justify-between mb-8">
            <h2 class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] flex items-center gap-3">
                <span class="w-8 h-px bg-gray-200"></span>
                Mis Entidades Registradas
            </h2>
        </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
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

    </section>
</div>
@endsection
