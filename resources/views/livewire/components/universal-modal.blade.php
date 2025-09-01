<div x-data="{ open: false }"
    x-on:open-modal.window="
        open = true;
        $wire.load($event.detail) // kirim request ke server untuk load komponen
    "
    x-on:modal-close.window="open = false" x-show="open"
    class="fixed inset-0 flex items-center justify-center bg-black/50" x-cloak>
    <div class="bg-white rounded-lg shadow-lg w-2/3 p-4">

        <div class="flex justify-between items-center border-b pb-2 mb-2">
            <h2 class="font-semibold text-lg" x-text="$wire.title"></h2>
            <button @click="open = false; ">✕</button>
        </div>

        <div>
            @if ($component)
                <div wire:loading.flex wire:target="load" class="justify-center py-8">
                    <div class="animate-spin rounded-full h-8 w-8 border-4 border-gray-400 border-t-transparent"></div>
                </div>
                <div wire:loading.remove wire:target="load">
                    <livewire:dynamic-component :is="$component" :params="$params" :key="$component" />
                </div>
            @else
                <p class="text-gray-600">Sedang memuat...</p>
            @endif
        </div>
    </div>
</div>
