<div>
    <h3>Modal</h3>

    {{-- modal footer --}}
    <x-nawasara-core::button class="px-4 py-2 bg-blue-600 text-white rounded-md"
        @click="$dispatch('open-modal', {id: 'delete-user'})">
        Modal confirm
    </x-nawasara-core::button>

    <x-nawasara-core::modal id="delete-user" title="Hapus User?">
        Apakah kamu yakin ingin menghapus user ini?
        <x-slot:footer>
            <x-nawasara-core::button class="px-4 py-2 bg-gray-200 rounded-md"
                @click="$dispatch('close-modal', {id: 'delete-user'})">Batal</x-nawasara-core::button>

            <x-nawasara-core::button class="px-4 py-2 bg-red-600 text-white rounded-md"
                wire:click="delete">Hapus</x-nawasara-core::button>
        </x-slot:footer>
    </x-nawasara-core::modal>


    {{-- modal slot --}}
    <button class="px-4 py-2 bg-blue-600 text-white rounded-md" @click="$dispatch('open-modal', {id: 'info-user'})">
        Modal Static
    </button>
    <x-nawasara-core::modal id="info-user" title="Detail User">
        <p>Nama: John Doe</p>
        <p>Email: john@example.com</p>
    </x-nawasara-core::modal>


    {{-- modal livewire --}}
    <button class="px-4 py-2 bg-blue-600 text-white rounded-md"
        @click="$dispatch('open-modal', {id: 'modal-livewire'})">
        Modal Livewire
    </button>
    <x-nawasara-core::modal id="modal-livewire" title="Detail User">
        @livewire('nawasara-core.pages.examples.demo-modal')
    </x-nawasara-core::modal>

    <button class="px-4 py-2 bg-blue-600 text-white rounded"
        @click="$dispatch('open-modal-livewire', {
                    id: 'modal-livewire-contoh1',
                    title: 'Form Input Data',
                    component: 'nawasara-core.pages.examples.demo-modal',
                    params: { userId: 5 }
                })">
        Modal Livewire Loading
    </button>

</div>
