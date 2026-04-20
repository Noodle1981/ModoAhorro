@props([
    'striped' => false,
    'hover' => true,
])

@php
$tableClasses = 'w-full text-sm text-left';
$headerClasses = 'text-xs text-gray-600 uppercase bg-gray-50 border-b border-gray-200';
$bodyClasses = 'divide-y divide-gray-100';
$rowClasses = $hover ? 'hover:bg-gray-50 transition-colors' : '';
if ($striped) $rowClasses .= ' even:bg-gray-50';
@endphp

<div class="overflow-x-auto rounded-xl border border-gray-200">
    <table {{ $attributes->merge(['class' => $tableClasses]) }}>
        @if(isset($head))
            <thead class="{{ $headerClasses }}">
                {{ $head }}
            </thead>
        @endif

        <tbody class="{{ $bodyClasses }}">
            {{ $slot }}
        </tbody>

        @if(isset($foot))
            <tfoot class="bg-gray-50 border-t border-gray-200">
                {{ $foot }}
            </tfoot>
        @endif
    </table>
</div>
