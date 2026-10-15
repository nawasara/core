<?php

namespace Nawasara\Core\Livewire\Setting;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Nawasara\AuthPrimitives\Attributes\RequiresSudo;
use Nawasara\AuthPrimitives\Traits\WithSudo;
use Nawasara\Core\Auth\AuthMode;
use Nawasara\Core\Models\Setting;
use Nawasara\Core\Services\SsoService;
use Nawasara\Core\Support\RoleAssignmentGuard;
use Nawasara\Ui\Livewire\Concerns\HasBrowserToast;
use Spatie\Permission\Models\Role;

class Auth extends Component
{
    use WithSudo;
    use HasBrowserToast;

    public string $mode = AuthMode::LOCAL;
    public bool $autoProvision = true;
    public string $defaultSsoRole = 'guest';

    public ?string $testResult = null;
    public ?bool $testSuccess = null;

    public function mount(): void
    {
        $this->mode = AuthMode::raw();
        $this->autoProvision = AuthMode::autoProvision();
        $this->defaultSsoRole = AuthMode::defaultSsoRole();
    }

    // Mode login dan role default SSO menentukan siapa yang dapat masuk dan
    // dengan hak apa. Mengubahnya sama beratnya dengan memberi role ke semua
    // user SSO baru (audit keamanan 15 Oktober 2026, T10).
    #[RequiresSudo(reason: 'mengubah pengaturan autentikasi')]
    public function save(): void
    {
        Gate::authorize('core.auth.manage');

        $this->validate([
            'mode' => ['required', 'in:'.implode(',', AuthMode::ALL_MODES)],
            'defaultSsoRole' => ['required', 'string', 'max:100', 'exists:roles,name'],
        ]);

        // Role bawaan SSO diberikan otomatis ke SETIAP user baru yang login.
        // Bila pemegang `core.auth.manage` yang bukan developer dapat memilih
        // `developer` di sini, ia memberi role developer ke semua orang
        // (termasuk akun keduanya sendiri) tanpa melewati form user.
        // Audit keamanan 15 Oktober 2026, temuan T10.
        $actorRoles = auth()->user()?->getRoleNames()->all() ?? [];
        if ($this->defaultSsoRole !== AuthMode::defaultSsoRole()
            && ($denied = RoleAssignmentGuard::checkRoleAssignment($actorRoles, [], [$this->defaultSsoRole], false)) !== null) {
            $this->addError('defaultSsoRole', $denied);

            return;
        }

        // Capture before-values for audit. Setting::set() does NOT
        // auto-log (the model deliberately doesn't use LogsActivity —
        // see Setting.php; would create too much noise on cache misses).
        $before = [
            'mode' => AuthMode::raw(),
            'auto_provision' => AuthMode::autoProvision(),
            'default_role' => AuthMode::defaultSsoRole(),
        ];

        Setting::set('auth.mode', $this->mode);
        Setting::set('auth.sso.auto_provision', $this->autoProvision);
        Setting::set('auth.sso.default_role', $this->defaultSsoRole);

        $after = [
            'mode' => $this->mode,
            'auto_provision' => $this->autoProvision,
            'default_role' => $this->defaultSsoRole,
        ];

        if ($before !== $after) {
            activity('auth-settings')
                ->causedBy(auth()->user())
                ->withProperties([
                    'attributes' => $after,
                    'old' => $before,
                ])
                ->log('Auth settings updated');
        }

        $this->toastSuccess('Pengaturan auth disimpan.');
    }

    public function testSso(SsoService $sso): void
    {
        Gate::authorize('core.auth.manage');

        $r = $sso->testConnection();
        $this->testSuccess = (bool) ($r['success'] ?? false);
        $this->testResult = (string) ($r['message'] ?? '');

        if ($this->testSuccess) {
            $this->toastSuccess('Test SSO OK: '.$this->testResult);
        } else {
            $this->toastError('Test SSO gagal: '.$this->testResult);
        }
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.setting.auth', [
            'roles' => Role::orderBy('name')->pluck('name')->all(),
            'effectiveMode' => AuthMode::current(),
            'ssoConfigured' => AuthMode::ssoConfigured(),
        ])->layout('nawasara-ui::components.layouts.app');
    }
}
