<div>
    <x-slot name="breadcrumb">
        <livewire:nawasara-core.shared-components.breadcrumb :items="[
            ['label' => 'User Management', 'url' => '/'],
            ['label' => 'Role', 'url' => route('nawasara-core.role.index'), 'navigate' => true],
            ['label' => 'Create New'],
        ]" />
    </x-slot>
    <x-nawasara-core::page.container>

        <x-slot name="title">
            <x-nawasara-core::page.title>Form - Nawasara Core</x-nawasara-core::page.title>
        </x-slot>

        {{-- <x-slot name="actions">
            <x-nawasara-core::page.actions>
                <x-nawasara-core::button size="sm" color="primary" permission="nawasara-core.role.create"
                    @click="$dispatch('save-role')">Save Role
                    Permission</x-nawasara-core::button>
            </x-nawasara-core::page.actions>
        </x-slot> --}}

        @livewire('nawasara-core.pages.role.section.role-permission-form')

    </x-nawasara-core::page.container>
</div>
