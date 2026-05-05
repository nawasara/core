<x-nawasara-ui::layouts.app>
    <x-nawasara-ui::page.container>
        <div class="max-w-2xl mx-auto py-12 text-center">
            <div class="mx-auto mb-4 size-16 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-600 dark:text-red-400">
                <x-lucide-alert-triangle class="size-8" />
            </div>

            <h1 class="text-xl font-semibold text-gray-800 dark:text-neutral-200 mb-2">
                Auto-login email gagal
            </h1>

            <p class="text-sm text-gray-600 dark:text-neutral-400 mb-6">
                {{ $message ?? 'Terjadi kesalahan saat menghubungkan ke server email.' }}
            </p>

            <div class="flex items-center justify-center gap-3">
                <x-nawasara-ui::button :href="url('webmail/launch')" color="primary">
                    <x-slot:icon><x-lucide-refresh-cw class="size-4" /></x-slot:icon>
                    Coba Lagi
                </x-nawasara-ui::button>

                <x-nawasara-ui::button :href="url('/home')" color="neutral" variant="outline">
                    Kembali ke Dashboard
                </x-nawasara-ui::button>
            </div>

            <p class="mt-6 text-xs text-gray-500 dark:text-neutral-500">
                Anda juga dapat <a href="https://gentapraja.ponorogo.go.id:2096/" target="_blank" class="underline text-blue-600 dark:text-blue-400">login manual ke webmail</a>.
            </p>
        </div>
    </x-nawasara-ui::page.container>
</x-nawasara-ui::layouts.app>
