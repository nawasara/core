<?php

namespace Nawasara\Core\Livewire\Setting;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Nawasara\Core\Auth\AuthMode;
use Nawasara\Core\Models\Setting;
use Nawasara\Core\Services\SsoService;
use Nawasara\Ui\Livewire\Concerns\HasBrowserToast;
use Spatie\Permission\Models\Role;

class Auth extends Component
{
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

    public function save(): void
    {
        Gate::authorize('core.auth.manage');

        $this->validate([
            'mode' => ['required', 'in:'.implode(',', AuthMode::ALL_MODES)],
            'defaultSsoRole' => ['required', 'string', 'max:100'],
        ]);

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
