<?php

namespace Nawasara\Core\Livewire\Role;

use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Form extends Component
{
    public $id;
    public function mount($id = null)
    {
        // Route already gates with `core.role.create|core.role.edit` via
        // PermissionMiddleware, but a Livewire component can be mounted
        // directly via JS dispatch even without the wrapping route. Re-check
        // here so the gate holds regardless of entry point.
        Gate::authorize($id ? 'core.role.edit' : 'core.role.create');

        $this->id = $id;
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.role.form')->layout('nawasara-ui::components.layouts.app');
    }
}
