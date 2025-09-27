<div>

    <x-slot:title>
        Role - Nawasara Core
    </x-slot:title>

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


        <x-slot name="actions">
            <x-nawasara-core::page.actions>
                <nawasara-core::button>Create New</nawasara-core::button>
            </x-nawasara-core::page.actions>
        </x-slot>

        @livewire('nawasara-core.pages.role.table')

    </x-nawasara-core::page.container>
</div>
