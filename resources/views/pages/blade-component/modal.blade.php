<div>
    <div class="mt-6">
        <h3>Modal</h3>
        <div class="space-y-6">
            <button class="px-4 py-2 bg-blue-600 text-white rounded"
                @click="$dispatch('open-modal', {
        title: 'Form Input Data',
        component: 'nawasara-core.examples.demo-modal',
        params: { userId: 5 }
    })">
                Buka Modal
            </button>
            <!-- 1. Static Content via JS Event -->
            <button class="px-4 py-2 bg-blue-600 text-white rounded"
                onclick="window.dispatchEvent(new CustomEvent('modal-open', {detail: {modalId:'contoh-static-modal', title: 'Modal Static', content: '<p>Ini modal static via JS event.</p>', size: 'md'}}))">Modal
                Static via JS</button>

            <!-- 3. Modal dengan Livewire sebagai konten -->
            <button class="px-4 py-2 bg-purple-600 text-white rounded"
                onclick="window.dispatchEvent(new CustomEvent('modal-open', 
                {
                    detail: {
                        modalId:'contoh-modal-livewire',
                        title: 'Modal Livewire',
                        content: 'nawasara-core.examples.demo-modal',
                        contentType: 'livewire', size: 'md'
                    }
                }))">
                ModalLivewire</button>

            <!-- 4. Modal Confirm/Alert -->
            <button class="px-4 py-2 bg-red-600 text-white rounded"
                onclick="window.dispatchEvent(new CustomEvent('modal-open', {detail: {modalId:'contoh-modal-confirm', title: 'Konfirmasi', content: '<p>Anda yakin ingin menghapus data?</p>', confirm: true, onConfirm: function() { alert('Data dihapus!'); }, size: 'sm'}}))">Modal
                Konfirmasi</button>
        </div>

    </div>
</div>
