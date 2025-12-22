@props([
    'label' => null,
    'name',
    'type' => 'text',
    'value' => null,
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'helper' => null,
    'error' => null,
])

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->merge([
            'class' => 'block w-full rounded-lg border-gray-300 shadow-sm transition-colors duration-200 
                       focus:border-emerald-500 focus:ring-emerald-500 
                       disabled:bg-gray-50 disabled:text-gray-500
                       ' . ($error ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300')
        ]) }}
    />

    @if($helper && !$error)
        <p class="text-xs text-gray-500">{{ $helper }}</p>
    @endif

    @if($error)
        <p class="text-xs text-red-600">{{ $error }}</p>
    @endif

    @error($name)
        <p class="text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
