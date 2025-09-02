<?php

namespace Nawasara\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
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
        self::permission();
        self::roleGivePermission();
    }

    public function permission()
    {
        $group = 'Menu';
        $permissions = PermissionService::create($group, ['user', 'kegiatan penanaman pohon', 'laporan', 'manajemen user']);
        $group = 'User';
        $permissions = PermissionService::create($group, ['view', 'create', 'edit', 'delete']);
        $group = 'Role';
        $permissions = PermissionService::create($group, ['view', 'create', 'edit', 'delete', 'permission']);
        $group = 'Permission';
        $permissions = PermissionService::create($group, ['view', 'create', 'edit', 'delete']);
        $group = 'Component';
        $permissions = PermissionService::create($group, ['view']);
    }

    public function roleGivePermission()
    {
        $role = Role::first();
        $keywords = ['kegiatan.penanaman', 'laporan'];
        

        foreach ($keywords as $key => $value) {
            $permissions = Permission::whereLike('name', '%'.$value.'%')->get();
            foreach ($permissions as $item) {
                $role->givePermissionTo($item->name);
            }
        }
    }
}
