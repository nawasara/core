<div>
    <div class="min-h-dvh flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white dark:bg-gray-900 p-8 rounded-xl shadow-lg">
            <div class="text-center mb-6">
                <img src="{{ asset('vendor/nawasara-core/assets/images/logo.png') }}" class="h-12 mx-auto mb-2"
                    alt="Logo Nandur Panguripan" />
                <h2 class="mt-2 text-2xl font-bold text-green-800 dark:text-green-300">Login</h2>
                <p class="mt-1 text-sm text-green-700 dark:text-green-200">Silakan masuk ke akun Anda.</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-4 rounded-lg bg-red-50 dark:bg-red-900 text-red-700 dark:text-red-200">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status'))
                <div class="mb-4 p-4 rounded-lg bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-200 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($rawAuthMode !== $authMode)
                <div class="mb-4 p-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 text-xs">
                    Catatan: mode auth diset ke <strong>{{ $rawAuthMode }}</strong>, tetapi credential SSO belum lengkap di Vault. Sementara fallback ke <strong>{{ $authMode }}</strong>.
                </div>
            @endif

            @if ($localEnabled)
                <form wire:submit.prevent="login" class="space-y-6">
                    <div>
                        <x-nawasara-ui::form.label for="identifier" value="Email / Username" />
                        <x-nawasara-ui::form.input id="identifier" wire:model.defer="identifier" type="text" required autofocus
                            autocomplete="username" />
                    </div>

                    <div>
                        <x-nawasara-ui::form.label for="password" value="Password" />
                        <x-nawasara-ui::form.input id="password" wire:model.defer="password" type="password"
                            required autocomplete="current-password" usePasswordField />
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <x-nawasara-ui::form.checkbox id="remember" wire:model.defer="remember" label="Remember me" />
                        </div>
                    </div>

                    <div>
                        <x-nawasara-ui::button type="submit" rounded="md"
                            class="w-full py-2.5 px-4 bg-green-600 dark:bg-green-700 text-white font-medium hover:bg-green-700 dark:hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition">
                            Log In
                        </x-nawasara-ui::button>
                    </div>
                </form>
            @endif

            @if ($localEnabled && $ssoEnabled)
                <div class="flex items-center justify-center my-4">
                    <span class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></span>
                    <span class="mx-2 text-gray-400 dark:text-gray-500 text-xs">atau</span>
                    <span class="h-px flex-1 bg-gray-200 dark:bg-gray-700"></span>
                </div>
            @endif

            @if ($ssoEnabled)
                <div>
                    <a href="{{ route('sso.redirect') }}" class="block">
                        <x-nawasara-ui::button aria-label="Login SSO" color="neutral" full rounded="md" type="button">
                            <x-slot:icon>
                                <x-lucide-log-in class="size-5" />
                            </x-slot:icon>
                            Login with SSO
                        </x-nawasara-ui::button>
                    </a>
                </div>
            @endif

            @if (! $localEnabled && ! $ssoEnabled)
                <div class="p-4 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-sm">
                    Belum ada metode login yang aktif. Hubungi administrator.
                </div>
            @endif
        </div>
    </div>
</div>
