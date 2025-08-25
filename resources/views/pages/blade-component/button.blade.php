<div>
    <div class="mt-6">
        <h3>Basic Button</h3>
        {{-- Solid default (primary) --}}
        <x-nawasara-core::button>
            Simpan
        </x-nawasara-core::button>

        {{-- Outline + icon kiri --}}
        <x-nawasara-core::button variant="outline" color="warning">
            <x-slot:icon>
                {{-- contoh pakai Heroicons Outline "Check" --}}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
            </x-slot:icon>
            Terverifikasi
        </x-nawasara-core::button>

        {{-- Ghost + trailing icon --}}
        <x-nawasara-core::button variant="ghost" color="primary">
            Selengkapnya
            <x-slot:trailing>
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M9.47 4.97a.75.75 0 0 1 1.06 0l6.5 6.5a.75.75 0 0 1 0 1.06l-6.5 6.5a.75.75 0 1 1-1.06-1.06L15.94 12 9.47 5.53a.75.75 0 0 1 0-1.06Z" />
                </svg>
            </x-slot:trailing>
        </x-nawasara-core::button>

        {{-- Flat color success, size lg --}}
        <x-nawasara-core::button variant="flat" color="success" size="lg">
            Publish
        </x-nawasara-core::button>

        {{-- Link variant sebagai anchor --}}
        <x-nawasara-core::button variant="link" href="/terms">
            Syarat & Ketentuan
        </x-nawasara-core::button>

        {{-- Icon-only (auto terdeteksi karena tanpa teks) --}}
        <x-nawasara-core::button aria-label="Tambah" color="success">
            {{-- contoh pakai Heroicons Solid "Plus" --}}
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2h6Z" />
                </svg>
            </x-slot:icon>
        </x-nawasara-core::button>

        {{-- Full width + disabled --}}
        <x-nawasara-core::button full :disabled="true">
            Mengirim...
        </x-nawasara-core::button>

    </div>
</div>
