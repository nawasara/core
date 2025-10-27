<div>

    <div class="grid grid-cols-2 gap-4">
        <x-nawasara-core::page.card class="p-6">
            <div class="flex">
                <x-nawasara-core::form.input placeholder="Search by NIP..." required autofocus
                    wire:model.defer="form.sso_id" class="py-1 px-2" />

                <x-nawasara-core::button class="mt-4" color="emerald" wire:click="store">Simpan</x-nawasara-core::button>
            </div>
        </x-nawasara-core::page.card>

        <x-nawasara-core::page.card class="p-6">
            Your
        </x-nawasara-core::page.card>

    </div>

</div>
