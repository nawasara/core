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
        Role::firstOrCreate(['name' => Constants::DEFAULT_ROLE]);
        // Default role untuk auto-provision SSO user. Sengaja tanpa permission
        // — admin promote ke role lain setelah verifikasi.
        Role::firstOrCreate(['name' => 'guest']);
    }

    public function permission()
    {
        $modules = [
            'user'       => ['view', 'create', 'edit', 'delete'],
            'role'       => ['view', 'create', 'edit', 'delete', 'permission'],
            'permission' => ['view', 'create', 'edit', 'delete'],
            'component'  => ['view'],
            'branding'   => ['view', 'manage'],
            'auth'       => ['manage'],
        ];

        foreach ($modules as $module => $actions) {
            PermissionService::create('nawasara-core.'.$module, $actions);
        }
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
