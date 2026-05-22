<?php

namespace Nawasara\Core\Livewire\Sudo;

use Livewire\Component;
use Nawasara\Core\Attributes\RequiresSudo;
use Nawasara\Core\Traits\WithSudo;

/**
 * Sudo mode diagnostic page.
 *
 * A live, low-risk page for exercising every sudo gating path — useful
 * when verifying the Keycloak step-up after a config change, or as a
 * reference for how to wire #[RequiresSudo] into real components.
 *
 * Reached two ways:
 *   - GET /sudo/demo            — plain page, shows current window status.
 *   - GET /sudo/demo/protected — route behind the `sudo` middleware;
 *                                bounces through the OTP step-up first.
 *
 * On the page itself, two buttons demonstrate the Livewire-side gates:
 *   - "Aksi declarative" → method annotated #[RequiresSudo]
 *   - "Aksi imperative"  → method calling $this->requireSudo()
 *
 * The actions do nothing destructive — they only set a result string —
 * so the page is safe to leave routable in any environment.
 */
class Demo extends Component
{
    use WithSudo;

    /** Log line shown after an action runs, to prove it executed. */
    public ?string $lastResult = null;

    /**
     * Declaratively gated action. The #[RequiresSudo] attribute aborts
     * this method and triggers the step-up when sudo is missing; the body
     * runs only inside an active window.
     */
    #[RequiresSudo(reason: 'menjalankan aksi demo declarative')]
    public function runDeclarative(): void
    {
        $this->lastResult = 'Aksi DECLARATIVE berhasil dijalankan pada '
            .now()->format('H:i:s').' — sudo window valid.';
    }

    /**
     * Imperatively gated action. requireSudo() returns false (and starts
     * the redirect) when sudo is missing — the method must return on false.
     */
    public function runImperative(): void
    {
        if (! $this->requireSudo('menjalankan aksi demo imperative')) {
            return;
        }

        $this->lastResult = 'Aksi IMPERATIVE berhasil dijalankan pada '
            .now()->format('H:i:s').' — sudo window valid.';
    }

    public function render()
    {
        return view('nawasara-core::livewire.pages.sudo.demo')
            ->layout('nawasara-ui::components.layouts.app');
    }
}
