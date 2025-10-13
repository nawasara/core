<div>
    <x-nawasara-core::page.card class="mb-5">
        <x-nawasara-core::form.input id="role_name" name="role_name" label="Role Name" placeholder="Enter role name"
            required autofocus wire:model.defer="role_name" />
    </x-nawasara-core::page.card>


    <div class="grid grid-cols-1 gap-4 mt-2">
        @foreach ($this->permissionGroups as $prefix => $groups)
            <x-nawasara-core::page.card class="border-l-4 border-l-blue-600 ">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                    {{ label($prefix) }}
                </h3>
                <div class="grid grid-cols-2 gap-4 mt-2">
                    @foreach ($groups as $group => $permissions)
                        <x-nawasara-core::page.card>
                            <x-nawasara-core::form.checkbox label="{{ label($group) }}" name="check-all-user"
                                value="" class="mb-2 font-bold " />
                            <ul class="flex flex-wrap gap-2">
                                @foreach ($permissions as $index => $permission)
                                    <li
                                        class="flex items-center gap-x-2 py-2 px-4 border border-gray-200 rounded-lg bg-white 
                   text-sm font-medium text-gray-800 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white
                   hover:bg-gray-50 dark:hover:bg-neutral-700 transition">
                                        <input id="perm-{{ $permission['id'] }}" name="permissions[]" type="checkbox"
                                            class="border-gray-300 rounded-sm text-blue-600 focus:ring-blue-500 dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500"
                                            {{ label($permission['name']) }}>
                                        <label for="perm-{{ $permission['id'] }}" class="cursor-pointer select-none">
                                            {{ label($permission['name']) }}
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        </x-nawasara-core::page.card>
                    @endforeach
                </div>
            </x-nawasara-core::page.card>
        @endforeach
    </div>

</div>
