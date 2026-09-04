<?php

namespace Nawasara\Core\Observers;

use Illuminate\Support\Facades\Log;
use Nawasara\Core\Models\ChangelogEntry;
use Nawasara\Notification\Facades\Notify;
use Nawasara\Vault\Facades\Vault;

/**
 * Menyiarkan catatan update ke kanal grup saat DITERBITKAN.
 *
 * ## Kenapa observer, bukan dipanggil dari halamannya
 *
 * Sebuah catatan dapat terbit lewat tiga jalan: dibuat langsung dalam keadaan
 * terbit, disunting dari draf menjadi terbit, atau ditekan tombol terbitnya.
 * Menaruh pengirimannya di salah satu jalan saja berarti dua jalan lain diam —
 * dan diamnya tidak terlihat sebagai kesalahan, hanya sebagai pengumuman yang
 * "kebetulan tidak terkirim".
 *
 * ## Hanya sekali, saat BERPINDAH menjadi terbit
 *
 * Yang memicu adalah perubahan `published_at` dari kosong menjadi terisi.
 * Menyunting salah ketik pada catatan yang sudah terbit TIDAK mengirim ulang —
 * kalau tidak, satu perbaikan tanda baca akan membangunkan seluruh grup.
 *
 * Draf sengaja tidak dikirim: draf memang belum untuk dibaca siapa pun.
 */
class ChangelogEntryObserver
{
    public function created(ChangelogEntry $entry): void
    {
        if ($entry->published_at !== null) {
            $this->siarkan($entry);
        }
    }

    public function updated(ChangelogEntry $entry): void
    {
        // wasChanged() saja tidak cukup: ia juga benar saat waktu terbitnya
        // sekadar digeser. Yang dicari adalah perpindahan dari BELUM terbit.
        if ($entry->wasChanged('published_at')
            && $entry->getOriginal('published_at') === null
            && $entry->published_at !== null) {
            $this->siarkan($entry);
        }
    }

    /**
     * Kirim ke kanal grup — Telegram dan kanal lain yang menuju satu tempat.
     *
     * Kegagalan di sini TIDAK boleh menggagalkan penyimpanan: catatannya sudah
     * tersimpan, dan melempar galat akan membuat staf mengira update-nya gagal
     * lalu menyimpannya dua kali.
     */
    protected function siarkan(ChangelogEntry $entry): void
    {
        if (! class_exists(Notify::class)) {
            return;
        }

        $channels = (array) config('nawasara.changelog.broadcast_channels', []);

        foreach ($channels as $channel) {
            $tujuan = $this->tujuanGrup($channel);

            if ($tujuan === null) {
                continue;
            }

            try {
                Notify::to($tujuan)
                    ->channel([$channel])
                    ->subject($this->judul($entry))
                    ->body($this->isi($entry))
                    ->context([
                        'kind' => 'core.changelog.published',
                        'changelog_id' => $entry->id,

                        // Pengumuman, bukan peringatan — tempatnya bersama
                        // ringkasan harian, bukan di antara hal yang menuntut
                        // tindakan.
                        'telegram_topic' => 'pengumuman',
                    ])
                    ->send();
            } catch (\Throwable $e) {
                Log::warning("[core] siaran changelog ke '{$channel}' gagal: ".$e->getMessage());
            }
        }
    }

    protected function judul(ChangelogEntry $entry): string
    {
        $penanda = $entry->is_major ? '🚀' : '📣';

        return $penanda.' '.$entry->title;
    }

    /**
     * Isi apa adanya — catatan update memang sudah ditulis untuk dibaca staf.
     *
     * Tidak ada yang perlu diringkas di sini, berbeda dari peringatan: ini
     * dibaca sekali dengan tenang, bukan dipindai saat sedang terjadi sesuatu.
     */
    protected function isi(ChangelogEntry $entry): string
    {
        $baris = [$entry->body];

        if ($entry->version_tag) {
            $baris[] = '';
            $baris[] = 'Versi: '.$entry->version_tag;
        }

        $baris[] = '';
        $baris[] = rtrim((string) config('app.url'), '/').'/changelog';

        return implode("\n", $baris);
    }

    protected function tujuanGrup(string $channel): ?string
    {
        $tujuan = config("nawasara.changelog.broadcast_recipients.{$channel}");

        if (! $tujuan && class_exists(Vault::class)) {
            try {
                $tujuan = Vault::get($channel, 'chat_id');
            } catch (\Throwable) {
                $tujuan = null;
            }
        }

        return ($tujuan !== null && $tujuan !== '') ? (string) $tujuan : null;
    }
}
