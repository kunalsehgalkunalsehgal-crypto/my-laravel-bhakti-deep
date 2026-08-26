<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pandit_availability_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('pandit_availability_settings', 'day_statuses')) {
                $table->json('day_statuses')->nullable()->after('advance_booking_days');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pandit_availability_settings', function (Blueprint $table) {
            if (Schema::hasColumn('pandit_availability_settings', 'day_statuses')) {
                $table->dropColumn('day_statuses');
            }
        });
    }
};
