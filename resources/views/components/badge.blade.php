@props([
    'variant' => 'default',
    'size' => 'md',
    'dot' => false,
])

@php
$variants = [
    'default' => 'bg-gray-100 text-gray-800',
    'primary' => 'bg-emerald-100 text-emerald-800',
    'success' => 'bg-green-100 text-green-800',
    'warning' => 'bg-amber-100 text-amber-800',
    'danger' => 'bg-red-100 text-red-800',
    'info' => 'bg-blue-100 text-blue-800',
    'purple' => 'bg-purple-100 text-purple-800',
];

$sizes = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-1 text-xs',
    'lg' => 'px-3 py-1.5 text-sm',
];

$dotColors = [
    'default' => 'bg-gray-500',
    'primary' => 'bg-emerald-500',
    'success' => 'bg-green-500',
    'warning' => 'bg-amber-500',
    'danger' => 'bg-red-500',
    'info' => 'bg-blue-500',
    'purple' => 'bg-purple-500',
];

$classes = 'inline-flex items-center gap-1.5 font-medium rounded-full ' . 
           ($variants[$variant] ?? $variants['default']) . ' ' . 
           ($sizes[$size] ?? $sizes['md']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColors[$variant] ?? $dotColors['default'] }}"></span>
    @endif
    {{ $slot }}
</span>
