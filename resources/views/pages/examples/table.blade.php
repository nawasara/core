<x-nawasara-core::layouts.app>
    <x-slot:title>
        Table Component - Nawasara Core
    </x-slot:title>

    <x-nawasara-core::layouts.container>
        <x-slot name="breadcrumb">
            <x-nawasara-core::layouts.breadcrumb :items="[
                ['label' => 'Dashboard', 'url' => '/'],
                ['label' => 'Users', 'url' => '/'],
                ['label' => 'Create'],
            ]" />
        </x-slot>

        <x-slot name="title">
            <x-nawasara-core::layouts.page-title>Tambah User</x-nawasara-core::layouts.page-title>
        </x-slot>

        <x-slot name="actions">
            <x-nawasara-core::layouts.actions>
                <a href="{{ '/' }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                    Kembali
                </a>
            </x-nawasara-core::layouts.actions>
        </x-slot>

        {{-- <x-nawasara-core::layouts.card> --}}
        <x-nawasara-core::table :headers="['Aksi', 'Tanggal', 'Jenis Kegiatan']" title="Data Kegiatan Gerakan Penanaman Pohon">
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
        {{-- </x-nawasara-core::layouts.card> --}}
    </x-nawasara-core::layouts.container>

</x-nawasara-core::layouts.app>
