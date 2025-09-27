<?php

namespace Nawasara\Core\Livewire\Forms;

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;
use Spatie\Permission\Models\Role;

class UserForm extends Form
{
    public $id = '';

    public $name = '';

    public $username = '';

    public $email = '';

    public $password = '';

    public $role;

    public $user;

    public function rules()
    {
        return [
            'name' => 'required|max:250',
            'username' => 'required|max:16',
            'role' => 'required',
            'password' => $this->user ? 'nullable' : 'required|string|regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/',
            // 'repassword' => !$this->password ? 'nullable' : 'required_with:password|same:password|min:6',
            'email' => [
                'required',
                'string',
                'email',
                Rule::unique('users')->ignore($this->user),
            ],
        ];

    }

    public function messages()
    {
        $password_message = 'must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.';

        return [
            'password.regex' => 'The :attribute '.$password_message,
            'database_password.regex' => 'The :attribute '.$password_message,
        ];
    }

    public function store()
    {
        $this->validate();

        $payload = [
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
        ];

        /* jika password terisi, maka masukkan ke proses simpan */
        if ($this->password) {
            $payload['password'] = bcrypt($this->password);
        }

        /* proses simpan */
        $user = User::updateOrCreate([
            'id' => $this->id,
        ], $payload);

        $role = Role::find($this->role);
        $user->syncRoles($role);

        return $user;
    }

    public function setModel(User $user)
    {
        $this->user = $user; // untuk validasi email unique

        $this->id = $user->id;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->role = $user->roles->first()->id;
    }
}
