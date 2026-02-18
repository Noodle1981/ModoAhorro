@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
            <div class="flex items-center gap-4">
                <div class="bg-gradient-to-br from-sky-500 to-blue-700 w-14 h-14 rounded-2xl flex items-center justify-center text-white shadow-lg">
                    <i class="bi bi-airplane text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Plan de Vacaciones</h1>
                    <p class="text-gray-500">{{ $entity->name }} · {{ $days }} días</p>
                </div>
            </div>
            <div class="flex gap-3">
                <x-button variant="secondary" href="{{ route('vacation.index', $entity->id) }}">
                    <i class="bi bi-arrow-left mr-2"></i> Volver
                </x-button>
                <form action="{{ route('vacation.confirm', $entity->id) }}" method="POST"
                    onsubmit="return confirm('¿Confirmar viaje? Las facturas de este período se marcarán como Modo Vacaciones.');">
                    @csrf
                    <input type="hidden" name="days" value="{{ $days }}">
                    <x-button type="submit" variant="primary">
                        <i class="bi bi-check-lg mr-2"></i> Confirmar Viaje
                    </x-button>
                </form>
            </div>
        </div>

        {{-- Savings Banner --}}
        @if($result['total_savings'] > 0)
        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-2xl p-6 text-white text-center mb-8 shadow-lg">
            <p class="text-emerald-100 text-sm font-medium uppercase tracking-wide mb-1">Ahorro potencial si seguís esta lista</p>
            <p class="text-5xl font-bold">${{ number_format($result['total_savings'], 0, ',', '.') }}</p>
            <p class="text-emerald-100 text-sm mt-2">
                Tarifa usada: ${{ number_format($result['tariff_used'], 1) }}/kWh
                (basada en tu última factura)
            </p>
        </div>
        @endif

        {{-- Checklist --}}
        <div class="space-y-4 mb-8">
            @foreach($result['checklist'] as $item)
                @php
                    $bgColor = match($item['category']) {
                        'critical'       => 'bg-red-50 border-red-200',
                        'security'       => 'bg-gray-50 border-gray-200',
                        'recommendation' => 'bg-amber-50 border-amber-200',
                        default          => 'bg-emerald-50 border-emerald-200',
                    };
                    $iconColor = match($item['color']) {
                        'danger'  => 'text-red-500',
                        'warning' => 'text-amber-500',
                        'success' => 'text-emerald-500',
                        'info'    => 'text-sky-500',
                        default   => 'text-gray-500',
                    };
                    $badgeText = match($item['category']) {
                        'critical'       => 'Crítico',
                        'security'       => 'Seguridad',
                        'recommendation' => 'Recomendación',
                        default          => 'Ahorro',
                    };
                    $badgeColor = match($item['category']) {
                        'critical'       => 'bg-red-100 text-red-700',
                        'security'       => 'bg-gray-200 text-gray-700',
                        'recommendation' => 'bg-amber-100 text-amber-700',
                        default          => 'bg-emerald-100 text-emerald-700',
                    };
                @endphp
                <div class="bg-white border {{ $bgColor }} rounded-2xl p-5 flex items-start gap-4 shadow-sm">
                    {{-- Checkbox --}}
                    <input type="checkbox" class="mt-1 w-5 h-5 rounded accent-emerald-500 cursor-pointer flex-shrink-0">

                    {{-- Icon --}}
                    <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center flex-shrink-0">
                        <i class="bi {{ $item['icon'] }} text-xl {{ $iconColor }}"></i>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h3 class="font-semibold text-gray-900">{{ $item['title'] }}</h3>
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $badgeColor }}">{{ $badgeText }}</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">{{ $item['description'] }}</p>
                        <span class="inline-block text-xs font-bold uppercase tracking-wide px-3 py-1 rounded-lg
                            {{ $item['category'] === 'critical' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $item['action'] }}
                        </span>
                    </div>

                    {{-- Savings --}}
                    @if(isset($item['savings']) && $item['savings'] > 0)
                        <div class="text-right flex-shrink-0">
                            <p class="text-xs text-gray-400">Ahorro</p>
                            <p class="text-lg font-bold text-emerald-600">+${{ number_format($item['savings'], 0, ',', '.') }}</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Tips --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-alert type="info">
                <div class="flex items-start gap-3">
                    <i class="bi bi-lightbulb text-xl"></i>
                    <div>
                        <strong>Tip Pro:</strong> ¿Dejás luces por seguridad? Una luz fija las 24hs delata que no estás.
                        Usá un foco inteligente programado para prenderse solo de 20 a 23hs.
                    </div>
                </div>
            </x-alert>
            <x-alert type="warning">
                <div class="flex items-start gap-3">
                    <i class="bi bi-shield-check text-xl"></i>
                    <div>
                        <strong>Antes de cerrar:</strong>
                        <ul class="mt-1 space-y-0.5 text-sm">
                            <li>✓ Cerrar llave de paso de agua</li>
                            <li>✓ Cerrar llave de gas (si aplica)</li>
                            <li>✓ Tirar la basura</li>
                        </ul>
                    </div>
                </div>
            </x-alert>
        </div>

    </div>
</div>
@endsection
