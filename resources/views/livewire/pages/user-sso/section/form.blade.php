<div>

    <div class="grid grid-cols-2 gap-4">
        <x-nawasara-ui::page.card class="p-6">
            <div class="flex">
                <x-nawasara-ui::form.input placeholder="Search by NIP..." required autofocus
                    wire:model.defer="form.sso_id" class="py-1 px-2" />

                <x-nawasara-ui::button class="mt-4" color="emerald" wire:click="store">Simpan</x-nawasara-ui::button>
            </div>
        </x-nawasara-ui::page.card>

        <x-nawasara-ui::page.card class="p-6">
            Your
        </x-nawasara-ui::page.card>

    </div>

</div>
