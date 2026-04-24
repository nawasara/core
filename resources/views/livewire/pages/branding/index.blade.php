<div>
    <x-slot name="breadcrumb">
        <livewire:nawasara-ui.shared-components.breadcrumb
            :items="[['label' => 'User Management', 'url' => '#'], ['label' => 'Branding']]" />
    </x-slot>

    <x-nawasara-ui::page.container>
        <x-nawasara-ui::page.title>Branding</x-nawasara-ui::page.title>

        <form wire:submit="save" class="space-y-6">
            {{-- App Identity --}}
            <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-neutral-200 mb-4">Identitas Aplikasi</h3>
                <div class="space-y-4">
                    <x-nawasara-ui::form.input label="Nama Aplikasi"
                        placeholder="Nawasara"
                        wire:model="appName"
                        useError errorVariable="appName" />

                    <x-nawasara-ui::form.input label="Subjudul (opsional)"
                        placeholder="Superapp Kominfo Ponorogo"
                        wire:model="appSubtitle"
                        useError errorVariable="appSubtitle" />
                </div>
            </div>

            {{-- Logos --}}
            <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-neutral-200 mb-4">Logo & Favicon</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Logo (light) --}}
                    <div>
                        <x-nawasara-ui::form.label value="Logo (Light Mode)" />
                        <div class="mt-2 space-y-3">
                            @if ($currentLogo)
                                <div class="p-4 bg-gray-50 dark:bg-neutral-900 rounded-lg border border-gray-200 dark:border-neutral-700 flex items-center justify-center h-24">
                                    <img src="{{ $currentLogo }}" class="max-h-16 max-w-full object-contain" alt="Logo" />
                                </div>
                                <button type="button" wire:click="removeLogo('logo')"
                                    class="text-xs text-red-600 hover:underline">Hapus logo</button>
                            @else
                                <div class="p-4 bg-gray-50 dark:bg-neutral-900 rounded-lg border border-dashed border-gray-300 dark:border-neutral-600 flex items-center justify-center h-24 text-xs text-gray-400">
                                    Belum ada logo
                                </div>
                            @endif

                            <input type="file" wire:model="logo" accept="image/*"
                                class="block w-full text-xs text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 dark:text-neutral-400 dark:file:bg-green-900/20 dark:file:text-green-400" />

                            @error('logo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

                            <div wire:loading wire:target="logo" class="text-xs text-gray-500">Uploading...</div>

                            @if ($logo)
                                <div class="p-2 bg-green-50 dark:bg-green-900/20 rounded text-xs text-green-700 dark:text-green-400">
                                    Preview: {{ $logo->getClientOriginalName() }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Logo Dark --}}
                    <div>
                        <x-nawasara-ui::form.label value="Logo (Dark Mode, opsional)" />
                        <div class="mt-2 space-y-3">
                            @if ($currentLogoDark)
                                <div class="p-4 bg-neutral-900 rounded-lg border border-gray-200 dark:border-neutral-700 flex items-center justify-center h-24">
                                    <img src="{{ $currentLogoDark }}" class="max-h-16 max-w-full object-contain" alt="Logo Dark" />
                                </div>
                                <button type="button" wire:click="removeLogo('logo_dark')"
                                    class="text-xs text-red-600 hover:underline">Hapus logo</button>
                            @else
                                <div class="p-4 bg-neutral-900 rounded-lg border border-dashed border-gray-300 dark:border-neutral-600 flex items-center justify-center h-24 text-xs text-gray-500">
                                    Belum ada logo dark
                                </div>
                            @endif

                            <input type="file" wire:model="logoDark" accept="image/*"
                                class="block w-full text-xs text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 dark:text-neutral-400 dark:file:bg-green-900/20 dark:file:text-green-400" />

                            @error('logoDark') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

                            <div wire:loading wire:target="logoDark" class="text-xs text-gray-500">Uploading...</div>
                        </div>
                    </div>

                    {{-- Favicon --}}
                    <div>
                        <x-nawasara-ui::form.label value="Favicon" />
                        <div class="mt-2 space-y-3">
                            @if ($currentFavicon)
                                <div class="p-4 bg-gray-50 dark:bg-neutral-900 rounded-lg border border-gray-200 dark:border-neutral-700 flex items-center justify-center h-24">
                                    <img src="{{ $currentFavicon }}" class="size-16 object-contain" alt="Favicon" />
                                </div>
                                <button type="button" wire:click="removeLogo('favicon')"
                                    class="text-xs text-red-600 hover:underline">Hapus favicon</button>
                            @else
                                <div class="p-4 bg-gray-50 dark:bg-neutral-900 rounded-lg border border-dashed border-gray-300 dark:border-neutral-600 flex items-center justify-center h-24 text-xs text-gray-400">
                                    Belum ada favicon
                                </div>
                            @endif

                            <input type="file" wire:model="favicon" accept="image/png,image/svg+xml,image/x-icon,.ico"
                                class="block w-full text-xs text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 dark:text-neutral-400 dark:file:bg-green-900/20 dark:file:text-green-400" />

                            @error('favicon') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror

                            <div wire:loading wire:target="favicon" class="text-xs text-gray-500">Uploading...</div>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-gray-500 dark:text-neutral-400 mt-4">
                    Logo terbaik: format PNG atau SVG dengan background transparan. Ukuran direkomendasikan max 200px lebar.
                </p>
            </div>

            {{-- Submit --}}
            <div class="flex justify-end gap-3">
                <x-nawasara-ui::button type="submit" color="primary">
                    <x-slot:icon><x-lucide-save class="size-4" /></x-slot:icon>
                    Simpan Branding
                </x-nawasara-ui::button>
            </div>
        </form>
    </x-nawasara-ui::page.container>
</div>
