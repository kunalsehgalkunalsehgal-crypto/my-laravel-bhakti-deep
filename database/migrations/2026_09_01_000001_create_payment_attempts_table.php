<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('donation_id')->nullable()->constrained('donations')->nullOnDelete();
            $table->string('payable_type')->nullable();
            $table->unsignedBigInteger('payable_id')->nullable();
            $table->string('purpose')->default('booking')->index();
            $table->string('gateway')->nullable()->index();
            $table->string('gateway_order_id')->nullable()->index();
            $table->string('gateway_payment_id')->nullable()->index();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('INR');
            $table->string('status')->default('pending')->index();
            $table->timestamp('hold_started_at')->nullable();
            $table->timestamp('hold_expires_at')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['payable_type', 'payable_id'], 'payment_attempts_payable_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
    }
};
