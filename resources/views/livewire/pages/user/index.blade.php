<div>
    <x-slot name="breadcrumb">
        <livewire:nawasara-core.shared-components.breadcrumb :items="[['label' => 'User Management', 'url' => '/'], ['label' => 'User', 'url' => '/']]" />
    </x-slot>
    <x-nawasara-core::page.container>

        <x-nawasara-core::page.title>User - Nawasara Core</x-nawasara-core::page.title>

        {{-- <x-slot name="title">
            <x-nawasara-core::page.title>User - Nawasara Core</x-nawasara-core::page.title>
        </x-slot> --}}


        <x-slot name="actions">
            <x-nawasara-core::page.actions>
                <x-nawasara-core::button color="success"
                    @click="openLivewireModal({
                    id: 'modal-user-form',
                    title: 'Form User',
                    component: 'nawasara-core.pages.user.modal.form-user',
                    params: {  }
                })">Create
                    New</x-nawasara-core::button>
            </x-nawasara-core::page.actions>
        </x-slot>

        @livewire('nawasara-core.pages.user.section.table')

        {{-- delete confirmation --}}
        <x-nawasara-core::modal-confirm-delete />

    </x-nawasara-core::page.container>
</div>
