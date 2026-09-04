<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_refunds', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_refunds', 'gateway_status')) {
                $table->string('gateway_status')->nullable()->after('gateway_refund_id')->index();
            }
        });

        Schema::table('disputes', function (Blueprint $table) {
            if (!Schema::hasColumn('disputes', 'resolution')) {
                $table->string('resolution')->nullable()->after('status')->index();
            }

            if (!Schema::hasColumn('disputes', 'resolved_by_admin_id')) {
                $table->foreignId('resolved_by_admin_id')
                    ->nullable()
                    ->after('resolution')
                    ->constrained('admins')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('disputes', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('resolved_by_admin_id')->index();
            }

            if (!Schema::hasColumn('disputes', 'payment_refund_id')) {
                $table->foreignId('payment_refund_id')
                    ->nullable()
                    ->after('resolved_at')
                    ->constrained('payment_refunds')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('disputes', function (Blueprint $table) {
            if (Schema::hasColumn('disputes', 'payment_refund_id')) {
                $table->dropConstrainedForeignId('payment_refund_id');
            }

            if (Schema::hasColumn('disputes', 'resolved_by_admin_id')) {
                $table->dropConstrainedForeignId('resolved_by_admin_id');
            }

            foreach (['resolved_at', 'resolution'] as $column) {
                if (Schema::hasColumn('disputes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('payment_refunds', function (Blueprint $table) {
            if (Schema::hasColumn('payment_refunds', 'gateway_status')) {
                $table->dropColumn('gateway_status');
            }
        });
    }
};
