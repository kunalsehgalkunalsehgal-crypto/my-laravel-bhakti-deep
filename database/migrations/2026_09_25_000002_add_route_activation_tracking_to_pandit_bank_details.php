<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pandit_bank_details', function (Blueprint $table) {
            $table->string('razorpay_product_id')->nullable()->after('razorpay_linked_account_id');
            $table->string('razorpay_settlement_account_id')->nullable()->after('razorpay_product_id');
            $table->string('razorpay_bank_verification_status')->nullable()->after('razorpay_linked_account_status');
            $table->uuid('razorpay_onboarding_idempotency_key')->nullable()->after('razorpay_payout_enabled');
            $table->timestamp('razorpay_synced_at')->nullable()->after('razorpay_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('pandit_bank_details', function (Blueprint $table) {
            $table->dropColumn([
                'razorpay_product_id',
                'razorpay_settlement_account_id',
                'razorpay_bank_verification_status',
                'razorpay_onboarding_idempotency_key',
                'razorpay_synced_at',
            ]);
        });
    }
};
