<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diya_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('diya_sessions', 'start_at')) {
                $table->timestamp('start_at')->nullable()->after('booking_date')->index();
            }

            if (!Schema::hasColumn('diya_sessions', 'end_at')) {
                $table->timestamp('end_at')->nullable()->after('start_at')->index();
            }
        });

        DB::table('diya_sessions')
            ->whereNull('start_at')
            ->update(['start_at' => DB::raw('created_at')]);

        if (Schema::hasColumn('diya_sessions', 'expires_at')) {
            DB::table('diya_sessions')
                ->whereNull('end_at')
                ->whereNotNull('expires_at')
                ->update(['end_at' => DB::raw('expires_at')]);
        }

        $now = now();

        DB::table('diya_sessions')
            ->where('payment_status', 'paid')
            ->whereNotNull('start_at')
            ->where('start_at', '>', $now)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->update(['status' => 'scheduled', 'updated_at' => $now]);

        DB::table('diya_sessions')
            ->where('payment_status', 'paid')
            ->whereNotNull('start_at')
            ->where('start_at', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('end_at')->orWhere('end_at', '>', $now);
            })
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->update(['status' => 'active', 'updated_at' => $now]);

        DB::table('diya_sessions')
            ->where('payment_status', 'paid')
            ->whereNotNull('end_at')
            ->where('end_at', '<=', $now)
            ->where('status', '!=', 'completed')
            ->update(['status' => 'completed', 'completed_at' => $now, 'updated_at' => $now]);
    }

    public function down(): void
    {
        Schema::table('diya_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('diya_sessions', 'end_at')) {
                $table->dropColumn('end_at');
            }

            if (Schema::hasColumn('diya_sessions', 'start_at')) {
                $table->dropColumn('start_at');
            }
        });
    }
};
