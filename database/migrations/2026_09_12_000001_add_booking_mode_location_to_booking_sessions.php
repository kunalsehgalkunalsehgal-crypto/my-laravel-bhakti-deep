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
                if (!Schema::hasColumn($tableName, 'booking_mode')) {
                    $table->string('booking_mode')->default('online')->after('service_type')->index();
                }

                if (!Schema::hasColumn($tableName, 'state')) {
                    $table->string('state')->nullable()->after('booking_mode');
                }

                if (!Schema::hasColumn($tableName, 'city')) {
                    $table->string('city')->nullable()->after('state');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['hawan_sessions', 'pooja_sessions'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach (['city', 'state', 'booking_mode'] as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
