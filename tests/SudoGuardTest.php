<?php

namespace Nawasara\Core\Tests;

use Nawasara\Core\Services\SudoService;
use PHPUnit\Framework\TestCase;

/**
 * Regresi audit keamanan 15 Oktober 2026, temuan T6.
 *
 * 1. Step-up hanya sah bila dilakukan akun Keycloak yang SAMA dengan pemilik
 *    sesi (`sub` === users.keycloak_id).
 * 2. #[RequiresSudo] tidak berjalan untuk listener event, jadi setiap method
 *    ber-#[On] yang juga ber-#[RequiresSudo] wajib memanggil requireSudo()
 *    di dalam badannya.
 */
class SudoGuardTest extends TestCase
{
    private const SUB = '8f14e45f-ceea-467a-9f3c-6d1b2a7c0001';

    public function test_step_up_oleh_pemilik_sesi_diterima(): void
    {
        $this->assertSame(
            SudoService::OUTCOME_OK,
            SudoService::evaluateClaims(['sub' => self::SUB, 'acr' => 'sudo'], 'sudo', self::SUB),
        );
    }

    public function test_step_up_oleh_akun_lain_ditolak(): void
    {
        $this->assertSame(
            SudoService::OUTCOME_SUBJECT_MISMATCH,
            SudoService::evaluateClaims(['sub' => 'akun-penyerang', 'acr' => 'sudo'], 'sudo', self::SUB),
        );
    }

    public function test_user_lokal_tanpa_keycloak_id_ditolak(): void
    {
        $claims = ['sub' => self::SUB, 'acr' => 'sudo'];

        $this->assertSame(SudoService::OUTCOME_SUBJECT_MISMATCH, SudoService::evaluateClaims($claims, 'sudo', null));
        $this->assertSame(SudoService::OUTCOME_SUBJECT_MISMATCH, SudoService::evaluateClaims($claims, 'sudo', ''));
    }

    public function test_token_tanpa_sub_ditolak(): void
    {
        $this->assertSame(
            SudoService::OUTCOME_SUBJECT_MISMATCH,
            SudoService::evaluateClaims(['acr' => 'sudo'], 'sudo', self::SUB),
        );
    }

    public function test_acr_salah_ditolak(): void
    {
        $this->assertSame(
            SudoService::OUTCOME_ACR_MISMATCH,
            SudoService::evaluateClaims(['sub' => self::SUB, 'acr' => '1'], 'sudo', self::SUB),
        );
        $this->assertSame(
            SudoService::OUTCOME_ACR_MISMATCH,
            SudoService::evaluateClaims(['sub' => self::SUB], 'sudo', self::SUB),
        );
    }

    public function test_acr_berbentuk_array_tetap_dibaca(): void
    {
        $this->assertSame(
            SudoService::OUTCOME_OK,
            SudoService::evaluateClaims(['sub' => self::SUB, 'acr' => ['sudo']], 'sudo', self::SUB),
        );
    }

    /**
     * Pindai sumber komponen Livewire core. Dibaca sebagai teks, bukan lewat
     * reflection, karena kelasnya bergantung pada alias yang baru dibuat
     * saat service provider boot.
     */
    public function test_listener_event_bersudo_memanggil_require_sudo(): void
    {
        $dir = dirname(__DIR__).'/src/Livewire';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        $checked = 0;

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            foreach ($this->methodsWithBothAttributes((string) file_get_contents($file->getPathname())) as $name => $body) {
                $checked++;
                $this->assertStringContainsString(
                    'requireSudo(',
                    $body,
                    "{$file->getFilename()}::{$name}() memakai #[On] dan #[RequiresSudo] tetapi tidak memanggil requireSudo(). Atribut tidak berjalan untuk event.",
                );
            }
        }

        // User dan Role Table: pastikan pemindai memang menemukan sesuatu,
        // supaya tes ini tidak lolos diam-diam bila polanya berubah.
        $this->assertGreaterThanOrEqual(2, $checked);
    }

    /**
     * @return array<string,string> nama method => badan method
     */
    private function methodsWithBothAttributes(string $source): array
    {
        $found = [];
        $pattern = '/((?:\s*#\[[^\n]*\]\s*)+)\s*(?:public|protected|private)\s+function\s+(\w+)\s*\(/';

        if (! preg_match_all($pattern, $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            return [];
        }

        foreach ($matches as $m) {
            $attrs = $m[1][0];
            if (! str_contains($attrs, '#[On(') || ! str_contains($attrs, 'RequiresSudo')) {
                continue;
            }

            $start = strpos($source, '{', $m[0][1] + strlen($m[0][0]));
            $depth = 0;
            $end = $start;
            for ($i = $start, $n = strlen($source); $i < $n; $i++) {
                if ($source[$i] === '{') {
                    $depth++;
                } elseif ($source[$i] === '}' && --$depth === 0) {
                    $end = $i;
                    break;
                }
            }

            $found[$m[2][0]] = substr($source, $start, $end - $start + 1);
        }

        return $found;
    }
}
