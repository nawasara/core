<?php

namespace Nawasara\Core\Livewire\Pages\Role\Section;

use Livewire\Component;
use Nawasara\Core\Models\Role;
use Livewire\Attributes\Computed;
use Spatie\Permission\Models\Permission;
use Illuminate\Routing\Controllers\Middleware;

class RolePermissionForm extends Component
{
    public $role_name;
    public $selectedPermissions = [];

    
    #[Computed]
    public function permissionGroups()
    {
        return Permission::all()->groupBy('group');
    }

    public function saveRole()
    {
        $this->validate([
            'role_name' => 'required|string|max:255',
        ]);

        $role = Role::create(['name' => $this->role_name]);
        $role->syncPermissions($this->selectedPermissions);

        session()->flash('success', 'Role berhasil dibuat.');
        $this->reset(['role_name', 'selectedPermissions']);
    }
    
    public function render()
    {
        return view('nawasara-core::livewire.pages.role.section.role-permission-form')->layout('nawasara-core::components.layouts.app');
    }
}
