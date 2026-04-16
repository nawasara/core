<div>
    <x-slot name="breadcrumb">
        <livewire:nawasara-ui.shared-components.breadcrumb :items="[['label' => 'User Management', 'url' => '/'], ['label' => 'User', 'url' => '/']]" />
    </x-slot>
    <x-nawasara-ui::page.container>

        <x-nawasara-ui::page.title>User - Nawasara Core</x-nawasara-ui::page.title>

        {{-- <x-slot name="title">
            <x-nawasara-ui::page.title>User - Nawasara Core</x-nawasara-ui::page.title>
        </x-slot> --}}


        <x-slot name="actions">
            <x-nawasara-ui::page.actions>
                <x-nawasara-ui::button color="success"
                    @click="openLivewireModal({
                    id: 'modal-user-form',
                    title: 'Form User',
                    component: 'nawasara-core.user.modal.form-user',
                    params: {  }
                })">
                    <x-slot:icon><x-lucide-plus class="size-4" /></x-slot:icon>
                    Tambah User
                </x-nawasara-ui::button>
            </x-nawasara-ui::page.actions>
        </x-slot>

        @livewire('nawasara-core.user.section.table')

        {{-- delete confirmation --}}
        <x-nawasara-ui::modal-confirm-delete />

    </x-nawasara-ui::page.container>
</div>
