<?php

namespace Nawasara\Core\Livewire\Forms;

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Form;
use Nawasara\Core\Models\Role as CoreRole;
use Spatie\Permission\Models\Permission as SpatiePermission;

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

    // PHP 8.4: explicit nullable instead of implicit-from-default (which is
    // a deprecation warning in 8.4 and a hard error in 9.0).
    public function setRoles(array|int|null $roles = null)
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

        // User update: LogsActivity on User auto-captures name + email +
        // username + auth_type changes (see app/Models/User.php).
        $user = User::updateOrCreate([
            'id' => $this->id,
        ], $payload);

        // Roles: Spatie's syncRoles is a pivot op, not seen by LogsActivity.
        // Log the diff manually with names rather than IDs so the audit log
        // is readable without joining.
        $beforeIds = $user->roles()->pluck('id')->sort()->values()->all();
        $user->syncRoles($this->roles);
        $afterIds = $user->roles()->pluck('id')->sort()->values()->all();

        if ($beforeIds !== $afterIds) {
            $beforeNames = CoreRole::whereIn('id', $beforeIds)->pluck('name')->sort()->values()->all();
            $afterNames = CoreRole::whereIn('id', $afterIds)->pluck('name')->sort()->values()->all();

            activity('user-roles')
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->withProperties([
                    'attributes' => ['roles' => $afterNames],
                    'old' => ['roles' => $beforeNames],
                ])
                ->log("User roles updated");
        }

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
