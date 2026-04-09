<div>
    <x-slot name="breadcrumb">
        <livewire:nawasara-ui.shared-components.breadcrumb :items="[['label' => 'User Management', 'url' => '/'], ['label' => 'User', 'url' => '/']]" />
    </x-slot>
    <x-nawasara-ui::page.container>

        <x-slot name="title">
            <x-nawasara-ui::page.title>User</x-nawasara-ui::page.title>
        </x-slot>


        <x-slot name="actions">
            <x-nawasara-ui::page.actions>
                <x-nawasara-ui::button color="primary"
                    @click="openLivewireModal({
                    id: 'modal-user-form',
                    title: 'Form User',
                    component: 'nawasara-core.user.modal.form-user',
                    params: {  }
                })">Create
                    New</x-nawasara-ui::button>
            </x-nawasara-ui::page.actions>
        </x-slot>

        @livewire('nawasara-core.user-sso.section.form')

    </x-nawasara-ui::page.container>
</div>
