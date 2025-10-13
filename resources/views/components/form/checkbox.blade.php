@props(['label', 'name', 'value' => 1])

@php
    // Allow callers to pass a `class` that targets the wrapper. Remove it from
    // the attributes forwarded to the input itself so it doesn't get duplicated.
$wrapperClass = $attributes->get('class') ?? '';
$inputAttributes = $attributes->except('class');
$inputDefaultClasses = 'rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500';
@endphp

<div class="flex items-center gap-2 {{ $wrapperClass }}">
    <input id="{{ $name }}" type="checkbox" name="{{ $name }}" value="{{ $value }}"
        {{ $inputAttributes->merge([
            'class' => $inputDefaultClasses,
        ]) }}
        @checked(old($name))>
    <label for="{{ $name }}" class="text-sm text-gray-700 dark:text-gray-300">
        {{ $label }}
    </label>
</div>
@error($name)
    <p class="text-xs text-red-600">{{ $message }}</p>
@enderror
