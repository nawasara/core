<?php

namespace Nawasara\Core\Exceptions;

use Illuminate\Http\Request;
use RuntimeException;

/**
 * Thrown when a `#[RequiresSudo]` action runs without an active sudo
 * window. It aborts the action; the dispatched `sudo-required` Livewire
 * event drives the actual step-up redirect on the frontend.
 *
 * It renders as a 403 (rather than a 500 stack trace) so that any code
 * path reaching it outside Livewire — a direct API hit, a queued retry —
 * still fails cleanly and legibly.
 */
class SudoRequiredException extends RuntimeException
{
    public function __construct(public ?string $sudoReason = null)
    {
        parent::__construct(
            $sudoReason
                ? "Aksi ini butuh konfirmasi sudo: {$sudoReason}"
                : 'Aksi ini butuh konfirmasi sudo.'
        );
    }

    /**
     * Render cleanly when this bubbles up to the HTTP layer.
     */
    public function render(Request $request)
    {
        $payload = [
            'error' => 'sudo_required',
            'message' => $this->getMessage(),
            'reason' => $this->sudoReason,
        ];

        if ($request->expectsJson()) {
            return response()->json($payload, 403);
        }

        return redirect()->route('sudo.redirect', [
            'intended' => url()->previous(),
        ]);
    }
}
