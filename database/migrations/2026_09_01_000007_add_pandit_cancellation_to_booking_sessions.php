<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['hawan_sessions', 'pooja_sessions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'pandit_cancel_reason')) {
                    $table->text('pandit_cancel_reason')->nullable()->after('admin_note');
                }

                if (!Schema::hasColumn($tableName, 'pandit_cancelled_at')) {
                    $table->timestamp('pandit_cancelled_at')->nullable()->after('pandit_cancel_reason');
                }

                if (!Schema::hasColumn($tableName, 'cancelled_by_pandit_id')) {
                    $table->foreignId('cancelled_by_pandit_id')
                        ->nullable()
                        ->after('pandit_cancelled_at')
                        ->constrained('pandits')
                        ->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['hawan_sessions', 'pooja_sessions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'cancelled_by_pandit_id')) {
                    $table->dropConstrainedForeignId('cancelled_by_pandit_id');
                }

                foreach (['pandit_cancelled_at', 'pandit_cancel_reason'] as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
