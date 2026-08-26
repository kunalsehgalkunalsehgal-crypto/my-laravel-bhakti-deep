<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['hawan_sessions', 'pooja_sessions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'service_type')) {
                    $table->string('service_type')->nullable()->after('service_id')->index();
                }

                if (!Schema::hasColumn($tableName, 'ritual_id')) {
                    $table->unsignedBigInteger('ritual_id')->nullable()->after('service_type')->index();
                }

                if (!Schema::hasColumn($tableName, 'ritual_slug')) {
                    $table->string('ritual_slug')->nullable()->after('ritual_id')->index();
                }

                if (!Schema::hasColumn($tableName, 'pandit_service_id')) {
                    $table->foreignId('pandit_service_id')->nullable()->after('pandit_id')->constrained('pandit_services')->nullOnDelete();
                }

                if (!Schema::hasColumn($tableName, 'slot_start_time')) {
                    $table->time('slot_start_time')->nullable()->after('slot')->index();
                }

                if (!Schema::hasColumn($tableName, 'slot_end_time')) {
                    $table->time('slot_end_time')->nullable()->after('slot_start_time')->index();
                }

                if (!Schema::hasColumn($tableName, 'live_session_token')) {
                    $table->string('live_session_token', 96)->nullable()->after('live_session_link')->unique();
                }
            });

            DB::table($tableName)->where('status', 'paid')->update(['status' => 'confirmed']);
            DB::table($tableName)->where('status', 'failed')->update(['status' => 'cancelled']);
        }

        Schema::table('diya_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('diya_sessions', 'live_session_token')) {
                $table->string('live_session_token', 96)->nullable()->after('live_session_link')->unique();
            }
        });

        DB::table('diya_sessions')->where('status', 'paid')->update(['status' => 'confirmed']);
        DB::table('diya_sessions')->where('status', 'failed')->update(['status' => 'cancelled']);
    }

    public function down(): void
    {
        Schema::table('diya_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('diya_sessions', 'live_session_token')) {
                $table->dropUnique(['live_session_token']);
                $table->dropColumn('live_session_token');
            }
        });

        foreach (['hawan_sessions', 'pooja_sessions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach ([
                    'live_session_token',
                    'slot_end_time',
                    'slot_start_time',
                    'pandit_service_id',
                    'ritual_slug',
                    'ritual_id',
                    'service_type',
                ] as $column) {
                    if (!Schema::hasColumn($tableName, $column)) {
                        continue;
                    }

                    if ($column === 'pandit_service_id') {
                        $table->dropConstrainedForeignId($column);
                    } elseif ($column === 'live_session_token') {
                        $table->dropUnique([$column]);
                        $table->dropColumn($column);
                    } else {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
