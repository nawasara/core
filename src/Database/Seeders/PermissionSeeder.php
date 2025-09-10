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
        $prefix = 'nawasara-core.';
        $group = $prefix.'menu';
        $permissions = PermissionService::create($group, ['user', 'component']);
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
        $keywords = ['kegiatan.penanaman', 'laporan'];
        

        foreach ($keywords as $key => $value) {
            $permissions = Permission::whereLike('name', '%'.$value.'%')->get();
            foreach ($permissions as $item) {
                $role->givePermissionTo($item->name);
            }
        }
    }
}
