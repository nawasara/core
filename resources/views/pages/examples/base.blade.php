<x-nawasara-core::layouts.app>
    <x-slot:title>
        Blade Component - Nawasara Core
    </x-slot:title>

    <x-slot name="breadcrumb">
        <livewire:nawasara-core.shared-components.breadcrumb :items="[['label' => 'Dashboard', 'url' => '/'], ['label' => 'Base Component', 'url' => '/']]" />
    </x-slot>
    <x-nawasara-core::page.container>

        <x-slot name="title">
            <x-nawasara-core::page.title>Blade Component - Nawasara Core</x-nawasara-core::page.title>
        </x-slot>
        <x-nawasara-core::page.card>
            @include('nawasara-core::pages.blade-component.button')
            @include('nawasara-core::pages.blade-component.button-group')
            @include('nawasara-core::pages.blade-component.toaster')
            @include('nawasara-core::pages.blade-component.modal')
        </x-nawasara-core::page.card>
    </x-nawasara-core::page.container>

</x-nawasara-core::layouts.app>
