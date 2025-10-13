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
    public $permissions = [];

    public function mount()
    {
        $this->permissions = Permission::select('id', 'name', 'group')->get();
    }
    
    #[Computed]
    public function permissionGroups()
    {
        return $this->permissions
        ->groupBy(function ($item) {
            // Ambil prefix utama "nawasara-core"
            return explode('.', $item->group)[0] ?? null;
        })
        ->map(function ($items) {
            return $items->groupBy(function ($item) {
                // Ambil sub-group misalnya "user", "role", "permission"
                return explode('.', $item->group)[1] ?? null;
            })->map(function ($subItems) {
                // Ambil nama permission terakhir misalnya "view", "create"
                return $subItems->map(function ($item) {
                    return [
                        'id'   => $item->id,
                        'name' => str_replace($item->group . '.', '', $item->name),
                    ];
                })->values();
            });
        });
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
