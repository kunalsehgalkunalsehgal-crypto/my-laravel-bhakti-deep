<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_refunds', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('payment_attempt_id')->nullable()->constrained('payment_attempts')->nullOnDelete();
            $table->foreignId('donation_id')->nullable()->constrained('donations')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('requested_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('processed_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('INR');
            $table->string('reason')->nullable();
            $table->string('status')->default('requested')->index();
            $table->string('gateway_refund_id')->nullable()->index();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_refunds');
    }
};
