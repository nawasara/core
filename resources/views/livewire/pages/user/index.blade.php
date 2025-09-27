<div>
    <x-slot:title>
        User - Nawasara Core
    </x-slot:title>

    <x-slot name="breadcrumb">
        <livewire:nawasara-core.shared-components.breadcrumb :items="[
            ['label' => 'User Management', 'url' => '/'],
            ['label' => 'User', 'url' => '/'],
            ['label' => 'Create New'],
        ]" />
    </x-slot>
    <x-nawasara-core::page.container>

        <x-slot name="title">
            <x-nawasara-core::page.title>User</x-nawasara-core::page.title>
        </x-slot>


        <x-slot name="actions">
            <x-nawasara-core::page.actions>
                <x-nawasara-core::button color="primary"
                    @click="openLivewireModal({
                    id: 'modal-user-form',
                    title: 'Form User',
                    component: 'nawasara-core.modals.form-user',
                    params: {  }
                })">Create
                    New</x-nawasara-core::button>
            </x-nawasara-core::page.actions>
        </x-slot>

        @livewire('nawasara-core.pages.user.table')

    </x-nawasara-core::page.container>
</div>
