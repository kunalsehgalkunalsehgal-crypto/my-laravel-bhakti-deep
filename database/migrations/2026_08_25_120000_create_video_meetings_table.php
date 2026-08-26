<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_meetings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->index();
            $table->string('external_meeting_id')->nullable()->index();
            $table->text('join_url')->nullable();
            $table->text('host_url')->nullable();
            $table->string('passcode')->nullable();
            $table->string('status')->default('scheduled')->index();
            $table->timestamp('starts_at')->nullable()->index();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->foreignId('pandit_id')->nullable()->constrained('pandits')->nullOnDelete();
            $table->string('session_type');
            $table->unsignedBigInteger('session_id');
            $table->timestamps();

            $table->unique(['session_type', 'session_id'], 'video_meetings_unique_session');
            $table->index(['session_type', 'session_id'], 'video_meetings_session_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_meetings');
    }
};
