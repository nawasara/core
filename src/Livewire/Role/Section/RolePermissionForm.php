<?php

namespace Nawasara\Core\Livewire\Role\Section;

use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Nawasara\Core\Constants\Constants;
use Nawasara\Core\Livewire\Forms\RoleForm;
use Nawasara\Core\Models\Role;
use Nawasara\Toaster\Concerns\HasToaster;
use Spatie\Permission\Models\Permission;

class RolePermissionForm extends Component
{
    use HasToaster;
    public RoleForm $form;

    public $id;
    public $selectedPermissions = [];
    public $permissions = [];

    public function mount($id = null)
    {
        Gate::authorize($id ? 'nawasara-core.role.edit' : 'nawasara-core.role.create');

        $this->id = $id;
        $this->permissions = Permission::select('id', 'name')->orderBy('name')->get();
        $this->initDataEdit();
    }

    /**
     * Group permissions by name structure: package.module.action
     *
     * Grouping is derived from the permission name itself (split by dots)
     * rather than the legacy `group` column, since other packages register
     * permissions without populating that column. This keeps the role form
     * working for ALL package permissions consistently.
     */
    #[Computed]
    public function permissionGroups()
    {
        return $this->permissions
            ->groupBy(fn ($item) => explode('.', $item->name)[0] ?? 'lainnya')
            ->map(function ($items) {
                return $items
                    ->groupBy(fn ($item) => explode('.', $item->name)[1] ?? 'umum')
                    ->map(function ($subItems) {
                        return $subItems->map(function ($item) {
                            $parts = explode('.', $item->name);
                            $action = count($parts) >= 3
                                ? implode('.', array_slice($parts, 2))
                                : end($parts);

                            return [
                                'id'   => $item->id,
                                'name' => $action,
                            ];
                        })->values();
                    });
            });
    }

    #[On('save-role')]
    public function saveRole(array $permission = [])
    {
        Gate::authorize($this->id ? 'nawasara-core.role.edit' : 'nawasara-core.role.create');

        // Normalize: cast to int, dedupe, reindex.
        $permissions = array_values(array_unique(array_map('intval', $permission)));

        $this->form->setPermissions($permissions);
        $this->form->store();

        $this->alert('success', Constants::NOTIFICATION_SUCCESS_CREATE);
        $this->redirect(route('nawasara-core.role.index'), navigate: true);
    }

    public function initDataEdit()
    {
        if (! $this->id) {
            return;
        }

        $role = Role::with('permissions')->find($this->id);
        $permissions = $role->permissions->pluck('id')->toArray();

        $this->selectedPermissions = $permissions;
        $this->form->setModel($role, $permissions);
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.role.section.role-permission-form')->layout('nawasara-ui::components.layouts.app');
    }
}
