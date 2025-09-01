<div x-data="{ open: false }" x-on:open-modal.window="if($event.detail.id === '{{ $id }}') open = true"
    x-on:close-modal.window="if($event.detail.id === '{{ $id }}') open = false" x-show="open" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center">
    <!-- Overlay -->
    <div class="fixed inset-0 bg-black/50" @click="open = false"></div>

    <!-- Modal Box -->
    <div x-transition class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg max-w-lg w-full p-6">
        <!-- Header -->
        @if (isset($title))
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h2 class="text-lg font-semibold">{{ $title }}</h2>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                    ✕
                </button>
            </div>
        @endif

        <!-- Content -->
        <div class="modal-body">
            {{ $slot }}
        </div>

        <!-- Footer -->
        @if (isset($footer))
            <div class="mt-4 flex justify-end space-x-2">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
