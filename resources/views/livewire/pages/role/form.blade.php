<div>

    <x-slot name="breadcrumb">
        <livewire:nawasara-core.shared-components.breadcrumb :items="[
            ['label' => 'User Management', 'url' => '/'],
            ['label' => 'Role', 'url' => '/'],
            ['label' => 'Create New'],
        ]" />
    </x-slot>
    <x-nawasara-core::page.container>

        <x-slot name="title">
            <x-nawasara-core::page.title>Form - Nawasara Core</x-nawasara-core::page.title>
        </x-slot>

        <p>Haloo form</p>
        {{-- @livewire('nawasara-core.pages.role.section.table') --}}

    </x-nawasara-core::page.container>
</div>
