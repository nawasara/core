<div>
    <x-slot name="breadcrumb">
        <livewire:nawasara-ui.shared-components.breadcrumb
            :items="[['label' => 'Settings', 'url' => '#'], ['label' => 'Authentication']]" />
    </x-slot>

    <x-nawasara-ui::page.container>
        <x-nawasara-ui::page.title>Authentication Settings</x-nawasara-ui::page.title>
        <p class="text-sm text-gray-500 dark:text-neutral-400 mb-6">
            Atur metode login: lokal (username/password), SSO (Keycloak), atau keduanya. Credential SSO disimpan di Vault group <code class="font-mono text-xs">sso</code>.
        </p>

        <form wire:submit="save" class="space-y-6">
            {{-- Auth Mode --}}
            <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-neutral-200 mb-1">Mode Login</h3>
                <p class="text-xs text-gray-500 dark:text-neutral-400 mb-4">
                    Pilih metode autentikasi yang aktif untuk seluruh sistem.
                </p>

                <div class="space-y-3">
                    @foreach ([
                        \Nawasara\Core\Auth\AuthMode::LOCAL => ['Login Lokal', 'Username/email + password tersimpan di database Nawasara.'],
                        \Nawasara\Core\Auth\AuthMode::SSO => ['SSO Only', 'Hanya Single Sign-On (Keycloak). Pastikan credential Vault sudah lengkap.'],
                        \Nawasara\Core\Auth\AuthMode::BOTH => ['Keduanya (Lokal + SSO)', 'User bisa pilih login lokal atau SSO. User auth_type lokal tidak boleh login lewat SSO dan sebaliknya.'],
                    ] as $value => [$label, $hint])
                        <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 dark:border-neutral-700 hover:border-green-400 dark:hover:border-green-700 cursor-pointer transition">
                            <input type="radio" wire:model="mode" value="{{ $value }}" class="mt-1 text-green-600 focus:ring-green-500" />
                            <div>
                                <div class="text-sm font-medium text-gray-800 dark:text-neutral-200">{{ $label }}</div>
                                <div class="text-xs text-gray-500 dark:text-neutral-400">{{ $hint }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>

                @if ($effectiveMode !== $mode)
                    <div class="mt-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-xs text-amber-800 dark:text-amber-300">
                        Saat ini mode efektif adalah <strong>{{ $effectiveMode }}</strong> (fallback dari <strong>{{ $mode }}</strong>) karena Vault SSO belum lengkap.
                    </div>
                @endif

                @error('mode') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
            </div>

            {{-- SSO Settings --}}
            <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-neutral-200 mb-1">SSO Behavior</h3>
                <p class="text-xs text-gray-500 dark:text-neutral-400 mb-4">
                    Konfigurasi alur user yang login lewat SSO.
                </p>

                <div class="space-y-4">
                    <label class="flex items-start gap-3">
                        <input type="checkbox" wire:model="autoProvision" class="mt-1 rounded text-green-600 focus:ring-green-500" />
                        <div>
                            <div class="text-sm font-medium text-gray-800 dark:text-neutral-200">Auto-provision user baru</div>
                            <div class="text-xs text-gray-500 dark:text-neutral-400">
                                Saat user SSO yang belum ada di Nawasara login pertama kali, akun baru otomatis dibuat dengan role di bawah.
                                Kalau dimatikan, user harus pre-create dulu oleh admin.
                            </div>
                        </div>
                    </label>

                    <div>
                        <x-nawasara-ui::form.label for="defaultSsoRole" value="Default Role untuk User SSO Baru" />
                        <x-nawasara-ui::form.select id="defaultSsoRole" wire:model="defaultSsoRole" :placeholder="false">
                            @foreach ($roles as $role)
                                <option value="{{ $role }}">{{ $role }}</option>
                            @endforeach
                        </x-nawasara-ui::form.select>
                        <p class="text-xs text-gray-500 dark:text-neutral-400 mt-1">
                            Pastikan role <code class="font-mono">guest</code> ada (atau pilih role minim privilege lain). Admin re-assign role nanti.
                        </p>
                        @error('defaultSsoRole') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- SSO Credential Status --}}
            <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-neutral-200 mb-1">SSO Credential</h3>
                <p class="text-xs text-gray-500 dark:text-neutral-400 mb-4">
                    Credential disimpan di Vault group <code class="font-mono">sso</code> (encrypted at rest, audit log read/write).
                </p>

                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-sm">
                        @if ($ssoConfigured)
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                <x-lucide-check-circle class="size-3" /> Configured
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
                                <x-lucide-circle-alert class="size-3" /> Belum lengkap
                            </span>
                        @endif

                        <a href="{{ url('nawasara-vault') }}" wire:navigate class="text-xs text-emerald-700 dark:text-emerald-400 hover:underline font-medium">
                            Edit di Vault →
                        </a>
                    </div>

                    @if ($ssoConfigured)
                        <x-nawasara-ui::button type="button" wire:click="testSso" color="neutral" variant="outline" size="sm">
                            <x-slot:icon>
                                <x-lucide-zap wire:loading.class="hidden" wire:target="testSso" class="size-4" />
                                <x-lucide-loader-2 wire:loading wire:target="testSso" class="size-4 animate-spin" />
                            </x-slot:icon>
                            Test Connection
                        </x-nawasara-ui::button>
                    @endif
                </div>

                @if ($testResult !== null)
                    <div class="mt-3 p-3 rounded-lg text-xs {{ $testSuccess ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                        {{ $testResult }}
                    </div>
                @endif
            </div>

            {{-- Submit --}}
            <div class="flex justify-end gap-3">
                <x-nawasara-ui::button type="submit" color="primary">
                    <x-slot:icon><x-lucide-save class="size-4" /></x-slot:icon>
                    Simpan Pengaturan
                </x-nawasara-ui::button>
            </div>
        </form>
    </x-nawasara-ui::page.container>
</div>
