<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // User-facing "What's New" entries. Written by admins in plain,
        // benefit-focused language — not git changelog.
        Schema::create('nawasara_changelog_entries', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');                              // markdown-ish plain text
            $table->string('category', 32)->default('feature'); // feature | improvement | fix | security
            $table->boolean('is_major')->default(false);        // highlight big updates
            $table->string('version_tag', 32)->nullable();      // optional ref e.g. "secscan v0.9.0"
            $table->timestamp('published_at')->nullable();      // null = draft, not shown to users
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['published_at']);
        });

        // Per-user "last seen" marker so the topbar badge can count unread
        // entries. Separate table (not a users column) to keep this within the
        // package and avoid touching the host app's users table.
        Schema::create('nawasara_changelog_reads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('last_seen_at');
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nawasara_changelog_reads');
        Schema::dropIfExists('nawasara_changelog_entries');
    }
};
