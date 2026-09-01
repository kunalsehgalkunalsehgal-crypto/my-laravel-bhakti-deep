<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_completion_proofs', function (Blueprint $table) {
            $table->id();
            $table->string('session_type');
            $table->unsignedBigInteger('session_id');
            $table->foreignId('pandit_id')->nullable()->constrained('pandits')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('proof_type')->default('note')->index();
            $table->string('file_path')->nullable();
            $table->text('external_url')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('pending')->index();
            $table->foreignId('reviewed_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['session_type', 'session_id'], 'completion_proofs_session_index');
        });

        Schema::create('booking_user_confirmations', function (Blueprint $table) {
            $table->id();
            $table->string('session_type');
            $table->unsignedBigInteger('session_id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending')->index();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('disputed_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['session_type', 'session_id'], 'booking_confirmations_session_index');
        });

        Schema::create('pandit_no_show_reports', function (Blueprint $table) {
            $table->id();
            $table->string('session_type');
            $table->unsignedBigInteger('session_id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pandit_id')->nullable()->constrained('pandits')->nullOnDelete();
            $table->string('reported_by_type')->nullable();
            $table->unsignedBigInteger('reported_by_id')->nullable();
            $table->string('status')->default('open')->index();
            $table->text('reason')->nullable();
            $table->text('resolution')->nullable();
            $table->foreignId('resolved_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('reported_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['session_type', 'session_id'], 'no_show_reports_session_index');
            $table->index(['reported_by_type', 'reported_by_id'], 'no_show_reports_reporter_index');
        });

        Schema::create('offline_arrival_otps', function (Blueprint $table) {
            $table->id();
            $table->string('session_type');
            $table->unsignedBigInteger('session_id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pandit_id')->nullable()->constrained('pandits')->nullOnDelete();
            $table->string('otp_hash');
            $table->string('otp_hint', 12)->nullable();
            $table->string('status')->default('pending')->index();
            $table->unsignedSmallInteger('verification_attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['session_type', 'session_id'], 'offline_arrival_otps_session_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offline_arrival_otps');
        Schema::dropIfExists('pandit_no_show_reports');
        Schema::dropIfExists('booking_user_confirmations');
        Schema::dropIfExists('session_completion_proofs');
    }
};
