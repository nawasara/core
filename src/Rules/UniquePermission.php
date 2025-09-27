<?php

namespace Nawasara\Core\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Spatie\Permission\Models\Permission;

class UniquePermission implements ValidationRule
{
    protected $permission;

    protected $name;

    public function __construct($name, $permission = null)
    {
        $this->name = $name;
        $this->permission = $permission;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = Permission::where('name', $this->name);

        if ($this->permission) {
            $query->where('id', '!=', $this->permission->id);
        }

        if ($query->exists()) {
            $fail('nama ini sudah ada. Silahkan pilih yang lain.');
        }
    }
}
