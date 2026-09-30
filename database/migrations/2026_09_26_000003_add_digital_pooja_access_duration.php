<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poojas', function (Blueprint $table) {
            if (! Schema::hasColumn('poojas', 'digital_pooja_access_minutes')) {
                $table->unsignedInteger('digital_pooja_access_minutes')
                    ->default(120)
                    ->after('digital_pooja_audio_id');
            }
        });

        Schema::table('pooja_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('pooja_sessions', 'digital_access_minutes')) {
                $table->unsignedInteger('digital_access_minutes')
                    ->nullable()
                    ->after('digital_audio_path');
            }

            if (! Schema::hasColumn('pooja_sessions', 'start_at')) {
                $table->dateTime('start_at')->nullable()->after('booking_date');
            }

            if (! Schema::hasColumn('pooja_sessions', 'expires_at')) {
                $table->dateTime('expires_at')->nullable()->after('completed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pooja_sessions', function (Blueprint $table) {
            foreach (['expires_at', 'start_at', 'digital_access_minutes'] as $column) {
                if (Schema::hasColumn('pooja_sessions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('poojas', function (Blueprint $table) {
            if (Schema::hasColumn('poojas', 'digital_pooja_access_minutes')) {
                $table->dropColumn('digital_pooja_access_minutes');
            }
        });
    }
};
