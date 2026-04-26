<?php

namespace Nawasara\Core\Traits;

use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Apply on top of spatie/permission's HasRoles trait.
 *
 * When `session('active_role')` is set (via SwitchRole Livewire), permission
 * checks are scoped down to that single role's permissions instead of the
 * union of all roles the user holds. Result: a user with both "developer"
 * and "operator" roles can switch to "operator" and only see/use what
 * operator allows.
 *
 * Falls back to spatie's default behaviour when no active role is set.
 */
trait RespectsActiveRole
{
    /**
     * Override spatie's hasPermissionTo so that an active role limits the
     * permission check to just that role's grants. Direct user permissions
     * (assigned via givePermissionTo without a role) are still honored.
     *
     * The host model must use this trait via:
     *   use HasRoles, RespectsActiveRole {
     *       RespectsActiveRole::hasPermissionTo insteadof HasRoles;
     *   }
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        $activeRoleName = session('active_role');

        if (! $activeRoleName) {
            return $this->defaultHasPermissionTo($permission, $guardName);
        }

        // User must actually have this role assigned, otherwise active_role
        // is stale (e.g. role removed) — fall back to default check.
        if (! $this->hasRole($activeRoleName)) {
            return $this->defaultHasPermissionTo($permission, $guardName);
        }

        $role = SpatieRole::where('name', $activeRoleName)
            ->where('guard_name', $guardName ?? $this->getDefaultGuardName())
            ->first();

        if (! $role) {
            return $this->defaultHasPermissionTo($permission, $guardName);
        }

        // Resolve the permission name (handles Permission objects, IDs, enums).
        $permissionName = $this->normalizePermissionName($permission, $guardName);

        // Direct user permissions are independent of role — keep them.
        if ($this->hasDirectPermission($permissionName)) {
            return true;
        }

        return $role->hasPermissionTo($permissionName, $guardName ?? $this->getDefaultGuardName());
    }

    /**
     * Spatie's original hasPermissionTo logic — duplicated here so we can
     * delegate when no active_role is in play. Mirrors HasPermissions::hasPermissionTo.
     */
    protected function defaultHasPermissionTo($permission, $guardName = null): bool
    {
        if ($this->getWildcardClass()) {
            return $this->hasWildcardPermission($permission, $guardName);
        }

        $permission = $this->filterPermission($permission, $guardName);

        return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
    }

    protected function normalizePermissionName($permission, ?string $guardName = null): string
    {
        if (is_string($permission)) {
            return $permission;
        }

        if ($permission instanceof \BackedEnum) {
            return (string) $permission->value;
        }

        if (is_object($permission) && property_exists($permission, 'name')) {
            return $permission->name;
        }

        if (is_int($permission)) {
            $perm = $this->getPermissionClass()::findById($permission, $guardName);
            return $perm->name;
        }

        return (string) $permission;
    }
}
