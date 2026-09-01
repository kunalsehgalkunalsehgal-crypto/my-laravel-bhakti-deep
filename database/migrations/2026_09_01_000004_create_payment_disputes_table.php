<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_disputes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('payment_attempt_id')->nullable()->constrained('payment_attempts')->nullOnDelete();
            $table->foreignId('donation_id')->nullable()->constrained('donations')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_type')->nullable();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->string('dispute_type')->default('payment')->index();
            $table->string('status')->default('open')->index();
            $table->decimal('amount_disputed', 12, 2)->nullable();
            $table->text('reason')->nullable();
            $table->text('resolution')->nullable();
            $table->foreignId('resolved_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['session_type', 'session_id'], 'payment_disputes_session_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_disputes');
    }
};
