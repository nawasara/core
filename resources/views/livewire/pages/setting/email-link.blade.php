{{-- ui-lint-skip: typeahead dropdown rows need multi-line content layout that <button> component does not support; needs <list-item> component later --}}
<div>
    <x-slot name="breadcrumb">
        <livewire:nawasara-ui.shared-components.breadcrumb
            :items="[['label' => 'Settings', 'url' => '#'], ['label' => 'Email Link']]" />
    </x-slot>

    <x-nawasara-ui::page.container>
        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
            <div>
                <x-nawasara-ui::page.title>Email Link Mapping</x-nawasara-ui::page.title>
                <p class="text-sm text-gray-500 dark:text-neutral-400">
                    Mapping user Nawasara ↔ mailbox <code class="font-mono">@ponorogo.go.id</code>.
                    Manual override selalu menang atas claim Keycloak <code class="font-mono">kominfo_email</code>.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <x-nawasara-ui::button color="neutral" variant="outline" size="sm"
                    wire:click="pruneSsoLinks" wire:confirm="Hapus semua cache SSO link? Manual link tetap aman.">
                    <x-slot:icon><x-lucide-refresh-cw class="size-4" /></x-slot:icon>
                    Re-resolve SSO
                </x-nawasara-ui::button>
                @can('webmail.link.manage')
                    <x-nawasara-ui::button color="primary" wire:click="openCreate">
                        <x-slot:icon><x-lucide-plus class="size-4" /></x-slot:icon>
                        Tambah Manual Link
                    </x-nawasara-ui::button>
                @endcan
            </div>
        </div>

        {{-- Status counts --}}
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-4">
            @php
                $manualCount = $this->totalsBySource['manual'] ?? 0;
                $ssoCount = $this->totalsBySource['sso_attribute'] ?? 0;
                $totalCount = $manualCount + $ssoCount;
            @endphp
            <div class="rounded-xl border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-3">
                <div class="text-xs font-medium uppercase text-gray-500 dark:text-neutral-400">Total Link</div>
                <div class="text-2xl font-bold mt-1">{{ $totalCount }}</div>
            </div>
            <div class="rounded-xl border border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/20 p-3">
                <div class="text-xs font-medium uppercase text-purple-700 dark:text-purple-300">Manual Override</div>
                <div class="text-2xl font-bold mt-1 text-purple-700 dark:text-purple-400">{{ $manualCount }}</div>
            </div>
            <div class="rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-3">
                <div class="text-xs font-medium uppercase text-blue-700 dark:text-blue-300">From Keycloak</div>
                <div class="text-2xl font-bold mt-1 text-blue-700 dark:text-blue-400">{{ $ssoCount }}</div>
            </div>
        </div>

        {{-- Filter bar --}}
        <x-nawasara-ui::filter-bar searchPlaceholder="Cari user atau mailbox..." searchModel="search">
            <x-nawasara-ui::filter-dropdown label="Source" model="sourceFilter"
                :items="['all' => 'Semua Sumber', 'manual' => 'Manual', 'sso_attribute' => 'From Keycloak']" />

            <x-slot:chips>
                @if ($search)
                    <x-nawasara-ui::filter-chip label="Cari: {{ $search }}" model="search" />
                @endif
                @if ($sourceFilter)
                    <x-nawasara-ui::filter-chip label="Source: {{ $sourceFilter }}" model="sourceFilter" />
                @endif
            </x-slot:chips>
        </x-nawasara-ui::filter-bar>

        <x-nawasara-ui::table
            :headers="['User', 'Mailbox', 'Source', 'Linked', 'Last Used', '']"
            :title="'Links ('.$this->links->total().' total)'">
            <x-slot:table>
                @forelse ($this->links as $link)
                    @php
                        $user = \App\Models\User::find($link->user_id);
                    @endphp
                    <tr wire:key="link-{{ $link->id }}">
                        <td class="px-6 py-3 text-sm">
                            @if ($user)
                                <div class="font-medium text-gray-800 dark:text-neutral-200">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-neutral-400 font-mono">{{ $user->email }}</div>
                            @else
                                <span class="text-gray-400 italic">User #{{ $link->user_id }} (deleted)</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-sm font-mono text-gray-700 dark:text-neutral-300">
                            {{ $link->email_account }}
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-sm">
                            @if ($link->source === 'manual')
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                                    <x-lucide-pin class="size-3" /> Manual
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                    <x-lucide-key class="size-3" /> Keycloak
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-xs text-gray-500 dark:text-neutral-400">
                            {{ $link->linked_at?->diffForHumans() ?? '—' }}
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-xs text-gray-500 dark:text-neutral-400">
                            {{ $link->last_used_at?->diffForHumans() ?? '—' }}
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap text-sm text-right">
                            @can('webmail.link.manage')
                                <x-nawasara-ui::button size="xs" variant="ghost" color="neutral"
                                    wire:click="openEdit({{ $link->id }})">
                                    <x-lucide-pencil class="size-3.5" />
                                </x-nawasara-ui::button>
                                <x-nawasara-ui::button size="xs" variant="ghost" color="danger"
                                    wire:click="delete({{ $link->id }})"
                                    wire:confirm="Hapus link ini? Resolver akan fall back ke claim Keycloak (kalau ada).">
                                    <x-lucide-trash-2 class="size-3.5" />
                                </x-nawasara-ui::button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-neutral-400">
                            Belum ada link. Link otomatis ke-cache saat user login lewat SSO (kalau attribute Keycloak ke-set), atau tambah manual.
                        </td>
                    </tr>
                @endforelse
            </x-slot:table>

            <x-slot:footer>
                {{ $this->links->links() }}
            </x-slot:footer>
        </x-nawasara-ui::table>

        @if (! $mailboxTableExists)
            <div class="mt-4 p-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/30 text-xs text-yellow-700 dark:text-yellow-300">
                <x-lucide-alert-triangle class="size-4 inline -mt-0.5" />
                Tabel <code class="font-mono">nawasara_whm_email_accounts</code> belum ada. Autocomplete mailbox di form akan kosong — admin harus ketik manual.
            </div>
        @endif

        {{-- Recent audit log --}}
        <div class="mt-8 bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-neutral-200 mb-3">Audit: 10 Webmail Launch Terbaru</h3>

            @if ($this->recentSessions->isEmpty())
                <p class="text-xs text-gray-500 dark:text-neutral-400">Belum ada session launch ter-record.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="text-gray-500 dark:text-neutral-400">
                            <tr>
                                <th class="text-left py-2 pr-3">Waktu</th>
                                <th class="text-left py-2 pr-3">User</th>
                                <th class="text-left py-2 pr-3">Mailbox</th>
                                <th class="text-left py-2 pr-3">Source</th>
                                <th class="text-left py-2 pr-3">Status</th>
                                <th class="text-left py-2">Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->recentSessions as $s)
                                @php $u = \App\Models\User::find($s->user_id); @endphp
                                <tr class="border-t border-gray-100 dark:border-neutral-700">
                                    <td class="py-2 pr-3 font-mono text-gray-600 dark:text-neutral-400">{{ $s->created_at->format('d M H:i:s') }}</td>
                                    <td class="py-2 pr-3 text-gray-700 dark:text-neutral-300">{{ $u->name ?? '#'.$s->user_id }}</td>
                                    <td class="py-2 pr-3 font-mono text-gray-700 dark:text-neutral-300">{{ $s->email_account ?? '—' }}</td>
                                    <td class="py-2 pr-3">{{ $s->match_strategy ?? '—' }}</td>
                                    <td class="py-2 pr-3">
                                        @php
                                            $sColor = match($s->status) {
                                                'issued' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                                'failed' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                                'rejected' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                                default => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $sColor }}">
                                            {{ $s->status }}
                                        </span>
                                    </td>
                                    <td class="py-2 text-gray-600 dark:text-neutral-400 truncate max-w-xs" title="{{ $s->error }}">
                                        {{ \Illuminate\Support\Str::limit($s->error, 60) ?: '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Form Modal --}}
        <x-nawasara-ui::modal id="email-link-form" maxWidth="lg" :title="$editingId ? 'Edit Manual Link' : 'Tambah Manual Link'">
            <form wire:submit="save" id="email-link-form-el" class="space-y-4">
                {{-- User picker --}}
                <div>
                    <x-nawasara-ui::form.label value="User" />
                    <x-nawasara-ui::form.input wire:model.live.debounce.300ms="formUserSearch"
                        placeholder="Cari nama / email / username (min 2 karakter)" />
                    @if (strlen($formUserSearch) >= 2 && $this->userOptions->isNotEmpty() && ! $formUserId)
                        <div class="mt-1 max-h-48 overflow-y-auto border border-gray-200 dark:border-neutral-700 rounded-lg bg-white dark:bg-neutral-800">
                            @foreach ($this->userOptions as $opt)
                                <button type="button"
                                    wire:click="pickUser({{ $opt->id }}, '{{ addslashes($opt->name.' ('.$opt->email.')') }}')"
                                    class="w-full px-3 py-2 text-left text-sm hover:bg-gray-50 dark:hover:bg-neutral-700">
                                    <div class="font-medium">{{ $opt->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-neutral-400 font-mono">{{ $opt->email }}</div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                    @if ($formUserId)
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                            <x-lucide-check class="size-3 inline" /> User terpilih (id #{{ $formUserId }})
                        </p>
                    @endif
                    @error('formUserId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Mailbox picker --}}
                <div>
                    <x-nawasara-ui::form.label value="Mailbox @ponorogo.go.id" />
                    <x-nawasara-ui::form.input wire:model.live.debounce.300ms="mailboxSearch"
                        placeholder="bambang@ponorogo.go.id" />
                    @if (strlen($mailboxSearch) >= 2 && $this->mailboxOptions->isNotEmpty() && $mailboxSearch !== $formMailbox)
                        <div class="mt-1 max-h-48 overflow-y-auto border border-gray-200 dark:border-neutral-700 rounded-lg bg-white dark:bg-neutral-800">
                            @foreach ($this->mailboxOptions as $email)
                                <button type="button"
                                    wire:click="pickMailbox('{{ addslashes($email) }}')"
                                    class="w-full px-3 py-2 text-left text-sm font-mono hover:bg-gray-50 dark:hover:bg-neutral-700">
                                    {{ $email }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                    @if ($formMailbox)
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                            <x-lucide-check class="size-3 inline" /> Mailbox: <code>{{ $formMailbox }}</code>
                        </p>
                    @endif
                    @error('formMailbox') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="text-xs text-gray-500 dark:text-neutral-400 bg-gray-50 dark:bg-neutral-900 p-3 rounded-lg">
                    Manual link akan menggantikan claim Keycloak <code class="font-mono">kominfo_email</code> untuk user ini.
                    Untuk kembali ke auto-resolve, hapus link manual.
                </div>
            </form>

            <x-slot:footer>
                <x-nawasara-ui::button color="neutral" variant="outline" @click="$dispatch('close-modal', 'email-link-form')">Batal</x-nawasara-ui::button>
                <x-nawasara-ui::button type="submit" form="email-link-form-el" color="primary">Simpan</x-nawasara-ui::button>
            </x-slot:footer>
        </x-nawasara-ui::modal>
    </x-nawasara-ui::page.container>
</div>
