<div>
    <h4 class="text-lg font-bold mb-2">Livewire Modal</h4>
    <p>{{ $message }}</p>
    <button class="mt-2 px-3 py-1 bg-gray-200 rounded" wire:click="$emit('modal-close')">Tutup Modal</button>
</div>
