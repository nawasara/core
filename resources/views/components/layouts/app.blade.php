<!DOCTYPE html>
<html lang="en" class="">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">



    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Toaster --}}
    @if (session('toast'))
        <script>
            window.Laravel = window.Laravel || {};
            window.Laravel.toast = @json(session('toast'));
        </script>
    @endif
    <script src="{{ asset('vendor/nawasara-toaster/js/toaster.js') }}"></script>
    {{-- End Toaster --}}

    @livewireStyles
</head>

<body>

    <div class="min-h-screen bg-gray-100 dark:bg-gray-950">
        @include('nawasara-core::components.layouts.navbar')

        <!-- Page Content -->
        <main class="container max-w-screen-xl p-4 mx-auto pt-20">
            {{ $slot }}
        </main>
        <!-- End Page Content -->

        @include('nawasara-core::components.layouts.footer')
        <livewire:nawasara-core.components.developer-tools />
    </div>

    <x-nawasara-toaster::toaster position="top-right" :duration="5000" />
    <livewire:nawasara-core.components.universal-modal />

    @livewireScripts
</body>

</html>
