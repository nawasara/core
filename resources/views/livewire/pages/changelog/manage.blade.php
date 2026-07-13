<div>
    <x-slot name="breadcrumb">
        <livewire:nawasara-ui.shared-components.breadcrumb
            :items="[['label' => 'Riwayat Update', 'url' => route('nawasara-core.changelog.index')], ['label' => 'Kelola']]" />
    </x-slot>

    <x-nawasara-ui::page.container>
        <x-nawasara-ui::page-header
            title="Kelola Riwayat Update"
            description="Tulis catatan update dalam bahasa yang mudah dipahami pengguna.">
            <x-nawasara-ui::button color="primary" size="sm" icon="lucide-plus"
                x-on:click="$dispatch('open-modal', { id: 'changelog-form', loading: false })"
                wire:click="create">
                Tambah Update
            </x-nawasara-ui::button>
        </x-nawasara-ui::page-header>

        <x-nawasara-ui::page.card>
            @if ($entries->isEmpty())
                <x-nawasara-ui::empty-state icon="lucide-sparkles"
                    title="Belum ada catatan"
                    description="Klik 'Tambah Update' untuk menulis catatan pertama." />
            @else
                <x-nawasara-ui::table stickyLast :headers="['Judul', 'Kategori', 'Tanggal', 'Status', '']">
                    <x-slot:table>
                        @foreach ($entries as $entry)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-neutral-800 dark:text-neutral-100">{{ $entry->title }}</span>
                                        @if ($entry->is_major)
                                            <x-nawasara-ui::badge color="warning">Besar</x-nawasara-ui::badge>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <x-nawasara-ui::badge :color="$entry->categoryColor()">{{ $entry->categoryLabel() }}</x-nawasara-ui::badge>
                                </td>
                                <td class="px-4 py-3 text-sm text-neutral-500 dark:text-neutral-400 whitespace-nowrap">
                                    {{ $entry->published_at?->translatedFormat('d M Y') ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($entry->isPublished())
                                        <x-nawasara-ui::badge color="success">Terbit</x-nawasara-ui::badge>
                                    @else
                                        <x-nawasara-ui::badge color="neutral">Draft</x-nawasara-ui::badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <x-nawasara-ui::dropdown-menu-action :id="$entry->id" :items="[
                                        ['type' => 'click', 'label' => 'Edit', 'wire:click' => 'edit('.$entry->id.')', 'modal' => 'changelog-form', 'icon' => 'lucide-pencil'],
                                        ['type' => 'click', 'label' => $entry->isPublished() ? 'Jadikan Draft' : 'Terbitkan', 'wire:click' => 'togglePublish('.$entry->id.')', 'icon' => $entry->isPublished() ? 'lucide-eye-off' : 'lucide-send'],
                                        ['type' => 'click', 'label' => 'Hapus', 'wire:click' => 'delete('.$entry->id.')', 'icon' => 'lucide-trash-2', 'confirm' => 'Hapus catatan ini?'],
                                    ]" />
                                </td>
                            </tr>
                        @endforeach
                    </x-slot:table>
                </x-nawasara-ui::table>
                <div class="mt-4">{{ $entries->links() }}</div>
            @endif
        </x-nawasara-ui::page.card>

        {{-- Form modal --}}
        <x-nawasara-ui::modal id="changelog-form" :title="$editingId ? 'Edit Update' : 'Tambah Update'" maxWidth="lg">
            <form wire:submit="save" class="space-y-4">
                <div>
                    <x-nawasara-ui::form.input label="Judul" wire:model="title"
                        placeholder="mis. Deteksi Situs Ter-retas" />
                    @error('title') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <x-nawasara-ui::form.textarea label="Deskripsi" wire:model="body" :rows="4"
                        hint="Bahasa non-teknis, fokus manfaat untuk pengguna."
                        placeholder="Jelaskan update ini dari sisi manfaat pengguna…" />
                    @error('body') <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-nawasara-ui::form.select label="Kategori" wire:model="category"
                            :options="\Nawasara\Core\Models\ChangelogEntry::categoryLabels()" />
                    </div>
                    <div>
                        <x-nawasara-ui::form.input label="Versi (opsional)" wire:model="versionTag"
                            placeholder="mis. secscan v0.9.0" />
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <x-nawasara-ui::form.checkbox wire:model="isMajor" label="Tandai sebagai Update Besar (di-highlight)" />
                    <x-nawasara-ui::form.checkbox wire:model="publishNow" label="Terbitkan sekarang (hilangkan centang untuk simpan sebagai draft)" />
                </div>
            </form>

            <x-slot:footer>
                <x-nawasara-ui::button color="neutral" x-on:click="$dispatch('close-modal', 'changelog-form')">Batal</x-nawasara-ui::button>
                <x-nawasara-ui::button color="primary" wire:click="save">Simpan</x-nawasara-ui::button>
            </x-slot:footer>
        </x-nawasara-ui::modal>
    </x-nawasara-ui::page.container>
</div>
