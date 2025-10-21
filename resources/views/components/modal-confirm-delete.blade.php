<div x-data="{
    id: null,
    name: '',
    modalOpen(p) {
        this.name = p.detail.name;
        this.id = p.detail.id;
        openModal({ id: 'modalConfirmDelete' })
    }
}" @modal-delete.window="modalOpen($event)">
    <x-nawasara-modal::modal id="modalConfirmDelete" title="Delete Confirmation">
        <p class="text-sm text-gray-700 dark:text-neutral-300"> Are you sure you want to delete <strong
                x-text="name"></strong> ? All
            of your data will be permanently removed. This action cannot be undone.</p>

        <x-slot:footer>
            <div wire:loading>
                <x-nawasara-core::loading />
            </div>
            <x-nawasara-core::button color="danger"
                @click="$dispatch('delete-role', {id: id})">Delete</x-nawasara-core::button>
            <x-nawasara-core::button color="neutral"
                @click="closeModal('modalConfirmDelete')">Cancel</x-nawasara-core::button>

        </x-slot:footer>
    </x-nawasara-modal::modal>
</div>
