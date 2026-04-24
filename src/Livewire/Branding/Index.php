<?php

namespace Nawasara\Core\Livewire\Branding;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Nawasara\Core\Models\Setting;

class Index extends Component
{
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
        Gate::authorize('nawasara-core.branding.manage');

        $this->validate([
            'appName' => 'required|max:100',
            'appSubtitle' => 'nullable|max:200',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'logoDark' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'favicon' => 'nullable|image|mimes:png,ico,svg|max:512',
        ]);

        Setting::set('branding.app_name', $this->appName);
        Setting::set('branding.app_subtitle', $this->appSubtitle);

        if ($this->logo) {
            $this->currentLogo = Setting::setFile('branding.logo', $this->logo);
            $this->logo = null;
        }

        if ($this->logoDark) {
            $this->currentLogoDark = Setting::setFile('branding.logo_dark', $this->logoDark);
            $this->logoDark = null;
        }

        if ($this->favicon) {
            $this->currentFavicon = Setting::setFile('branding.favicon', $this->favicon);
            $this->favicon = null;
        }

        toaster_success('Branding berhasil disimpan. Refresh halaman untuk melihat perubahan.');
    }

    public function removeLogo(string $variant): void
    {
        Gate::authorize('nawasara-core.branding.manage');

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

        toaster_success('Logo dihapus');
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.branding.index')
            ->layout('nawasara-ui::components.layouts.app');
    }
}
