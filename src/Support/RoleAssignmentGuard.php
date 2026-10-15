<?php

namespace Nawasara\Core\Support;

use Nawasara\Core\Constants\Constants;

/**
 * Aturan siapa boleh memberi role/izin kepada siapa.
 *
 * Audit keamanan 15 Oktober 2026, temuan T10: pemegang `core.user.create`
 * atau `core.user.edit` dapat memberi role apa pun, termasuk `developer`,
 * kepada siapa pun termasuk dirinya sendiri, dan dapat mengganti sandi akun
 * lokal developer. Pemegang `core.role.edit` dapat menambah izin apa pun ke
 * role yang ia pegang sendiri. Dua-duanya eskalasi hak: izin "kelola user"
 * berubah menjadi "jadi developer".
 *
 * Kelas ini sengaja murni (hanya menerima nama role/izin berupa string,
 * tanpa model, tanpa DB, tanpa Auth) supaya aturannya dapat diuji dengan
 * PHPUnit biasa dan dipakai sama persis oleh form user, form role, dan tabel
 * hapus. Setiap metode check*() mengembalikan pesan penolakan berbahasa
 * Indonesia, atau null bila diizinkan.
 */
final class RoleAssignmentGuard
{
    /**
     * Role tertinggi. Pemegang role ini melewati semua izin (lihat
     * PermissionSeeder: developer diberi semua permission), jadi memberikan
     * role ini sama dengan memberi seluruh sistem.
     */
    public const PRIVILEGED_ROLE = Constants::DEFAULT_ROLE;

    /**
     * @param  array<int,string>  $roleNames
     */
    public static function isPrivileged(array $roleNames): bool
    {
        return in_array(self::PRIVILEGED_ROLE, $roleNames, true);
    }

    /**
     * Boleh-kah aktor mengubah atau menghapus akun target?
     *
     * Akun pemegang `developer` hanya boleh disentuh oleh developer lain.
     * Tanpa ini, admin biasa dapat mengganti sandi lokal developer lalu
     * masuk sebagai developer.
     *
     * @param  array<int,string>  $actorRoles
     * @param  array<int,string>  $targetRoles
     */
    public static function checkUserManagement(array $actorRoles, array $targetRoles): ?string
    {
        if (self::isPrivileged($targetRoles) && ! self::isPrivileged($actorRoles)) {
            return 'Hanya developer yang boleh mengubah atau menghapus akun developer.';
        }

        return null;
    }

    /**
     * Boleh-kah aktor menghapus akun target? Sama dengan aturan ubah,
     * ditambah larangan menghapus akun sendiri (mengunci diri keluar dan
     * menghapus jejak siapa yang melakukan perubahan).
     *
     * @param  array<int,string>  $actorRoles
     * @param  array<int,string>  $targetRoles
     */
    public static function checkUserDeletion(array $actorRoles, array $targetRoles, bool $isSelf): ?string
    {
        if ($isSelf) {
            return 'Anda tidak dapat menghapus akun Anda sendiri.';
        }

        return self::checkUserManagement($actorRoles, $targetRoles);
    }

    /**
     * Boleh-kah aktor mengganti role target dari $currentRoles menjadi
     * $requestedRoles?
     *
     * - Mengubah role akun sendiri dilarang sama sekali: itu jalan pintas
     *   paling langsung untuk menaikkan hak sendiri.
     * - Role `developer` hanya boleh diberikan atau dicabut oleh developer.
     *
     * @param  array<int,string>  $actorRoles
     * @param  array<int,string>  $currentRoles
     * @param  array<int,string>  $requestedRoles
     */
    public static function checkRoleAssignment(array $actorRoles, array $currentRoles, array $requestedRoles, bool $isSelf): ?string
    {
        if (! self::setsDiffer($currentRoles, $requestedRoles)) {
            return null;
        }

        if ($isSelf) {
            return 'Anda tidak dapat mengubah role akun Anda sendiri. Minta admin lain melakukannya.';
        }

        $touchesPrivileged = self::isPrivileged($currentRoles) || self::isPrivileged($requestedRoles);

        if ($touchesPrivileged && ! self::isPrivileged($actorRoles)) {
            return 'Hanya developer yang boleh memberikan atau mencabut role developer.';
        }

        return null;
    }

    /**
     * Boleh-kah aktor mengubah role bernama $roleName (nama atau izinnya)?
     * Role `developer` hanya boleh diubah atau dihapus oleh developer.
     *
     * @param  array<int,string>  $actorRoles
     */
    public static function checkRoleManagement(array $actorRoles, ?string $roleName): ?string
    {
        if ($roleName === self::PRIVILEGED_ROLE && ! self::isPrivileged($actorRoles)) {
            return 'Hanya developer yang boleh mengubah atau menghapus role developer.';
        }

        return null;
    }

    /**
     * Boleh-kah aktor mengganti izin sebuah role dari $currentPermissions
     * menjadi $requestedPermissions?
     *
     * Selain developer, aktor hanya boleh MENAMBAHKAN izin yang ia pegang
     * sendiri. Tanpa batas ini, pemegang `core.role.permission` dapat
     * menambahkan izin apa pun ke role yang ia pegang, lalu memakainya.
     * Mencabut izin tidak dibatasi: itu tidak menaikkan hak siapa pun.
     *
     * @param  array<int,string>  $actorRoles
     * @param  array<int,string>  $actorPermissions
     * @param  array<int,string>  $currentPermissions
     * @param  array<int,string>  $requestedPermissions
     */
    public static function checkPermissionGrant(
        array $actorRoles,
        array $actorPermissions,
        array $currentPermissions,
        array $requestedPermissions,
    ): ?string {
        if (self::isPrivileged($actorRoles)) {
            return null;
        }

        $added = array_values(array_diff($requestedPermissions, $currentPermissions));
        $notHeld = array_values(array_diff($added, $actorPermissions));

        if ($notHeld !== []) {
            sort($notHeld);

            return 'Anda hanya boleh memberikan izin yang Anda miliki sendiri. Tidak diizinkan: '
                .implode(', ', $notHeld).'.';
        }

        return null;
    }

    /**
     * Apakah dua himpunan berbeda (urutan dan duplikat diabaikan)?
     *
     * @param  array<int,string|int>  $a
     * @param  array<int,string|int>  $b
     */
    public static function setsDiffer(array $a, array $b): bool
    {
        $a = array_values(array_unique(array_map('strval', $a)));
        $b = array_values(array_unique(array_map('strval', $b)));
        sort($a);
        sort($b);

        return $a !== $b;
    }
}
