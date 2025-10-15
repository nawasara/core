<div x-data="{
    id: null,
    name: '',
    modalOpen(p) {
        console.log(p);
        this.name = p.detail.name;
        openModal({ id: 'modalConfirmDelete' })
    }
}" @modal-delete.window="modalOpen($event)">
    <x-nawasara-modal::modal id="modalConfirmDelete" title="Confirm Delete">
        <div class="py-4">
            <p class="text-sm text-gray-700 dark:text-neutral-300">Are you sure you want to delete <strong
                    x-text="name"></strong> ?</p>
        </div>

        <x-slot:footer>
            <x-nawasara-core::button class="px-4 py-2 bg-gray-200 rounded-md"
                @click="show = false">Cancel</x-nawasara-core::button>

            <x-nawasara-core::button class="px-4 py-2 bg-red-600 text-white rounded-md"
                @click="
                if (id) {
                    Livewire.dispatch('confirm-delete', { id: id, name: name });
                }
                show = false;
            ">Delete</x-nawasara-core::button>
        </x-slot:footer>
    </x-nawasara-modal::modal>
</div>
