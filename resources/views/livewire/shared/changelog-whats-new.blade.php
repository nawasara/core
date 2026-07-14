<div>
    {{-- Login "What's New" modal. Livewire-driven visibility via wire:model="show".
         Shows once after a major update; dismissing marks everything seen. --}}
    <x-nawasara-ui::modal wire:model="show" maxWidth="lg" title="✨ Ada Update Baru!"
        subtitle="Beberapa peningkatan terbaru di Nawasara">

        <div class="space-y-3">
            @foreach ($entries as $e)
                <div class="flex items-start gap-3 rounded-lg border border-neutral-200 p-3 dark:border-neutral-700">
                    <div class="mt-0.5 shrink-0">
                        <x-nawasara-ui::badge :color="$e['category_color']">{{ $e['category_label'] }}</x-nawasara-ui::badge>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-neutral-800 dark:text-neutral-100">
                            {{ $e['title'] }}
                            @if ($e['is_major'])
                                <span class="ml-1 align-middle text-[10px] font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-400">Penting</span>
                            @endif
                        </p>
                        @if ($e['version_tag'])
                            <p class="mt-0.5 text-xs text-neutral-500 dark:text-neutral-400">{{ $e['version_tag'] }}</p>
                        @endif
                    </div>
                </div>
            @endforeach

            @if ($count > count($entries))
                <p class="text-center text-xs text-neutral-500 dark:text-neutral-400">
                    dan {{ $count - count($entries) }} update lainnya…
                </p>
            @endif
        </div>

        <x-slot:footer>
            <x-nawasara-ui::button color="neutral" variant="outline" wire:click="acknowledge">
                Tutup
            </x-nawasara-ui::button>
            {{-- markSeen first (acknowledge), then navigate to the full page.
                 href auto-renders an <a>; wire:navigate keeps SPA nav. --}}
            <x-nawasara-ui::button
                color="primary"
                :href="route('nawasara-core.changelog.index')"
                wire:navigate
                wire:click="acknowledge">
                Lihat Semua
            </x-nawasara-ui::button>
        </x-slot:footer>
    </x-nawasara-ui::modal>
</div>
