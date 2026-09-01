<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            if (!Schema::hasColumn('donations', 'payment_purpose')) {
                $table->string('payment_purpose')->default('donation')->after('service_id')->index();
            }

            if (!Schema::hasColumn('donations', 'session_type')) {
                $table->string('session_type')->nullable()->after('payment_purpose');
            }

            if (!Schema::hasColumn('donations', 'session_id')) {
                $table->unsignedBigInteger('session_id')->nullable()->after('session_type');
            }

            if (!Schema::hasColumn('donations', 'latest_payment_attempt_id')) {
                $table->foreignId('latest_payment_attempt_id')
                    ->nullable()
                    ->after('payment_status')
                    ->constrained('payment_attempts')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('donations', 'refunded_amount')) {
                $table->decimal('refunded_amount', 12, 2)->default(0)->after('amount');
            }

            if (!Schema::hasColumn('donations', 'disputed_amount')) {
                $table->decimal('disputed_amount', 12, 2)->default(0)->after('refunded_amount');
            }

            $table->index(['session_type', 'session_id'], 'donations_session_index');
        });

        Schema::table('payment_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_logs', 'payment_attempt_id')) {
                $table->foreignId('payment_attempt_id')
                    ->nullable()
                    ->after('donation_id')
                    ->constrained('payment_attempts')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('payment_logs', 'loggable_type')) {
                $table->string('loggable_type')->nullable()->after('payment_attempt_id');
            }

            if (!Schema::hasColumn('payment_logs', 'loggable_id')) {
                $table->unsignedBigInteger('loggable_id')->nullable()->after('loggable_type');
            }

            if (!Schema::hasColumn('payment_logs', 'event_type')) {
                $table->string('event_type')->nullable()->after('gateway')->index();
            }

            if (!Schema::hasColumn('payment_logs', 'occurred_at')) {
                $table->timestamp('occurred_at')->nullable()->after('status')->index();
            }

            $table->index(['loggable_type', 'loggable_id'], 'payment_logs_loggable_index');
        });

        foreach (['hawan_sessions', 'pooja_sessions', 'diya_sessions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'payment_hold_started_at')) {
                    $table->timestamp('payment_hold_started_at')->nullable()->after('payment_status');
                }

                if (!Schema::hasColumn($tableName, 'payment_hold_expires_at')) {
                    $table->timestamp('payment_hold_expires_at')->nullable()->after('payment_hold_started_at')->index();
                }

                if (!Schema::hasColumn($tableName, 'latest_payment_attempt_id')) {
                    $table->foreignId('latest_payment_attempt_id')
                        ->nullable()
                        ->after('payment_hold_expires_at')
                        ->constrained('payment_attempts')
                        ->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['hawan_sessions', 'pooja_sessions', 'diya_sessions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'latest_payment_attempt_id')) {
                    $table->dropConstrainedForeignId('latest_payment_attempt_id');
                }

                foreach (['payment_hold_expires_at', 'payment_hold_started_at'] as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::table('payment_logs', function (Blueprint $table) {
            if (Schema::hasColumn('payment_logs', 'payment_attempt_id')) {
                $table->dropConstrainedForeignId('payment_attempt_id');
            }

            if (Schema::hasColumn('payment_logs', 'loggable_type') && Schema::hasColumn('payment_logs', 'loggable_id')) {
                $table->dropIndex('payment_logs_loggable_index');
            }

            foreach (['occurred_at', 'event_type', 'loggable_id', 'loggable_type'] as $column) {
                if (Schema::hasColumn('payment_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('donations', function (Blueprint $table) {
            if (Schema::hasColumn('donations', 'latest_payment_attempt_id')) {
                $table->dropConstrainedForeignId('latest_payment_attempt_id');
            }

            if (Schema::hasColumn('donations', 'session_type') && Schema::hasColumn('donations', 'session_id')) {
                $table->dropIndex('donations_session_index');
            }

            foreach (['disputed_amount', 'refunded_amount', 'session_id', 'session_type', 'payment_purpose'] as $column) {
                if (Schema::hasColumn('donations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
