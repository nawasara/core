<?php

namespace Nawasara\Core\Livewire\User\Modal;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Nawasara\Core\Constants\Constants;
use Nawasara\Core\Livewire\Forms\UserForm;
use Nawasara\Core\Livewire\User\Section\Table;
use Nawasara\Toaster\Concerns\HasToaster;
use Spatie\Permission\Models\Role;

class FormUser extends Component
{
    use HasToaster;

    public UserForm $form;

    protected $listeners = [
        'refreshComponent' => '$refresh'
    ];

    public $roles;

    public $params = [];

    /** Kata kunci pencarian direktori Keycloak (khusus mode SSO). */
    public string $ssoSearch = '';

    public function mount($params = [])
    {
        Gate::authorize(isset($params['id']) ? 'core.user.edit' : 'core.user.create');

        $this->roles = Role::all();

        $this->params = $params;
        $this->initDataEdit();
    }
    
    public function initDataEdit()
    {
        if (!isset($this->params['id'])) return;

        $user = User::find($this->params['id']);
        $this->form->setModel($user);
    }

    /**
     * Apakah picker direktori tersedia — yaitu package nawasara/keycloak
     * terpasang DAN snapshot user-nya sudah pernah ter-sync. Kalau belum,
     * blade jatuh ke input manual supaya admin tidak terkunci.
     */
    #[Computed]
    public function ssoDirectoryAvailable(): bool
    {
        if (! class_exists(\Nawasara\Keycloak\Models\KeycloakUser::class)) {
            return false;
        }

        return \Nawasara\Keycloak\Models\KeycloakUser::query()->exists();
    }

    /**
     * Hasil pencarian direktori Keycloak, tanpa orang yang sudah punya akun
     * Nawasara — satu orang satu akun, dan `users.keycloak_id` unique.
     *
     * @return array<int, array{kc_id:string, name:string, username:?string, nip:?string, email:?string}>
     */
    #[Computed]
    public function ssoResults(): array
    {
        if (! $this->ssoDirectoryAvailable()) {
            return [];
        }

        $term = trim($this->ssoSearch);
        if (mb_strlen($term) < 2) {
            return [];
        }

        $takenIds = User::query()->whereNotNull('keycloak_id')->pluck('keycloak_id')->all();
        $takenUsernames = User::query()->pluck('username')->filter()
            ->map(fn ($u) => mb_strtolower($u))->all();

        return \Nawasara\Keycloak\Models\KeycloakUser::query()
            ->search($term)
            ->where('enabled', true)
            ->limit(45)
            ->get()
            ->reject(fn ($kc) => in_array($kc->user_id, $takenIds, true)
                || in_array(mb_strtolower((string) $kc->username), $takenUsernames, true))
            ->take(15)
            ->map(fn ($kc) => [
                'kc_id' => $kc->user_id,
                'name' => $kc->full_name ?: ($kc->username ?? '—'),
                'username' => $kc->username,
                'nip' => $kc->nip,
                'email' => $kc->email,
            ])
            ->values()
            ->all();
    }

    /**
     * Ambil identitas dari direktori Keycloak ke dalam form.
     *
     * Index merujuk posisi di hasil pencarian yang dihitung ulang server-side
     * — lihat CLAUDE.md 13.f.
     */
    public function pickSsoUser(int $index): void
    {
        $picked = $this->ssoResults()[$index] ?? null;

        if (! $picked) {
            $this->alert('error', 'Pilihan tidak valid, coba cari ulang.');

            return;
        }

        $this->form->keycloak_id = $picked['kc_id'];
        $this->form->name = $picked['name'];
        $this->form->username = $picked['username'];
        $this->form->email = $picked['email'] ?: ($picked['username'].'@sso.local');

        $this->ssoSearch = '';
        $this->resetValidation();
    }

    /** Batalkan pilihan, kembali ke pencarian. */
    public function clearSsoUser(): void
    {
        $this->form->keycloak_id = null;
        $this->form->name = null;
        $this->form->username = null;
        $this->form->email = null;
    }

    /**
     * Ganti tipe user. Berpindah tipe membuang identitas yang sudah terisi,
     * karena asal datanya berbeda: SSO diambil dari direktori, lokal diketik.
     */
    public function setAuthType(string $type): void
    {
        if (! in_array($type, ['local', 'sso'], true) || $this->form->auth_type === $type) {
            return;
        }

        $this->form->auth_type = $type;

        // Saat mengedit user existing, identitasnya jangan dikosongkan —
        // admin mungkin hanya mengubah tipe, bukan mengganti orangnya.
        if (! $this->form->user) {
            $this->clearSsoUser();
            $this->form->password = null;
        }

        $this->ssoSearch = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.user.modal.form-user');
    }

    #[On('store')]
    public function store($roles = [])
    {
        Gate::authorize(isset($this->params['id']) ? 'core.user.edit' : 'core.user.create');

        DB::beginTransaction();

        $this->form->setRoles($roles);

        $this->form->store();

        DB::commit();

        /* close modal */
        $this->dispatch('close-livewire-modal', id: 'modal-user-form');
        
        /* show toaster */
        $this->alert('success', Constants::NOTIFICATION_SUCCESS_CREATE);

        // ⚠️ TANPA `navigate: true`.
        //
        // wire:navigate menyajikan halaman tujuan dari SNAPSHOT yang disimpan
        // peramban saat halaman itu terakhir dibuka, lalu memperbaruinya di
        // belakang layar. Untuk perpindahan biasa itu terasa cepat; setelah
        // menyimpan, yang muncul lebih dulu justru daftar SEBELUM perubahan —
        // nama yang baru diubah masih tampil versi lamanya.
        //
        // Gejalanya khas dan menyesatkan: "simpannya cepat, tapi datanya
        // kembali lama". Datanya sudah tersimpan; yang tertinggal tampilannya.
        //
        // Redirect penuh memang sedikit lebih lambat, tetapi halaman yang
        // muncul selalu menggambarkan keadaan setelah penyimpanan.
        $this->redirect(route('nawasara-core.user.index'));

    }
}
