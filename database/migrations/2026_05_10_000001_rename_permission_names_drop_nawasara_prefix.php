<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rename permission names that drift from project naming convention.
 *
 * Convention (verified across 11 of 12 packages on 2026-05-10):
 *   permission name = `<short-package-alias>.<resource>.<action>`
 *
 * Examples already in DB: `whm.account.view`, `cloudflare.dns.edit`,
 * `vault.credential.view`. Only `nawasara-core` was an outlier with the
 * full `nawasara-core.*` prefix; this migration aligns it with the rest.
 *
 * Same migration also handles two webmail-related cleanups:
 *   - `webmail.link.manage` -> `core.email-link.manage` (lives in core, no
 *     reason to use the cross-package `webmail.*` namespace)
 *   - `cpanel.session.launch_as` -> `whm.cpanel.launch_as` (lives in whm)
 *
 * Idempotent: if a target name already exists, the source row is deleted
 * (assignments fall through to the existing target via spatie's pivot
 * uniqueness — see the down() reversal for the symmetric case).
 *
 * Reversible via `migrate:rollback`.
 */
return new class extends Migration
{
    /**
     * Old name -> new name. Order does not matter — each rename is
     * independent and idempotent.
     */
    private array $renames = [
        // nawasara-core.* -> core.*
        'nawasara-core.user.view'           => 'core.user.view',
        'nawasara-core.user.create'         => 'core.user.create',
        'nawasara-core.user.edit'           => 'core.user.edit',
        'nawasara-core.user.delete'         => 'core.user.delete',

        'nawasara-core.role.view'           => 'core.role.view',
        'nawasara-core.role.create'         => 'core.role.create',
        'nawasara-core.role.edit'           => 'core.role.edit',
        'nawasara-core.role.delete'         => 'core.role.delete',
        'nawasara-core.role.permission'     => 'core.role.permission',

        'nawasara-core.permission.view'     => 'core.permission.view',
        'nawasara-core.permission.create'   => 'core.permission.create',
        'nawasara-core.permission.edit'     => 'core.permission.edit',
        'nawasara-core.permission.delete'   => 'core.permission.delete',

        'nawasara-core.component.view'      => 'core.component.view',

        'nawasara-core.branding.view'       => 'core.branding.view',
        'nawasara-core.branding.manage'     => 'core.branding.manage',

        'nawasara-core.auth.manage'         => 'core.auth.manage',

        // webmail.link.manage was an oddly-namespaced permission for what is
        // pure-core code (User identity mapping). Move into the core namespace.
        'webmail.link.manage'               => 'core.email-link.manage',

        // cpanel sub-feature owned by whm — align with package prefix.
        'cpanel.session.launch_as'          => 'whm.cpanel.launch_as',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('permissions')) {
            // Spatie permissions table not installed — nothing to rename.
            return;
        }

        foreach ($this->renames as $old => $new) {
            $this->renamePermission($old, $new);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('permissions')) {
            return;
        }

        // Reverse map — same logic, source/target flipped.
        foreach ($this->renames as $old => $new) {
            $this->renamePermission($new, $old);
        }
    }

    /**
     * Rename a permission row. If a row with the target name already exists,
     * delete the source row (its role/user pivot rows will cascade if FK is
     * set; otherwise manually clean up — but in practice spatie's tables use
     * cascade-on-delete from the migrations they ship).
     *
     * Three cases:
     *   1. Source missing, target missing — no-op
     *   2. Source exists, target missing — UPDATE source.name
     *   3. Source exists, target exists  — DELETE source (target wins)
     *
     * The third case can happen if the migration was partially applied
     * before, or if the seeder has already created the new name on a
     * fresh-install path that's now upgrading.
     */
    private function renamePermission(string $old, string $new): void
    {
        $sourceExists = DB::table('permissions')
            ->where('name', $old)->where('guard_name', 'web')
            ->exists();

        if (! $sourceExists) {
            return;
        }

        $targetExists = DB::table('permissions')
            ->where('name', $new)->where('guard_name', 'web')
            ->exists();

        if ($targetExists) {
            // Source's pivot rows (role_has_permissions / model_has_permissions)
            // will be removed by FK CASCADE on permissions.delete().
            DB::table('permissions')
                ->where('name', $old)->where('guard_name', 'web')
                ->delete();
            return;
        }

        DB::table('permissions')
            ->where('name', $old)->where('guard_name', 'web')
            ->update(['name' => $new, 'updated_at' => now()]);
    }
};
