<?php

namespace Nawasara\Core\Models;

use App\Models\User;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as RoleSpatie;


class Role extends RoleSpatie
{
    use LogsActivity;

    /**
     * Activity log: capture role name + guard changes. Permission
     * attach/detach is NOT seen by this trait (those are pivot ops
     * on role_has_permissions), so RolePermissionForm logs them
     * manually.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'guard_name'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => "Role {$event}");
    }

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
