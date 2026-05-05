<?php

namespace Nawasara\Core\Livewire\Setting;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Nawasara\Core\Models\UserEmailLink;
use Nawasara\Core\Models\WebmailSession;
use Nawasara\Ui\Livewire\Concerns\HasBrowserToast;

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
        Gate::authorize('webmail.link.manage');
        $this->resetForm();
        $this->dispatch('modal-open:email-link-form');
    }

    public function openEdit(int $id): void
    {
        Gate::authorize('webmail.link.manage');

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
        $this->formUserId = (string) $userId;
        $this->formUserSearch = $label;
    }

    public function pickMailbox(string $email): void
    {
        $this->formMailbox = $email;
        $this->mailboxSearch = $email;
    }

    public function save(): void
    {
        Gate::authorize('webmail.link.manage');

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
        Gate::authorize('webmail.link.manage');

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
        Gate::authorize('webmail.link.manage');

        $count = UserEmailLink::query()
            ->where('source', UserEmailLink::SOURCE_SSO_ATTRIBUTE)
            ->delete();

        unset($this->links, $this->totalsBySource);
        $this->toastSuccess("Pruned {$count} cached SSO link. Akan re-cache otomatis saat user login.");
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
