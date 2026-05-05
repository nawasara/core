<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mapping table: user app Nawasara ↔ mailbox @ponorogo.go.id.
 *
 * Source semantics:
 *   - sso_attribute : kominfo_email claim dari Keycloak ID token (auto-cache)
 *   - manual        : admin override lewat Setting UI (priority tertinggi)
 *
 * Manual link selalu menang atas sso_attribute — biar admin punya escape
 * hatch kalau attribute Keycloak salah/kosong dan butuh quick fix.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nawasara_user_email_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('email_account', 255);
            $table->enum('source', ['sso_attribute', 'manual']);
            $table->timestamp('linked_at')->useCurrent();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->unique(['user_id', 'email_account']);
            $table->index('email_account');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nawasara_user_email_links');
    }
};
