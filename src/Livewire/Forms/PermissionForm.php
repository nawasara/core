<?php

namespace Nawasara\Core\Livewire\Forms;

use App\Rules\UniquePermission;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Spatie\Permission\Models\Permission;

class PermissionForm extends Form
{
    public $id = ''; // digunakan untuk edit

    // #[Validate('required|max:250')]
    public $name = '';

    public $permission;

    public function rules()
    {
        return [
            'name' => [
                'required',
                'max:250',
                new UniquePermission($this->name, $this->permission),
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama permission maksimal 250 karakter.',
        ];
    }

    public function store()
    {
        $this->validate();

        $payload = [
            'name' => $this->name,
            'guard_name' => 'web',
        ];

        /* proses simpan */
        $model = Permission::updateOrCreate([
            'id' => $this->id,
        ], $payload);

        return $model;
    }

    public function setModel(Permission $permission)
    {
        $this->permission = $permission; // untuk validasi email unique

        $this->id = $permission->id;
        $this->name = $permission->name;
    }
}
