<?php

namespace Nawasara\Core\Livewire\User\Modal;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Nawasara\AuthPrimitives\Traits\WithSudo;
use Nawasara\Core\Constants\Constants;
use Nawasara\Core\Livewire\Forms\UserForm;
use Nawasara\Core\Support\RoleAssignmentGuard;
use Nawasara\Toaster\Concerns\HasToaster;
use Spatie\Permission\Models\Role;

class FormUser extends Component
{
    use HasToaster;
    use WithSudo;

    public UserForm $form;

    protected $listeners = [
        'refreshComponent' => '$refresh'
    ];

    public $roles;

    /**
     * Parameter dari pembuka modal; `params.id` menentukan user yang diedit.
     *
     * #[Locked]: audit keamanan 15 Oktober 2026, temuan T10. Tanpa kunci,
     * browser dapat mengganti `params.id` setelah mount (izin dicek untuk
     * user A, lalu yang disimpan user B) atau menghapusnya untuk berpindah
     * mode. Browser tidak pernah menulis properti ini.
     */
    #[Locked]
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

        $user = User::with('roles')->findOrFail($this->params['id']);

        // Audit keamanan 15 Oktober 2026, temuan T10: akun developer hanya
        // boleh dibuka untuk diubah oleh developer. Ditolak sejak mount
        // supaya admin tidak mengisi form yang pasti ditolak saat simpan.
        $denied = RoleAssignmentGuard::checkUserManagement($this->actorRoleNames(), $user->getRoleNames()->all());
        if ($denied !== null) {
            throw new AuthorizationException($denied);
        }

        $this->form->setModel($user);
    }

    /**
     * Tolak pembaruan dari browser untuk state yang hanya boleh ditulis
     * server.
     *
     * #[Locked] tidak bekerja pada properti di dalam objek Form (Livewire
     * hanya membaca atribut milik komponen), jadi penjagaannya di sini.
     * `form.id` dan `form.user` menentukan baris yang ditulis, sedangkan
     * `form.keycloak_id` hanya diisi lewat pickSsoUser() dari hasil
     * direktori. Mengganti objek `form` utuh juga ditolak karena membawa
     * ketiganya sekaligus.
     *
     * Ini lapis kedua. Lapis pertama adalah store() yang tidak lagi membaca
     * id dari form sama sekali. Audit keamanan 15 Oktober 2026, temuan T10.
     */
    public function updating($property): void
    {
        $locked = ['form', 'form.id', 'form.user', 'form.keycloak_id'];

        if (in_array($property, $locked, true) || str_starts_with((string) $property, 'form.user.')) {
            throw new CannotUpdateLockedPropertyException($property);
        }
    }

    /**
     * Nama role milik user yang sedang login.
     *
     * @return array<int,string>
     */
    protected function actorRoleNames(): array
    {
        return auth()->user()?->getRoleNames()->all() ?? [];
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

        // Target diambil dari $params yang dikunci, bukan dari form.id.
        // Mode create (tanpa params.id) selalu membuat user baru.
        // Audit keamanan 15 Oktober 2026, temuan T10.
        $target = isset($this->params['id'])
            ? User::with('roles')->findOrFail($this->params['id'])
            : null;

        $this->form->setRoles($roles);

        // Role diterima sebagai id dari browser. Petakan ke model nyata: id
        // yang tidak ada ditolak, bukan dilewati diam-diam, karena nama
        // role-nya diperlukan untuk aturan developer di bawah.
        $roleIds = array_values(array_unique(array_map('intval', (array) $this->form->roles)));
        $requestedRoles = Role::whereIn('id', $roleIds)->get();

        if ($requestedRoles->count() !== count($roleIds)) {
            $this->addError('form.roles', 'Role yang dipilih tidak valid.');

            return;
        }

        $this->form->roles = $roleIds;

        // Validasi lebih dulu, sebelum sudo: kalau isian salah, jangan
        // sampai user dibawa ke langkah OTP lalu ditolak juga.
        $this->form->user = $target;
        $this->form->id = $target?->id;
        $this->form->validate();

        $actorRoles = $this->actorRoleNames();
        $currentRoles = $target ? $target->getRoleNames()->all() : [];
        $requestedNames = $requestedRoles->pluck('name')->all();
        $isSelf = $target !== null && (int) $target->getKey() === (int) auth()->id();

        $denied = $target
            ? RoleAssignmentGuard::checkUserManagement($actorRoles, $currentRoles)
            : null;
        $denied ??= RoleAssignmentGuard::checkRoleAssignment($actorRoles, $currentRoles, $requestedNames, $isSelf);

        if ($denied !== null) {
            $this->alert('error', $denied);

            return;
        }

        // Pemberian role adalah aksi kritis, jadi wajib sudo. Dipanggil
        // imperatif karena store() juga berjalan sebagai listener event
        // `store`, jalur yang tidak dievaluasi #[RequiresSudo] (temuan T6).
        // Edit yang tidak mengubah role tidak meminta sudo.
        if (RoleAssignmentGuard::setsDiffer($currentRoles, $requestedNames)
            && ! $this->requireSudo('mengubah role user')) {
            return;
        }

        DB::beginTransaction();

        $this->form->store($target);

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
