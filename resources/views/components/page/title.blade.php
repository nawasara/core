@php
    $titleText = trim((string) $slot);
@endphp

@section('nawasaraTitle', $titleText)

<h1 class="text-2xl font-bold text-gray-800 dark:text-white">
    {{ $slot }}
</h1>
