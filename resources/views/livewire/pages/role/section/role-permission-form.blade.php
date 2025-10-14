<div>
    <x-nawasara-core::page.card class="mb-5">
        <x-nawasara-core::form.input id="role_name" name="role_name" label="Role Name" placeholder="Enter role name"
            required autofocus wire:model.defer="form.name" />
        @error('form.name')
            <span class="text-red-500 mt-5">{{ $message }}</span>
        @enderror
    </x-nawasara-core::page.card>

    <div class="grid grid-cols-1 gap-4 mt-2">
        {{-- Loop through permission groups --}}
        @foreach ($this->permissionGroups as $prefix => $groups)
            <x-nawasara-core::page.card class="border-l-4 border-l-green-600">
                <h3 class="text-lg font-semibold text-green-800 dark:text-white">
                    {{ label($prefix) }}'s Permissions
                </h3>

                <div class="grid grid-cols-2 gap-4 mt-2">
                    @foreach ($groups as $group => $permissions)
                        <x-nawasara-core::page.card x-data="{
                            prefix: '{{ $prefix }}_{{ $group }}',
                            // Initialize selected with server-side selectedPermissions filtered by this group's ids
                            selected: {{ json_encode(array_values(array_intersect(collect($permissions)->pluck('id')->toArray(), $selectedPermissions ?? []))) }},
                            allIds: {{ json_encode(collect($permissions)->pluck('id')) }},
                            toggleAll() {
                                if (this.isAllChecked()) {
                                    this.selected = [];
                                } else {
                                    this.selected = [...this.allIds];
                                }
                            },
                            isAllChecked() {
                                return this.selected.length === this.allIds.length;
                            },
                            updateStore() {
                                try {
                                    // Ensure Alpine and the store are available (Livewire navigation may initialize components in different order)
                                    if (typeof Alpine === 'undefined' || typeof Alpine.store !== 'function') return;
                                    const store = Alpine.store('form');
                                    if (!store) return;
                                    if (!store.selectedAll || typeof store.selectedAll !== 'object') {
                                        store.selectedAll = {};
                                    }
                                    // Ensure we store an array
                                    store.selectedAll[this.prefix] = Array.isArray(this.selected) ? this.selected : [];
                                } catch (e) {
                                    // swallow any unexpected errors during initialization
                                }
                            }
                        }" x-effect="updateStore()">
                            {{-- Checkbox utama (Check All) --}}
                            <x-nawasara-core::form.checkbox name="check_all_{{ Str::slug($group) }}"
                                class="mb-2 font-bold cursor-pointer"
                                input-class="text-blue-600 border-gray-300 rounded-sm focus:ring-blue-500"
                                label="{{ label($group) }}" @change="toggleAll" x-bind:checked="isAllChecked()" />

                            {{-- Daftar permission --}}
                            <ul class="flex flex-wrap gap-2">
                                @foreach ($permissions as $permission)
                                    <li
                                        class="flex items-center gap-x-2 py-2 px-4 border border-gray-200 rounded-lg bg-white 
                                            text-sm font-medium text-gray-800 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white
                                            hover:bg-gray-50 dark:hover:bg-neutral-700 transition">
                                        <x-nawasara-core::form.checkbox name="permissions[]"
                                            value="{{ $permission['id'] }}"
                                            input-class="border-gray-300 rounded-sm text-blue-600 focus:ring-blue-500 dark:bg-neutral-800 dark:border-neutral-700 dark:checked:bg-blue-500"
                                            class="cursor-pointer select-none" label="{{ label($permission['name']) }}"
                                            x-model="selected" />
                                    </li>
                                @endforeach
                            </ul>
                        </x-nawasara-core::page.card>
                    @endforeach
                </div>
            </x-nawasara-core::page.card>
        @endforeach
        @error('form.permissions')
            <span class="text-red-500">{{ $message }}</span>
        @enderror
    </div>

    <div class="flex justify-end mt-5">
        <x-nawasara-core::button color="success"
            @click="(typeof Alpine !== 'undefined' && Alpine.store && Alpine.store('form') ? Alpine.store('form').save() : null)"
            rounded="md">
            Save Role
        </x-nawasara-core::button>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('form', {
            selectedAll: {},
            save() {
                console.log(this.selectedAll);
                let permission = JSON.parse(JSON.stringify(this.selectedAll));
                console.log(permission);
                Livewire.dispatch('save-role', {
                    permission: permission
                });
            }
        })
    });
</script>
