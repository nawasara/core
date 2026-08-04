{{-- ui-lint-skip: auth-type segmented control belum punya padanan komponen yang
     mendukung dua tombol dengan warna berbeda per state --}}
<div>
    <form wire:submit="store" class="space-y-2" x-data="{
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
        <div>
            <x-nawasara-ui::form.label value="Tipe User" required />
            <div class="flex rounded-lg overflow-hidden border border-gray-200 dark:border-neutral-700">
                <button type="button" wire:click="setAuthType('local')"
                    class="flex-1 py-2 px-4 text-sm font-medium text-center transition-colors
                    {{ $form->auth_type === 'local' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-800 dark:text-neutral-300' }}">
                    User Biasa
                </button>
                <button type="button" wire:click="setAuthType('sso')"
                    class="flex-1 py-2 px-4 text-sm font-medium text-center transition-colors
                    {{ $form->auth_type === 'sso' ? 'bg-cyan-700 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-800 dark:text-neutral-300' }}">
                    User SSO
                </button>
            </div>
            @error('form.auth_type')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        @if ($form->auth_type === 'sso' && $this->ssoDirectoryAvailable)
            {{-- Identitas user SSO datang dari Keycloak, tidak diketik: mengetik
                 manual berarti salah satu huruf saja sudah membuat akun yang
                 tak pernah bisa login. --}}
            @if ($form->keycloak_id)
                <div class="rounded-lg border border-cyan-200 bg-cyan-50 p-3 dark:border-cyan-800 dark:bg-cyan-900/20">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-neutral-800 dark:text-neutral-100">
                                {{ $form->name }}
                            </p>
                            <p class="truncate text-xs text-neutral-600 dark:text-neutral-300">
                                {{ $form->username }}@if ($form->email) · {{ $form->email }} @endif
                            </p>
                            <p class="mt-1 text-xs text-cyan-700 dark:text-cyan-400">
                                Identitas diambil dari Keycloak
                            </p>
                        </div>
                        @unless ($form->user)
                            <x-nawasara-ui::button color="neutral" variant="outline" size="sm"
                                wire:click="clearSsoUser">
                                Ganti
                            </x-nawasara-ui::button>
                        @endunless
                    </div>
                </div>
            @else
                <div>
                    <x-nawasara-ui::form.label value="Cari Orang di Keycloak" required />
                    <x-nawasara-ui::form.input
                        wire:model.live.debounce.400ms="ssoSearch"
                        placeholder="Ketik nama, username, atau email (min. 2 huruf)…"
                        autocomplete="off" />
                    @error('form.keycloak_id')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror

                    <div class="mt-2 max-h-56 overflow-y-auto rounded-lg border border-neutral-200 dark:border-neutral-700">
                        @forelse ($this->ssoResults as $idx => $person)
                            <button type="button"
                                wire:key="sso-{{ $person['kc_id'] }}"
                                wire:click="pickSsoUser({{ $idx }})"
                                class="flex w-full items-center justify-between gap-3 border-b border-neutral-100 px-3 py-2.5 text-left last:border-0 hover:bg-cyan-50 dark:border-neutral-800 dark:hover:bg-cyan-900/20">
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-medium text-neutral-800 dark:text-neutral-100">{{ $person['name'] }}</span>
                                    <span class="block truncate text-xs text-neutral-500 dark:text-neutral-400">
                                        @if ($person['nip'])
                                            NIP {{ $person['nip'] }}
                                        @elseif ($person['email'])
                                            {{ $person['email'] }}
                                        @else
                                            {{ $person['username'] }}
                                        @endif
                                    </span>
                                </span>
                                <span class="shrink-0 text-xs font-medium text-cyan-700 dark:text-cyan-400">Pilih</span>
                            </button>
                        @empty
                            <div class="px-3 py-5 text-center text-sm text-neutral-500 dark:text-neutral-400">
                                @if (mb_strlen(trim($ssoSearch)) < 2)
                                    Ketik minimal 2 huruf untuk mencari.
                                @else
                                    Tidak ada yang cocok, atau semuanya sudah punya akun Nawasara.
                                @endif
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif
        @else
            @if ($form->auth_type === 'sso')
                <p class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                    Direktori Keycloak belum tersedia — jalankan <code>keycloak:sync</code> dulu.
                    Sementara ini identitas diisi manual dan harus persis sama dengan Keycloak.
                </p>
            @endif

            <x-nawasara-ui::form.input id="name" name="name" label="Name" placeholder="Your name" useError="true"
                errorVariable="form.name" autofocus wire:model.defer="form.name" />

            <x-nawasara-ui::form.input id="username" name="username" label="Username" placeholder="Username"
                useError="true" errorVariable="form.username" wire:model.defer="form.username" />

            <x-nawasara-ui::form.input id="email" name="email" type="email" label="Email"
                placeholder="Your email" wire:model.defer="form.email" useError="true" errorVariable="form.email" />
        @endif

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
