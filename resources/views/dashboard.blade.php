<x-nawasara-core::layouts.app>
    @push('profile-links')
        <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700 dark:focus:text-neutral-300"
            href="#">
            <x-lucide-user-round class="shrink-0 size-4" />
            Di inject dari dashboard.blade.php
        </a>
    @endpush

    <div class="p-6">
        <div class="bg-white dark:bg-neutral-800 shadow rounded-xl p-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-green-400">Selamat Datang di Nawasara Core</h1>
            <p class="mt-2 text-gray-600 dark:text-green-500">
                Ini adalah halaman default dashboard dari <span class="font-semibold">nawasara-core</span>.
            </p>
            <p class="mt-2 text-gray-500 text-sm dark:text-green-400">
                Anda bisa mengganti halaman ini dengan menonaktifkan `use_default_home`
                di <code>config/nawasara-core.php</code>.
            </p>
        </div>
    </div>
</x-nawasara-core::layouts.app>
