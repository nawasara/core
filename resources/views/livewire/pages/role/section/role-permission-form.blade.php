{{-- ui-lint-skip: search-clear icon button (absolute-positioned) and disclosure-toggle pattern require custom raw button; needs <icon-button> + <disclosure> components later --}}
<div x-data="rolePermissionForm({
    initial: @js($selectedPermissions ?? []),
    groups: @js($this->permissionGroups),
})">
    {{-- Sticky Header --}}
    <div class="sticky top-0 z-10 -mx-4 px-4 py-3 mb-4 bg-white/95 dark:bg-neutral-900/95 backdrop-blur border-b border-gray-200 dark:border-neutral-800">
        <div class="flex flex-col lg:flex-row lg:items-end gap-3">
            <div class="flex-1">
                <x-nawasara-ui::form.label value="Nama Role" />
                <x-nawasara-ui::form.input
                    id="role_name"
                    placeholder="Misal: editor, viewer, admin-opd"
                    wire:model.blur="form.name"
                    autofocus />
                @error('form.name')
                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center gap-3 lg:pb-1">
                <div class="text-sm text-gray-600 dark:text-neutral-400">
                    <span class="font-bold text-gray-900 dark:text-white" x-text="count"></span>
                    /
                    <span x-text="totalPermissions"></span>
                    <span class="hidden sm:inline">permission</span>
                </div>

                <x-nawasara-ui::button color="neutral" variant="outline" size="sm" x-on:click="selectAll()">
                    Pilih Semua
                </x-nawasara-ui::button>
                <x-nawasara-ui::button color="neutral" variant="outline" size="sm" x-on:click="clearAll()">
                    Hapus Semua
                </x-nawasara-ui::button>
                <x-nawasara-ui::button color="success" size="sm"
                    x-bind:disabled="saving"
                    x-on:click="save()">
                    <x-slot:icon>
                        <x-lucide-save x-show="!saving" />
                        <x-lucide-loader-circle x-show="saving" class="animate-spin" />
                    </x-slot:icon>
                    Simpan Role
                </x-nawasara-ui::button>
            </div>
        </div>

        @error('form.permissions')
            <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- Search --}}
    <div class="mb-4">
        <div class="relative">
            <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3">
                <x-lucide-search class="size-4 text-gray-400" />
            </div>
            <input type="text" x-model="search"
                placeholder="Cari permission... (misal: dns, delete, vault.credential)"
                class="w-full py-2.5 ps-10 pe-4 border border-gray-200 rounded-lg text-sm
                       dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-100
                       focus:border-green-500 focus:ring-green-500" />
            <button x-show="search" x-on:click="search = ''"
                class="absolute inset-y-0 end-0 flex items-center pe-3 text-gray-400 hover:text-gray-600">
                <x-lucide-x class="size-4" />
            </button>
        </div>
    </div>

    {{-- Empty State --}}
    <template x-if="Object.keys(filteredGroups).length === 0">
        <div class="text-center py-16 border-2 border-dashed border-gray-200 dark:border-neutral-700 rounded-xl">
            <x-lucide-search-x class="size-12 mx-auto text-gray-300 dark:text-neutral-600" />
            <p class="mt-3 text-sm text-gray-500 dark:text-neutral-400">
                Tidak ada permission yang cocok dengan
                "<span class="font-semibold text-gray-700 dark:text-neutral-300" x-text="search"></span>"
            </p>
        </div>
    </template>

    {{-- Permission Groups --}}
    <div class="space-y-3">
        <template x-for="(modules, prefix) in filteredGroups" :key="prefix">
            <div class="border border-gray-200 dark:border-neutral-700 rounded-xl bg-white dark:bg-neutral-800 overflow-hidden">
                {{-- Package header --}}
                <div class="flex items-center gap-3 px-5 py-3 bg-gray-50 dark:bg-neutral-800/60 border-b border-gray-200 dark:border-neutral-700">
                    <input type="checkbox"
                        x-bind:checked="prefixAllSelected(prefix)"
                        x-bind:indeterminate="prefixSomeSelected(prefix) && !prefixAllSelected(prefix)"
                        x-on:change="toggleAllInPrefix(prefix)"
                        class="size-4 rounded border-gray-300 text-green-600 focus:ring-green-500" />

                    <button type="button" x-on:click="toggleCollapse(prefix)"
                        class="flex-1 flex items-center justify-between text-left">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-neutral-100" x-text="label(prefix)"></h3>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-mono text-gray-500 dark:text-neutral-400">
                                <span x-text="prefixSelectedCount(prefix)"></span>/<span x-text="prefixTotal(prefix)"></span>
                            </span>
                            <x-lucide-chevron-down class="size-4 text-gray-400 transition-transform"
                                x-bind:class="collapsed[prefix] ? '-rotate-90' : ''" />
                        </div>
                    </button>
                </div>

                {{-- Modules --}}
                <div x-show="!collapsed[prefix]" x-collapse>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 p-4">
                        <template x-for="(perms, module) in modules" :key="prefix + '-' + module">
                            <div class="border border-gray-200 dark:border-neutral-700 rounded-lg p-3 bg-gray-50/50 dark:bg-neutral-900/30">
                                <label class="flex items-center gap-2 mb-2 cursor-pointer">
                                    <input type="checkbox"
                                        x-bind:checked="moduleAllSelected(prefix, module)"
                                        x-bind:indeterminate="moduleSomeSelected(prefix, module) && !moduleAllSelected(prefix, module)"
                                        x-on:change="toggleAllInModule(prefix, module)"
                                        class="size-4 rounded border-gray-300 text-green-600 focus:ring-green-500" />
                                    <span class="text-sm font-semibold text-gray-700 dark:text-neutral-200" x-text="label(module)"></span>
                                    <span class="text-xs text-gray-400 ms-auto">
                                        <span x-text="moduleSelectedCount(prefix, module)"></span>/<span x-text="perms.length"></span>
                                    </span>
                                </label>

                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="perm in perms" :key="perm.id">
                                        <label class="cursor-pointer">
                                            <input type="checkbox" :checked="has(perm.id)" x-on:change="toggle(perm.id)" class="sr-only peer" />
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium border transition-colors
                                                peer-checked:bg-green-100 peer-checked:border-green-300 peer-checked:text-green-800
                                                dark:peer-checked:bg-green-900/40 dark:peer-checked:border-green-700 dark:peer-checked:text-green-300
                                                bg-white border-gray-200 text-gray-600 hover:bg-gray-50
                                                dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:hover:bg-neutral-700">
                                                <x-lucide-check class="size-3 hidden peer-checked:inline" />
                                                <span x-text="label(perm.name)"></span>
                                            </span>
                                        </label>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

{{-- @script (Livewire 3) instead of @push('script') + alpine:init: @script
     re-runs on every component mount INCLUDING after wire:navigate, so the
     Alpine.data is never "not defined" on a navigated-to page. @push does not
     re-run on navigate. See reference_alpine_magic_wire_navigate. --}}
@script
<script>
        // ⚠️ Pilihan izin dan pohon grup SENGAJA di luar data reaktif Alpine.
        //
        // Dulu `selected` (array reaktif) dicek dengan selected.includes(id)
        // di setiap kotak centang, setiap modul, dan setiap grup. Pada array
        // reaktif, includes() mencatat ketergantungan ke SETIAP elemennya:
        // 224 izin dengan 117 tercentang berarti sekitar 2.000 panggilan
        // x 117 elemen, kira-kira 230 ribu pencatatan saat halaman dibuka,
        // dan semuanya diulang setiap satu kotak diklik. Halaman edit role
        // terasa sangat lambat dan berat (Oktober 2026).
        //
        // Sekarang pilihan disimpan di Set biasa, id per grup dan per modul
        // dihitung sekali, dan tampilan bergantung pada SATU angka reaktif
        // (`rev`) yang dinaikkan setiap pilihan berubah.
        Alpine.data('rolePermissionForm', (config) => {
            const groups = config.groups || {};
            const chosen = new Set((config.initial || []).map(Number));
            const prefixIdMap = {};
            const moduleIdMap = {};
            let total = 0;

            for (const prefix in groups) {
                prefixIdMap[prefix] = [];
                for (const module in groups[prefix]) {
                    const ids = groups[prefix][module].map(p => Number(p.id));
                    moduleIdMap[prefix + '\u0000' + module] = ids;
                    prefixIdMap[prefix].push(...ids);
                    total += ids.length;
                }
            }

            const countIn = (ids) => {
                let n = 0;
                for (const id of ids) if (chosen.has(id)) n++;
                return n;
            };

            return {
                rev: 0,
                count: chosen.size,
                totalPermissions: total,
                search: '',
                collapsed: {},
                saving: false,

                touch() {
                    this.rev++;
                    this.count = chosen.size;
                },

                label(s) {
                    if (!s) return '';
                    return String(s).replace(/[-_]/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                },

                get filteredGroups() {
                    if (!this.search) return groups;
                    const q = this.search.toLowerCase();
                    const result = {};
                    for (const prefix in groups) {
                        const modules = {};
                        for (const module in groups[prefix]) {
                            const matched = groups[prefix][module].filter(p => {
                                const haystack = `${prefix}.${module}.${p.name}`.toLowerCase();
                                return haystack.includes(q);
                            });
                            if (matched.length) modules[module] = matched;
                        }
                        if (Object.keys(modules).length) result[prefix] = modules;
                    }
                    return result;
                },

                has(id) {
                    this.rev;
                    return chosen.has(Number(id));
                },

                toggle(id) {
                    id = Number(id);
                    chosen.has(id) ? chosen.delete(id) : chosen.add(id);
                    this.touch();
                },

                prefixSelectedCount(prefix) {
                    this.rev;
                    return countIn(prefixIdMap[prefix] || []);
                },

                prefixTotal(prefix) {
                    return (prefixIdMap[prefix] || []).length;
                },

                prefixAllSelected(prefix) {
                    const total = this.prefixTotal(prefix);
                    return total > 0 && this.prefixSelectedCount(prefix) === total;
                },

                prefixSomeSelected(prefix) {
                    return this.prefixSelectedCount(prefix) > 0;
                },

                moduleSelectedCount(prefix, module) {
                    this.rev;
                    return countIn(moduleIdMap[prefix + '\u0000' + module] || []);
                },

                moduleAllSelected(prefix, module) {
                    const ids = moduleIdMap[prefix + '\u0000' + module] || [];
                    return ids.length > 0 && this.moduleSelectedCount(prefix, module) === ids.length;
                },

                moduleSomeSelected(prefix, module) {
                    return this.moduleSelectedCount(prefix, module) > 0;
                },

                setMany(ids, on) {
                    for (const id of ids) on ? chosen.add(id) : chosen.delete(id);
                    this.touch();
                },

                toggleAllInPrefix(prefix) {
                    this.setMany(prefixIdMap[prefix] || [], !this.prefixAllSelected(prefix));
                },

                toggleAllInModule(prefix, module) {
                    this.setMany(moduleIdMap[prefix + '\u0000' + module] || [], !this.moduleAllSelected(prefix, module));
                },

                toggleCollapse(prefix) {
                    this.collapsed[prefix] = !this.collapsed[prefix];
                },

                selectAll() {
                    for (const prefix in prefixIdMap) for (const id of prefixIdMap[prefix]) chosen.add(id);
                    this.touch();
                },

                clearAll() {
                    chosen.clear();
                    this.touch();
                },

                async save() {
                    if (this.saving) return;
                    this.saving = true;
                    try {
                        await this.$wire.dispatch('save-role', { permission: [...chosen] });
                    } finally {
                        setTimeout(() => this.saving = false, 2000);
                    }
                },
            };
        });
</script>
@endscript
