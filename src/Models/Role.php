<?php

namespace Nawasara\Core\Models;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as RoleSpatie;


class Role extends RoleSpatie
{
    public function scopeSearch($q, $search = null)
    {
        return $this->where('name', 'like', '%'.$search.'%');
    }

    public function scopeOrderByDefault($q)
    {
        $q->orderBy('name');
    }

    // public function permissions()
    // {
    //     return $this->belongsToMany(Permission::class, 'role_has_permissions', 'role_id', 'permission_id');
    // }

    // public function users()
    // {
    //     return $this->belongsToMany(User::class, 'model_has_roles', 'role_id', 'model_id');
    // }
}
