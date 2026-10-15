<?php

namespace Nawasara\Core\Livewire\Role\Section;

use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Nawasara\AuthPrimitives\Traits\WithSudo;
use Nawasara\Core\Constants\Constants;
use Nawasara\Core\Livewire\Forms\RoleForm;
use Nawasara\Core\Models\Role;
use Nawasara\Core\Support\RoleAssignmentGuard;
use Nawasara\Toaster\Concerns\HasToaster;
use Spatie\Permission\Models\Permission;

class RolePermissionForm extends Component
{
    use HasToaster;
    use WithSudo;

    public RoleForm $form;

    /**
     * Id role yang diedit, null untuk mode create.
     *
     * #[Locked]: audit keamanan 15 Oktober 2026, temuan T10. Tanpa kunci,
     * browser dapat mengisi id setelah mount sehingga form "create" menimpa
     * role yang sudah ada. Browser tidak pernah menulis properti ini.
     */
    #[Locked]
    public $id;
    public $selectedPermissions = [];
    public $permissions = [];

    public function mount($id = null)
    {
        Gate::authorize($id ? 'core.role.edit' : 'core.role.create');

        $this->id = $id;
        $this->permissions = Permission::select('id', 'name')->orderBy('name')->get();
        $this->initDataEdit();
    }

    /**
     * Tolak pembaruan dari browser untuk `form.id`, `form.role`, atau objek
     * `form` utuh. #[Locked] tidak berlaku untuk properti di dalam objek
     * Form, jadi penjagaannya di sini. Lapis pertamanya tetap saveRole()
     * yang mengambil target dari $this->id yang terkunci.
     * Audit keamanan 15 Oktober 2026, temuan T10.
     */
    public function updating($property): void
    {
        $locked = ['form', 'form.id', 'form.role'];

        if (in_array($property, $locked, true) || str_starts_with((string) $property, 'form.role.')) {
            throw new CannotUpdateLockedPropertyException($property);
        }
    }

    /**
     * Group permissions by name structure: package.module.action
     *
     * Grouping is derived from the permission name itself (split by dots)
     * rather than the legacy `group` column, since other packages register
     * permissions without populating that column. This keeps the role form
     * working for ALL package permissions consistently.
     */
    #[Computed]
    public function permissionGroups()
    {
        return $this->permissions
            ->groupBy(fn ($item) => explode('.', $item->name)[0] ?? 'lainnya')
            ->map(function ($items) {
                return $items
                    ->groupBy(fn ($item) => explode('.', $item->name)[1] ?? 'umum')
                    ->map(function ($subItems) {
                        return $subItems->map(function ($item) {
                            $parts = explode('.', $item->name);
                            $action = count($parts) >= 3
                                ? implode('.', array_slice($parts, 2))
                                : end($parts);

                            return [
                                'id'   => $item->id,
                                'name' => $action,
                            ];
                        })->values();
                    });
            });
    }

    #[On('save-role')]
    public function saveRole(array $permission = [])
    {
        Gate::authorize($this->id ? 'core.role.edit' : 'core.role.create');

        // Target dari $this->id yang terkunci; mode create selalu membuat
        // role baru. Audit keamanan 15 Oktober 2026, temuan T10.
        $target = $this->id ? Role::with('permissions')->findOrFail($this->id) : null;

        // Normalize: cast to int, dedupe, reindex.
        $permissions = array_values(array_unique(array_map('intval', $permission)));

        $requested = Permission::whereIn('id', $permissions)->pluck('name', 'id');
        if ($requested->count() !== count($permissions)) {
            $this->addError('form.permissions', 'Ada permission yang tidak valid.');

            return;
        }

        $this->form->setPermissions($permissions);

        // Validasi lebih dulu supaya isian salah tidak membawa user ke
        // langkah OTP sudo lalu ditolak juga.
        $this->form->role = $target;
        $this->form->id = $target?->id ?? '';
        $this->form->validate();

        $actor = auth()->user();
        $actorRoles = $actor?->getRoleNames()->all() ?? [];
        $currentNames = $target ? $target->permissions->pluck('name')->all() : [];
        $requestedNames = $requested->values()->all();

        // Role developer hanya boleh diubah developer (temuan T10).
        $denied = RoleAssignmentGuard::checkRoleManagement($actorRoles, $target?->name);
        if ($denied !== null) {
            $this->alert('error', $denied);

            return;
        }

        if (RoleAssignmentGuard::setsDiffer($currentNames, $requestedNames)) {
            // Izin `core.role.permission` dibuat di seeder tetapi dulu tidak
            // pernah diperiksa, sehingga pemegang `core.role.edit` bebas
            // mengubah izin role apa pun (temuan T10).
            Gate::authorize('core.role.permission');

            $denied = RoleAssignmentGuard::checkPermissionGrant(
                $actorRoles,
                $actor?->getAllPermissions()->pluck('name')->all() ?? [],
                $currentNames,
                $requestedNames,
            );

            if ($denied !== null) {
                $this->alert('error', $denied);

                return;
            }
        }

        // Perubahan role dan izinnya adalah aksi kritis, jadi wajib sudo.
        // Imperatif karena saveRole() berjalan sebagai listener event
        // `save-role`, jalur yang tidak dievaluasi #[RequiresSudo] (temuan
        // T6). Route halaman ini juga memakai middleware `sudo`, jadi pada
        // umumnya jendela sudo sudah aktif saat tombol Simpan ditekan dan
        // pilihan izin tidak hilang karena redirect.
        if (! $this->requireSudo('mengubah role dan permission')) {
            return;
        }

        $this->form->store($target);

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
        $this->redirect(route('nawasara-core.role.index'));
    }

    public function initDataEdit()
    {
        if (! $this->id) {
            return;
        }

        $role = Role::with('permissions')->findOrFail($this->id);

        // Role developer hanya boleh dibuka untuk diubah oleh developer.
        // Audit keamanan 15 Oktober 2026, temuan T10.
        $denied = RoleAssignmentGuard::checkRoleManagement(
            auth()->user()?->getRoleNames()->all() ?? [],
            $role->name,
        );
        if ($denied !== null) {
            throw new AuthorizationException($denied);
        }

        $permissions = $role->permissions->pluck('id')->toArray();

        $this->selectedPermissions = $permissions;
        $this->form->setModel($role, $permissions);
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.role.section.role-permission-form')->layout('nawasara-ui::components.layouts.app');
    }
}
