<?php

namespace Nawasara\Core\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Dashboard hero stats aggregator.
 *
 * Reads from optional Nawasara packages (registry, whm, sync, cloudflare).
 * Tiap stat permission-gated dan defensive — kalau package tidak terinstall,
 * stat-nya simply tidak muncul (bukan crash).
 *
 * Caching: 5 menit per stat. Bukan untuk performance saja — supaya dashboard
 * tidak hammering DB tiap navigate. Trend (delta vs period sebelumnya) butuh
 * 2 query, jadi total bisa naik kalau full refresh tiap request.
 *
 * Pattern stat:
 *   - kalau package class tidak ada → return null (skip dari display)
 *   - kalau user tidak punya permission → return null
 *   - kalau ada → return ['value' => N, 'trend' => ['direction' => up|down|flat, 'value' => '12%'], ...]
 */
class DashboardStatsService
{
    public const CACHE_TTL_SECONDS = 300; // 5 menit

    public function __construct(protected User $user)
    {
    }

    public static function for(User $user): self
    {
        return new self($user);
    }

    /**
     * Return all stats yang user boleh lihat. Order matters — yang paling
     * relevant duluan (operational > admin overview).
     *
     * @return array<int, array{key:string, label:string, value:int|string, icon:string, color:string, trend?:array, description?:string}>
     */
    public function all(): array
    {
        return array_values(array_filter([
            $this->dnsHealth(),
            $this->syncSuccessRate(),
            $this->totalMailboxes(),
            $this->totalOpd(),
        ]));
    }

    /**
     * DNS health % — operational. Hitung dari EndpointHealth state 'ok' /
     * total. Kalau belum ada data sama sekali, skip (bukan tampil 0%).
     */
    protected function dnsHealth(): ?array
    {
        if (! $this->user->can('cloudflare.health.view')) {
            return null;
        }

        if (! class_exists(\Nawasara\Cloudflare\Models\EndpointHealth::class)) {
            return null;
        }

        return Cache::remember('dashboard.stats.dns_health', self::CACHE_TTL_SECONDS, function () {
            $total = \Nawasara\Cloudflare\Models\EndpointHealth::query()->count();
            if ($total === 0) {
                return null;
            }

            $ok = \Nawasara\Cloudflare\Models\EndpointHealth::query()->where('state', 'ok')->count();
            $pct = (int) round($ok / $total * 100);

            return [
                'key' => 'dns_health',
                'label' => 'DNS Health',
                'value' => $pct.'%',
                'icon' => 'lucide-globe',
                'color' => $pct >= 95 ? 'success' : ($pct >= 80 ? 'warning' : 'danger'),
                'description' => "{$ok} dari {$total} endpoint sehat",
            ];
        });
    }

    /**
     * Sync success rate 24h — operational. Penting untuk operator tahu
     * "kemarin malam queue jalan lancar atau ada masalah?".
     */
    protected function syncSuccessRate(): ?array
    {
        if (! $this->user->can('sync.job.view')) {
            return null;
        }

        if (! class_exists(\Nawasara\Sync\Models\SyncJob::class)) {
            return null;
        }

        return Cache::remember('dashboard.stats.sync_success_24h', self::CACHE_TTL_SECONDS, function () {
            $since = Carbon::now()->subDay();
            $total = \Nawasara\Sync\Models\SyncJob::query()
                ->where('created_at', '>=', $since)
                ->whereIn('status', ['success', 'failed', 'conflict'])
                ->count();

            if ($total === 0) {
                return null;
            }

            $success = \Nawasara\Sync\Models\SyncJob::query()
                ->where('created_at', '>=', $since)
                ->where('status', 'success')
                ->count();

            $pct = (int) round($success / $total * 100);

            // Trend: bandingkan dengan 24h sebelumnya (24-48h ago)
            $prevSince = Carbon::now()->subDays(2);
            $prevUntil = Carbon::now()->subDay();
            $prevTotal = \Nawasara\Sync\Models\SyncJob::query()
                ->whereBetween('created_at', [$prevSince, $prevUntil])
                ->whereIn('status', ['success', 'failed', 'conflict'])
                ->count();

            $trend = null;
            if ($prevTotal > 0) {
                $prevSuccess = \Nawasara\Sync\Models\SyncJob::query()
                    ->whereBetween('created_at', [$prevSince, $prevUntil])
                    ->where('status', 'success')
                    ->count();
                $prevPct = (int) round($prevSuccess / $prevTotal * 100);
                $delta = $pct - $prevPct;
                if ($delta !== 0) {
                    $trend = [
                        'direction' => $delta > 0 ? 'up' : 'down',
                        'value' => abs($delta).' pp',
                    ];
                }
            }

            return [
                'key' => 'sync_success',
                'label' => 'Sync Success (24j)',
                'value' => $pct.'%',
                'icon' => 'lucide-refresh-cw',
                'color' => $pct >= 95 ? 'success' : ($pct >= 80 ? 'warning' : 'danger'),
                'trend' => $trend,
                'description' => "{$success} sukses dari {$total} job",
            ];
        });
    }

    /**
     * Total active mailbox — admin overview. Filter suspended supaya angka
     * mencerminkan "real users", bukan total registered.
     */
    protected function totalMailboxes(): ?array
    {
        if (! $this->user->can('whm.email.view')) {
            return null;
        }

        if (! class_exists(\Nawasara\Whm\Models\WhmEmailAccount::class)) {
            return null;
        }

        return Cache::remember('dashboard.stats.mailboxes', self::CACHE_TTL_SECONDS, function () {
            // Schema WhmEmailAccount cuma punya 2 suspension flag: suspended_login
            // (block webmail/IMAP) + suspended_incoming (block SMTP receive).
            // "Active" = tidak ke-suspend di salah satu mode.
            $active = \Nawasara\Whm\Models\WhmEmailAccount::query()
                ->where('suspended_login', false)
                ->where('suspended_incoming', false)
                ->count();

            $total = \Nawasara\Whm\Models\WhmEmailAccount::query()->count();
            $suspended = $total - $active;

            return [
                'key' => 'mailboxes',
                'label' => 'Mailbox Aktif',
                'value' => number_format($active, 0, ',', '.'),
                'icon' => 'lucide-mail',
                'color' => 'primary',
                'description' => $suspended > 0 ? "+{$suspended} suspended" : "Total {$total} mailbox",
            ];
        });
    }

    /**
     * Total OPD — admin overview. Stable count, tapi tetap di-cache supaya
     * tidak query tiap dashboard load.
     */
    protected function totalOpd(): ?array
    {
        if (! $this->user->can('registry.opd.view')) {
            return null;
        }

        if (! class_exists(\Nawasara\Registry\Models\Opd::class)) {
            return null;
        }

        return Cache::remember('dashboard.stats.opd', self::CACHE_TTL_SECONDS, function () {
            $total = \Nawasara\Registry\Models\Opd::query()->count();
            $withPic = \Nawasara\Registry\Models\Opd::query()
                ->whereHas('pics', fn ($q) => $q->where('is_primary', true))
                ->count();

            $unassigned = $total - $withPic;

            return [
                'key' => 'opd',
                'label' => 'Total OPD',
                'value' => number_format($total, 0, ',', '.'),
                'icon' => 'lucide-building-2',
                'color' => 'info',
                'description' => $unassigned > 0
                    ? "{$unassigned} belum punya PIC"
                    : "Semua OPD sudah punya PIC",
            ];
        });
    }

    /**
     * Manual cache flush — dipanggil saat user klik "Refresh" dashboard.
     */
    public function flushCache(): void
    {
        Cache::forget('dashboard.stats.dns_health');
        Cache::forget('dashboard.stats.sync_success_24h');
        Cache::forget('dashboard.stats.mailboxes');
        Cache::forget('dashboard.stats.opd');
    }
}
