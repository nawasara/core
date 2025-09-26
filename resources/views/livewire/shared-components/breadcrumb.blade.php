<!-- Breadcrumb -->
<div
    class="sticky top-0 inset-x-0 z-20 bg-white border-y border-gray-200 px-4 sm:px-6 lg:px-8 lg:hidden dark:bg-neutral-800 dark:border-neutral-700">
    <div class="flex items-center py-2">
        <!-- Navigation Toggle -->
        <button type="button"
            class="size-8 flex justify-center items-center gap-x-2 border border-gray-200 text-gray-800 hover:text-gray-500 rounded-lg focus:outline-hidden focus:text-gray-500 disabled:opacity-50 disabled:pointer-events-none dark:border-neutral-700 dark:text-neutral-200 dark:hover:text-neutral-500 dark:focus:text-neutral-500"
            aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-application-sidebar"
            aria-label="Toggle navigation" data-hs-overlay="#hs-application-sidebar">
            <span class="sr-only">Toggle Navigation</span>
            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <rect width="18" height="18" x="3" y="3" rx="2" />
                <path d="M15 3v18" />
                <path d="m8 9 3 3-3 3" />
            </svg>
        </button>
        <!-- End Navigation Toggle -->

        <!-- Breadcrumb -->
        <ol class="ms-3 flex items-center whitespace-nowrap">
            @foreach ($items as $breadcrumb)
                <li class="flex items-center text-sm {{ !$loop->last ? 'text-gray-800 dark:text-neutral-400' : 'font-semibold text-gray-800 truncate dark:text-neutral-400' }}"
                    {{ $loop->last ? 'aria-current=page' : '' }}>
                    @if (!$loop->last)
                        <a href="{{ $breadcrumb['link'] ?? '#' }}" class="hover:underline">
                            {{ $breadcrumb['label'] }}
                        </a>
                        <svg class="shrink-0 mx-3 overflow-visible size-2.5 text-gray-400 dark:text-eutral-500"
                            width="16" height="16" viewBox="0 0 16 16" fill="none"n
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 1L10.6869 7.16086C10.8637 7.35239 10.8637 7.64761 10.6869 7.83914L5 14"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    @else
                        {{ $breadcrumb['label'] }}
                    @endif
                </li>
            @endforeach
        </ol>
        <!-- End Breadcrumb -->
    </div>
</div>
<!-- End Breadcrumb -->
