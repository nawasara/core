<div>

    {{-- breadcrumb: show on mobile only --}}
    <x-slot name="breadcrumb">
        <livewire:nawasara-ui.shared-components.breadcrumb :items="[
            ['label' => 'User Management', 'url' => '/'],
            ['label' => 'Role', 'url' => route('nawasara-core.role.index'), 'navigate' => true],
        ]" />
    </x-slot>
    {{-- end breadcrumb --}}

    {{-- container --}}
    <x-nawasara-ui::page.container>

        {{-- title --}}
        <x-nawasara-ui::page.title>Role - Nawasara Core</x-nawasara-ui::page.title>

        {{-- button action --}}
        <x-slot name="actions">
            <x-nawasara-ui::page.actions>
                <x-nawasara-ui::button color="success" href="{{ route('nawasara-core.role.form') }}" wire:navigate
                    permission="nawasara-core.role.create">Create
                    New</x-nawasara-ui::button>
            </x-nawasara-ui::page.actions>
        </x-slot>

        {{-- table / content --}}
        @livewire('nawasara-core.role.section.table')

        {{-- delete confirmation --}}
        <x-nawasara-ui::modal-confirm-delete />

    </x-nawasara-ui::page.container>
    {{-- end container --}}
</div>
