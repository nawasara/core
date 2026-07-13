<div>
    <a href="{{ route('nawasara-core.changelog.index') }}" wire:navigate
       class="relative inline-flex items-center justify-center size-9 rounded-lg text-neutral-500 hover:bg-neutral-100 hover:text-neutral-700 dark:text-neutral-400 dark:hover:bg-neutral-800 dark:hover:text-neutral-200 transition-colors"
       title="Riwayat Update{{ $unread > 0 ? ' — '.$unread.' update baru' : '' }}">
        <x-lucide-sparkles class="size-5" />
        @if ($unread > 0)
            <span class="absolute -top-0.5 -right-0.5 flex min-w-[1.1rem] h-[1.1rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold text-white ring-2 ring-white dark:ring-neutral-900">
                {{ $unread > 9 ? '9+' : $unread }}
            </span>
        @endif
    </a>
</div>
