<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_meeting_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_meeting_id')->nullable()->constrained('video_meetings')->nullOnDelete();
            $table->string('session_type');
            $table->unsignedBigInteger('session_id');
            $table->string('participant_type')->default('unknown')->index();
            $table->unsignedBigInteger('participant_id')->nullable();
            $table->string('zoom_participant_id')->nullable()->index();
            $table->string('provider')->default('zoom')->index();
            $table->string('event_type')->index();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->string('provider_event_id')->nullable()->unique();
            $table->json('provider_metadata')->nullable();
            $table->timestamps();

            $table->index(['session_type', 'session_id'], 'meeting_attendance_session_index');
            $table->index(['participant_type', 'participant_id'], 'meeting_attendance_participant_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_meeting_attendances');
    }
};
