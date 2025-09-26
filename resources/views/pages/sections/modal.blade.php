<div>
    <h3>Modal</h3>

    {{-- modal footer --}}
    <x-nawasara-core::button class="px-4 py-2 bg-blue-600 text-white rounded-md" @click="openModal('delete-user')">
        Modal confirm
    </x-nawasara-core::button>

    <x-nawasara-modal::modal id="delete-user" title="Hapus User?">
        Apakah kamu yakin ingin menghapus user ini?
        <x-slot:footer>
            <x-nawasara-core::button class="px-4 py-2 bg-gray-200 rounded-md"
                @click="closeModal({id: 'delete-user'})">Batal</x-nawasara-core::button>

            <x-nawasara-core::button class="px-4 py-2 bg-red-600 text-white rounded-md"
                wire:click="delete">Hapus</x-nawasara-core::button>
        </x-slot:footer>
    </x-nawasara-modal::modal>


    {{-- modal slot --}}
    <x-nawasara-core::button class="px-4 py-2 bg-blue-600 text-white rounded-md" @click="openModal({id: 'info-user'})">
        Modal Static
    </x-nawasara-core::button>
    <x-nawasara-modal::modal id="info-user" title="Detail User">
        <p>Nama: John Doe</p>
        <p>Email: john@example.com</p>
    </x-nawasara-modal::modal>


    {{-- modal livewire --}}
    <x-nawasara-core::button class="px-4 py-2 bg-blue-600 text-white rounded-md"
        @click="openModal({id: 'modal-livewire'})">
        Modal Livewire
    </x-nawasara-core::button>
    <x-nawasara-modal::modal id="modal-livewire" title="Detail User">
        @livewire('nawasara-core.pages.examples.demo-modal')
    </x-nawasara-modal::modal>

    <x-nawasara-core::button class="px-4 py-2 bg-blue-600 text-white rounded-md"
        @click="openLivewireModal({
                    id: 'modal-livewire-contoh1',
                    title: 'Form Input Data',
                    component: 'nawasara-core.pages.examples.demo-modal',
                    params: { userId: 5 }
                })">
        Modal Livewire Loading
    </x-nawasara-core::button>
</div>
