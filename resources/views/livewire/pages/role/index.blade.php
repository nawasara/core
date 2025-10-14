<div>

    <x-slot name="breadcrumb">
        <livewire:nawasara-core.shared-components.breadcrumb :items="[
            ['label' => 'User Management', 'url' => '/'],
            ['label' => 'Role', 'url' => route('nawasara-core.role.index'), 'navigate' => true],
        ]" />
    </x-slot>
    <x-nawasara-core::page.container>

        <x-slot name="title">
            <x-nawasara-core::page.title>Form - Nawasara Core</x-nawasara-core::page.title>
        </x-slot>


        <x-slot name="actions">
            <x-nawasara-core::page.actions>
                <x-nawasara-core::button color="primary" href="{{ route('nawasara-core.role.form') }}" wire:navigate
                    permission="nawasara-core.role.create">Create
                    New</x-nawasara-core::button>
            </x-nawasara-core::page.actions>
        </x-slot>

        @livewire('nawasara-core.pages.role.section.table')

    </x-nawasara-core::page.container>
</div>
