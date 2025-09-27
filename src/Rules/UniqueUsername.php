<?php

namespace Nawasara\Core\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueUsername implements ValidationRule
{
    protected $user;

    protected $username;

    public function __construct($username, $user = null)
    {
        $this->username = $username;
        $this->user = $user;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = User::where('username', $this->username);

        if ($this->user) {
            $query->where('id', '!=', $this->user->id);
        }

        if ($query->exists()) {
            $fail('Username ini sudah ada. Silahkan pilih yang lain.');
        }
    }
}
