<?php

namespace Nawasara\Core\Livewire\Auth;

use Illuminate\Support\Facades\Route;
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

        // Redirect to the app's home/dashboard. The package can't assume
        // a specific route name, so check common candidates and fall back
        // to '/' if none are defined.
        $target = match (true) {
            Route::has('dashboard') => route('dashboard'),
            Route::has('home') => route('home'),
            default => url('/'),
        };

        return redirect()->intended($target);
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.auth.switch-role')
            ->layout('nawasara-ui::components.layouts.guest');
    }
}
