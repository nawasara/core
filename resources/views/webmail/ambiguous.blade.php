<x-nawasara-ui::layouts.app>
    <x-nawasara-ui::page.container>
        <div class="max-w-2xl mx-auto py-12 text-center">
            <div class="mx-auto mb-4 size-16 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center text-yellow-600 dark:text-yellow-400">
                <x-lucide-mails class="size-8" />
            </div>

            <h1 class="text-xl font-semibold text-gray-800 dark:text-neutral-200 mb-2">
                Beberapa mailbox terdeteksi
            </h1>

            <p class="text-sm text-gray-600 dark:text-neutral-400 mb-6">
                Akun SSO Anda terhubung ke lebih dari satu mailbox. Hubungi administrator
                untuk menentukan mailbox primary yang akan dibuka otomatis.
            </p>

            @if (! empty($candidates))
                <div class="bg-gray-50 dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-lg p-4 mb-6 text-left">
                    <p class="text-xs font-medium text-gray-700 dark:text-neutral-300 mb-2">Mailbox terdaftar:</p>
                    <ul class="space-y-1">
                        @foreach ($candidates as $c)
                            <li class="text-sm font-mono text-gray-600 dark:text-neutral-400 flex items-center gap-2">
                                <x-lucide-mail class="size-3.5" />
                                {{ $c['mailbox'] }}
                                <span class="ml-auto text-xs text-gray-400">({{ $c['source'] }})</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 text-left text-xs text-blue-800 dark:text-blue-300 mb-6">
                <p class="font-medium mb-1">Untuk Administrator:</p>
                <p>
                    Set salah satu mailbox sebagai <strong>manual link</strong> (override) di
                    <a href="{{ url('nawasara-core/settings/email-link') }}" class="underline">Settings → Email Link</a>.
                    Manual link selalu menang atas mapping otomatis dari Keycloak.
                </p>
            </div>

            <x-nawasara-ui::button :href="url('/home')" color="neutral" variant="outline">
                <x-slot:icon><x-lucide-arrow-left class="size-4" /></x-slot:icon>
                Kembali ke Dashboard
            </x-nawasara-ui::button>
        </div>
    </x-nawasara-ui::page.container>
</x-nawasara-ui::layouts.app>
