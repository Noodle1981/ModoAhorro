@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'disabled' => false,
])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

$variants = [
    'primary' => 'bg-emerald-500 hover:bg-emerald-600 text-white focus:ring-emerald-500 shadow-sm hover:shadow-md',
    'secondary' => 'bg-gray-100 hover:bg-gray-200 text-gray-800 focus:ring-gray-400',
    'danger' => 'bg-red-500 hover:bg-red-600 text-white focus:ring-red-500',
    'warning' => 'bg-amber-500 hover:bg-amber-600 text-white focus:ring-amber-500',
    'success' => 'bg-green-500 hover:bg-green-600 text-white focus:ring-green-500',
    'outline' => 'border-2 border-emerald-500 text-emerald-600 hover:bg-emerald-50 focus:ring-emerald-500',
    'ghost' => 'text-gray-600 hover:bg-gray-100 focus:ring-gray-400',
];

$sizes = [
    'xs' => 'px-2.5 py-1.5 text-xs',
    'sm' => 'px-3 py-2 text-sm',
    'md' => 'px-4 py-2.5 text-sm',
    'lg' => 'px-5 py-3 text-base',
    'xl' => 'px-6 py-3.5 text-lg',
];

$classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes, 'disabled' => $disabled]) }}>
        {{ $slot }}
    </button>
@endif
