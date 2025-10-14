<?php

namespace Nawasara\Core\Livewire\Pages\Role\Section;

use Livewire\Component;
use Livewire\Attributes\On;
use Nawasara\Core\Models\Role;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Nawasara\Core\Constants\Constants;
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

        // dd($this->permissions);
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

    #[On('save-role')] 
    public function saveRole($permission = [])
    {
        $permissions = self::flattenPermissions($permission);

        $this->validate([
            'role_name' => 'required|string|max:255',
        ]);

        if (!$permissions) {
            toaster_error('Please select at least one permission.');
            return;
        }
        
        DB::beginTransaction();
            // Buat Role baru
            $role = Role::create(['name' => $this->role_name]);
            if (!empty($permissions)) {
                $role->syncPermissions($permissions);
            }
        DB::commit();

        toaster_success(Constants::NOTIFICATION_SUCCESS_CREATE);
        $this->redirect(route('nawasara-core.role.index'), navigate: true);
    }

    public function flattenPermissions(array $data): array
    {
        $merged = [];
        foreach ($data as $group) {
            if (is_array($group) && !empty($group)) {
                // gabungkan secara bertahap
                $merged = array_merge($merged, $group);
            }
        }

        // unique & reindex
        return array_values(array_unique($merged));
    }
    
    public function render()
    {
        return view('nawasara-core::livewire.pages.role.section.role-permission-form')->layout('nawasara-core::components.layouts.app');
    }
}
