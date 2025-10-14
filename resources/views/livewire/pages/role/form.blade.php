<div>
    <x-slot name="breadcrumb">
        <livewire:nawasara-core.shared-components.breadcrumb :items="[
            ['label' => 'User Management', 'url' => '/'],
            ['label' => 'Role', 'url' => route('nawasara-core.role.index'), 'navigate' => true],
            ['label' => $id ? 'Edit' : 'Create New'],
        ]" />
    </x-slot>
    <x-nawasara-core::page.container>

        <x-slot name="title">
            <x-nawasara-core::page.title>{{ $id ? 'Update' : 'Create New' }} Role - Nawasara
                Core</x-nawasara-core::page.title>
        </x-slot>

        @livewire('nawasara-core.pages.role.section.role-permission-form', ['id' => $id])

    </x-nawasara-core::page.container>
</div>
