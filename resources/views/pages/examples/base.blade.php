<x-nawasara-core::layouts.app>
    <x-slot:title>
        Blade Component - Nawasara Core
    </x-slot:title>
    <div class="p-6" x-data>
        <div class="bg-white shadow rounded-xl p-6">
            <h1 class="text-2xl font-bold text-gray-800">Blade component</h1>
            <p class="mt-2 text-gray-600">
                Semua blade component ada disini ya. <span class="font-semibold">nawasara-core</span>.
            </p>
        </div>

        @include('nawasara-core::pages.blade-component.button')
        @include('nawasara-core::pages.blade-component.button-group')
        @include('nawasara-core::pages.blade-component.toaster')
        @include('nawasara-core::pages.blade-component.modal')
    </div>

</x-nawasara-core::layouts.app>
