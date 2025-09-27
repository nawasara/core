<?php

namespace Nawasara\Core\Database\Seeders;

use Nawasara\Core\Models\Role;
use Illuminate\Database\Seeder;
use Nawasara\Core\Constants\Constants;
use Spatie\Permission\Models\Permission;
use Nawasara\Core\Services\PermissionService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        self::role();
        self::permission();
        self::roleGivePermission();
    }

    public function role()
    {
        Role::create(['name' => Constants::DEFAULT_ROLE]);
    }

    public function permission()
    {
        $prefix = 'nawasara-core.';
        $group = $prefix.'user';
        $permissions = PermissionService::create($group, ['view', 'create', 'edit', 'delete']);
        $group = $prefix.'role';
        $permissions = PermissionService::create($group, ['view', 'create', 'edit', 'delete', 'permission']);
        $group = $prefix.'permission';
        $permissions = PermissionService::create($group, ['view', 'create', 'edit', 'delete']);
        $group = $prefix.'component';
        $permissions = PermissionService::create($group, ['view']);
    }

    public function roleGivePermission()
    {
        $role = Role::first();
        $permissions = Permission::all();
        foreach ($permissions as $item) {
            $role->givePermissionTo($item->name);
        }
    }
}
