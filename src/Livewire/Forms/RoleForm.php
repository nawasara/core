<?php

namespace Naasara\Core\Livewire\Forms;

use Livewire\Form;
use Livewire\Attributes\Validate;
use Spatie\Permission\Models\Role;
use Nawasara\Core\Rules\UniqueRole;

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

        /* role */
        $model = Role::updateOrCreate([
            'id' => $this->id,
        ], $payload);

        /* set permission */
        if (!empty($this->permissions)) {
            $model->syncPermissions($this->permissions);
        }

        return $model;
    }

    public function setPermissions($permissions = [])
    {
        $this->permissions = $permissions;
    }

    public function setModel(Role $role)
    {
        $this->role = $role; // untuk validasi email unique

        $this->id = $role->id;
        $this->name = $role->name;
    }
}
