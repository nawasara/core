<?php

namespace Nawasara\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Nawasara\Core\Models\ChangelogEntry;

/**
 * Seeds the initial "Riwayat Update" entries from work already shipped, written
 * in plain, benefit-focused language for end users. Idempotent — keyed by title
 * so re-running won't duplicate.
 */
class ChangelogSeeder extends Seeder
{
    public function run(): void
    {
        $entries = [
            [
                'title'    => 'Blokir Otomatis IP Penyerang',
                'body'     => "Nawasara kini bisa mem-blokir otomatis alamat IP yang menyerang situs OPD — langsung di Cloudflare, sebelum serangan sampai ke server. Serangan berat yang berulang (SQL injection, akses file terlarang, dsb.) ditangani tanpa perlu tindakan manual.\n\nUntuk keamanan, fitur ini punya banyak pengaman: daftar putih (IP kantor, Cloudflare, mesin pencari tidak akan pernah di-blokir) dan mode uji-coba sebelum benar-benar aktif.",
                'category' => ChangelogEntry::CATEGORY_SECURITY,
                'is_major' => true,
                'version_tag' => 'secscan v0.9.0',
                'days_ago' => 3,
            ],
            [
                'title'    => 'Deteksi Situs Judi Online & Obat Ilegal',
                'body'     => "Scanner keamanan Nawasara kini mendeteksi situs OPD yang disusupi konten judi online (judol) maupun iklan obat ilegal (obat aborsi, dll.) — baik yang tersimpan di database, di file, maupun yang hanya muncul untuk mesin pencari (cloaking). Temuan langsung tampil di dashboard keamanan dengan bukti dan tingkat bahayanya.",
                'category' => ChangelogEntry::CATEGORY_SECURITY,
                'is_major' => true,
                'version_tag' => 'secscan v0.8.x',
                'days_ago' => 5,
            ],
            [
                'title'    => 'Identifikasi Alamat IP Penyerang Asli',
                'body'     => "Sebelumnya, serangan yang lewat Cloudflare tercatat dengan IP milik Cloudflare, bukan penyerang sebenarnya. Sekarang server sudah mengenali alamat IP asli di balik Cloudflare, sehingga setiap insiden menampilkan penyerang yang benar — dan blokir otomatis menyasar pihak yang tepat.",
                'category' => ChangelogEntry::CATEGORY_IMPROVEMENT,
                'is_major' => false,
                'version_tag' => null,
                'days_ago' => 3,
            ],
            [
                'title'    => 'Klasifikasi Serangan Standar (MITRE ATT&CK)',
                'body'     => "Setiap insiden keamanan kini diberi label teknik serangan standar internasional (MITRE ATT&CK) — misalnya brute force, web shell, atau exploit. Operator bisa langsung memahami jenis serangan dan cara menanganinya dengan sekali klik ke referensi resmi.",
                'category' => ChangelogEntry::CATEGORY_FEATURE,
                'is_major' => false,
                'version_tag' => 'secscan v0.6.0',
                'days_ago' => 6,
            ],
            [
                'title'    => 'Agent Keamanan Server',
                'body'     => "Nawasara kini bisa memasang agent ringan di server (termasuk yang berbasis Docker) untuk memantau serangan dan memindai file berbahaya (web shell, backdoor) secara langsung dari dalam server. Mendukung berbagai jenis server: Nginx, Apache/WHM, hingga Caddy/FrankenPHP.",
                'category' => ChangelogEntry::CATEGORY_FEATURE,
                'is_major' => false,
                'version_tag' => 'agent v0.8.x',
                'days_ago' => 6,
            ],
            [
                'title'    => 'Ekspor Data & Tampilan Tab',
                'body'     => "Tabel-tabel keamanan (insiden, temuan, daftar agent) kini bisa diekspor ke CSV, Excel, atau JSON. Halaman detail agent juga dirapikan dengan tampilan tab agar lebih mudah dinavigasi.",
                'category' => ChangelogEntry::CATEGORY_IMPROVEMENT,
                'is_major' => false,
                'version_tag' => 'secscan v0.7.0',
                'days_ago' => 6,
            ],
            [
                'title'    => 'Login SSO Lebih Aman',
                'body'     => "Sesi login Nawasara kini terhubung dengan sesi SSO (Keycloak). Jika Anda logout dari SSO, Nawasara ikut logout secara otomatis — mencegah sesi yang seharusnya sudah berakhir tetap aktif.",
                'category' => ChangelogEntry::CATEGORY_SECURITY,
                'is_major' => false,
                'version_tag' => 'core v0.3.6',
                'days_ago' => 3,
            ],
        ];

        foreach ($entries as $e) {
            ChangelogEntry::firstOrCreate(
                ['title' => $e['title']],
                [
                    'body'         => $e['body'],
                    'category'     => $e['category'],
                    'is_major'     => $e['is_major'],
                    'version_tag'  => $e['version_tag'],
                    'published_at' => now()->subDays($e['days_ago']),
                ],
            );
        }
    }
}
