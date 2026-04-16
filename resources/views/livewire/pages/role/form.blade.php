<div>
    <x-slot name="breadcrumb">
        <livewire:nawasara-ui.shared-components.breadcrumb :items="[
            ['label' => 'User Management', 'url' => '/'],
            ['label' => 'Role', 'url' => route('nawasara-core.role.index'), 'navigate' => true],
            ['label' => $id ? 'Edit' : 'Create New'],
        ]" />
    </x-slot>
    <x-nawasara-ui::page.container>

        <x-slot name="title">
            <x-nawasara-ui::page.title>{{ $id ? 'Update' : 'Create New' }} Role - Nawasara
                Core</x-nawasara-ui::page.title>
        </x-slot>

        @livewire('nawasara-core.role.section.role-permission-form', ['id' => $id])

    </x-nawasara-ui::page.container>
</div>
