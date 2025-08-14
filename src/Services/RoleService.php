<?php

namespace Nawasara\Core\Services;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleService
{
    public function createRole(string $name, string $guardName = 'web')
    {
        return Role::create(['name' => $name, 'guard_name' => $guardName]);
    }

    public function createPermission(string $name, string $guardName = 'web')
    {
        return Permission::create(['name' => $name, 'guard_name' => $guardName]);
    }
}
