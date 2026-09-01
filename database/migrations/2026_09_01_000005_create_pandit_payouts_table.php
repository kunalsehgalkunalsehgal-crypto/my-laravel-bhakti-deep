<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pandit_payouts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('pandit_id')->constrained('pandits')->cascadeOnDelete();
            $table->foreignId('payment_attempt_id')->nullable()->constrained('payment_attempts')->nullOnDelete();
            $table->foreignId('donation_id')->nullable()->constrained('donations')->nullOnDelete();
            $table->string('session_type')->nullable();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->string('payout_type')->default('booking')->index();
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('platform_fee', 12, 2)->default(0);
            $table->decimal('dakshina_amount', 12, 2)->default(0);
            $table->decimal('payout_amount', 12, 2);
            $table->string('currency', 3)->default('INR');
            $table->string('status')->default('pending')->index();
            $table->string('payout_reference')->nullable()->index();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['session_type', 'session_id'], 'pandit_payouts_session_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pandit_payouts');
    }
};
