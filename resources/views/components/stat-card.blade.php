@props([
    'title',
    'value',
    'subtitle' => null,
    'icon' => null,
    'trend' => null,
    'trendUp' => true,
    'color' => 'emerald',
])

@php
$colors = [
    'emerald' => 'from-emerald-500 to-emerald-600',
    'blue' => 'from-blue-500 to-blue-600',
    'amber' => 'from-amber-500 to-amber-600',
    'red' => 'from-red-500 to-red-600',
    'purple' => 'from-purple-500 to-purple-600',
    'gray' => 'from-gray-500 to-gray-600',
];
$gradient = $colors[$color] ?? $colors['emerald'];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow']) }}>
    <div class="flex items-start justify-between">
        <div class="space-y-2">
            <p class="text-sm font-medium text-gray-500">{{ $title }}</p>
            <p class="text-3xl font-bold text-gray-900">{{ $value }}</p>
            
            @if($subtitle)
                <p class="text-xs text-gray-500">{{ $subtitle }}</p>
            @endif

            @if($trend)
                <div class="flex items-center gap-1 text-sm {{ $trendUp ? 'text-emerald-600' : 'text-red-600' }}">
                    <i class="bi {{ $trendUp ? 'bi-arrow-up' : 'bi-arrow-down' }}"></i>
                    <span>{{ $trend }}</span>
                </div>
            @endif
        </div>

        @if($icon)
            <div class="bg-gradient-to-br {{ $gradient }} p-3 rounded-xl text-white shadow-lg">
                <i class="bi {{ $icon }} text-xl"></i>
            </div>
        @endif
    </div>
</div>
