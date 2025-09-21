<div>
    <!-- Floating Button -->
    <button wire:click="toggleTools"
        class="fixed bottom-6 right-6 w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex items-center justify-center z-50 transition-all duration-300"
        aria-label="Developer Tools" style="box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>

    </button>

    <!-- Popup -->
    @if ($isOpen)
        <div class="fixed bottom-24 right-6 w-80 bg-white rounded-lg shadow-xl z-50 border border-gray-200 overflow-hidden"
            style="box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);">
            <div class="bg-blue-600 text-white px-4 py-2 font-semibold flex justify-between items-center">
                <span>Developer Tools</span>
                <button wire:click="toggleTools" class="text-white hover:text-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-4 max-h-96 overflow-y-auto">
                <!-- Command Buttons -->
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <button wire:click="runMigrateFresh" wire:loading.attr="disabled"
                        class="bg-red-500 hover:bg-red-600 text-white text-xs py-2 px-3 rounded transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="runMigrateFresh">Migrate Fresh</span>
                        <span wire:loading wire:target="runMigrateFresh">Running...</span>
                    </button>

                    <button wire:click="runMigrateFreshSeed" wire:loading.attr="disabled"
                        class="bg-red-600 hover:bg-red-700 text-white text-xs py-2 px-3 rounded transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="runMigrateFreshSeed">Fresh + Seed</span>
                        <span wire:loading wire:target="runMigrateFreshSeed">Running...</span>
                    </button>

                    <button wire:click="runDbSeed" wire:loading.attr="disabled"
                        class="bg-green-500 hover:bg-green-600 text-white text-xs py-2 px-3 rounded transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="runDbSeed">DB Seed</span>
                        <span wire:loading wire:target="runDbSeed">Running...</span>
                    </button>

                    <button wire:click="runVendorPublish" wire:loading.attr="disabled"
                        class="bg-purple-500 hover:bg-purple-600 text-white text-xs py-2 px-3 rounded transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="runVendorPublish">Vendor Publish</span>
                        <span wire:loading wire:target="runVendorPublish">Running...</span>
                    </button>

                    <button wire:click="runClearCache" wire:loading.attr="disabled"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs py-2 px-3 rounded transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="runClearCache">Clear Cache</span>
                        <span wire:loading wire:target="runClearCache">Running...</span>
                    </button>

                    <button wire:click="runRouteClear" wire:loading.attr="disabled"
                        class="bg-blue-500 hover:bg-blue-600 text-white text-xs py-2 px-3 rounded transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="runRouteClear">Clear Route</span>
                        <span wire:loading wire:target="runRouteClear">Running...</span>
                    </button>

                    <button wire:click="runOptimize" wire:loading.attr="disabled"
                        class="bg-indigo-500 hover:bg-indigo-600 text-white text-xs py-2 px-3 rounded transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="runOptimize">Optimize</span>
                        <span wire:loading wire:target="runOptimize">Running...</span>
                    </button>

                    <button wire:click="runStorageLink" wire:loading.attr="disabled"
                        class="bg-pink-500 hover:bg-pink-600 text-white text-xs py-2 px-3 rounded transition-colors disabled:opacity-50">
                        <span wire:loading.remove wire:target="runStorageLink">Storage Link</span>
                        <span wire:loading wire:target="runStorageLink">Running...</span>
                    </button>
                </div>

                <!-- Output -->
                @if ($output)
                    <div class="mt-4 border-t pt-3">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium">Output:</span>
                            <button wire:click="resetOutput" class="text-xs text-gray-500 hover:text-gray-700">
                                Clear
                            </button>
                        </div>
                        <div
                            class="bg-gray-100 p-3 rounded text-xs font-mono whitespace-pre-wrap overflow-x-auto max-h-40">
                            {{ $output }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Overlay -->
    @if ($isOpen)
        <div class="fixed inset-0 z-40" wire:click="toggleTools"></div>
    @endif
</div>
