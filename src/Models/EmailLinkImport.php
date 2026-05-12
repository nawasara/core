<?php

namespace Nawasara\Core\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Represents one Excel upload batch for email-link import.
 *
 * Status lifecycle:
 *   queued      — row inserted by the upload UI; worker hasn't picked it up
 *   processing  — worker started; counts are partial
 *   completed   — worker finished without crashing; counts are final
 *   failed      — worker crashed mid-batch; `worker_error` populated
 *
 * `completed` does NOT mean every row succeeded — check
 * `error_count + skipped_count` for partial-success batches.
 */
class EmailLinkImport extends Model
{
    use LogsActivity;

    public const STATUS_QUEUED = 'queued';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $table = 'nawasara_email_link_imports';

    protected $fillable = [
        'user_id',
        'original_filename',
        'file_size_bytes',
        'storage_path',
        'status',
        'started_at',
        'completed_at',
        'total_rows',
        'success_count',
        'skipped_count',
        'error_count',
        'errors_json',
        'worker_error',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'errors_json' => 'array',
        'total_rows' => 'integer',
        'success_count' => 'integer',
        'skipped_count' => 'integer',
        'error_count' => 'integer',
        'file_size_bytes' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        // Status transitions matter for audit; raw counts don't (those are
        // visible in the model itself anyway).
        return LogOptions::defaults()
            ->logOnly(['status', 'started_at', 'completed_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => "EmailLinkImport {$event}");
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeRecent(Builder $query, int $limit = 10): Builder
    {
        return $query->orderByDesc('created_at')->limit($limit);
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_FAILED], true);
    }

    public function durationSeconds(): ?int
    {
        if (! $this->started_at || ! $this->completed_at) {
            return null;
        }

        return $this->completed_at->diffInSeconds($this->started_at);
    }
}
