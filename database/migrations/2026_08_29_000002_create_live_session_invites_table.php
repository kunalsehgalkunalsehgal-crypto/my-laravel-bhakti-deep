<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_session_invites', function (Blueprint $table) {
            $table->id();
            $table->string('session_type');
            $table->unsignedBigInteger('session_id');
            $table->string('name');
            $table->string('relation');
            $table->string('token_hash')->unique();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index(['session_type', 'session_id'], 'live_session_invites_session_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_session_invites');
    }
};
