<div>

    {{-- breadcrumb: show on mobile only --}}
    <x-slot name="breadcrumb">
        <livewire:nawasara-core.shared-components.breadcrumb :items="[
            ['label' => 'User Management', 'url' => '/'],
            ['label' => 'Role', 'url' => route('nawasara-core.role.index'), 'navigate' => true],
        ]" />
    </x-slot>
    {{-- end breadcrumb --}}

    {{-- container --}}
    <x-nawasara-core::page.container>

        {{-- title --}}
        <x-nawasara-core::page.title>Role - Nawasara Core</x-nawasara-core::page.title>

        {{-- button action --}}
        <x-slot name="actions">
            <x-nawasara-core::page.actions>
                <x-nawasara-core::button color="success" href="{{ route('nawasara-core.role.form') }}" wire:navigate
                    permission="nawasara-core.role.create">Create
                    New</x-nawasara-core::button>
            </x-nawasara-core::page.actions>
        </x-slot>

        {{-- table / content --}}
        @livewire('nawasara-core.pages.role.section.table')

        {{-- delete confirmation --}}
        <x-nawasara-core::modal-confirm-delete />

    </x-nawasara-core::page.container>
    {{-- end container --}}
</div>
