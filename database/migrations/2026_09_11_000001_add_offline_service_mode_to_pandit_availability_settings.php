<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pandit_availability_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('pandit_availability_settings', 'offline_hawan')) {
                $table->boolean('offline_hawan')->default(false)->after('offline_service_available');
            }

            if (!Schema::hasColumn('pandit_availability_settings', 'offline_pooja')) {
                $table->boolean('offline_pooja')->default(false)->after('offline_hawan');
            }

            if (!Schema::hasColumn('pandit_availability_settings', 'service_state')) {
                $table->string('service_state')->nullable()->after('offline_pooja');
            }
        });

        DB::table('pandit_availability_settings')
            ->where('offline_service_available', true)
            ->update(['offline_hawan' => true, 'offline_pooja' => true]);
    }

    public function down(): void
    {
        Schema::table('pandit_availability_settings', function (Blueprint $table) {
            foreach (['offline_hawan', 'offline_pooja', 'service_state'] as $column) {
                if (Schema::hasColumn('pandit_availability_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
