{{-- ui-lint-skip: auth-type segmented uses $set() magic which doesn't fit segmented-control API; needs setAuthType() Livewire method first --}}
<div>
    <form wire:submit.prevent="store" class="space-y-2" x-data="{
        selectedRoles: @entangle('form.roles') ?? [],
        toggleRole(roleId) {
            const index = this.selectedRoles.indexOf(roleId);
            if (index > -1) { this.selectedRoles.splice(index, 1); } else { this.selectedRoles.push(roleId); }
        },
        store() {
            Livewire.dispatch('store', { roles: this.selectedRoles });
        }
    }">
        {{-- Auth Type Toggle --}}
        {{-- TODO: migrate to <x-nawasara-ui::segmented-control> after adding
             setAuthType($value) method to FormUser livewire component (current
             $set magic syntax doesn't fit segmented-control API). --}}
        <div>
            <x-nawasara-ui::form.label value="Tipe User" required />
            <div class="flex rounded-lg overflow-hidden border border-gray-200 dark:border-neutral-700">
                <button type="button" wire:click="$set('form.auth_type', 'local')"
                    class="flex-1 py-2 px-4 text-sm font-medium text-center transition-colors
                    {{ $form->auth_type === 'local' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-800 dark:text-neutral-300' }}">
                    User Biasa
                </button>
                <button type="button" wire:click="$set('form.auth_type', 'sso')"
                    class="flex-1 py-2 px-4 text-sm font-medium text-center transition-colors
                    {{ $form->auth_type === 'sso' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-800 dark:text-neutral-300' }}">
                    User SSO
                </button>
            </div>
            @error('form.auth_type')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <x-nawasara-ui::form.input id="name" name="name" label="Name" placeholder="Your name" useError="true"
            errorVariable="form.name" autofocus wire:model.defer="form.name" />

        <div>
            <x-nawasara-ui::form.input id="username" name="username" label="Username" placeholder="Username"
                useError="true" errorVariable="form.username" wire:model.defer="form.username" />
            @if ($form->auth_type === 'sso')
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">Username harus sama dengan username di Keycloak</p>
            @endif
        </div>

        <x-nawasara-ui::form.input id="email" name="email" type="email" label="Email"
            placeholder="Your email" wire:model.defer="form.email" useError="true" errorVariable="form.email" />

        <div>
            <x-nawasara-ui::form.label for="roles" value="Roles" required />
            <x-nawasara-ui::button-group>
                @foreach ($roles as $item)
                    <x-nawasara-ui::button-group.item @click="toggleRole({{ $item->id }})"
                        x-bind:class="selectedRoles.includes({{ $item->id }}) ?
                            'bg-green-100 text-green-800 border-green-300' :
                            'bg-gray-100 text-gray-700 border-gray-200 '">
                        {{ $item->name }}
                    </x-nawasara-ui::button-group.item>
                @endforeach
            </x-nawasara-ui::button-group>
        </div>
        @error('form.roles')
            <span class="text-red-500">{{ $message }}</span>
        @enderror

        @if ($form->auth_type !== 'sso')
            <x-nawasara-ui::form.input id="password" name="password" type="password" label="Password"
                usePasswordField="true" useGenPassword="true" placeholder="Your password"
                wire:model.defer="form.password" useError="true" errorVariable="form.password" />
        @endif

        <div class="flex justify-end gap-2">
            <x-nawasara-ui::button color="neutral" variant="outline"
                @click="$dispatch('close-livewire-modal', { id: 'modal-user-form' })">
                Batal
            </x-nawasara-ui::button>
            <x-nawasara-ui::button color="success"
                x-data="{ disabledSubmit: false }" x-init="disabledSubmit = false"
                x-on:click="if (disabledSubmit) return; disabledSubmit = true; setTimeout(() => disabledSubmit = false, 2500); store();"
                x-bind:disabled="disabledSubmit" wire:loading.attr="disabled">
                Simpan
            </x-nawasara-ui::button>
        </div>
    </form>
</div>
