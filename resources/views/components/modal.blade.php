<div x-data="modalManager()" x-init="init()" @modal-open.window="open($event.detail)" @modal-close.window="close()"
    x-show="visible" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
    style="display: none;">
    <div :class="sizeClass" class="bg-white dark:bg-neutral-900 rounded-lg shadow-lg w-full max-w-lg mx-4"
        @click.away="close()" @keydown.escape.window="close()">
        <div class="flex justify-between items-center px-4 py-2 border-b dark:border-neutral-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="title"></h3>
            <button @click="close()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-4">
            <p x-text="content" class="text-gray-800"></p>
            <template x-if="contentType == 'html'">
                <div x-html="content"></div>
            </template>
            <template x-if="contentType == 'livewire' && livewire">
                {{-- <div x-html="livewire"></div> --}}
                {{-- <livewire:dynamic-component :is="content" /> --}}
            </template>
            <template x-if="contentType == 'slot'">
                <div>
                    {{ $slot }}
                </div>
            </template>
        </div>
    </div>
    <div class="flex justify-end gap-2 px-4 py-2 border-t dark:border-neutral-700" x-show="showConfirm">
        <button @click="close()"
            class="px-4 py-2 rounded bg-gray-200 dark:bg-neutral-800 text-gray-700 dark:text-white">Batal</button>
        <button @click="confirm()" class="px-4 py-2 rounded bg-green-600 text-white">OK</button>
    </div>
</div>
</div>

<script>
    function modalManager() {
        return {
            modalId: null,
            visible: false,
            livewire: null,
            title: '',
            content: '',
            contentType: 'html', // html, livewire, slot
            size: 'md',
            showConfirm: false,
            confirmCallback: null,
            get sizeClass() {
                return {
                    'max-w-sm': this.size === 'sm',
                    'max-w-md': this.size === 'md',
                    'max-w-lg': this.size === 'lg',
                    'max-w-xl': this.size === 'xl',
                };
            },
            init() {},
            open(options = {}) {
                this.modalId = options.modalId || null;
                this.title = options.title || '';
                this.content = options.content || '';
                this.contentType = options.contentType || 'html';
                this.size = options.size || 'md';
                this.showConfirm = !!options.confirm;
                this.confirmCallback = options.onConfirm || null;
                this.visible = true;
            },
            close() {
                this.visible = false;
                this.title = '';
                this.content = '';
                this.showConfirm = false;
                this.confirmCallback = null;
                this.modalId = null;
            },
            confirm() {
                if (typeof this.confirmCallback === 'function') {
                    this.confirmCallback();
                }
                this.close();
            }
        }
    }
</script>
