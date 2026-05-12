<?php

namespace Nawasara\Core\Livewire\Forms;

use Livewire\Form;
use Nawasara\Core\Models\Role;        // local subclass with LogsActivity
use Nawasara\Core\Rules\UniqueRole;
use Spatie\Permission\Models\Permission as SpatiePermission;

class RoleForm extends Form
{
    public $id = ''; // digunakan untuk edit
    public $name;
    public $permissions = [];
    public $role;

    public function rules()
    {
        return [
            'name' => [
                'required',
                'max:250',
                'regex:/^[a-zA-Z\s]+$/',
                new UniqueRole($this->name, $this->role),
            ],
            'permissions' => 'required|array|min:1',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Role is required.',
            'name.max' => 'Role name max 250 character.',
            'name.regex' => 'Format invalid.',
            'permissions.required' => 'Please select at least one permission.',
            'permissions.array' => 'Invalid permissions format.',
        ];
    }

    public function store()
    {
        $this->validate();

        $payload = [
            'name' => $this->name,
            'guard_name' => 'web',
        ];

        /* role: updateOrCreate triggers LogsActivity automatically for
           the model fields (name + guard_name). */
        $model = Role::updateOrCreate([
            'id' => $this->id,
        ], $payload);

        /* permissions: Spatie's syncPermissions() is a pivot operation
           and is invisible to the model's LogsActivity trait, so log
           the diff manually. We compute before/after to give the
           audit log a useful before+after pair. */
        if (! empty($this->permissions)) {
            $before = $model->permissions()->pluck('id')->sort()->values()->all();
            $model->syncPermissions($this->permissions);
            $after = $model->permissions()->pluck('id')->sort()->values()->all();

            // Only log if the permission set actually changed.
            if ($before !== $after) {
                $beforeNames = SpatiePermission::whereIn('id', $before)->pluck('name')->sort()->values()->all();
                $afterNames = SpatiePermission::whereIn('id', $after)->pluck('name')->sort()->values()->all();

                activity('role-permissions')
                    ->performedOn($model)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'attributes' => ['permissions' => $afterNames],
                        'old' => ['permissions' => $beforeNames],
                    ])
                    ->log("Role permissions updated");
            }
        }

        return $model;
    }

    public function setPermissions($permissions = [])
    {
        $this->permissions = $permissions;
    }

    public function setModel(Role $role, $permissions = [])
    {
        $this->role = $role; // untuk validasi nama unique (UniqueRole rule)

        $this->id = $role->id;
        $this->name = $role->name;
        $this->permissions = $permissions;
    }
}
