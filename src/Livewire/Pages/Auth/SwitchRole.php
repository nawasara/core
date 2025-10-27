<?php

namespace Nawasara\Core\Livewire\Pages\Auth;

use Livewire\Component;

class SwitchRole extends Component
{
    public $roles = [];

    public function mount()
    {
        $this->roles = auth()->user()?->roles ?? [];
    }

    public function switchRole($roleName)
    {
        // Logika ubah role aktif
        session(['active_role' => $roleName]);

        // Redirect ke halaman sesuai role
        return redirect()->route('nawasara-core.dashboard', ['role' => $roleName]);
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.auth.switch-role')
            ->layout('nawasara-core::components.layouts.guest');
    }
}
