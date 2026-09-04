<?php

namespace Nawasara\Core\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Kapan catatan update DISIARKAN ke grup, dan kapan tidak.
 *
 * Catatan dapat terbit lewat tiga jalan: dibuat langsung dalam keadaan terbit,
 * draf yang disunting menjadi terbit, atau tombol terbit ditekan. Karena itu
 * pemicunya observer, bukan panggilan di salah satu halaman — menaruhnya di
 * satu jalan saja membuat dua jalan lain diam, dan diamnya tidak terlihat
 * sebagai kesalahan.
 */
class ChangelogBroadcastTest extends TestCase
{
    /**
     * Cerminan syarat di ChangelogEntryObserver.
     *
     * @param  ?string  $sebelum  nilai published_at SEBELUM perubahan
     * @param  ?string  $sesudah  nilai published_at SESUDAH perubahan
     */
    private function menyiarkan(?string $sebelum, ?string $sesudah, bool $baru = false): bool
    {
        if ($baru) {
            return $sesudah !== null;
        }

        return $sebelum === null && $sesudah !== null;
    }

    public function test_draf_tidak_disiarkan(): void
    {
        $this->assertFalse($this->menyiarkan(null, null, baru: true));
    }

    public function test_dibuat_langsung_terbit_disiarkan(): void
    {
        $this->assertTrue($this->menyiarkan(null, '2026-09-04 10:00', baru: true));
    }

    public function test_draf_menjadi_terbit_disiarkan(): void
    {
        $this->assertTrue($this->menyiarkan(null, '2026-09-04 10:00'));
    }

    /**
     * Inti penjagaannya: menyunting catatan yang SUDAH terbit tidak mengirim
     * ulang. Tanpa ini, satu perbaikan tanda baca membangunkan seluruh grup.
     */
    public function test_menyunting_yang_sudah_terbit_tidak_mengirim_ulang(): void
    {
        $this->assertFalse($this->menyiarkan('2026-09-04 10:00', '2026-09-04 10:00'));
    }

    /**
     * Waktu terbit yang digeser juga bukan penerbitan baru.
     *
     * `wasChanged('published_at')` saja akan menganggapnya begitu — itulah
     * sebabnya nilai SEBELUMNYA ikut diperiksa.
     */
    public function test_menggeser_waktu_terbit_tidak_mengirim_ulang(): void
    {
        $this->assertFalse($this->menyiarkan('2026-09-04 10:00', '2026-09-04 12:00'));
    }

    /** Menarik kembali ke draf jelas bukan alasan mengumumkan. */
    public function test_ditarik_kembali_jadi_draf_tidak_disiarkan(): void
    {
        $this->assertFalse($this->menyiarkan('2026-09-04 10:00', null));
    }

    /** Pengumuman, bukan peringatan — tempatnya bukan di antara yang genting. */
    public function test_masuk_topik_pengumuman(): void
    {
        $topik = fn (string $kind) => $kind === 'core.changelog.published' ? 'pengumuman' : 'lain';

        $this->assertSame('pengumuman', $topik('core.changelog.published'));
    }
}
