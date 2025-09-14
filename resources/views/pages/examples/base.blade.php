<x-nawasara-core::layouts.app>
    <x-slot:title>
        Blade Component - Nawasara Core
    </x-slot:title>

    <x-nawasara-core::layouts.container>

        <x-slot name="title">
            <x-nawasara-core::layouts.page-title>Blade Component - Nawasara Core</x-nawasara-core::layouts.page-title>
        </x-slot>
        <x-nawasara-core::layouts.card>
            @include('nawasara-core::pages.blade-component.button')
            @include('nawasara-core::pages.blade-component.button-group')
            @include('nawasara-core::pages.blade-component.toaster')
            @include('nawasara-core::pages.blade-component.modal')
        </x-nawasara-core::layouts.card>
    </x-nawasara-core::layouts.container>

</x-nawasara-core::layouts.app>
