<?php

namespace Nawasara\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit log untuk webmail session launch — issued / failed / rejected.
 *
 * Catatan: ini bukan sync_jobs tracker (yang scope-nya state mutation
 * cross-service). Webmail launch read-only dari user perspective + tidak
 * touch DB Nawasara, jadi taruh di tabel terpisah lebih clean.
 */
class WebmailSession extends Model
{
    public const STATUS_ISSUED = 'issued';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REJECTED = 'rejected';

    protected $table = 'nawasara_webmail_sessions';

    protected $fillable = [
        'user_id',
        'email_account',
        'match_strategy',
        'ip',
        'user_agent',
        'status',
        'error',
    ];

    public function user(): BelongsTo
    {
        $userModel = config('auth.providers.users.model');
        return $this->belongsTo($userModel, 'user_id');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeIssued(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ISSUED);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_FAILED, self::STATUS_REJECTED]);
    }
}
