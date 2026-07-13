<?php

namespace Nawasara\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Per-user marker of the last time they viewed the changelog. Drives the
 * "unread updates" badge in the topbar.
 */
class ChangelogRead extends Model
{
    protected $table = 'nawasara_changelog_reads';

    protected $fillable = ['user_id', 'last_seen_at'];

    protected $casts = ['last_seen_at' => 'datetime'];
}
