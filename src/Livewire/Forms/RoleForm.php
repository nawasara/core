<?php

namespace Nawasara\Core\Livewire\Forms;

use App\Rules\UniqueRole;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Spatie\Permission\Models\Role;

class RoleForm extends Form
{
    public $id = ''; // digunakan untuk edit

    // #[Validate('required|max:250')]
    public $name = '';

    public $role;

    public function rules()
    {
        return [
            'name' => [
                'required',
                'max:250',
                new UniqueRole($this->name, $this->role),
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama role maksimal 250 karakter.',
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
        $model = Role::updateOrCreate([
            'id' => $this->id,
        ], $payload);

        return $model;
    }

    public function setModel(Role $role)
    {
        $this->role = $role; // untuk validasi email unique

        $this->id = $role->id;
        $this->name = $role->name;
    }
}
