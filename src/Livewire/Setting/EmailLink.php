<?php

namespace Nawasara\Core\Livewire\Setting;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Nawasara\Core\Jobs\ProcessEmailLinkImport;
use Nawasara\Core\Models\EmailLinkImport;
use Nawasara\Core\Models\UserEmailLink;
use Nawasara\Ui\Livewire\Concerns\HasBrowserToast;
use Nawasara\Whm\Models\WebmailSession;

/**
 * Admin UI untuk override mapping user ↔ mailbox.
 *
 * Use case utama:
 *   - Keycloak attribute `kominfo_email` salah/kosong, admin perlu quick fix
 *   - User punya >1 mailbox dari claim (ambiguous), admin set primary
 *   - Mailbox di-rotate, admin pindahkan link
 *
 * Manual link selalu menang atas SSO attribute saat resolve. Kalau admin
 * hapus manual link, resolver otomatis fall back ke SSO attribute.
 */
class EmailLink extends Component
{
    use HasBrowserToast;
    use WithFileUploads;
    use WithPagination;

    public string $search = '';
    public string $sourceFilter = '';
    public int $perPage = 25;

    // Form modal state
    public ?int $editingId = null;
    public string $formUserId = '';
    public string $formMailbox = '';
    public string $formUserSearch = '';
    public string $mailboxSearch = '';

    // Import modal state — Livewire's WithFileUploads gives us $importFile.
    // null until user selects a file; cleared on dispatch + modal close.
    public $importFile = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSourceFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function links()
    {
        return UserEmailLink::query()
            ->when($this->search, fn (Builder $q) => $q->where(function (Builder $qq) {
                $qq->where('email_account', 'like', '%'.$this->search.'%')
                    ->orWhereExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('users')
                            ->whereColumn('users.id', 'nawasara_user_email_links.user_id')
                            ->where(function ($u) {
                                $u->where('users.name', 'like', '%'.$this->search.'%')
                                    ->orWhere('users.email', 'like', '%'.$this->search.'%')
                                    ->orWhere('users.username', 'like', '%'.$this->search.'%');
                            });
                    });
            }))
            ->when($this->sourceFilter, fn (Builder $q) => $q->where('source', $this->sourceFilter))
            ->orderByDesc('updated_at')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function totalsBySource(): array
    {
        return UserEmailLink::query()
            ->selectRaw('source, count(*) as c')
            ->groupBy('source')
            ->pluck('c', 'source')
            ->all();
    }

    #[Computed]
    public function recentSessions()
    {
        return WebmailSession::query()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function userOptions()
    {
        if (strlen($this->formUserSearch) < 2) {
            return collect();
        }

        return User::query()
            ->where(function ($q) {
                $q->where('name', 'like', '%'.$this->formUserSearch.'%')
                    ->orWhere('email', 'like', '%'.$this->formUserSearch.'%')
                    ->orWhere('username', 'like', '%'.$this->formUserSearch.'%');
            })
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name', 'email', 'username']);
    }

    /**
     * Suggest mailbox dari WHM sync table (kalau package whm terpasang).
     * Pakai DB query langsung untuk hindari boot dep ke whm.
     */
    #[Computed]
    public function mailboxOptions()
    {
        if (strlen($this->mailboxSearch) < 2) {
            return collect();
        }

        if (! $this->mailboxAccountTableExists()) {
            return collect();
        }

        return DB::table('nawasara_whm_email_accounts')
            ->where('email', 'like', '%'.$this->mailboxSearch.'%')
            ->orderBy('email')
            ->limit(15)
            ->pluck('email');
    }

    public function openCreate(): void
    {
        Gate::authorize('core.email-link.manage');
        $this->resetForm();
        $this->dispatch('modal-open:email-link-form');
    }

    public function openEdit(int $id): void
    {
        Gate::authorize('core.email-link.manage');

        $link = UserEmailLink::find($id);
        if (! $link) return;

        $user = User::find($link->user_id);

        $this->editingId = $link->id;
        $this->formUserId = (string) $link->user_id;
        $this->formMailbox = $link->email_account;
        $this->formUserSearch = $user ? "{$user->name} ({$user->email})" : '';
        $this->mailboxSearch = $link->email_account;

        $this->dispatch('modal-open:email-link-form');
    }

    public function pickUser(int $userId, string $label): void
    {
        // Gate even though save() also gates — these public methods are
        // dispatchable from the browser (Livewire wire:click), so without
        // a re-check anyone with access to the page can poke at form
        // state. The page itself is gated by route middleware, but a
        // permissioned user without `email-link.manage` could still call
        // these via the JS console.
        Gate::authorize('core.email-link.manage');

        $this->formUserId = (string) $userId;
        $this->formUserSearch = $label;
    }

    public function pickMailbox(string $email): void
    {
        Gate::authorize('core.email-link.manage');

        $this->formMailbox = $email;
        $this->mailboxSearch = $email;
    }

    public function save(): void
    {
        Gate::authorize('core.email-link.manage');

        $this->validate([
            'formUserId' => ['required', 'integer', 'exists:users,id'],
            'formMailbox' => ['required', 'string', 'email', 'max:255'],
        ], attributes: [
            'formUserId' => 'user',
            'formMailbox' => 'mailbox',
        ]);

        $payload = [
            'source' => UserEmailLink::SOURCE_MANUAL,
            'linked_at' => now(),
        ];

        try {
            if ($this->editingId) {
                $link = UserEmailLink::find($this->editingId);
                if (! $link) {
                    $this->toastError('Link tidak ditemukan.');
                    return;
                }

                // Kalau user/mailbox berubah, pastikan unique constraint tidak crash
                $exists = UserEmailLink::query()
                    ->where('user_id', (int) $this->formUserId)
                    ->where('email_account', strtolower($this->formMailbox))
                    ->where('id', '!=', $this->editingId)
                    ->exists();

                if ($exists) {
                    $this->toastError('Mapping ini sudah ada untuk user yang dipilih.');
                    return;
                }

                $link->update(array_merge($payload, [
                    'user_id' => (int) $this->formUserId,
                    'email_account' => strtolower($this->formMailbox),
                ]));
                $this->toastSuccess('Manual link diperbarui.');
            } else {
                UserEmailLink::updateOrCreate(
                    ['user_id' => (int) $this->formUserId, 'email_account' => strtolower($this->formMailbox)],
                    $payload,
                );
                $this->toastSuccess('Manual link ditambahkan.');
            }

            $this->dispatch('modal-close:email-link-form');
            $this->resetForm();
            unset($this->links, $this->totalsBySource);
        } catch (\Throwable $e) {
            $this->toastError($e->getMessage());
        }
    }

    public function delete(int $id): void
    {
        Gate::authorize('core.email-link.manage');

        try {
            UserEmailLink::where('id', $id)->delete();
            $this->toastSuccess('Link dihapus. Resolver akan fall back ke claim Keycloak (kalau ada).');
            unset($this->links, $this->totalsBySource);
        } catch (\Throwable $e) {
            $this->toastError($e->getMessage());
        }
    }

    /**
     * Hapus semua link source=sso_attribute supaya resolver re-cache dari
     * claim saat user login berikutnya. Manual link tidak ke-touch.
     */
    public function pruneSsoLinks(): void
    {
        Gate::authorize('core.email-link.manage');

        $count = UserEmailLink::query()
            ->where('source', UserEmailLink::SOURCE_SSO_ATTRIBUTE)
            ->delete();

        unset($this->links, $this->totalsBySource);
        $this->toastSuccess("Pruned {$count} cached SSO link. Akan re-cache otomatis saat user login.");
    }

    // ─── Excel Import ───────────────────────────────────────

    /**
     * Recent imports for the "Riwayat Import" section. Returns the 10
     * most recent batches across all users so admins can see what their
     * colleagues did too — audit transparency.
     */
    #[Computed]
    public function recentImports()
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('nawasara_email_link_imports')) {
            return collect();
        }

        return EmailLinkImport::recent(10)->with('user:id,name,username')->get();
    }

    public function openImport(): void
    {
        Gate::authorize('core.email-link.import');
        $this->reset('importFile');
        $this->resetErrorBag('importFile');
        $this->dispatch('modal-open:email-link-import');
    }

    /**
     * Validate the upload, persist it, create the batch row, queue the
     * worker. The worker does all the heavy lifting; this just hands off.
     */
    public function submitImport(): void
    {
        Gate::authorize('core.email-link.import');

        $this->validate([
            // 5 MB is well above the realistic ~1KB-per-row size for a
            // 5000-row sheet but cuts off accidental huge uploads early.
            'importFile' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [
            'importFile.required' => 'Pilih file Excel/CSV dulu.',
            'importFile.mimes' => 'Format harus .xlsx, .xls, atau .csv.',
            'importFile.max' => 'Maksimum 5 MB.',
        ]);

        $original = $this->importFile->getClientOriginalName();
        $size = $this->importFile->getSize();

        // Stash the file on the default 'local' disk under a per-import
        // folder so it doesn't collide with anything else and is easy
        // to prune. The worker reads it back from this path.
        $stored = $this->importFile->store('email-link-imports');

        $import = EmailLinkImport::create([
            'user_id' => Auth::id(),
            'original_filename' => $original,
            'file_size_bytes' => $size,
            'storage_path' => Storage::disk('local')->path($stored),
            'status' => EmailLinkImport::STATUS_QUEUED,
        ]);

        ProcessEmailLinkImport::dispatch($import->id);

        activity('email-link-import')
            ->performedOn($import)
            ->causedBy(Auth::user())
            ->withProperties([
                'attributes' => [
                    'filename' => $original,
                    'size_bytes' => $size,
                ],
            ])
            ->log('Email-link import queued');

        unset($this->recentImports);
        $this->reset('importFile');
        $this->dispatch('modal-close:email-link-import');
        $this->toastSuccess("Import '{$original}' di-queue. Cek tabel Riwayat Import di bawah untuk progress.");
    }

    /**
     * Manual refresh trigger for the Riwayat table — the user can click a
     * refresh button without reloading the page. Idempotent + cheap.
     */
    public function refreshImports(): void
    {
        unset($this->recentImports);
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->formUserId = '';
        $this->formMailbox = '';
        $this->formUserSearch = '';
        $this->mailboxSearch = '';
        $this->resetErrorBag();
    }

    protected function mailboxAccountTableExists(): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasTable('nawasara_whm_email_accounts');
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.setting.email-link', [
            'mailboxTableExists' => $this->mailboxAccountTableExists(),
        ])->layout('nawasara-ui::components.layouts.app');
    }
}
