<?php

namespace Nawasara\Core\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Berkas Branding (logo, logo gelap, favicon, gambar OG) disajikan lewat rute
 * Laravel, BUKAN lewat URL /storage.
 *
 * Kenapa: di produksi, public/storage adalah volume kosong — storage:link
 * gagal diam-diam setiap boot — sehingga setiap URL /storage/... menjawab 404.
 * Setting::setFile() tetap menyimpan URL /storage itu ke basis data. Sebelum
 * kelas ini ada, meta-head melihat setelan favicon "ada", memasang URL yang
 * 404, dan melewatkan favicon bawaan; hasilnya Nawasara tanpa favicon sama
 * sekali (15 Nov 2026).
 *
 * URL rute sengaja TANPA ekstensi gambar: blok statis nginx
 * (`location ~* \.(png|jpe?g|svg|...)$`) menelan URL berakhiran ekstensi
 * sebelum sampai ke PHP.
 */
class BrandingAsset
{
    public const KEYS = ['logo', 'logo_dark', 'favicon', 'og_image'];

    private const DISK = 'public';

    /**
     * Jalur relatif terhadap disk public dari nilai yang disimpan
     * Setting::setFile(), mis. "https://x/storage/branding/a.png" →
     * "branding/a.png". Null bila nilainya bukan berkas di disk ini.
     */
    public static function path(?string $stored): ?string
    {
        if (! is_string($stored) || $stored === '') {
            return null;
        }

        $urlPath = parse_url($stored, PHP_URL_PATH) ?: '';
        $pos = strpos($urlPath, '/storage/');
        if ($pos === false) {
            return null;
        }

        $path = rawurldecode(substr($urlPath, $pos + strlen('/storage/')));

        // Hanya berkas di dalam folder branding, tanpa lompatan direktori.
        if ($path === '' || str_contains($path, '..') || ! str_starts_with($path, 'branding/')) {
            return null;
        }

        return $path;
    }

    /**
     * URL yang aman ditampilkan untuk sebuah kunci branding:
     * - berkas di disk public yang MASIH ADA → URL rute + penanda versi
     * - berkas yang hilang → null, supaya pemanggil jatuh ke bawaan
     * - nilai di luar disk ini (mis. URL absolut ke CDN) → dikembalikan apa adanya
     */
    public static function url(string $key, ?string $stored): ?string
    {
        if (! is_string($stored) || $stored === '') {
            return null;
        }

        $path = self::path($stored);
        if ($path === null) {
            return str_contains($stored, '/storage/') ? null : $stored;
        }

        try {
            if (! Storage::disk(self::DISK)->exists($path)) {
                return null;
            }
        } catch (\Throwable) {
            return null;
        }

        return route('nawasara.branding.asset', [
            'key' => $key,
            'v' => substr(md5($stored), 0, 8),
        ]);
    }

    public static function disk(): string
    {
        return self::DISK;
    }
}
