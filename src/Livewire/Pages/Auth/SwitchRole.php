<?php

namespace Nawasara\Core\Livewire\Pages\Auth;

use Livewire\Component;
use Spatie\Permission\Models\Role;

class SwitchRole extends Component
{
    public $roles = [];

    public function mount()
    {
        $this->roles = auth()->user()?->roles ?? [];
    }

    public function switchRole($roleName)
    {
        $user = auth()->user();
        if (! $user) {
            return redirect()->route('login');
        }

        // normalize role name
        $roleName = (string) $roleName;

        // verify role exists and user has this role
        $role = Role::where('name', $roleName)->first();
        if (! $role || ! $user->hasRole($roleName)) {
            // optional: flash error or dispatch browser event
            session()->flash('toast', [
                'type' => 'error',
                'message' => "You don't have the role {$roleName} or it does not exist.",
            ]);
            return redirect()->back();
        }

        // set active role in session
        session(['active_role' => $roleName]);

        session()->flash('toast', [
            'type' => 'success',
            'message' => "Active role switched to {$roleName}.",
        ]);

        return redirect()->route('nawasara-core.dashboard', ['role' => $roleName]);
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.auth.switch-role')
            ->layout('nawasara-core::components.layouts.guest');
    }
}
