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

        // Permission naming: short prefix `core.*` (consistent with whm.*,
        // cloudflare.*, vault.*, etc — all 12 packages now use the bare
        // package alias for permission names).
        foreach ($modules as $module => $actions) {
            PermissionService::create('core.'.$module, $actions);
        }

        // Email-link manager — admin UI to manually override the auto-derived
        // OPD email mapping. Logic + UI fully owned by core (User identity
        // mapping is core's domain), so the permission lives here too.
        Permission::firstOrCreate(['name' => 'core.email-link.manage', 'guard_name' => 'web']);

        // Bulk Excel import for email-link. Separate from .manage because
        // it touches Keycloak attributes + can auto-provision Laravel users —
        // a larger blast radius than the per-row manual UI.
        Permission::firstOrCreate(['name' => 'core.email-link.import', 'guard_name' => 'web']);

        // Changelog author permission. Reading the Riwayat Update page needs no
        // permission (any logged-in user); only writing/publishing is gated.
        Permission::firstOrCreate(['name' => 'core.changelog.manage', 'guard_name' => 'web']);
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
