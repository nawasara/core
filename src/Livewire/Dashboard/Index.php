<?php

namespace Nawasara\Core\Livewire\Dashboard;

use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Nawasara\Core\Services\DashboardStatsService;
use Nawasara\Ui\Services\WorkspaceManager;

/**
 * Home / dashboard landing page.
 *
 * Bukan livewire untuk reaktivitas data — data sudah di-cache 5 menit di
 * service layer. Livewire dipakai untuk:
 *   - Refresh button: flush cache + re-render
 *   - Cached "last updated at" timestamp untuk transparency
 *   - Future: real-time activity feed kalau scope berkembang
 */
class Index extends Component
{
    /**
     * Timestamp render terakhir (server-time). Refresh action akan reset.
     */
    public ?string $lastUpdatedAt = null;

    public function mount(): void
    {
        $this->lastUpdatedAt = Carbon::now()->toIso8601String();
    }

    /**
     * Stats untuk user current. Permission gating + class_exists guard di
     * service. Computed property supaya re-evaluate kalau dependency change
     * (e.g. setelah refresh).
     */
    #[Computed]
    public function stats(): array
    {
        return DashboardStatsService::for(auth()->user())->all();
    }

    #[Computed]
    public function workspaces(): array
    {
        /** @var WorkspaceManager $ws */
        $ws = app('nawasara.workspaces');
        return $ws->accessible();
    }

    /**
     * Greeting frasa berdasarkan jam sekarang. Kecil tapi penting untuk
     * "feel" yang tidak repetitive — beda jam = beda sapaan.
     */
    #[Computed]
    public function greeting(): string
    {
        $hour = (int) Carbon::now()->format('G');
        return match (true) {
            $hour < 11 => 'Selamat pagi',
            $hour < 15 => 'Selamat siang',
            $hour < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };
    }

    public function refresh(): void
    {
        DashboardStatsService::for(auth()->user())->flushCache();
        unset($this->stats); // force re-compute
        $this->lastUpdatedAt = Carbon::now()->toIso8601String();
        $this->dispatch('toast', message: 'Data dashboard di-refresh', type: 'success');
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.dashboard.index')
            ->layout('nawasara-ui::components.layouts.app');
    }
}
