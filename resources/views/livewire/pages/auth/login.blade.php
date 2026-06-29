<div>
    <div class="relative min-h-dvh flex items-center justify-center px-4 py-12 overflow-hidden">

        {{-- Ambient background: soft emerald glow blobs over the layout gradient. --}}
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -top-32 -left-24 size-96 rounded-full bg-emerald-400/20 dark:bg-emerald-500/10 blur-3xl"></div>
            <div class="absolute -bottom-40 -right-24 size-[28rem] rounded-full bg-teal-400/20 dark:bg-emerald-600/10 blur-3xl"></div>
        </div>

        <div class="relative w-full max-w-md">
            {{-- Brand mark floating above the card --}}
            <div class="flex flex-col items-center mb-7">
                <div class="flex items-center justify-center size-16 rounded-2xl bg-white/70 dark:bg-neutral-800/70 ring-1 ring-black/5 dark:ring-white/10 shadow-lg shadow-emerald-900/5 backdrop-blur">
                    <x-nawasara-ui::brand-logo height="h-9" :show-name="false" />
                </div>
                <h1 class="mt-4 text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                    {{ function_exists('brand') ? brand('app_name', 'Nawasara') : 'Nawasara' }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-neutral-400">
                    Silakan masuk untuk melanjutkan
                </p>
            </div>

            {{-- Card --}}
            <div class="rounded-2xl bg-white/80 dark:bg-neutral-800/80 backdrop-blur-xl ring-1 ring-black/5 dark:ring-white/10 shadow-xl shadow-gray-900/5 p-7 sm:p-8">

                @if ($errors->any())
                    <div class="mb-5 flex gap-3 p-3.5 rounded-xl bg-rose-50 dark:bg-rose-900/20 ring-1 ring-rose-200 dark:ring-rose-800/40">
                        <x-lucide-circle-alert class="size-5 shrink-0 text-rose-500 dark:text-rose-400 mt-0.5" />
                        <ul class="space-y-0.5 text-sm text-rose-700 dark:text-rose-300">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                    <div class="mb-5 flex gap-3 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 ring-1 ring-emerald-200 dark:ring-emerald-800/40 text-sm text-emerald-700 dark:text-emerald-300">
                        <x-lucide-circle-check class="size-5 shrink-0 text-emerald-500 dark:text-emerald-400 mt-0.5" />
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($rawAuthMode !== $authMode)
                    <div class="mb-5 flex gap-3 p-3.5 rounded-xl bg-amber-50 dark:bg-amber-900/20 ring-1 ring-amber-200 dark:ring-amber-800/40 text-xs text-amber-800 dark:text-amber-300">
                        <x-lucide-triangle-alert class="size-4 shrink-0 mt-0.5" />
                        <span>Mode auth diset ke <strong>{{ $rawAuthMode }}</strong>, tetapi credential SSO belum lengkap di Vault. Sementara fallback ke <strong>{{ $authMode }}</strong>.</span>
                    </div>
                @endif

                @if ($localEnabled)
                    <form wire:submit="login" class="space-y-5">
                        <div>
                            <x-nawasara-ui::form.label for="identifier" value="Email / Username" />
                            <x-nawasara-ui::form.input id="identifier" wire:model.defer="identifier" type="text" required autofocus
                                autocomplete="username" placeholder="nama@ponorogo.go.id" />
                        </div>

                        <div>
                            <x-nawasara-ui::form.label for="password" value="Password" />
                            <x-nawasara-ui::form.input id="password" wire:model.defer="password" type="password"
                                required autocomplete="current-password" usePasswordField placeholder="••••••••" />
                        </div>

                        <div class="flex items-center justify-between">
                            <x-nawasara-ui::form.checkbox id="remember" wire:model.defer="remember" label="Ingat saya" />
                        </div>

                        <button type="submit"
                            class="group relative w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-neutral-800 transition-colors"
                            wire:loading.attr="disabled" wire:target="login">
                            <span wire:loading.remove wire:target="login" class="inline-flex items-center gap-2">
                                Masuk
                                <x-lucide-arrow-right class="size-4 transition-transform group-hover:translate-x-0.5" />
                            </span>
                            <span wire:loading wire:target="login" class="inline-flex items-center gap-2">
                                <x-lucide-loader-circle class="size-4 animate-spin" />
                                Memproses…
                            </span>
                        </button>
                    </form>
                @endif

                @if ($localEnabled && $ssoEnabled)
                    <div class="flex items-center gap-3 my-5">
                        <span class="h-px flex-1 bg-gray-200 dark:bg-neutral-700"></span>
                        <span class="text-xs font-medium text-gray-400 dark:text-neutral-500">atau</span>
                        <span class="h-px flex-1 bg-gray-200 dark:bg-neutral-700"></span>
                    </div>
                @endif

                @if ($ssoEnabled)
                    <a href="{{ route('sso.redirect') }}"
                        class="group w-full inline-flex items-center justify-center gap-2.5 rounded-xl px-4 py-3 text-sm font-semibold transition-colors
                            {{ $localEnabled
                                ? 'bg-white dark:bg-neutral-800 text-gray-700 dark:text-neutral-200 ring-1 ring-gray-200 dark:ring-neutral-700 hover:bg-gray-50 dark:hover:bg-neutral-700/60'
                                : 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20 hover:bg-emerald-700' }}">
                        <x-lucide-shield-check class="size-5 {{ $localEnabled ? 'text-emerald-600 dark:text-emerald-400' : '' }}" />
                        Login dengan SSO
                        <x-lucide-arrow-right class="size-4 opacity-60 transition-transform group-hover:translate-x-0.5" />
                    </a>
                    @if (! $localEnabled)
                        <p class="mt-4 text-center text-xs text-gray-400 dark:text-neutral-500">
                            Gunakan akun ASN Ponorogo untuk masuk.
                        </p>
                    @endif
                @endif

                @if (! $localEnabled && ! $ssoEnabled)
                    <div class="flex gap-3 p-4 rounded-xl bg-rose-50 dark:bg-rose-900/20 ring-1 ring-rose-200 dark:ring-rose-800/40 text-sm text-rose-700 dark:text-rose-300">
                        <x-lucide-circle-alert class="size-5 shrink-0 mt-0.5" />
                        <span>Belum ada metode login yang aktif. Hubungi administrator.</span>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <p class="mt-6 text-center text-xs text-gray-400 dark:text-neutral-500">
                © {{ date('Y') }} {{ function_exists('brand') ? brand('app_name', 'Nawasara') : 'Nawasara' }} — Dinas Kominfo Kabupaten Ponorogo
            </p>
        </div>
    </div>
</div>
