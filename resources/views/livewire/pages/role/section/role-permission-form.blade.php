<div>
    <x-nawasara-core::page.card class="mb-5">
        <x-nawasara-core::form.input id="role_name" name="role_name" label="Role Name" placeholder="Enter role name"
            required autofocus wire:model.defer="role_name" />
    </x-nawasara-core::page.card>


    <x-nawasara-core::page.card class="border-l-4 border-l-blue-600 ">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
            Nawasara Core
        </h3>
        <div class="grid grid-cols-2 gap-4">
            <x-nawasara-core::page.card>
                <h4 class="text-md font-medium text-gray-700 dark:text-neutral-200 mb-3">Users</h4>
                <ul class="flex flex-wrap gap-2">
                    @foreach (['Create', 'Update', 'Delete', 'View', 'Report'] as $index => $action)
                        <li
                            class="flex items-center gap-x-2 py-2 px-4 border border-gray-200 rounded-lg bg-white 
                   text-sm font-medium text-gray-800 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white
                   hover:bg-gray-50 dark:hover:bg-neutral-700 transition">
                            <input id="perm-{{ strtolower($action) }}" name="permissions[]" type="checkbox"
                                class="border-gray-300 rounded-sm text-blue-600 focus:ring-blue-500 dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500"
                                {{ $action === 'Create' ? 'checked' : '' }}>
                            <label for="perm-{{ strtolower($action) }}" class="cursor-pointer select-none">
                                {{ $action }}
                            </label>
                        </li>
                    @endforeach
                </ul>

            </x-nawasara-core::page.card>
            <x-nawasara-core::page.card>
                <h4 class="text-md font-medium text-gray-700 dark:text-neutral-200 mb-3">Permission</h4>
                <ul class="flex flex-wrap gap-2">
                    @foreach (['Create', 'Update', 'Delete', 'View', 'Report'] as $index => $action)
                        <li
                            class="flex items-center gap-x-2 py-2 px-4 border border-gray-200 rounded-lg bg-white 
                   text-sm font-medium text-gray-800 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white
                   hover:bg-gray-50 dark:hover:bg-neutral-700 transition">
                            <input id="perm-{{ strtolower($action) }}" name="permissions[]" type="checkbox"
                                class="border-gray-300 rounded-sm text-blue-600 focus:ring-blue-500 dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500"
                                {{ $action === 'Create' ? 'checked' : '' }}>
                            <label for="perm-{{ strtolower($action) }}" class="cursor-pointer select-none">
                                {{ $action }}
                            </label>
                        </li>
                    @endforeach
                </ul>

            </x-nawasara-core::page.card>
        </div>
    </x-nawasara-core::page.card>

</div>
