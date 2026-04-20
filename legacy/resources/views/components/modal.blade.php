@props([
    'name',
    'title' => '',
    'maxWidth' => 'md',
])

@php
$maxWidthClasses = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
];
$width = $maxWidthClasses[$maxWidth] ?? $maxWidthClasses['md'];
@endphp

<div 
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') open = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
    x-on:keydown.escape.window="open = false"
    {{ $attributes }}
>
    <!-- Trigger -->
    @if(isset($trigger))
        <div @click="open = true">
            {{ $trigger }}
        </div>
    @endif

    <!-- Modal Backdrop -->
    <div 
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
        @click="open = false"
        style="display: none;"
    ></div>

    <!-- Modal Content -->
    <div 
        x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display: none;"
    >
        <div class="bg-white rounded-xl shadow-xl w-full {{ $width }}" @click.stop>
            <!-- Header -->
            @if($title || isset($header))
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    @if(isset($header))
                        {{ $header }}
                    @else
                        <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                    @endif
                    <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="bi bi-x-lg text-xl"></i>
                    </button>
                </div>
            @endif

            <!-- Body -->
            <div class="px-6 py-4">
                {{ $slot }}
            </div>

            <!-- Footer -->
            @if(isset($footer))
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
