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

    <x-nawasara-toaster::script />
    @livewireStyles
</head>

<body x-data class="dark:bg-neutral-900">
    <livewire:nawasara-core.shared-components.topbar />
    <livewire:nawasara-core.shared-components.breadcrumb />
    <livewire:nawasara-core.shared-components.sidebar />


    <!-- Content -->
    <div class="w-full lg:ps-64">
        <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
            {{ $slot }}
        </div>
    </div>
    <!-- End Content -->

    <livewire:nawasara-developer-tools.components.developer-tools />
    <x-nawasara-toaster::toaster position="top-right" :duration="5000" />
    <livewire:nawasara-core.components.universal-modal />

    @livewireScripts
</body>

</html>
