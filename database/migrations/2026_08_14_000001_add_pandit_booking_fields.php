<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hawan_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('hawan_sessions', 'pandit_id')) {
                $table->foreignId('pandit_id')->nullable()->after('sankalp_form_id')->constrained('pandits')->nullOnDelete();
            }
        });

        Schema::table('pandit_availability_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('pandit_availability_settings', 'offline_service_available')) {
                $table->boolean('offline_service_available')->default(false)->after('advance_booking_days');
            }

            if (!Schema::hasColumn('pandit_availability_settings', 'service_city')) {
                $table->string('service_city')->nullable()->after('offline_service_available');
            }

            if (!Schema::hasColumn('pandit_availability_settings', 'travel_radius_km')) {
                $table->unsignedInteger('travel_radius_km')->nullable()->after('service_city');
            }

            if (!Schema::hasColumn('pandit_availability_settings', 'other_service_cities')) {
                $table->json('other_service_cities')->nullable()->after('travel_radius_km');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hawan_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('hawan_sessions', 'pandit_id')) {
                $table->dropConstrainedForeignId('pandit_id');
            }
        });

        Schema::table('pandit_availability_settings', function (Blueprint $table) {
            foreach (['offline_service_available', 'service_city', 'travel_radius_km', 'other_service_cities'] as $column) {
                if (Schema::hasColumn('pandit_availability_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
