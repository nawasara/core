<?php

namespace Nawasara\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Nawasara\Core\Models\EmailLinkImport;
use Nawasara\Core\Services\EmailLinkImportService;
use Throwable;

/**
 * Async worker for one Excel import batch.
 *
 * Dispatched by the upload Livewire action; processed by the
 * `nawasara-dev-worker` container (queue:work on the database driver).
 *
 * Concurrency: at most one job per import row at a time (we re-read status
 * before processing; if not 'queued', someone else got it). For multiple
 * concurrent uploads (different files), each gets its own EmailLinkImport
 * row + its own job, so they parallelise naturally.
 */
class ProcessEmailLinkImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Try 3 times. Most failures here are transient Keycloak hiccups
     * (network blip, JWT expiry), worth retrying. The Service itself
     * tolerates per-row Keycloak failures (skips the row, sets reason),
     * so a retry would only re-process the SAME spreadsheet — that's
     * what we want for transient outages.
     */
    public int $tries = 3;

    /**
     * 10-minute timeout. For a 5000-row Excel with a slow Keycloak,
     * 1 row ≈ 100ms × 5000 = ~8 min. Generous headroom.
     */
    public int $timeout = 600;

    public function __construct(public readonly int $importId)
    {
    }

    public function handle(EmailLinkImportService $service): void
    {
        $import = EmailLinkImport::find($this->importId);

        if (! $import) {
            Log::warning('[email-link-import] import row missing on dispatch', [
                'import_id' => $this->importId,
            ]);
            return;
        }

        if ($import->status !== EmailLinkImport::STATUS_QUEUED) {
            Log::info('[email-link-import] import already in non-queued state, skipping', [
                'import_id' => $this->importId,
                'status' => $import->status,
            ]);
            return;
        }

        $service->process($import);
    }

    public function failed(Throwable $e): void
    {
        $import = EmailLinkImport::find($this->importId);
        if (! $import) {
            return;
        }

        $import->update([
            'status' => EmailLinkImport::STATUS_FAILED,
            'worker_error' => 'Job exhausted after '.$this->tries.' tries: '.$e->getMessage(),
            'completed_at' => now(),
        ]);

        Log::error('[email-link-import] job failed after retries', [
            'import_id' => $this->importId,
            'error' => $e->getMessage(),
        ]);
    }
}
