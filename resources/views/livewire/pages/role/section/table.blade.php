<div>
    <x-nawasara-core::table :headers="['#', 'Name', 'Total Permission', 'Total Role User', 'Created At']" title="Role Data">
        <!-- Table Content -->
        <x-slot:table>
            @foreach ($this->items as $index => $item)
                <tr>
                    <td>
                        <div class="m-5">

                            @php
                                $menuItems = [];
                            @endphp
                            @php
                                $menuItems = [
                                    [
                                        'type' => 'href-navigate',
                                        'label' => 'Edit',
                                        'url' => route('nawasara-core.role.form', ['id' => $item->id]),
                                        'color' => 'text-gray-800',
                                        'navigate' => true,
                                        'permission' => 'nawasara-core.role.edit',
                                    ],
                                    [
                                        'type' => 'delete',
                                        'label' => 'Delete',
                                        'color' => 'text-red-600',
                                        'permission' => 'nawasara-core.role.delete',
                                        'name' => $item->name,
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
                        {{ $item->permissions->count() ?? 0 }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-neutral-200">
                        {{ $item->users->count() ?? 0 }}
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
</div>
