<div>
    <x-nawasara-core::table :headers="['#', 'Name', 'Email', 'Username', 'Role', 'Created At']" title="Data User">
        <!-- Table Content -->
        <x-slot:table>
            @foreach ($this->items as $index => $item)
                @php
                    $histories = $item->histories;
                @endphp
                <tr>
                    <td>
                        <div class="m-5">

                            @php
                                $menuItems = [];
                            @endphp
                            @php
                                $menuItems = [
                                    [
                                        'type' => 'link',
                                        'label' => 'Edit',
                                        'url' => '',
                                        'color' => 'text-gray-800',
                                        'navigate' => false,
                                        'permission' => 'user.update',
                                    ],
                                    [
                                        'type' => 'delete',
                                        'label' => 'Delete',
                                        'color' => 'text-red-600',
                                        'permission' => 'user.delete',
                                    ],
                                ];
                            @endphp
                            {{-- @endif --}}

                            <x-nawasara-core::dropdown-menu-action :id="$item->id" :items="$menuItems" />
                        </div>
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $item->name }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $item->email }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $item->username }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {!! $item->renderRoles() !!}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ date_format_human($item->created_at) }}
                    </td>
                </tr>
            @endforeach
        </x-slot:table>

        <!-- Footer for Pagination -->
        <x-slot:footer>
            {{ $this->items->links() }}
        </x-slot:footer>
    </x-nawasara-core::table>

    {{-- modal confirm --}}
    {{-- <x-utils.modal-delete desc="Anda yakin ingin menghapus data ini ? data yang sudah dihapus tidak dapat dikembalikan!"
        id="modalConfirmDelete" wire:ignore /> --}}
</div>
