<x-nawasara-core::layouts.app>
    <x-slot:title>
        Table Component - Nawasara Core
    </x-slot:title>

    <div class="py-6">
        <div class="sm:flex sm:items-center mb-5">
            <div class="sm:flex-auto">
                <h1 class="text-base font-semibold leading-6 text-gray-900">Kegiatan</h1>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <x-nawasara-core::button variant="solid" color="success" href="#">
                    Buat Baru
                </x-nawasara-core::button>
            </div>
        </div>
        {{-- <div class="bg-white shadow rounded-lg p-6"> --}}
        <div>
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
        </div>

        {{-- </div> --}}
    </div>

</x-nawasara-core::layouts.app>
