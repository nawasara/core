<div class="max-w-3xl mx-auto py-8 space-y-6">

    <div>
        <h1 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100">
            Uji Sudo Mode
        </h1>
        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">
            Halaman sementara untuk memverifikasi step-up Keycloak. Aman dihapus
            setelah pengujian selesai.
        </p>
    </div>

    {{-- Current sudo window status --}}
    <x-nawasara-ui::page.card>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-neutral-700 dark:text-neutral-200">
                    Status sudo window
                </p>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                    Window berlaku {{ \Nawasara\AuthPrimitives\Auth\Sudo::windowMinutes() }} menit
                    sejak konfirmasi OTP terakhir.
                </p>
            </div>

            @if (sudo_active())
                <x-nawasara-ui::badge color="success" icon="lucide-shield-check">
                    Aktif &middot; sisa {{ gmdate('i:s', sudo_remaining_seconds()) }}
                </x-nawasara-ui::badge>
            @else
                <x-nawasara-ui::badge color="neutral" icon="lucide-shield-off">
                    Tidak aktif
                </x-nawasara-ui::badge>
            @endif
        </div>
    </x-nawasara-ui::page.card>

    {{-- Route-level gate --}}
    <x-nawasara-ui::page.card>
        <p class="text-sm font-medium text-neutral-700 dark:text-neutral-200">
            1. Gerbang level route (middleware <code>sudo</code>)
        </p>
        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1 mb-3">
            Membuka halaman di bawah ini. Jika window belum aktif, middleware
            <code>EnsureSudo</code> melempar ke step-up Keycloak lebih dulu,
            lalu kembali ke sini.
        </p>
        <x-nawasara-ui::button
            href="{{ route('sudo.demo.protected') }}"
            variant="outline"
            color="primary"
        >
            Buka halaman ber-middleware sudo
        </x-nawasara-ui::button>
    </x-nawasara-ui::page.card>

    {{-- Livewire action gates --}}
    <x-nawasara-ui::page.card>
        <p class="text-sm font-medium text-neutral-700 dark:text-neutral-200">
            2. Gerbang level aksi Livewire
        </p>
        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1 mb-3">
            Dua tombol di bawah memicu aksi yang digerbang sudo. Jika window
            belum aktif, klik akan mengarahkan ke step-up; setelah OTP, window
            aktif dan aksi bisa dijalankan.
        </p>

        <div class="flex gap-3">
            <x-nawasara-ui::button
                wire:click="runDeclarative"
                color="warning"
            >
                Aksi declarative <span class="opacity-60 ml-1">#[RequiresSudo]</span>
            </x-nawasara-ui::button>

            <x-nawasara-ui::button
                wire:click="runImperative"
                color="warning"
                variant="outline"
            >
                Aksi imperative <span class="opacity-60 ml-1">requireSudo()</span>
            </x-nawasara-ui::button>
        </div>

        @if ($lastResult)
            <div class="mt-4 rounded-md bg-emerald-50 border border-emerald-200 px-3 py-2
                        dark:bg-emerald-900/20 dark:border-emerald-800">
                <p class="text-sm text-emerald-700 dark:text-emerald-300">
                    {{ $lastResult }}
                </p>
            </div>
        @endif
    </x-nawasara-ui::page.card>

</div>
