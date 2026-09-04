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
            if (!Schema::hasColumn('pandit_payouts', 'booking_amount')) {
                $table->decimal('booking_amount', 12, 2)->default(0)->after('payout_type');
            }

            if (!Schema::hasColumn('pandit_payouts', 'pandit_amount')) {
                $table->decimal('pandit_amount', 12, 2)->default(0)->after('booking_amount');
            }

            if (!Schema::hasColumn('pandit_payouts', 'platform_amount')) {
                $table->decimal('platform_amount', 12, 2)->default(0)->after('pandit_amount');
            }

            if (!Schema::hasColumn('pandit_payouts', 'provider_payout_id')) {
                $table->string('provider_payout_id')->nullable()->after('payout_reference');
                $table->index('provider_payout_id', 'pandit_payouts_provider_payout_id_index');
            }

            if (!Schema::hasColumn('pandit_payouts', 'eligible_at')) {
                $table->timestamp('eligible_at')->nullable()->after('approved_at');
                $table->index('eligible_at', 'pandit_payouts_eligible_at_index');
            }

            if (!Schema::hasColumn('pandit_payouts', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('failed_at');
            }
        });

        DB::table('pandit_payouts')->where('status', 'pending')->update(['status' => 'hold']);
        DB::table('pandit_payouts')->where('status', 'on_hold')->update(['status' => 'hold']);
        DB::table('pandit_payouts')->where('status', 'approved')->update(['status' => 'ready']);

        DB::table('pandit_payouts')
            ->where('booking_amount', 0)
            ->update([
                'booking_amount' => DB::raw('gross_amount'),
                'pandit_amount' => DB::raw('payout_amount'),
                'platform_amount' => DB::raw('platform_fee'),
            ]);

        Schema::table('pandit_payouts', function (Blueprint $table) {
            $table->unique(['payment_attempt_id', 'payout_type'], 'pandit_payouts_attempt_type_unique');
        });
    }

    public function down(): void
    {
        Schema::table('pandit_payouts', function (Blueprint $table) {
            $table->dropUnique('pandit_payouts_attempt_type_unique');
            $table->dropIndex('pandit_payouts_provider_payout_id_index');
            $table->dropIndex('pandit_payouts_eligible_at_index');
        });

        Schema::table('pandit_payouts', function (Blueprint $table) {
            if (Schema::hasColumn('pandit_payouts', 'provider_payout_id')) {
                $table->dropColumn('provider_payout_id');
            }

            foreach (['cancelled_at', 'eligible_at', 'platform_amount', 'pandit_amount', 'booking_amount'] as $column) {
                if (Schema::hasColumn('pandit_payouts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
