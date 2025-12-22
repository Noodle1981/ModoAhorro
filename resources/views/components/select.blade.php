@props([
    'label' => null,
    'name',
    'options' => [],
    'selected' => null,
    'placeholder' => 'Seleccionar...',
    'required' => false,
    'disabled' => false,
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

    <select
        name="{{ $name }}"
        id="{{ $name }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->merge([
            'class' => 'block w-full rounded-lg shadow-sm transition-colors duration-200 
                       focus:border-emerald-500 focus:ring-emerald-500 
                       disabled:bg-gray-50 disabled:text-gray-500
                       ' . ($error ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300')
        ]) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach($options as $value => $label)
            <option value="{{ $value }}" @if(old($name, $selected) == $value) selected @endif>
                {{ $label }}
            </option>
        @endforeach

        {{ $slot }}
    </select>

    @if($error)
        <p class="text-xs text-red-600">{{ $error }}</p>
    @endif

    @error($name)
        <p class="text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
