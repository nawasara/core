<?php

namespace Nawasara\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A user-facing "What's New" entry. Published entries appear on the Riwayat
 * Update page for all logged-in users; drafts (published_at = null) are only
 * visible to admins in the manage screen.
 */
class ChangelogEntry extends Model
{
    protected $table = 'nawasara_changelog_entries';

    protected $fillable = [
        'title', 'body', 'category', 'is_major', 'version_tag',
        'published_at', 'created_by',
    ];

    protected $casts = [
        'is_major'     => 'boolean',
        'published_at' => 'datetime',
    ];

    public const CATEGORY_FEATURE     = 'feature';
    public const CATEGORY_IMPROVEMENT = 'improvement';
    public const CATEGORY_FIX         = 'fix';
    public const CATEGORY_SECURITY    = 'security';

    /** @return array<string,string> */
    public static function categoryLabels(): array
    {
        return [
            self::CATEGORY_FEATURE     => 'Fitur Baru',
            self::CATEGORY_IMPROVEMENT => 'Peningkatan',
            self::CATEGORY_FIX         => 'Perbaikan',
            self::CATEGORY_SECURITY    => 'Keamanan',
        ];
    }

    public function categoryLabel(): string
    {
        return self::categoryLabels()[$this->category] ?? ucfirst($this->category);
    }

    public function categoryColor(): string
    {
        return match ($this->category) {
            self::CATEGORY_FEATURE     => 'primary',
            self::CATEGORY_IMPROVEMENT => 'info',
            self::CATEGORY_SECURITY    => 'danger',
            self::CATEGORY_FIX         => 'neutral',
            default                    => 'neutral',
        };
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->lte(now());
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    /**
     * Count of published entries a user hasn't seen yet (drives the topbar
     * badge). "Seen" = published after the user's last_seen marker.
     */
    public static function unreadCountFor(int $userId): int
    {
        $lastSeen = ChangelogRead::where('user_id', $userId)->value('last_seen_at');

        return self::published()
            ->when($lastSeen, fn ($q) => $q->where('published_at', '>', $lastSeen))
            ->count();
    }

    /** Stamp that this user has now seen everything up to now. */
    public static function markSeen(int $userId): void
    {
        ChangelogRead::updateOrCreate(
            ['user_id' => $userId],
            ['last_seen_at' => now()],
        );
    }
}
