@props([
    'padding' => true,
    'shadow' => true,
    'hover' => false,
])

@php
$classes = 'bg-white rounded-xl border border-gray-200';
if ($padding) $classes .= ' p-6';
if ($shadow) $classes .= ' shadow-sm';
if ($hover) $classes .= ' hover:shadow-md transition-shadow duration-200';
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if(isset($header))
        <div class="border-b border-gray-100 pb-4 mb-4">
            {{ $header }}
        </div>
    @endif

    {{ $slot }}

    @if(isset($footer))
        <div class="border-t border-gray-100 pt-4 mt-4">
            {{ $footer }}
        </div>
    @endif
</div>
