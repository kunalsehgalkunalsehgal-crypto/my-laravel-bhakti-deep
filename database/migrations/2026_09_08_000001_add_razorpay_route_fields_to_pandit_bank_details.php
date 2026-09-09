<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pandit_bank_details', function (Blueprint $table) {
            $table->string('razorpay_linked_account_id')->nullable()->index()->after('verification_status');
            $table->string('razorpay_linked_account_status')->nullable()->after('razorpay_linked_account_id');
            $table->boolean('razorpay_payout_enabled')->default(false)->after('razorpay_linked_account_status');
            $table->timestamp('razorpay_verified_at')->nullable()->after('razorpay_payout_enabled');
            $table->text('razorpay_last_error')->nullable()->after('razorpay_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('pandit_bank_details', function (Blueprint $table) {
            $table->dropIndex(['razorpay_linked_account_id']);
            $table->dropColumn([
                'razorpay_linked_account_id',
                'razorpay_linked_account_status',
                'razorpay_payout_enabled',
                'razorpay_verified_at',
                'razorpay_last_error',
            ]);
        });
    }
};
