<?php

namespace Nawasara\Core\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Nawasara\Core\Models\Setting;
use Nawasara\Core\Support\BrandingAsset;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menyajikan berkas Branding dari disk public. Lihat BrandingAsset untuk
 * alasan tidak memakai URL /storage.
 *
 * Tanpa auth: favicon dan logo tampil di halaman masuk, dan gambar OG dibaca
 * perayap WhatsApp/Telegram. Isinya memang aset publik.
 */
class BrandingAssetController
{
    public function __invoke(string $key): Response
    {
        abort_unless(in_array($key, BrandingAsset::KEYS, true), 404);

        $path = BrandingAsset::path(Setting::get("branding.{$key}"));
        $disk = Storage::disk(BrandingAsset::disk());

        abort_if($path === null || ! $disk->exists($path), 404);

        // URL membawa ?v=<hash nilai>, jadi berkas baru selalu mendapat URL
        // baru dan cache panjang aman.
        return $disk->response($path, null, [
            'Cache-Control' => 'public, max-age=604800',
            'X-Content-Type-Options' => 'nosniff',
            // Logo boleh SVG. CSP ini mencegah skrip di dalam SVG berjalan
            // bila berkasnya dibuka langsung di peramban.
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; img-src data:",
        ]);
    }
}
