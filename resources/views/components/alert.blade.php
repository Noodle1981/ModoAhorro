@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
$types = [
    'info' => [
        'bg' => 'bg-blue-50',
        'border' => 'border-blue-200',
        'text' => 'text-blue-800',
        'icon' => 'bi-info-circle-fill',
        'iconColor' => 'text-blue-500',
    ],
    'success' => [
        'bg' => 'bg-emerald-50',
        'border' => 'border-emerald-200',
        'text' => 'text-emerald-800',
        'icon' => 'bi-check-circle-fill',
        'iconColor' => 'text-emerald-500',
    ],
    'warning' => [
        'bg' => 'bg-amber-50',
        'border' => 'border-amber-200',
        'text' => 'text-amber-800',
        'icon' => 'bi-exclamation-triangle-fill',
        'iconColor' => 'text-amber-500',
    ],
    'danger' => [
        'bg' => 'bg-red-50',
        'border' => 'border-red-200',
        'text' => 'text-red-800',
        'icon' => 'bi-x-circle-fill',
        'iconColor' => 'text-red-500',
    ],
];

$config = $types[$type] ?? $types['info'];
@endphp

<div {{ $attributes->merge(['class' => "rounded-lg border p-4 {$config['bg']} {$config['border']}"]) }}
     @if($dismissible) x-data="{ show: true }" x-show="show" x-transition @endif>
    <div class="flex items-start gap-3">
        <i class="bi {{ $config['icon'] }} {{ $config['iconColor'] }} text-lg flex-shrink-0 mt-0.5"></i>
        
        <div class="flex-1 {{ $config['text'] }}">
            @if($title)
                <h4 class="font-semibold mb-1">{{ $title }}</h4>
            @endif
            <div class="text-sm">
                {{ $slot }}
            </div>
        </div>

        @if($dismissible)
            <button @click="show = false" class="{{ $config['text'] }} hover:opacity-70 transition-opacity">
                <i class="bi bi-x-lg"></i>
            </button>
        @endif
    </div>
</div>
