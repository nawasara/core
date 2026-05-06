<div class="space-y-8">
    {{-- Greeting block — context-aware (time of day + user name) --}}
    <div class="flex items-end justify-between gap-4 flex-wrap">
        <div>
            <p class="text-sm text-gray-500 dark:text-neutral-400 font-medium">
                {{ now()->isoFormat('dddd, D MMMM Y') }}
            </p>
            <h1 class="mt-1 text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
                {{ $this->greeting }}, <span class="text-emerald-700 dark:text-emerald-400">{{ auth()->user()->name ?? 'Admin' }}</span>
            </h1>
            <p class="mt-1.5 text-sm text-gray-600 dark:text-neutral-400 max-w-xl">
                Berikut ringkasan operasional {{ brand('app_name', 'Nawasara') }} hari ini.
            </p>
        </div>

        <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-neutral-500">
            @if ($lastUpdatedAt)
                {{-- wire:key memaksa Livewire untuk re-render element ini ketika
                     $lastUpdatedAt berubah, sehingga Alpine x-data + x-init
                     fire ulang dengan timestamp baru. Tanpa key, x-init capture
                     timestamp lama lewat closure dan tidak update saat refresh. --}}
                <span class="inline-flex items-center gap-1" wire:key="last-updated-{{ $lastUpdatedAt }}">
                    <x-lucide-clock class="size-3.5" />
                    Diperbarui
                    <time datetime="{{ $lastUpdatedAt }}"
                        x-data="{
                            rel: '',
                            timestamp: new Date('{{ $lastUpdatedAt }}').getTime(),
                            tick() {
                                const diff = Math.floor((Date.now() - this.timestamp) / 1000);
                                this.rel = diff < 5
                                    ? 'baru saja'
                                    : (diff < 60 ? diff + ' detik lalu'
                                    : (diff < 3600 ? Math.floor(diff / 60) + ' menit lalu'
                                    : Math.floor(diff / 3600) + ' jam lalu'));
                            }
                        }"
                        x-init="tick(); setInterval(() => tick(), 15000);"
                        x-text="rel"></time>
                </span>
            @endif
            <x-nawasara-ui::button color="neutral" variant="outline" size="sm" wire:click="refresh">
                <x-slot:icon>
                    <x-lucide-refresh-cw wire:loading.class="animate-spin" wire:target="refresh" />
                </x-slot:icon>
                Refresh
            </x-nawasara-ui::button>
        </div>
    </div>

    {{-- Hero stats row --}}
    @if (count($this->stats) > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($this->stats as $stat)
                <x-nawasara-ui::stat-card
                    :label="$stat['label']"
                    :value="$stat['value']"
                    :icon="$stat['icon']"
                    :color="$stat['color']"
                    :trend="$stat['trend'] ?? null"
                    :description="$stat['description'] ?? null"
                    accent />
            @endforeach
        </div>
    @endif

    {{-- Workspace launcher --}}
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">Workspace</h2>
                <p class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5">
                    Pilih workspace untuk mulai mengelola layanan
                </p>
            </div>
            @if (count($this->workspaces) > 0)
                <span class="text-xs font-medium text-gray-500 dark:text-neutral-400">
                    {{ count($this->workspaces) }} workspace tersedia
                </span>
            @endif
        </div>

        @if (count($this->workspaces) === 0)
            {{-- Proper empty state with CTA --}}
            <div class="text-center py-16 px-6 border-2 border-dashed border-gray-200 dark:border-neutral-700 rounded-xl bg-gray-50/50 dark:bg-neutral-900/40">
                <div class="inline-flex items-center justify-center size-14 rounded-2xl bg-gray-100 dark:bg-neutral-800 mb-4">
                    <x-lucide-lock class="size-7 text-gray-400 dark:text-neutral-500" />
                </div>
                <p class="text-base font-semibold text-gray-800 dark:text-neutral-200">
                    Belum ada workspace yang bisa diakses
                </p>
                <p class="mt-2 text-sm text-gray-500 dark:text-neutral-400 max-w-sm mx-auto">
                    Akun kamu belum punya permission untuk membuka workspace mana pun.
                    Hubungi administrator untuk meminta akses sesuai role kerjamu.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach ($this->workspaces as $workspace)
                    <a href="{{ $workspace['first_url'] ?? '#' }}" wire:navigate
                        class="group relative bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl p-5
                               hover:border-emerald-600 dark:hover:border-emerald-500 hover:shadow-md hover:-translate-y-0.5
                               transition-all duration-200">
                        <div class="flex items-start gap-3">
                            <div class="flex items-center justify-center size-11 rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
                                        group-hover:bg-emerald-700 group-hover:text-white dark:group-hover:bg-emerald-600
                                        transition-colors duration-200">
                                <x-dynamic-component :component="$workspace['icon']" class="size-5" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-900 dark:text-neutral-100 truncate">
                                    {{ $workspace['label'] }}
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5">
                                    {{ $workspace['submenu_count'] }} menu tersedia
                                </p>
                            </div>
                            <x-lucide-arrow-up-right
                                class="size-4 text-gray-300 dark:text-neutral-600 shrink-0
                                       group-hover:text-emerald-700 dark:group-hover:text-emerald-400
                                       group-hover:-translate-y-0.5 group-hover:translate-x-0.5
                                       transition-all duration-200" />
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
