<div>
    <form wire:submit.prevent="store" class="space-y-2" x-data="{
        selectedRoles: [],
        toggleRole(roleId) {
            console.log('Toggling role: ' + roleId);
            const index = this.selectedRoles.indexOf(roleId);
            if (index > -1) { this.selectedRoles.splice(index, 1); } else { this.selectedRoles.push(roleId); }
            console.log(this.selectedRoles);
        },
        store() {
            Livewire.dispatch('store', { roles: this.selectedRoles });
        }
    }">
        <x-nawasara-core::form.input id="name" name="name" label="Name" placeholder="Your name" useError="true"
            errorVariable="form.name" autofocus wire:model.defer="form.name" />
        <x-nawasara-core::form.input id="name" name="name" label="Username" placeholder="Username"
            useError="true" errorVariable="form.username" autofocus wire:model.defer="form.username" />
        <x-nawasara-core::form.input id="email" name="email" type="email" label="Email"
            placeholder="Your email" wire:model.defer="form.email" useError="true" errorVariable="form.email" />
        <div>
            <x-nawasara-core::form.label for="roles" value="Roles" required />
            {{-- Button group for roles --}}
            <x-nawasara-core::button-group>
                @php
                    $icon =
                        '<svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eclipse-icon lucide-eclipse"><circle cx="12" cy="12" r="10"/><path d="M12 2a7 7 0 1 0 10 10"/></svg>';
                @endphp
                @foreach ($roles as $item)
                    <x-nawasara-core::button-group.item @click="toggleRole({{ $item->id }})"
                        x-bind:class="selectedRoles.includes({{ $item->id }}) ?
                            'bg-green-100 text-green-800 border-green-300' :
                            'bg-gray-100 text-gray-700 border-gray-200 '">
                        {{-- {!! $icon !!} --}}
                        {{ $item->name }}
                    </x-nawasara-core::button-group.item>
                @endforeach
            </x-nawasara-core::button-group>
        </div>
        @error('form.roles')
            <span class="text-red-500">{{ $message }}</span>
        @enderror

        <x-nawasara-core::form.input id="password" name="password" type="password" label="Password"
            usePasswordField="true" useGenPassword="true" placeholder="Your password" wire:model.defer="form.password"
            useError="true" errorVariable="form.password" />


        <div class="flex justify-end gap-2">
            <button type="button" wire:click="closeModal" class="px-4 py-2 bg-gray-200 rounded-md">Batal</button>
            <button type="button" x-data="{ disabledSubmit: false }" x-init="disabledSubmit = false"
                x-on:click="if (disabledSubmit) return; disabledSubmit = true; setTimeout(() => disabledSubmit = false, 2500); store();"
                x-bind:disabled="disabledSubmit" wire:loading.attr="disabled"
                class="px-4 py-2 bg-emerald-600 text-white rounded-md disabled:opacity-50">Simpan</button>
        </div>
    </form>
</div>
