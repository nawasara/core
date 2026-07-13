<div>
    <x-slot name="breadcrumb">
        <livewire:nawasara-ui.shared-components.breadcrumb :items="[['label' => 'Riwayat Update']]" />
    </x-slot>

    <x-nawasara-ui::page.container>
        <x-nawasara-ui::page-header
            title="Riwayat Update"
            description="Fitur dan perbaikan terbaru di Nawasara.">
            @can('core.changelog.manage')
                <x-nawasara-ui::button color="primary" size="sm" icon="lucide-pencil"
                    href="{{ route('nawasara-core.changelog.manage') }}" wire:navigate>
                    Kelola
                </x-nawasara-ui::button>
            @endcan
        </x-nawasara-ui::page-header>

        @if ($entries->isEmpty())
            <x-nawasara-ui::empty-state icon="lucide-sparkles"
                title="Belum ada catatan update"
                description="Update fitur dan perbaikan akan muncul di sini." />
        @else
            {{-- Timeline: entri terbaru di atas --}}
            <div class="relative space-y-6">
                @foreach ($entries as $entry)
                    <div class="relative flex gap-4">
                        {{-- rail + dot --}}
                        <div class="flex flex-col items-center">
                            <span @class([
                                'flex size-9 items-center justify-center rounded-full ring-4 ring-white dark:ring-neutral-900',
                                'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400' => $entry->category === 'feature',
                                'bg-sky-100 text-sky-600 dark:bg-sky-900/40 dark:text-sky-400' => $entry->category === 'improvement',
                                'bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400' => $entry->category === 'security',
                                'bg-neutral-100 text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400' => $entry->category === 'fix',
                            ])>
                                <x-dynamic-component :component="match($entry->category) {
                                    'feature' => 'lucide-sparkles',
                                    'improvement' => 'lucide-trending-up',
                                    'security' => 'lucide-shield-check',
                                    default => 'lucide-wrench',
                                }" class="size-4" />
                            </span>
                            @unless ($loop->last)
                                <span class="mt-1 w-px flex-1 bg-neutral-200 dark:bg-neutral-700"></span>
                            @endunless
                        </div>

                        {{-- content --}}
                        <x-nawasara-ui::page.card class="flex-1 mb-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                <x-nawasara-ui::badge :color="$entry->categoryColor()">{{ $entry->categoryLabel() }}</x-nawasara-ui::badge>
                                @if ($entry->is_major)
                                    <x-nawasara-ui::badge color="warning">Update Besar</x-nawasara-ui::badge>
                                @endif
                                <span class="text-xs text-neutral-500 dark:text-neutral-400">
                                    {{ $entry->published_at?->translatedFormat('d F Y') }}
                                </span>
                            </div>
                            <h3 class="text-base font-semibold text-neutral-800 dark:text-neutral-100">{{ $entry->title }}</h3>
                            <div class="mt-1.5 text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed whitespace-pre-line">{{ $entry->body }}</div>
                            @if ($entry->version_tag)
                                <div class="mt-2 text-xs font-mono text-neutral-400 dark:text-neutral-500">{{ $entry->version_tag }}</div>
                            @endif
                        </x-nawasara-ui::page.card>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $entries->links() }}</div>
        @endif
    </x-nawasara-ui::page.container>
</div>
