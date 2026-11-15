<?php

namespace Nawasara\Core\Tests;

use Nawasara\Core\Support\BrandingAsset;
use PHPUnit\Framework\TestCase;

/**
 * Regresi 15 Nov 2026: favicon Branding menunjuk URL /storage yang 404 di
 * produksi, sehingga Nawasara tampil tanpa favicon. Berkas kini disajikan
 * lewat rute; path() menentukan berkas mana yang boleh disajikan.
 */
class BrandingAssetTest extends TestCase
{
    public function test_url_storage_absolut_diurai_menjadi_jalur_disk(): void
    {
        $this->assertSame(
            'branding/abc123.png',
            BrandingAsset::path('https://nawasara.ponorogo.go.id/storage/branding/abc123.png'),
        );
    }

    public function test_url_storage_relatif_juga_diterima(): void
    {
        $this->assertSame('branding/x.svg', BrandingAsset::path('/storage/branding/x.svg'));
    }

    public function test_lompatan_direktori_ditolak(): void
    {
        $this->assertNull(BrandingAsset::path('https://x/storage/branding/../../.env'));
        $this->assertNull(BrandingAsset::path('https://x/storage/branding/%2e%2e/%2e%2e/.env'));
    }

    public function test_berkas_di_luar_folder_branding_ditolak(): void
    {
        $this->assertNull(BrandingAsset::path('https://x/storage/avatars/u1.png'));
    }

    public function test_nilai_kosong_atau_bukan_storage_menghasilkan_null(): void
    {
        $this->assertNull(BrandingAsset::path(null));
        $this->assertNull(BrandingAsset::path(''));
        $this->assertNull(BrandingAsset::path('https://cdn.example.com/logo.png'));
    }

    public function test_url_luar_disk_dikembalikan_apa_adanya(): void
    {
        $this->assertSame(
            'https://cdn.example.com/logo.png',
            BrandingAsset::url('logo', 'https://cdn.example.com/logo.png'),
        );
    }
}
