<?php

namespace Nawasara\Core\Livewire\Forms;

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Form;

class UserForm extends Form
{
    public $id;

    public $name;

    public $username;

    public $roles = [];

    public $email;

    public $password;

    public $auth_type = 'local';

    public $user;

    public function rules()
    {
        $passwordRules = 'nullable';

        if ($this->auth_type === 'local' && ! $this->user) {
            $passwordRules = 'required|string|regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/';
        } elseif ($this->auth_type === 'local' && $this->user) {
            $passwordRules = 'nullable|string|regex:/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/';
        }

        return [
            'name' => 'required|max:250',
            'username' => [
                'required',
                'max:'.($this->auth_type === 'sso' ? '50' : '16'),
                Rule::unique('users', 'username')->ignore($this->user),
            ],
            'auth_type' => 'required|in:local,sso',
            'roles' => 'required',
            'password' => $passwordRules,
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
        ];
    }

    public function setRoles(array|int $roles = null)
    {
        $this->roles = is_int($roles) ? [$roles] : $roles ?? [];
    }

    public function store()
    {
        $this->validate();

        $payload = [
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'auth_type' => $this->auth_type,
        ];

        if ($this->auth_type === 'sso') {
            $payload['password'] = null;
        } elseif ($this->password) {
            $payload['password'] = bcrypt($this->password);
        }

        $user = User::updateOrCreate([
            'id' => $this->id,
        ], $payload);

        $user->syncRoles($this->roles);

        return $user;
    }

    public function setModel(User $user)
    {
        $this->user = $user;

        $this->id = $user->id;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->auth_type = $user->auth_type ?? 'local';

        $this->roles = $user->roles->pluck('id')->toArray();
    }
}
