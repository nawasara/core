@props([
    'label' => null,
    'name',
    'rows' => 3,
    'placeholder' => '',
    'hint' => null,
])

<div class="flex flex-col gap-1">
    @if ($label)
        <label for="{{ $name }}" class="text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
        </label>
    @endif

    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
        {{ $attributes->merge([
            'class' => 'w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
        ]) }}>{{ old($name) }}</textarea>

    @if ($hint)
        <p class="text-xs text-gray-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
