<x-nawasara-ui::layouts.app>
    <x-nawasara-ui::page.container>
        <div class="max-w-2xl mx-auto py-12 text-center">
            <div class="mx-auto mb-4 size-16 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center text-yellow-600 dark:text-yellow-400">
                <x-lucide-mail-question class="size-8" />
            </div>

            <h1 class="text-xl font-semibold text-gray-800 dark:text-neutral-200 mb-2">
                Email Anda belum terhubung
            </h1>

            <p class="text-sm text-gray-600 dark:text-neutral-400 mb-6">
                Akun SSO Anda (<span class="font-mono">{{ $userEmail ?? '—' }}</span>) belum dipetakan ke mailbox <span class="font-mono">@ponorogo.go.id</span>.
                Hubungi administrator untuk melakukan linking.
            </p>

            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 text-left text-xs text-blue-800 dark:text-blue-300">
                <p class="font-medium mb-1">Untuk Administrator:</p>
                <ol class="list-decimal list-inside space-y-1">
                    <li>Set attribute <code class="font-mono">kominfo_email</code> di Keycloak user, atau</li>
                    <li>Tambahkan manual link di <a href="{{ url('nawasara-core/settings/email-link') }}" class="underline">Settings → Email Link</a></li>
                </ol>
            </div>

            <div class="mt-6">
                <x-nawasara-ui::button :href="url('/home')" color="neutral" variant="outline">
                    <x-slot:icon><x-lucide-arrow-left class="size-4" /></x-slot:icon>
                    Kembali ke Dashboard
                </x-nawasara-ui::button>
            </div>
        </div>
    </x-nawasara-ui::page.container>
</x-nawasara-ui::layouts.app>
