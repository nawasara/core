<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracking table for Excel-driven email-link import batches.
 *
 * One row per upload. Status moves: queued → processing → completed
 * (or failed if the worker itself crashed). Per-row results are kept in
 * `errors_json` (only the rows that did NOT cleanly succeed — successful
 * rows are noise at audit time).
 *
 * Retention: keep all rows for now; add archival job later if the table
 * grows beyond a few thousand entries.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nawasara_email_link_imports', function (Blueprint $table) {
            $table->id();

            // Who uploaded
            $table->foreignId('user_id')->comment('Admin who initiated this import');

            // Source file metadata — kept for audit traceability, not for re-processing.
            $table->string('original_filename', 255);
            $table->unsignedInteger('file_size_bytes');
            $table->string('storage_path', 500)->nullable()
                ->comment('Where the uploaded file lives on the storage disk; nullable so we can prune file but keep audit row');

            // Lifecycle
            $table->string('status', 32)->default('queued')
                ->comment('queued | processing | completed | failed');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Counts (denormalized so listing the table is fast — no joins)
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0)
                ->comment('Rows that hit a defined skip condition (e.g. keycloak user not found)');
            $table->unsignedInteger('error_count')->default(0)
                ->comment('Rows that hit an unexpected error (e.g. network, malformed row)');

            // Per-row details for the failed/skipped subset — successful rows omitted.
            // Shape: [{row: 5, username: "12345", reason: "keycloak_user_not_found", message: "..."}, ...]
            $table->json('errors_json')->nullable();

            // Worker error message + stack trace if the job itself crashed (status=failed).
            $table->text('worker_error')->nullable();

            $table->timestamps();

            // Lookups
            $table->index('user_id');
            $table->index('status');
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nawasara_email_link_imports');
    }
};
