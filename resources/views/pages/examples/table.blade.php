<x-nawasara-core::layouts.app>
    <x-slot:title>
        Table Component - Nawasara Core
    </x-slot:title>

    <x-slot name="breadcrumb">
        <livewire:nawasara-core.shared-components.breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => '/'],
            ['label' => 'Component', 'url' => '/'],
            ['label' => 'Table'],
        ]" />
    </x-slot>

    <x-nawasara-core::page.container>
        <x-slot name="title">
            <x-nawasara-core::page.title>Tambah User</x-nawasara-core::page.title>
        </x-slot>

        <x-slot name="actions">
            <x-nawasara-core::page.actions>
                <a href="{{ '/' }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                    Kembali
                </a>
            </x-nawasara-core::page.actions>
        </x-slot>

        {{-- <x-nawasara-core::page.card> --}}
        <x-nawasara-core::table :headers="['Aksi', 'Tanggal', 'Jenis Kegiatan']" title="Data Kegiatan Gerakan Penanaman Pohon" useSearch="true">
            <!-- Table Content -->
            <x-slot:table>
                <tr>
                    <td class="p-4"><x-nawasara-core::dropdown-menu-action id="1" modalName="deleteModal"
                            :items="[
                                [
                                    'label' => 'Edit',
                                    'type' => 'href-navigate',
                                    'url' => '#',
                                    'permission' => 'village.edit',
                                ],
                                [
                                    'label' => 'Delete',
                                    'type' => 'delete',
                                    'permission' => 'village.delete',
                                ],
                                [
                                    'label' => 'Custom Action',
                                    'type' => 'click',
                                    'action' => 'doSomething',
                                    'param' => 1,
                                    'permission' => 'village.custom',
                                ],
                            ]" />
                    </td>
                    <td class="p-4">2 Januari 2025</td>
                    <td class="p-4">Reboisasi</td>
                </tr>
                <td class="p-4"><x-nawasara-core::dropdown-menu-action id="2" modalName="deleteModal"
                        :items="[
                            [
                                'label' => 'Edit',
                                'type' => 'href-navigate',
                                'url' => '#',
                                'permission' => 'village.edit',
                            ],
                            [
                                'label' => 'Delete',
                                'type' => 'delete',
                                'permission' => 'village.delete',
                            ],
                            [
                                'label' => 'Custom Action',
                                'type' => 'click',
                                'action' => 'doSomething',
                                'param' => 1,
                                'permission' => 'village.custom',
                            ],
                        ]" />
                </td>
                <td class="p-4">03 Juni 2025</td>
                <td class="p-4">CSR</td>
                </tr>
            </x-slot:table>

            <!-- Footer for Pagination -->
            <x-slot:footer>
                {{-- {{ $this->items->links() }} --}}
            </x-slot:footer>
        </x-nawasara-core::table>
        {{-- </x-nawasara-core::page.card> --}}
    </x-nawasara-core::page.container>

</x-nawasara-core::layouts.app>
