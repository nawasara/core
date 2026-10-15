<?php

namespace Nawasara\Core\Tests;

use Nawasara\Core\Support\RoleAssignmentGuard;
use PHPUnit\Framework\TestCase;

/**
 * Aturan pemberian role/izin. Audit keamanan 15 Oktober 2026, temuan T10:
 * pemegang izin kelola user dulu dapat memberi role `developer` ke siapa
 * pun, termasuk dirinya sendiri.
 */
class RoleAssignmentGuardTest extends TestCase
{
    public function test_admin_tidak_boleh_memberi_role_developer(): void
    {
        $this->assertNotNull(RoleAssignmentGuard::checkRoleAssignment(['admin'], ['guest'], ['developer'], false));
    }

    public function test_developer_boleh_memberi_role_developer(): void
    {
        $this->assertNull(RoleAssignmentGuard::checkRoleAssignment(['developer'], ['guest'], ['developer'], false));
    }

    public function test_admin_tidak_boleh_mencabut_role_developer(): void
    {
        $this->assertNotNull(RoleAssignmentGuard::checkRoleAssignment(['admin'], ['developer'], ['guest'], false));
    }

    public function test_admin_boleh_memberi_role_biasa(): void
    {
        $this->assertNull(RoleAssignmentGuard::checkRoleAssignment(['admin'], ['guest'], ['guest', 'hibah-operator'], false));
    }

    public function test_tidak_boleh_mengubah_role_sendiri_meski_developer(): void
    {
        $this->assertNotNull(RoleAssignmentGuard::checkRoleAssignment(['developer'], ['developer'], ['developer', 'admin'], true));
        $this->assertNotNull(RoleAssignmentGuard::checkRoleAssignment(['admin'], ['admin'], ['admin', 'developer'], true));
    }

    public function test_simpan_akun_sendiri_tanpa_mengubah_role_diizinkan(): void
    {
        // Urutan dan duplikat tidak dianggap perubahan.
        $this->assertNull(RoleAssignmentGuard::checkRoleAssignment(['admin'], ['admin', 'guest'], ['guest', 'admin', 'admin'], true));
    }

    public function test_akun_developer_hanya_boleh_diubah_developer(): void
    {
        $this->assertNotNull(RoleAssignmentGuard::checkUserManagement(['admin'], ['developer']));
        $this->assertNull(RoleAssignmentGuard::checkUserManagement(['developer'], ['developer']));
        $this->assertNull(RoleAssignmentGuard::checkUserManagement(['admin'], ['guest']));
    }

    public function test_hapus_akun(): void
    {
        $this->assertNotNull(RoleAssignmentGuard::checkUserDeletion(['developer'], ['admin'], true), 'hapus diri sendiri');
        $this->assertNotNull(RoleAssignmentGuard::checkUserDeletion(['admin'], ['developer'], false));
        $this->assertNull(RoleAssignmentGuard::checkUserDeletion(['admin'], ['guest'], false));
    }

    public function test_role_developer_hanya_boleh_diubah_developer(): void
    {
        $this->assertNotNull(RoleAssignmentGuard::checkRoleManagement(['admin'], 'developer'));
        $this->assertNull(RoleAssignmentGuard::checkRoleManagement(['developer'], 'developer'));
        $this->assertNull(RoleAssignmentGuard::checkRoleManagement(['admin'], 'guest'));
        $this->assertNull(RoleAssignmentGuard::checkRoleManagement(['admin'], null), 'mode create');
    }

    public function test_hanya_boleh_menambah_izin_yang_dimiliki(): void
    {
        $actorPerms = ['core.user.view', 'core.role.edit'];

        $this->assertNull(RoleAssignmentGuard::checkPermissionGrant(['admin'], $actorPerms, [], ['core.user.view']));

        $denied = RoleAssignmentGuard::checkPermissionGrant(['admin'], $actorPerms, [], ['core.user.view', 'vault.credential.reveal']);
        $this->assertNotNull($denied);
        $this->assertStringContainsString('vault.credential.reveal', $denied);
    }

    public function test_izin_yang_sudah_ada_atau_dicabut_tidak_dibatasi(): void
    {
        // Role sudah memegang izin yang tidak dimiliki aktor: mempertahankan
        // atau mencabutnya tidak menaikkan hak siapa pun.
        $this->assertNull(RoleAssignmentGuard::checkPermissionGrant(['admin'], [], ['secscan.view', 'vault.view'], ['secscan.view']));
    }

    public function test_developer_boleh_memberi_izin_apa_pun(): void
    {
        $this->assertNull(RoleAssignmentGuard::checkPermissionGrant(['developer'], [], [], ['vault.credential.reveal']));
    }
}
