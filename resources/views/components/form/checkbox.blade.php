@props(['label', 'name', 'value' => 1])

<div class="flex items-center gap-2">
    <input id="{{ $name }}" type="checkbox" name="{{ $name }}" value="{{ $value }}"
        {{ $attributes->merge([
            'class' => 'rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500',
        ]) }}
        @checked(old($name))>
    <label for="{{ $name }}" class="text-sm text-gray-700 dark:text-gray-300">
        {{ $label }}
    </label>
</div>
@error($name)
    <p class="text-xs text-red-600">{{ $message }}</p>
@enderror
