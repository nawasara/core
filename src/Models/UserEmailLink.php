<?php

namespace Nawasara\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Mapping user app Nawasara ↔ mailbox @ponorogo.go.id.
 *
 * Source priority saat resolve:
 *   1. manual          (admin override, selalu menang)
 *   2. sso_attribute   (auto-cache dari Keycloak claim kominfo_email)
 *
 * Multi-row per user diizinkan (1 user bisa punya >1 mailbox), tapi
 * resolver akan reject dengan status `ambiguous` kalau user punya >1
 * link tanpa salah satunya `manual`. Admin harus pilih primary lewat
 * Setting UI.
 */
class UserEmailLink extends Model
{
    use LogsActivity;

    public const SOURCE_SSO_ATTRIBUTE = 'sso_attribute';
    public const SOURCE_MANUAL = 'manual';

    protected $table = 'nawasara_user_email_links';

    protected $fillable = [
        'user_id',
        'email_account',
        'source',
        'linked_at',
        'last_used_at',
    ];

    protected $casts = [
        'linked_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    /**
     * Activity log: capture mapping changes. `last_used_at` is excluded
     * because that gets bumped on every webmail launch — would flood
     * the audit log with low-signal events.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['user_id', 'email_account', 'source', 'linked_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => "EmailLink {$event}");
    }

    public function user(): BelongsTo
    {
        $userModel = config('auth.providers.users.model');
        return $this->belongsTo($userModel, 'user_id');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeManual(Builder $query): Builder
    {
        return $query->where('source', self::SOURCE_MANUAL);
    }

    public function scopeFromSso(Builder $query): Builder
    {
        return $query->where('source', self::SOURCE_SSO_ATTRIBUTE);
    }

    public function isManual(): bool
    {
        return $this->source === self::SOURCE_MANUAL;
    }

    public function touchUsage(): void
    {
        $this->forceFill(['last_used_at' => now()])->save();
    }
}
