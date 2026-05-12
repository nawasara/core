<?php

namespace Nawasara\Core\Livewire\Branding;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Nawasara\Core\Models\Setting;
use Nawasara\Ui\Livewire\Concerns\HasBrowserToast;

class Index extends Component
{
    use HasBrowserToast;
    use WithFileUploads;

    public string $appName = '';
    public string $appSubtitle = '';

    public $logo = null;
    public $logoDark = null;
    public $favicon = null;

    public ?string $currentLogo = null;
    public ?string $currentLogoDark = null;
    public ?string $currentFavicon = null;

    public function mount(): void
    {
        $this->appName = Setting::get('branding.app_name', 'Nawasara');
        $this->appSubtitle = Setting::get('branding.app_subtitle', '');

        $this->currentLogo = Setting::get('branding.logo');
        $this->currentLogoDark = Setting::get('branding.logo_dark');
        $this->currentFavicon = Setting::get('branding.favicon');
    }

    public function save(): void
    {
        Gate::authorize('core.branding.manage');

        $this->validate([
            'appName' => 'required|max:100',
            'appSubtitle' => 'nullable|max:200',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'logoDark' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'favicon' => 'nullable|image|mimes:png,ico,svg|max:512',
        ]);

        // Capture before for text fields (file uploads logged separately below).
        $before = [
            'app_name' => (string) Setting::get('branding.app_name', ''),
            'app_subtitle' => (string) Setting::get('branding.app_subtitle', ''),
        ];

        Setting::set('branding.app_name', $this->appName);
        Setting::set('branding.app_subtitle', $this->appSubtitle);

        $changedFiles = [];

        if ($this->logo) {
            $this->currentLogo = Setting::setFile('branding.logo', $this->logo);
            $this->logo = null;
            $changedFiles[] = 'logo';
        }

        if ($this->logoDark) {
            $this->currentLogoDark = Setting::setFile('branding.logo_dark', $this->logoDark);
            $this->logoDark = null;
            $changedFiles[] = 'logo_dark';
        }

        if ($this->favicon) {
            $this->currentFavicon = Setting::setFile('branding.favicon', $this->favicon);
            $this->favicon = null;
            $changedFiles[] = 'favicon';
        }

        $after = [
            'app_name' => $this->appName,
            'app_subtitle' => (string) $this->appSubtitle,
        ];

        if ($before !== $after || $changedFiles) {
            activity('branding')
                ->causedBy(auth()->user())
                ->withProperties([
                    'attributes' => $after + ['files_changed' => $changedFiles],
                    'old' => $before,
                ])
                ->log('Branding updated');
        }

        $this->toastSuccess('Branding berhasil disimpan. Refresh halaman untuk melihat perubahan.');
    }

    public function removeLogo(string $variant): void
    {
        Gate::authorize('core.branding.manage');

        $key = match ($variant) {
            'logo' => 'branding.logo',
            'logo_dark' => 'branding.logo_dark',
            'favicon' => 'branding.favicon',
            default => null,
        };

        if (! $key) return;

        Setting::forget($key);

        match ($variant) {
            'logo' => $this->currentLogo = null,
            'logo_dark' => $this->currentLogoDark = null,
            'favicon' => $this->currentFavicon = null,
        };

        activity('branding')
            ->causedBy(auth()->user())
            ->withProperties(['variant' => $variant])
            ->log("Branding {$variant} removed");

        $this->toastSuccess('Logo dihapus');
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.branding.index')
            ->layout('nawasara-ui::components.layouts.app');
    }
}
