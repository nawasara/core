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
        @include('nawasara-core::components.layouts.sidebar')
        <div class="ml-0 md:ml-64 flex flex-col min-h-screen">
            <div class="sticky top-0 z-30" style="margin-left:0;">
                @include('nawasara-core::components.layouts.navbar')
            </div>
            <main class="flex-1 p-6 md:p-8 lg:p-10 xl:p-12 pt-24">
                {{ $slot }}
            </main>
            @include('nawasara-core::components.layouts.footer')
            <livewire:nawasara-developer-tools.components.developer-tools />
        </div>
    </div>

    <x-nawasara-toaster::toaster position="top-right" :duration="5000" />
    <livewire:nawasara-core.components.universal-modal />

    @livewireScripts
</body>

</html>
