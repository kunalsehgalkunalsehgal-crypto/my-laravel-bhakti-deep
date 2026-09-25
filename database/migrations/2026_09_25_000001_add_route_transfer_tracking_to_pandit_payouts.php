<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pandit_payouts', function (Blueprint $table) {
            $table->decimal('service_amount', 12, 2)->default(0)->after('payout_type');
            $table->decimal('commission_percent', 8, 4)->default(0)->after('platform_amount');
            $table->string('razorpay_transfer_id')->nullable()->unique()->after('provider_payout_id');
            $table->string('gateway_status')->nullable()->after('razorpay_transfer_id');
            $table->unsignedInteger('transfer_attempts')->default(0)->after('gateway_status');
            $table->uuid('processing_token')->nullable()->after('transfer_attempts');
            $table->timestamp('processing_started_at')->nullable()->after('processing_token');
            $table->text('last_error')->nullable()->after('processing_started_at');
        });

        DB::table('pandit_payouts')->orderBy('id')->eachById(function ($payout) {
            $serviceAmount = max(round((float) $payout->booking_amount - (float) $payout->dakshina_amount, 2), 0);
            $commissionPercent = $serviceAmount > 0
                ? round(((float) $payout->platform_amount / $serviceAmount) * 100, 4)
                : 0;

            DB::table('pandit_payouts')->where('id', $payout->id)->update([
                'service_amount' => $serviceAmount,
                'commission_percent' => $commissionPercent,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('pandit_payouts', function (Blueprint $table) {
            $table->dropUnique(['razorpay_transfer_id']);
            $table->dropColumn([
                'service_amount',
                'commission_percent',
                'razorpay_transfer_id',
                'gateway_status',
                'transfer_attempts',
                'processing_token',
                'processing_started_at',
                'last_error',
            ]);
        });
    }
};
